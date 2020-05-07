<?php

namespace Matrix\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Matrix\Contracts\UcManager;
use Exception;

class CheckCustomerMoney extends Command
{
    const STATUS_REQUEST = 0;
    const STATUS_APPROVE = 1;
    const STATUS_REJECT  = 2;

    const OPERATOR_USER_ID = 0;

    const SOURCE_AUTO_PROGRAM = 'auto_program';

    const REVIEW_STATUS_APPROVE = 0;

    const UN_QUALIFIED = 0;

    private $ucenter;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:checkCustomerMoney {standard=10000} {batchNum=100}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(UcManager $ucenter)
    {
        $this->description .= "php artisan command:checkCustomerMoney 10000 100";
        $this->description .= "php artisan command:checkCustomerMoney standard=10000 batchNum=100";
        parent::__construct();
        $this->ucenter = $ucenter;
    }

    private function createTwitterGuard(string $categoryCode, string $openId, $reviewStatus = NULL)
    {
        try {
            $twitterGuardObj = DB::table('twitter_guards')->insert(
                [
                    'category_code' => $categoryCode,
                    'open_id' => $openId,
                    'operator_user_id' => self::OPERATOR_USER_ID,
                    'status' => self::STATUS_REQUEST,
                    'source_type' => self::SOURCE_AUTO_PROGRAM,
                    'review_status' => $reviewStatus,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        } catch (Exception $e) {
            $twitterGuardObj = NULL;
        }

        return empty($twitterGuardObj) ? [] : $twitterGuardObj;
    }

    private function updateExecuteAt(array $openIds)
    {
        $nowMonthDate = date('Y-m-01');
        DB::table('twitter_guards')
            ->whereIn('open_id', $openIds)
            ->update(['execute_at' => $nowMonthDate]);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        set_time_limit(86400);

        try {
            $standard = $this->argument('standard');
            $batchNum = $this->argument('batchNum');

            $nowMonthDate = date('Y-m-01');
            // 获取需要 调用Uc查询的open_id数据
            $twitterGuards = DB::table('twitter_guards')
                        ->select('open_id')
                        ->where('updated_at', '<', $nowMonthDate)
                        ->where(function ($query) use($nowMonthDate) {
                            $query->where('execute_at', '<', $nowMonthDate)
                                ->orWhereNull('execute_at');
                        })
                        ->groupBy('open_id')
                        ->take($batchNum)
                        ->get()
                        ->toArray();
            $openIds = array_column($twitterGuards, 'open_id');

            if (empty($openIds)) {
                $this->line('程序执行成功, 数据已经处理完！');
                return ;
            }

            $ucInfoData = $this->ucenter->getUserMoneys($openIds, 'hk');

            if (array_get($ucInfoData, 'code') != SYS_STATUS_OK) {
                $this->line('程序执行失败，调用uc接口失败');
                return ;
            }

            $moneysData = array_get($ucInfoData, 'data');
            
            $CustomerList = [];
            foreach($moneysData as $moneyData) {
                $openId = array_get($moneyData, 'openId');

                $twitterGuardList = [];
                $twitterGuardsData = DB::table('twitter_guards')
                                    ->where('open_id', '=', $openId)
                                    ->orderBy('created_at', 'desc')
                                    ->orderBy('updated_at', 'desc')
                                    ->get();
                
                foreach ($twitterGuardsData as $twitterGuardData) {
                    if (array_key_exists($twitterGuardData->category_code, $twitterGuardList)) {
                        continue;
                    }
                    $twitterGuardList[$twitterGuardData->category_code] = $twitterGuardData->status;
                    if (self::STATUS_APPROVE == $twitterGuardData->status && $twitterGuardData->updated_at < $nowMonthDate) {
                        if ( !array_key_exists($openId, $CustomerList) ) {
                            $totalRemitHKD =  (double)array_get($moneyData, 'totalRemitHKD');
                            $totalWithdrawHKD = (double)array_get($moneyData, 'totalWithdrawHKD');
                            $netProceeds = $totalRemitHKD - $totalWithdrawHKD;
                            $CustomerList[$openId] = $netProceeds;
                        }
                        $categoryCode = $twitterGuardData->category_code;
                        $netProceeds = $CustomerList[$openId];
                        if ($netProceeds < $standard) {
                            if (self::UN_QUALIFIED == $twitterGuardData->is_qualified) {
                                $twitterGuard = self::createTwitterGuard($categoryCode, $openId, self::REVIEW_STATUS_APPROVE);
                            } else {
                                $twitterGuard = self::createTwitterGuard($categoryCode, $openId);
                            }
                            if (empty($twitterGuard)) {
                                $this->error('自动程序执行失败');
                                return ;
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $this->error('自动程序执行失败');
            return ;
        }
        

        self::updateExecuteAt($openIds);
        $this->line('自动程序执行成功');
    }
}
