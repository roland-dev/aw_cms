<?php

namespace Matrix\Console\Commands;

use Matrix\Exceptions\MatrixException;
use Matrix\Contracts\BossManager;
use Matrix\Contracts\ArticleManager;
use Matrix\Contracts\UserManager;
use Illuminate\Console\Command;
use Matrix\Models\Feed;
use Exception;
use Log; 
class PushArticleFromFeedToQy extends Command
{
    // 允许发送的feed_type 12 文章
    const FeedTyps = [
        12
    ];
    const PUSH_SUCCESS_STATUS = 1000;

    protected $bossManager;
    protected $articleManager; 
    protected $userManager;
    protected $file_path = 'lock_file.txt';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:pushArticleFromFeedToQy';

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
    public function __construct(BossManager $bossManager, ArticleManager $articleManager, UserManager $userManager)
    {
        $this->description .= "php artisan command:pushArticleFromFeedToQy";
        $this->bossManager = $bossManager;
        $this->articleManager = $articleManager;
        $this->userManager = $userManager;
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
            $feedList = self::getArticleFeedListOfPush();

            //dd($feedList);
            foreach ($feedList as $feed) {
                $articleInfo = self::getArticleInfo(array_get($feed, 'source_id', 0));//获取文章信息
                $userInfo = self::getUserInfo(array_get($articleInfo, 'data.article.modify_user_id'));//获取用户信息
                $msgData = [
                    'feed_id' => array_get($feed, 'feed_id'),
                    'owner' => array_get($feed, 'feed_owner'),
                    'feed_type' => array_get($feed, 'feed_type'),
                    'type' => array_get($feed, 'msg_type'),
                    'owner_id' => array_get($feed, 'owner_id'),
                    'title' => array_get($feed, 'title'),
                    'description' => array_get($feed, 'summary'),
                    'thumb_cdn_url' => array_get($feed, 'thumb_cdn_url'),
                    'origin_image_url' => array_get($feed, 'origin_image_url'),
                    'content' => array_get($articleInfo, 'data.article.content', ''),//内容已经是经过处理之后的内容，boss端即便不处理也不会有问题
                    'category_code' => array_get($feed, 'category_key'),
                    'user_enterprise_id' => array_get($userInfo, 'ucInfo.enterprise_userid', ''),
                    'url' => config("app.url") . array_get($feed, 'source_url'),
                    'preview' => 0,
                    'is_wechat_push' => 1,
                    'is_app_push' => 0,
                ];

                $res = $this->bossManager->pushArticleToQywx($msgData);

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
    public function getArticleFeedListOfPush()
    {
        $result = [];

        try {
            //需要修改
            //$result = Feed::where('qywx_status', 1)->where('add_time', '>', '2019-08-11 16:59:53')->whereIn('feed_type', self::FeedTyps)->get();
            $result = Feed::where('qywx_status', 1)->whereIn('feed_type', self::FeedTyps)->get();
        } catch (Exception $e) {
            throw new Exception("获取push数据失败" . $e->getMessage(), SYS_STATUS_ERROR_UNKNOW);
        }

        return $result;
    }

    /**
    *获取文章信息
    *@param $sourceId int 文章id
    *@param array
    */
    public function getArticleInfo($sourceId)
    {
        if( 0 === $sourceId){
            throw new Exception("文章不存在, 文章id为0");
        }

        try{
            $articleInfo = $this->articleManager->getArticleInfo($sourceId, '', '');
        }catch(Exception $e){
            throw new Exception("获取文章信息失败" . $e->getMessage(), SYS_STATUS_ERROR_UNKNOW);
        }

        return $articleInfo;
    }

    /**
    *获取用户信息
    *@param $userId int 用户id
    *@param array
    */
    public function getUserInfo($userId)
    {
        if( 0 === $userId){
            throw new Exception(" 用户不存在, 用户id为0");
        }

        try{
            $userInfo = $this->userManager->getUserInfo($userId);
        }catch(Exception $e){
            throw new Exception("获取用户信息失败" . $e->getMessage(), SYS_STATUS_ERROR_UNKNOW);
        }

        return $userInfo;
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
