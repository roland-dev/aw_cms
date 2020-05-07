<?php

namespace Matrix\Models;

use DB;

class TwitterGuard extends BaseModel
{
    //
    const STATUS_REQUEST = 0;
    const STATUS_APPROVE = 1;
    const STATUS_REJECT  = 2;

    const SOURCE_CUSTOMER = 'customer';
    const SOURCE_MANAGE_SYSTEM = 'manage_system';
    const SOURCE_AUTO_PROGRAM = 'auto_program';
    
    const REVIEW_APPROVE = 0;
    const REVIEW_REJECT = 1;


    const UN_QUALIFIED = 0;
    const IS_QUALIFIED = 1;

    protected $fillable = ['category_code', 'open_id', 'operator_user_id', 'status', 'source_type', 'review_status', 'is_qualified'];

    public function process(int $twitterGuardId, int $operate, int $operatorUserId, int $isQualified)
    {
        $twitterGuard = self::findOrFail($twitterGuardId);
        $twitterGuard->status = $operate;
        $twitterGuard->operator_user_id = $operatorUserId;
        $twitterGuard->is_qualified = $isQualified;
        $twitterGuard->save();

        return $twitterGuard->toArray();
    }

    public function getLastTwitterGuard(string $openId, string $categoryCode)
    {
        $twitterGuard = self::where('open_id', $openId)->where('category_code', $categoryCode)->orderBy('created_at', 'desc')->take(1)->firstOrFail();

        return $twitterGuard->toArray();
    }

    public function getApprovedList(string $openId)
    {
        $approvedList = [];
        $categoryList = [];
        $twitterGuardList = self::where('open_id', $openId)->orderBy('created_at', 'desc')->orderBy('updated_at', 'desc')->get()->toArray();
   
        foreach ($twitterGuardList as $twitterGuard) {
            if (array_key_exists($twitterGuard['category_code'], $categoryList)) {
                continue;
            }
            $categoryList[$twitterGuard['category_code']] = $twitterGuard;
            if (TwitterGuard::STATUS_APPROVE == $twitterGuard['status'] || (TwitterGuard::STATUS_REQUEST == $twitterGuard['status'] && TwitterGuard::SOURCE_AUTO_PROGRAM == $twitterGuard['source_type'])) {
                $approvedList[] = $twitterGuard;
            }
        }
        return empty($approvedList) ? [] : $approvedList;
    }

    public function getTwitterGuardList(string $openId) {
        $twitterGuardList = [];

        $twitterGuardList = self::where('open_id', $openId)->orderBy('created_at', 'desc')->orderBy('updated_at', 'desc')->get()->toArray();

        return empty($twitterGuardList) ? [] : $twitterGuardList;
    }

    public function getRequestList(array $cond)
    {
        // $model = self::whereIn('status', $cond['status']);

        if (array_key_exists('source_type', $cond)) {
            $model = self::whereIn('status', $cond['status']);
            $model = $model->where('source_type', $cond['source_type']);
        } else if (count($cond['status']) == 1 && TwitterGuard::STATUS_REJECT == $cond['status'][0]) {
            $model = self::where(function ($query) use ($cond) {
                $query->where([
                        ['status', '=', $cond['status'][0]],
                        ['source_type', '=', TwitterGuard::SOURCE_AUTO_PROGRAM],
                        ['is_qualified', '=', TwitterGuard::IS_QUALIFIED]
                    ])
                    ->orWhere([
                        ['status', '=', $cond['status'][0]],
                        ['source_type', '<>', TwitterGuard::SOURCE_AUTO_PROGRAM]
                    ]);
            });
        } else {
            $model = self::whereIn('status', $cond['status']);
        }

        if (array_key_exists('is_qualified', $cond)) {
            $model = $model->where('is_qualified', $cond['is_qualified']);
        }

        if (array_key_exists('open_id', $cond)) {
            $model = $model->whereIn('open_id', $cond['open_id']);
        }

        $requestList = $model->orderBy('created_at', 'desc')->get();

        return empty($requestList) ? [] : $requestList->toArray();
    }

    public function getTwitterFollowCountList(array $categoryCodeList)
    {
        $followCountList = [];
        foreach ($categoryCodeList as $categoryCode) {
            $followCount = 0;
            $categoryList = [];
            $twitterGuardList = self::where('category_code', $categoryCode)->orderBy('created_at', 'desc')->orderBy('updated_at', 'desc')->get()->toArray();
    
            foreach ($twitterGuardList as $twitterGuard) {
                if (array_key_exists($twitterGuard['open_id'], $categoryList)) {
                    continue;
                }
                $categoryList[$twitterGuard['open_id']] = $twitterGuard;
                if (TwitterGuard::STATUS_APPROVE == $twitterGuard['status'] || (TwitterGuard::STATUS_REQUEST == $twitterGuard['status'] && TwitterGuard::SOURCE_AUTO_PROGRAM == $twitterGuard['source_type'])) {
                    $followCount ++;
                }
            }
            $followCountList[] = [
                'category_code' => $categoryCode,
                'follow_cnt' => $followCount,
            ];
        }

        return empty($followCountList) ? [] : $followCountList;
    }

    public function getTwitterFollowCount(string $categoryCode)
    {
        $followCount = 0;
        $categoryList = [];
        $twitterGuardList = self::where('category_code', $categoryCode)->orderBy('created_at', 'desc')->orderBy('updated_at', 'desc')->get()->toArray();
   
        foreach ($twitterGuardList as $twitterGuard) {
            if (array_key_exists($twitterGuard['open_id'], $categoryList)) {
                continue;
            }
            $categoryList[$twitterGuard['open_id']] = $twitterGuard;
            if (TwitterGuard::STATUS_APPROVE == $twitterGuard['status'] || (TwitterGuard::STATUS_REQUEST == $twitterGuard['status'] && TwitterGuard::SOURCE_AUTO_PROGRAM == $twitterGuard['source_type'])) {
                $followCount ++;
            }
        }

        return $followCount;
    }
}
