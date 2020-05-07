<?php

namespace Matrix\Models;

class Ucenter extends BaseModel
{
    protected $fillable = ['enterprise_userid', 'user_id'];
    //
    public function getUcByEnterpriseUserId(string $enterpriseUserId)
    {
        $uc = self::where('enterprise_userid', $enterpriseUserId)->first();

        return empty($uc) ? [] : $uc->toArray();
    }

    public function bindEnterpriseUserId(int $userId, string $enterpriseUserId)
    {
        try {
            $enterpriseBind = self::create([
                'user_id' => $userId,
                'enterprise_userid' => $enterpriseUserId,
            ]);
        } catch(Exception $e) {
            $enterpriseBind = NULL;
        }

        return empty($enterpriseBind) ? [] : $enterpriseBind->toArray();
    }

    public function getUcByUserId(int $userId)
    {
        $uc = self::where('user_id', $userId)->first();

        return empty($uc) ? [] : $uc->toArray();
    }

    public function updateEnterpriseUserId(int $userId, string $enterpriseUserId)
    {
        $uc = self::where('user_id', $userId)->first();
        if (empty($uc)) {
            $uc = self::create([
                'user_id' => $userId,
                'enterprise_userid' => $enterpriseUserId,
            ]);
        } else {
            $uc->enterprise_userid = $enterpriseUserId;
            $uc->save();
        }
        return $uc->toArray();
    }

    public function getUcListByUserIdList(array $userIdList)
    {
        $ucList = self::whereIn('user_id', $userIdList)->get();

        return empty($ucList) ? [] : $ucList->toArray();
    }

    public function getUcListByEnterpriseUserIdList(array $enterpriseUserIdList)
    {
        $ucList = self::whereIn('enterprise_userid', $enterpriseUserIdList)->get();

        return empty($ucList) ? [] : $ucList->toArray();
    }
}
