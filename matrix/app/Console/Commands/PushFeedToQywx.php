<?php

namespace Matrix\Console\Commands;

use Illuminate\Console\Command;
use Exception;
use Log;
use Matrix\Models\Feed;
use Matrix\Contracts\BossManager;
use Matrix\Exceptions\MatrixException;

class PushFeedToQywx extends Command
{
    // 允许发送的feed_type 1 研报
    const FeedTyps = [
        1
    ];
    const PUSH_SUCCESS_STATUS = 1000;

    protected $bossManager;
    protected $file_path;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:pushFeedToQywx';

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
    public function __construct(BossManager $bossManager)
    {
        $this->description .= "php artisan command:pushFeedToQywx";
        $this->bossManager = $bossManager;
        $this->file_path = storage_path('push_report.txt');
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            self::checkProgramOfRunning();
        } catch (Exception $e) {
            Log::error("file add lock error:" . $e->getMessage(), [$e]);
            $this->error($e->getMessage());
            return ;
        }

        $successNum = 0;
        try {
            // 获取需要推送到企业微信的 feed_list
            $feedList = self::getFeedListOfPush();

            foreach ($feedList as $feed) {
                $serviceCode = array_get($feed, 'access_level');
                $msgType = 'article';
                $msgData = [
                    "title" => array_get($feed, 'title'),
                    "description" => array_get($feed, 'summary'),
                    "url" => config("app.url") . array_get($feed, 'source_url')
                ];
                $res = $this->bossManager->pushQywx($serviceCode, $msgType, $msgData);
                if ((int)array_get($res, 'code') === self::PUSH_SUCCESS_STATUS) {
                    $feed->qywx_status = 3;
                    $successNum ++;
                } else {
                    $feed->qywx_status = 2;
                    $feed->qywx_error = array_get($res, 'msg');
                }
                $feed->qywx_time = (string)date('Y-m-d H:i:s');
                $feed->save();
            }
            $this->line('推送完成, 推送 ' . count($feedList) . " 条， 成功推送 " . $successNum . " 条");
        } catch (MatrixException $e) {
            Log::error("push feed to qywx error:" . $e->getMessage(), [$e]);
            $this->error($e->getMessage());
            return ;
        } catch (Exception $e) {
            Log::error("push feed to qywx error:" . $e->getMessage(), [$e]);
            $this->error($e->getMessage());
            return ;
        }

        try {
            self::closeProgram();
        } catch(Exception $e) {
            Log::error("file close error:" . $e->getMessage(), [$e]);
            $this->error($e->getMessage());
        }
    }

    /**
     * 获取需要推送数据
     */
    public function getFeedListOfPush()
    {
        $result = [];

        try {
            $result = Feed::where('qywx_status', 1)->whereIn('feed_type', self::FeedTyps)->get();
        } catch (Exception $e) {
            throw new Exception("获取push数据失败" . $e->getMessage(), SYS_STATUS_ERROR_UNKNOW);
        }

        return $result;
    }

    /**
     * 程序加锁
     */
    public function checkProgramOfRunning()
    {
        if (!file_exists($this->file_path)) {
            if (file_put_contents($this->file_path, (string)date('Y-m-d H:i:s'), LOCK_EX) === FALSE) {
                throw new Exception("运行加锁失败");
            }
        } else {
            throw new Exception("自动程序正在执行，或程序已发生错误");
        }
    }

    /**
     * 关闭程序
     */
    public function closeProgram()
    {
        if (file_exists($this->file_path)) {
            if (unlink($this->file_path) === FALSE) {
                throw new Exception("关闭文件失败", SYS_STATUS_ERROR_UNKNOW);
            }
        }
    }
}
