<?php

namespace Matrix\Models;

use DB;

class Feed extends BaseModel
{
    //
    const FEED_TYPE_STOCK_REPORT = 1; // 个股报告
    const FEED_TYPR_KIT_REPORT = 13; // 达人锦囊报告
    const FEED_TYPE_LIST = [
        [
            'id' => 0,
            'name' => '调仓',
        ],
        [
            'id' => 1,
            'name' => '研报'
        ],
        [
            'id' => 2,
            'name' => '大盘分析'
        ],
        [
            'id' => 3,
            'name' => '学战法',
        ],
        [
            'id' => 4,
            'name' => '周战报',
        ],
        [
            'id' => 5,
            'name' => '量化金股池',
        ],
        [
            'id' => 6,
            'name' => '产业金股池',
        ],
        [
            'id' => 7,
            'name' => '智能仓位',
        ],
        [
            'id' => 8,
            'name' => '热点轮动',
        ],
        [
            'id' => 9,
            'name' => '模拟交易',
        ],
        [
            'id' => 10,
            'name' => '实盘交易'
        ],
        [
            'id' => 11,
            'name' => '动态（香江论剑）',
        ],
        [
            'id' => 12,
            'name' => '文章',
        ],
        [
            'id' => 13,
            'name' => '锦囊报告'
        ],
        [
            'id' => 99,
            'name' => '客服盈盈',
        ]
    ];

    const QYWX_STATUS = [
        [
            'id' => 0,
            'name' => '不推送'
        ],
        [
            'id' => 1,
            'name' => '未推送'
        ],
        [
            'id' => 2,
            'name' => '推送失败'
        ],
        [
            'id' =>3,
            'name' => '推送成功'
        ]
    ];

    const PUSH_STATUS = [
        [
            'id' => 0,
            'name' => '未推送'
        ],
        [
            'id' => 1,
            'name' => 'wx推送失败'
        ],
        [
            'id' => 2,
            'name' => 'wx推送成功'
        ],
        [
            'id' => 3,
            'name' => 'push失败'
        ],
        [
            'id' => 4,
            'name' => 'push成功'
        ],
        [
            'id' => 5,
            'name' => '不推送'
        ]
    ];
    CONST SYNC_TO_DYNAMIC_AD_TYPE = 'feed';
    CONST DYNAMIC_AD_TERMINAL_TYPES = ['pc'];
    

    protected $primaryKey = 'feed_id';
    protected $connection = 'mysql_no_prefix';
    protected $table = 'feed';
    public $timestamps = false;
    protected $fillable = ['feed_owner', 'feed_type', 'category_key', 'msg_type', 'owner_id', 'source_id', 'source_url', 'title', 'summary', 'thumb_cdn_url', 'origin_image_url', 'access_level', 'refer', 'push_status', 'push_time', 'qywx_status', 'add_time', 'is_elite', 'creator'];
    protected $casts = [ 'refer' => 'array' ];

    public function syncInFeed(array $feedList)
    {
        foreach ($feedList as $feed) {
            self::create($feed);
        }
    }

    public function getFeedListPageination(int $pageNo, int $pageSize, array $pushTimes, array $cond = [], string $title = '')
    {
        $model = self::whereBetween('add_time', $pushTimes);
        if (!empty($cond)) {
            $model = $model->where($cond);
        }
        if (!empty($title)) {
            $model = $model->where('title', 'like', "%$title%");
        }

        $model = $model->orderBy('feed_id', 'desc')->skip(($pageNo - 1) * $pageSize)
            ->take($pageSize);

        $feedList = $model->get();

        return empty($feedList) ? [] : $feedList->toArray();
    }

    public function getFeedListCount($pushTimes, $cond, $title)
    {
        $model = self::whereBetween('add_time', $pushTimes);
        if (!empty($cond)) {
            $model = $model->where($cond);
        }
        if (!empty($title)) {
            $model = $model->where('title', 'like', "%$title%");
        }
        $feedListCount = $model->count();

        return $feedListCount;
    }

    public function getFeedInfo(int $feedId)
    {
        $feedInfo = self::where('feed_id', $feedId)->first();
        return empty($feedInfo) ? [] : $feedInfo->toArray();
    }

    public function getFeedInfoByCategoryAndSourceId(string $categoryKey, string $sourceId)
    {
        $feedInfo = self::where('category_key', $categoryKey)->where('source_id', $sourceId)->first();
        return empty($feedInfo) ? [] : $feedInfo->toArray();
    }

    public function removeRecord(int $feedId)
    {
        self::where('feed_id', $feedId)->delete();
    }

    public function getFeedTypeList()
    {
        return self::FEED_TYPE_LIST;
    }
}
