<?php

namespace Matrix\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Exception;

class MigrationOfStockReport extends Command
{
    const CATEGORY_IDS = [
        1,
        3
    ]; // 1 众赢解股 3 财务分析
    const ZHONGYINGJIEGU = 1;
    const CAIWUFENXI = 3;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:migrationOfStockReport {defaultCreator=fuxuena}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description\n';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->description .= "php artisan command:migrationOfStockReport fuxuena";
        $this->description .= "php artisan command:migrationOfStockReport defaultCreator=fuxuena";
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $defaultCreator = $this->argument('defaultCreator');
        //检查老数据合理性
        if (!self::checkCreator()) {
            $this->error("检查成功， 请进行数据支持后进行迁移");
            return ;
        }

        try {
            DB::beginTransaction();
            $defaultUserId = DB::table('ucenters')->where('enterprise_userId', $defaultCreator)->value('user_id');

            $ucenters = DB::table('ucenters')->select('user_id', DB::raw('lower(enterprise_userId) as enterprise_userId'))->pluck('user_id', 'enterprise_userId');
            
            $reports = DB::select('select *, lower(creator) as creator from report where category_id in (' . self::ZHONGYINGJIEGU.',' . self::CAIWUFENXI . ') and report_format in (0, 1)');
            
            $stockReports = [];
            foreach ($reports as $report) {
                $stockReport = [];
                $stockReport['report_id'] = $report->report_id;
                $stockReport['category_id'] = $report->category_id;
                $stockReport['stock_code'] = $report->stock_code;
                $stockReport['report_format'] = $report->report_format;
                $stockReport['report_title'] = $report->report_title;
                $stockReport['report_content'] = $report->report_content;
                $stockReport['report_url'] = $report->report_url;
                $stockReport['report_date'] = $report->report_date;
                $stockReport['creator'] = $report->creator ? $ucenters[$report->creator] : $defaultUserId;
                $stockReport['last_modify_user_id'] = $stockReport['creator'];
                $stockReport['report_summary'] = $report->report_summary;
                $stockReport['external_id'] = $report->external_id;
                $stockReport['publish'] = 1;
                $stockReport['created_at'] = $report->add_time;
                $stockReport['updated_at'] = empty($report->update_time) ? $report->add_time : $report->update_time;
                if ($report->is_delete === 1) {
                    $stockReport['deleted_at'] = date("Y-m-d H:i:s");
                } else {
                    $stockReport['deleted_at'] = null;
                }
                $stockReports[] = $stockReport;
            }

            DB::table('stock_reports')->insert($stockReports);

            $this->line("迁移成功");
            DB::commit();
            try {
                $stockCodeList = self::getStockCodeList();
                $this->line("迁移数据当中的股票代码列表");
                $this->line(json_encode($stockCodeList));
            } catch (Exception $e) {
                $this->error("查询股票代码列表失败，请手动查询");
                return ;
            }
        } catch(Exception $e) {
            DB::rollback();
            $this->error("迁移失败");
        }

    }

    /**
     * 检测老数据合理性
     * 
     * @return bool
     */
    public function checkCreator()
    {
        $result = false;

        try {
            $reportRet = DB::select('select LOWER(creator) as creator  from report where category_id in (' . self::ZHONGYINGJIEGU.',' . self::CAIWUFENXI . ') and report_format in (0, 1) and creator is not null group by creator');
            $rCreators = array_column($reportRet, 'creator');
            $ucenterRet = DB::select('select LOWER(enterprise_userid) as enterprise_userid from cms_ucenters');
            $qyUserIds = array_column($ucenterRet, 'enterprise_userid');

            $creatorOfUndefined = [];
            foreach ($rCreators as $creator) {
                if (!in_array($creator, $qyUserIds)) {
                    $creatorOfUndefined[] = [
                        'enterprise_userid' => $creator
                    ];
                }
            }
            if (count($creatorOfUndefined) > 0) {
                $this->line("检查成功，report表 creator 迁移缺失 支持数据");
                $headers = ['enterprise_userid'];
                $this->table($headers, $creatorOfUndefined);
            } else {
                $this->line("检查成功，开始迁移数据");
                $result = true;
            }

            $ids = DB::select('select id from report where creator is null');
            if (count($ids) > 0) {
                $this->line("report 表中无 creator, 迁移新表中为默认值");
                $headers = ['id'];
                $this->table($headers, $ids);
            }
        } catch (Exception $e) {
            $this->error("检查失败" . $e->getMessage());
        }

        return $result;
    }

    /**
     * 获取迁移成功的股票代码，进行手动执行sql添加后缀
     */
    public function getStockCodeList()
    {
        $stockCodeList = DB::select('select distinct stock_code as stock_code from cms_stock_reports');
        $stockCodeList = array_column($stockCodeList, 'stock_code');
        return $stockCodeList;
    }
}
