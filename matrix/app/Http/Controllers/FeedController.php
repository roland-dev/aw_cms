<?php

namespace Matrix\Http\Controllers;

use Illuminate\Http\Request;
use Matrix\Contracts\FeedManager;
use Matrix\Contracts\ArticleManager;
use Matrix\Contracts\TwitterManager;
use Matrix\Contracts\UserManager;
use Matrix\Contracts\CategoryManager;
use Matrix\Contracts\BossManager;
use Illuminate\Support\Facades\Auth;
use Exception;
use Log;
use Matrix\Contracts\OperateLogContract;
use Matrix\Exceptions\MatrixException;

class FeedController extends Controller
{
    //
    private $request;
    private $feedManager;
    private $articleManager;
    private $twitterManager;
    private $bossManager;
    private $userManager;
    private $operateLogContract;

    public function __construct (
        Request $request, 
        FeedManager $feedManager, 
        ArticleManager $articleManager, 
        TwitterManager $twitterManager, 
        BossManager $bossManager, 
        UserManager $userManager,
        OperateLogContract $operateLogContract
    )
    {
        $this->request = $request;
        $this->feedManager = $feedManager;
        $this->articleManager = $articleManager;
        $this->twitterManager = $twitterManager;
        $this->bossManager = $bossManager;
        $this->userManager = $userManager;
        $this->operateLogContract = $operateLogContract;
    }

    protected function fitDetailUrl(string $url)
    {
        if (strpos($url, 'http') === 0) { // http or https
            return $url;
        } elseif (strpos($url, '//') === 0) { // //www.zhongyingtougu.com/
            return "http:$url";
        } else {
            return sprintf('%s%s', config('app.h5_api_url'), $url);
        }
    }

    public function getFeedList (FeedManager $feedManager, CategoryManager $categoryManager, UserManager $userManager)
    {
        $credentials = $this->request->validate([
            'page_no' => 'required|integer',
            'page_size' => 'required|integer',
            'begin_time' => 'required|date_format:Y-m-d',
            'end_time' => 'required|date_format:Y-m-d',
            'title' => 'nullable',
            'category_code' => 'nullable',
            'owner_id' => 'nullable',
            'elite' => 'nullable|integer',
        ]);

        try {
            $feedList = $feedManager->getFeedListPageination($credentials);
            if (!empty($feedList)) {
                $categoryListData = $categoryManager->getCategoryList();
                $categoryList = array_get($categoryListData, 'data.category_list');
                $categoryList = array_column($categoryList, 'name', 'code');

                $qyUserIdList = array_column($feedList, 'owner_id');
                $ucList = $userManager->getUcListByEnterpriseUserIdList($qyUserIdList);
                $ucList = array_column($ucList, 'user_id', 'enterprise_userid');
                $userListData = $userManager->getUserListByUserIdList(array_values($ucList));
                $userList = array_get($userListData, 'data.user_list');
                $userList = array_column($userList, 'name', 'id');

                foreach ($feedList as &$feed) {
                    $feed['category_name'] = (string)array_get($categoryList, $feed['category_key']);
                    $feed['source_url'] = empty($feed['source_url']) ? '' : $this->fitDetailUrl($feed['source_url']);
                    $feed['feed_owner'] = empty(trim($feed['owner_id'])) ? '' : (string)array_get($userList, (string)array_get($ucList, (string)array_get($feed, 'owner_id')));
                    $feed['bypass'] = empty($feed['access_backup']) ? 0 : 1;
                }
                $feedListTotalCount = $feedManager->getFeedListCount($credentials);
            } else {
                $feedListTotalCount = 0;
            }

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'feed_list' => $feedList,
                    'feed_list_total_count' => $feedListTotalCount,
                ],
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        }

        return $ret;
    }

    public function getFeedListOfDate()
    {
        $credentials = $this->request->validate([
            'page_no' => 'nullable|integer',
            'page_size' => 'nullable|integer',
            'date' => 'nullable|string',
            'feed_type' => 'nullable',
        ]);

        try {
            $cond = [
                'page_no' => array_get($credentials, 'page_no', 1),
                'page_size' => array_get($credentials, 'page_size', 10),
                'date' => array_get($credentials, 'date', date('Y-m-d')),
                'feed_type' => (string)array_get($credentials, 'feed_type'),
            ];

            $feedList = $this->feedManager->getFeedListOfDay($cond);
            $feedCnt = $this->feedManager->getFeedCntOfDay($cond);
            
            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => 'success',
                'data' => [
                    'feed_list' => $feedList,
                    'feed_cnt' => $feedCnt,
                ],
            ];
        } catch (MatrixException $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage(),
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => $e->getMessage(),
            ];
        }

        return $ret;
    }

    public function getFeedTypeList()
    {
        try {
            $feedTypes = $this->feedManager->getFeedTypeList();
            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => 'success',
                'data' => [
                    'feed_type_list' => $feedTypes,
                ],
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '未知错误',
            ];
        }

        return $ret;
    }

    public function eliteFeed(FeedManager $feedManager, int $feedId)
    {
        $operate = $this->request->input('operate');
        if (!in_array($operate, [0, 1])) {
            abort(400);
        }

        try {
            $syncDynamicAd = $feedManager->eliteFeedList([$feedId], $operate);
            if (!empty($syncDynamicAd)) {
                if ($operate) {
                    $operateCode = 'create';
                } else {
                    $operateCode = 'delete';
                }
                $this->operateLogContract->record($operateCode, 'dynamic_ad', $syncDynamicAd->id, "用户 ".Auth::user()->name." 同步创建了一个跑马灯 {$syncDynamicAd}", $this->request->ip(), Auth::user()->id);
            }

            $ret = ['code' => SYS_STATUS_OK];
        } catch (MatrixException $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => $e->getCode(),
                'msg' => $e->gteMessage()
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        }

        return $ret;
    }

    public function deleteRecord(FeedManager $feedManager, int $feedId, int $delOriginal)
    {
        try{
            $feedInfo = $feedManager->getFeedInfo($feedId);
            $addTime = array_get($feedInfo, 'add_time');
            $datetimeAddTime = date_create($addTime);
            $datetimeNow = date_create("now");
            $interval = date_diff($datetimeAddTime, $datetimeNow);
            $intervalDay = $interval->format('%a');
            if((int)$intervalDay >= 1){
               $ret =  [
                   'code' => SYS_STATUS_FEED_DELETE,
                   'msg'  => "精选内容已发布超过24小时，不能删除",
               ];

               return $ret;
            }

            if(!empty($delOriginal)){
                $feedType = array_get($feedInfo, 'feed_type');
                $sourceId = array_get($feedInfo, 'source_id');
                $resp = $this->feedTypeRemove($feedType, $sourceId);
                if($resp['code'] != SYS_STATUS_OK){
                   $ret =  [
                       'code' => SYS_STATUS_FEED_DELETE,
                       'msg'  => array_get($resp, 'msg'),
                   ];
                   return $ret;
                }
            }


            $this->feedManager->removeFeed($feedId);

            return ['code' => SYS_STATUS_OK];
        }catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        }
    }

    public function feedTypeRemove($feedType, $sourceId)
    {
        try{
            switch ($feedType) {
                case 2:
                case 4:
                    $this->feedManager->tjWxSendLogRemove($sourceId);
                    $ret = ['code' => SYS_STATUS_OK];
                    break;
                case 11:
                    $userId = Auth::id();
                    $userInfo = $this->userManager->getUserInfo($userId);
                    $uname = array_get($userInfo, 'userInfo.name');
                    $twitterInfo = $this->twitterManager->getTwitterInfo($sourceId);
                    $kgsId = array_get($twitterInfo, 'source_id');

                    $resp = $this->bossManager->kgsMsgDelete($kgsId, $uname);

                    if( empty($resp) || $resp['code'] != 1000){
                        $ret = [
                            'code' => SYS_STATUS_FEED_DELETE,
                            'msg' => $resp['msg'],
                        ];
                        Log::info('看高手删除失败:'.json_encode($ret));
                        break;
                    }
                    $this->twitterManager->twitterRemove($sourceId);
                    $ret = ['code' => SYS_STATUS_OK];
                    break;
                case 12:
                    $this->articleManager->trashArticle($sourceId);
                    $ret = ['code' => SYS_STATUS_OK];
                    break;
                default:
                    $ret = ['code' => SYS_STATUS_OK];
                    break;
            }

            return $ret;

        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        }
    }

    public function bypassFeed(FeedManager $feedManager, int $feedId)
    {
        $operate = $this->request->input('operate');
        if (!in_array($operate, [0, 1])) {
            abort(400);
        }

        try {
            $feedManager->bypassFeedList([$feedId], $operate);
            $ret = ['code' => SYS_STATUS_OK];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        }

        return $ret;
    }

}
