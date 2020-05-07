<?php
namespace Matrix\Services;

use Matrix\Contracts\FeedManager;
use Matrix\Models\Feed;
use Matrix\Models\TjWxSendLogDetail;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Matrix\Exceptions\VideoException;
use DB;
use Matrix\Contracts\BossManager;
use Matrix\Contracts\DynamicAdManager;
use Matrix\Contracts\LogManager;
use Matrix\Contracts\UserManager;
use Matrix\Models\CategoryGroup;
use Matrix\Models\DynamicAd;
use Exception;
use Log;

class FeedService extends BaseService implements FeedManager
{
    const FEED = 'feed';
    const TJWXSENDLOGDETAIL = 'tjwxsendlogdetail';
    const DELETE = 'delete';

    private $feed;
    private $tjWxSendLogDetail;
    private $dynamicAdManager;
    private $userManager;
    private $bossManager;
    private $categoryGroup;
    private $logManager;

    public function __construct(
        Feed $feed, 
        TjWxSendLogDetail $tjWxSendLogDetail, 
        DynamicAdManager $dynamicAdManager, 
        UserManager $userManager,
        BossManager $bossManager,
        CategoryGroup $categoryGroup,
        LogManager $logManager
    )
    {
        $this->feed = $feed;
        $this->tjWxSendLogDetail = $tjWxSendLogDetail;
        $this->dynamicAdManager = $dynamicAdManager;
        $this->userManager = $userManager;
        $this->bossManager = $bossManager;
        $this->categoryGroup = $categoryGroup;
        $this->logManager = $logManager;
    }

    public function syncInFeed(array $feedList)
    {
        $this->feed->syncInFeed($feedList);
    }

    public function getFeedListPageination(array $credentials)
    {
        $pageNo = array_get($credentials, 'page_no');
        $pageSize = array_get($credentials, 'page_size');
        $beginTime = array_get($credentials, 'begin_time');
        $endTime = array_get($credentials, 'end_time');
        $pushTimes = [
            empty($beginTime) ? '1970-01-01 00:00:00' : date('Y-m-d 00:00:00', strtotime($beginTime)),
            empty($endTime) ? '9999-12-31 23:59:59' : date('Y-m-d 23:59:59', strtotime($endTime)),
        ];

        $title = (string)array_get($credentials, 'title');
        $cond = [];
        foreach ($credentials as $k => $v) {
            switch ($k) {
                case 'category_code':
                    $cond['category_key'] = $v;
                    break;
                case 'owner_id':
                    $cond['owner_id'] = $v;
                    break;
                case 'elite':
                    $cond['is_elite'] = $v;
                    break;
                default:
                    continue;
            }
        }

        $feedList = $this->feed->getFeedListPageination($pageNo, $pageSize, $pushTimes, $cond, $title);

        return $feedList;
    }

    public function eliteFeedList(array $feedIdList, int $operate)
    {
        $categoryCodes = CategoryGroup::where('code', config('category_group.feed_sync_to_dynamic_ad'))->pluck('category_code')->toArray();

        foreach ($feedIdList as  $feedId) {
            $feed = $this->feed->where('feed_id', $feedId)->first()->toArray();
            if (in_array(array_get($feed, 'category_key'), $categoryCodes)) {
                $dynamicAd = self::syncToDynamicAd($feed, $operate);
            }
        }
        $this->feed->whereIn('feed_id', $feedIdList)->update(['is_elite' => $operate]);
        return empty($dynamicAd) ? [] : $dynamicAd;
    }

    private function syncToDynamicAd(array $feedData, int $operate)
    {
        if ($operate) {

            // 解盘类型数据没有 title 字段
            // 获取 twitter_group_a 栏目分组下栏目
            $categoryList = $this->categoryGroup->getCategoryGroupListByCode('twitter_group_a');
            $categoryCodeList = array_column($categoryList, 'category_code');
            if (in_array(array_get($feedData, 'category_key'), $categoryCodeList)) {
                $title = mb_strlen($feedData['summary']) > 30 ? sprintf('%s...', mb_substr($feedData['summary'], 0, 30)) : $feedData['summary'];
            } else {
                $title = array_get($feedData, 'title');
            }

            $startAt = date('Y-m-d H:i:s', time());
            $endAt = date('Y-m-d H:i:s', strtotime('+10 minute'));

            $serviceCode = (string)array_get($feedData, 'access_level');
            if (config('packagetype.basic_sevice') === $serviceCode) {
                $permissionCodes = [config('packagetype.basic_package')];
            } else {
                $packageListOfServiceCode = $this->bossManager->getPackagesOfServiceCode();
                $permissionCodes = array_get($packageListOfServiceCode, $serviceCode);
            }

            $credentials = [
                'title' => $title,
                'content_url' => (string)array_get($feedData, 'source_url'),
                'start_at' => $startAt,
                'end_at' => $endAt,
                'terminal_codes' => Feed::DYNAMIC_AD_TERMINAL_TYPES,
                'permission_codes' => $permissionCodes,
                'active' => DynamicAd::ACTIVE_DEFAULT,
                'sign' => DynamicAd::SIGN_DEFAULT,
                'source_type' => Feed::SYNC_TO_DYNAMIC_AD_TYPE,
                'source_id' => (int)array_get($feedData, 'feed_id')
            ];
            $dynamicAd = $this->dynamicAdManager->createDynamicAd($credentials);
        } else {
            $dynamicAd = DynamicAd::where('source_type', Feed::SYNC_TO_DYNAMIC_AD_TYPE)->where('source_id', array_get($feedData, 'feed_id'))->first();
            if (!empty($dynamicAd)) {
                $dynamicAdId = array_get($dynamicAd, 'id');
                $dynamicAd = $this->dynamicAdManager->deleteDynamicAd($dynamicAdId);
            }
        }

        return $dynamicAd;
    }

    public function bypassFeedList(array $feedIdList, int $operate, array $typeList = [11])
    {
        $feedList = $this->feed->whereIn('feed_type', $typeList)->whereIn('feed_id', $feedIdList)->get();
        $feedList->each(function ($item, $key) use ($operate) {
            if ($operate == 0) {
                $item->access_level = $item->access_backup;
                $item->access_backup = '';
            } else {
                $item->access_backup = $item->access_level;
                $item->access_level = 'basic';
            }
            $item->save();
        });
    }

    public function getFeedListCount(array $credentials)
    {
        $beginTime = array_get($credentials, 'begin_time');
        $endTime = array_get($credentials, 'end_time');
        $pushTimes = [
            empty($beginTime) ? '1970-01-01 00:00:00' : date('Y-m-d 00:00:00', strtotime($beginTime)),
            empty($endTime) ? '9999-12-31 23:59:59' : date('Y-m-d 23:59:59', strtotime($endTime)),
        ];
        $title = (string)array_get($credentials, 'title');
        $cond = [];
        foreach ($credentials as $k => $v) {
            switch ($k) {
                case 'category_code':
                    $cond['category_key'] = $v;
                    break;
                case 'owner_id':
                    $cond['owner_id'] = $v;
                    break;
                case 'elite':
                    $cond['is_elite'] = $v;
                    break;
                default:
                    continue;
            }
        }

        $feedListCount = $this->feed->getFeedListCount($pushTimes, $cond, $title);

        return $feedListCount;
    }

    public function getFeedInfo(int $feedId)
    {
        $feedInfo = $this->feed->getFeedInfo($feedId);
        return $feedInfo;
    }

    public function getFeedInfoByCategoryAndSourceId(string $categoryKey, string $sourceId)
    {
        $feedInfo = $this->feed->getFeedInfoByCategoryAndSourceId($categoryKey, $sourceId);
        return $feedInfo;
    }

    public function removeFeed(int $feedId)
    {
        try{
            $feedInfo = self::getFeedInfo($feedId);

            $this->logManager->createOperationLog(self::FEED, Auth::id(), json_encode($feedInfo), self::DELETE);

            $this->feed->removeRecord($feedId);

        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
        }
    }

    public function tjWxSendLogRemove(int $sourceId)
    {
        try{
            $tjWxSendLogDetail = self::getTjWxSendLogDetail($sourceId);

            $this->logManager->createOperationLog(self::TJWXSENDLOGDETAIL, Auth::id(), json_encode($tjWxSendLogDetail), self::DELETE);

            $this->tjWxSendLogDetail->removeRecord($sourceId);

            $ret = ['code' => SYS_STATUS_OK];
            return $ret;
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
        }
    }

    public function getTjWxSendLogDetail(int $detailId)
    {
        try {
            $tjWxSendLogDetail = TjWxSendLogDetail::findOrFail($detailId);
            return $tjWxSendLogDetail;
        } catch (ModelNotFoundException $e) {
            throw new VideoException("{$detailId}我没找着这个节目详情.", TALKSHOW_NOT_FOUND);
        }
    }

    public function getFeedTypeList()
    {
        $feedTypes = $this->feed->getFeedTypeList();
        return $feedTypes;
    }

    public function getFeedListOfDay(array $credentials)
    {
        $pageNo = array_get($credentials, 'page_no');
        $pageSize = array_get($credentials, 'page_size');
        $date = array_get($credentials, 'date');
        $pushTimes = [
            empty($date) ? date('Y-m-d 00:00:00') : date('Y-m-d 00:00:00', strtotime($date)),
            empty($date) ? date('Y-m-d 23:59:59') : date('Y-m-d 23:59:59', strtotime($date)),
        ];

        $cond = [];
        foreach ($credentials as $k => $v) {
            if (!in_array($k, ['page_no', 'page_size', 'date']) && $v !== "" && $v !== null) {
                $cond[] = [$k, $v];
            }
        }

        $fields = [
            'feed_id',
            'title',
            'feed_type',
            'push_status',
            'push_time',
            'qywx_status',
            'qywx_time',
            'add_time'
        ];
        $feedList = Feed::select($fields)->whereBetween('add_time', $pushTimes);
        if (!empty($cond)) {
            $feedList = $feedList->where($cond);
        }

        $feedList = $feedList->where(function ($query) {
            $query->where('push_status', '<>', 5)->orWhere('qywx_status', '<>', 0);
        })->orderBy('feed_id', 'desc')->skip(($pageNo - 1) * $pageSize)->take($pageSize)->get()->toArray();

        $feedTypeArr = array_column(Feed::FEED_TYPE_LIST, 'name', 'id');
        $pushStatusArr = array_column(Feed::PUSH_STATUS, 'name', 'id');
        $qywxStatusArr = array_column(Feed::QYWX_STATUS, 'name', 'id');
        

        foreach ($feedList as &$feed) {
            $feed['feed_type_text'] = $feedTypeArr[array_get($feed, 'feed_type')];
            $feed['push_status_text'] = $pushStatusArr[array_get($feed, 'push_status')];
            $feed['qywx_status_text'] = $qywxStatusArr[array_get($feed, 'qywx_status')];
        }
        
        return $feedList;
    }

    public function getFeedCntOfDay(array $credentials)
    {
        $date = array_get($credentials, 'date');
        if (empty($date)) {
            $pushTimes = [
                date('Y-m-d 00:00:00'),
                date('Y-m-d 23:59:59')
            ];
        } else {
            $pushTimes = [
                date('Y-m-d 00:00:00', strtotime($date)),
                date('Y-m-d 23:59:59', strtotime($date))
            ];
        }
        
        $cond = [];
        foreach ($credentials as $k => $v) {
            if (!in_array($k, ['page_no', 'page_size', 'date']) && !empty($v)) {
                $cond[] = [$k, $v];
            }
        }

        $feedCnt = Feed::whereBetween('add_time', $pushTimes);
        if (!empty($cond)) {
            $feedCnt = $feedCnt->where($cond);
        }

        $feedCnt = $feedCnt->where(function ($query) {
            $query->where('push_status', '<>', 5)->orWhere('qywx_status', '<>', 0);
        })->count();
        return $feedCnt;

    }
}
