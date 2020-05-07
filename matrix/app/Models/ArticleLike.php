<?php

namespace Matrix\Models;

use DB;
use Exception;
use Matrix\Exceptions\MatrixException;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ArticleLike extends BaseModel
{
    // article_id 为字符类型，所以在查询时，要强制使用stirng 才能使用索引
    const TYPE_ARTICLE = 'article';
    const TYPE_TWITTER = 'twitter';

    protected $fillable = ['article_id', 'type', 'open_id', 'udid', 'session_id', 'user_type', 'created_at', 'updated_at'];

    public function record($articleId, $type, $openId = '', $udid = '', $sessionId = '', $userType = '')
    {
        if (empty($openId) && empty($udid)) {
            throw new MatrixException('此时此刻, 没法点赞.', OPEN_API_PARAMS_ERROR);
        }

        $articleLike = self::where('article_id', (string)$articleId)->where('type', $type)->where('open_id', (string)$openId);

        if (empty($openId) && (!empty($udid))) {
            $articleLike->where('udid', $udid);
        }

        $articleLike = $articleLike->take(1)->first();

        if (!empty($articleLike)) {
            $effectRows = (int)$articleLike->delete();

            $ret = [
                'status' => 0,
                'effect_rows' => $effectRows,
            ];
        } else {
            $ret = ['status' => 1];
            try {
                $likeData = [
                            'article_id' => $articleId,
                            'open_id' => (string)$openId,
                            'udid' => $udid,
                            'type' => $type,
                            'session_id' => $sessionId,//added by Jzd
                            'user_type' => $userType,//added by Jzd
                        ];

                // unique key:`article_id`,`type`,`udid`,`open_id`
                $articleLike = self::updateOrCreate([
                                'article_id' => (string)$articleId,
                                'open_id' => (string)$openId,
                                'udid' => $udid,
                                'type' => $type,
                            ], $likeData );
                            
                $ret['effect_rows'] = 1;
            } catch (QueryException $e) {
                $ret['effect_rows'] = 0;
            }
        }

        return $ret;
    }

    public function getRecord($articleId, $type, $openId = '', $udid = '')
    {
        try {
            if (!empty($openId)) {
                $articleLike = self::where('article_id', (string)$articleId)
                    ->where('type', $type)
                    ->where('open_id', $openId)
                    ->take(1)->firstOrFail();
            } elseif (!empty($udid)) {
                $articleLike = self::where('article_id', (string)$articleId)
                    ->where('type', $type)
                    ->where('udid', $udid)
                    ->take(1)->firstOrFail();
            }

            return 1;
        } catch (ModelNotFoundException $e) {
            return 0;
        }
    }

    public function getRecordList($articleIdList, $type, $openId = '', $udid = '')
    {
        $articleIdList = array_map('strval',$articleIdList);
        if (!empty($openId)) {
            $articleLikeList = self::whereIn('article_id', $articleIdList)
                ->where('type', $type)
                ->where('open_id', $openId)
                ->get()->toArray();
        } elseif (!empty($udid)) {
            $articleLikeList = self::whereIn('article_id', $articleIdList)
                ->where('type', $type)
                ->where('udid', $udid)
                ->get()->toArray();
        }

        return $articleLikeList;
    }

    public function getArticleLikeCount($articleId, $type)
    {
        $articleLikeCount = self::where('article_id', (string)$articleId)->where('type', $type)->count();

        return $articleLikeCount;
    }

    public function getLikeCountListByArticleIdList($articleIdList, $type)
    {
        $articleIdList = array_map('strval',$articleIdList);
        $articleLikeCountList = 
            self::select('article_id', DB::raw('count(*) as cnt'))
            ->where('type', $type)
            ->whereIn('article_id', $articleIdList)
            ->groupBy('article_id')->get();
        return empty($articleLikeCountList) ? [] : $articleLikeCountList->toArray();
    }

    public function getMyArticleLikeList(string $openId, string $type)
    {
        $articleLikeList = self::where('open_id', $openId)
            ->where('type', $type)->get();
        return empty($articleLikeList) ? [] : $articleLikeList->toArray();
    }
}
