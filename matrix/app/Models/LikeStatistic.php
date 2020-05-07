<?php

namespace Matrix\Models;

use Exception;
use Log;
use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class LikeStatistic extends BaseModel
{
    const STAFF_TYPE = 'ROLE_STAFF';
    const CUSTOMER_TYPE = 'ROLE_CUSTOMER';

    protected $fillable = ['article_id', 'type', 'like_sum', 'customer_like_sum', 'staff_like_sum', 'created_at', 'updated_at'];

    public function likeSum($articleId, $type, $userType, $isLike)
    {
        try {
            if (empty($articleId) || empty($type)) {
                throw new Exception('Like statistic error.');
            }
            $articleStatisticLike = self::where('article_id', $articleId)
                ->where('type', $type)
                ->take(1)->firstOrFail();

            $cond = [
                'article_id' => $articleId,
                'type' => $type,
            ];

            $updateCond = [
                'like_sum' => empty($isLike) ? DB::raw('like_sum - 1') : DB::raw('like_sum + 1'),
                'updated_at' => (string)date('Y-m-d H:i:s'),
            ];

            if ($userType == self::STAFF_TYPE) {
                $updateCond['staff_like_sum'] = empty($isLike) ? DB::raw('staff_like_sum - 1') : DB::raw('staff_like_sum + 1');
            } else {
                $updateCond['customer_like_sum'] = empty($isLike) ? DB::raw('customer_like_sum - 1') : DB::raw('customer_like_sum + 1');
            }

            self::where($cond)->update($updateCond);

            $articleStatisticLike = self::where(['article_id' => $articleId, 'type' => $type])->take(1)->first();

            return $articleStatisticLike->toArray();

        } catch (ModelNotFoundException $e) {
            $createCond = [
                'article_id' => $articleId,
                'type' => $type,
                'like_sum' => 1,
                'customer_like_sum' => (int)$userType != self::STAFF_TYPE,
                'staff_like_sum' => (int)$userType == self::STAFF_TYPE,
            ];

            $articleStatisticLike = self::create($createCond);
            return $articleStatisticLike->toArray();
        }
    }

    public function getLikeStatisticInfo($articleId, $type)
    {
        try{
            $statisticInfo = self::where('article_id', $articleId)->where('type', $type)->first();

            return empty($statisticInfo) ? [] : $statisticInfo->toArray();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getLikeStatisticList(array $articleIdList, string $type)
    {
        try {
            $statisticList = self::whereIn('article_id', $articleIdList)->where('type', $type)->get();

            return empty($statisticList) ? [] : $statisticList->toArray();
        } catch (Exception $e) {
            throw $e;
        }
    }
}
