<?php

namespace Matrix\Models;

use Exception;

class PrivateMessageGuard extends BaseModel
{
    //
    const STATUS_REQUEST = 0;
    const STATUS_APPROVE = 1;
    const STATUS_REJECT  = 2;

    const SOURCE_CUSTOMER = 'customer';
    const SOURCE_RE_REVIEW = 're_review';

    const REVIEW_APPROVE = 0;
    const REVIEW_REJECT = 1;

    protected $fillable = ['teacher_id', 'open_id', 'operator_user_id', 'status', 'source_type', 'review_status'];


    public function process(int $privateMessageGuardId, int $operate, int $operatorUserId)
    {
        $privateMessageGuard = self::findOrFail($privateMessageGuardId);
        $privateMessageGuard->status = $operate;
        $privateMessageGuard->operator_user_id = $operatorUserId;
        $privateMessageGuard->save();

        return $privateMessageGuard->toArray();
    }

    public function createPrivateMessageRequest(array $newPrivateMessageGuard)
    {
        try {
            $privateMessageGuardObj = self::create([
                'teacher_id' => array_get($newPrivateMessageGuard, 'teacher_id'),
                'open_id' => array_get($newPrivateMessageGuard, 'open_id'),
                'operator_user_id' => array_get($newPrivateMessageGuard, 'operator_user_id'),
                'status' => array_get($newPrivateMessageGuard, 'status'),
                'source_type' => array_get($newPrivateMessageGuard, 'source_type'),
            ]);
        } catch (Exception $e) {
            $privateMessageGuardObj = NULL;
        }

        return empty($privateMessageGuardObj) ? [] : $privateMessageGuardObj->toArray();
    }

    public function getRequestList(array $cond)
    {
        $model = self::whereIn('status', $cond['status']);
        if (array_key_exists('source_type', $cond)) {
            $model = $model->where('source_type', $cond['source_type']);
        } else if (count($cond['status']) == 1 && PrivateMessageGuard::STATUS_REJECT == $cond['status'][0]) {
            $model = $model->where('source_type', '<>', PrivateMessageGuard::SOURCE_RE_REVIEW);
        }
        if (array_key_exists('open_id', $cond)) {
            $model = $model->whereIn('open_id', $cond['open_id']);
        }

        $requestList = $model->orderBy('created_at', 'desc')->get();

        return empty($requestList) ? [] : $requestList->toArray();
    }

    public function getLastPrivateMessageGuard(int $teacherId, string $openId)
    {
        $pmGuard = self::where('teacher_id', $teacherId)->where('open_id', $openId)->orderBy('created_at', 'desc')->take(1)->firstOrFail();
        return $pmGuard->toArray();
    }
}
