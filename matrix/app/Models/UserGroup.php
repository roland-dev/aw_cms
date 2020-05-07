<?php

namespace Matrix\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

use Exception;

class UserGroup extends BaseModel
{
    //
    const USER_GROUP_CODE_APPROVED_REPLY = 'teacher_approved_reply';
    const USER_GROUP_STOCK_A = 'teacher_stock_a';

    use SoftDeletes;
    protected $dates = ['deleted_at'];

    protected $fillable = ['id', 'code', 'name', 'user_id', 'sort'];

    public function getUserIdListByCode(string $code)
    {
        $userIdList = self::where('code', $code)->get()->pluck('user_id');

        return $userIdList->toArray();
    }

    public function getUserGroup(int $userId, string $code)
    {
        $userGroup = self::where('user_id', $userId)->where('code', $code)->first();

        return empty($userGroup) ? [] : $userGroup->toArray();
    } 

    public function getIdListByCode(string $code)
    {
        $idList = self::where('code', $code)->get()->pluck('id');

        return $idList->toArray();
    }

    public function getUserGroupList()
    {
        $userGroupList = self::select('code', 'name')->groupBy('code')->get();
        return empty($userGroupList) ? [] : $userGroupList->toArray();
    }

    public function getUserGroupByCode(string $code)
    {
        $userGroup = self::select('code', 'name')->where('code', $code)->groupBy('code')->first();
        return empty($userGroup) ? [] : $userGroup->toArray();
    }

    public function getUserListByCode(string $code)
    {
        $userIdList = self::where('code', $code)->get();
        return empty($userIdList) ? [] : $userIdList->toArray();
    }

    public function createUserGroup(array $userGroup)
    {
        try {
            $condition = [
                'code' => array_get($userGroup, 'code'),
                'user_id' => array_get($userGroup, 'user_id')
            ];
            $userGroupData = self::where($condition)->take(1)->first();
            if ( empty($userGroupData) ) {
                $userGroupObj = self::onlyTrashed()->where($condition)->get()->toArray();
                if (empty($userGroupObj)) {
                    $userGroupData = self::create($userGroup);
                } else {
                    $restoreNum = self::onlyTrashed()->where($condition)->restore();
                    self::where($condition)->update($userGroup);
                    $userGroupData = self::where($condition)->take(1)->firstOrFail();
                }
            } else {
                $params = ['name', 'sort'];

                foreach ($userGroup as $key => $value) {
                    if (in_array($key, $params)) {
                        $userGroupData->{$key} = (string)$value;
                    }
                }
                
                $userGroupData->save();
            }            
        } catch (Exception $e) {
            $userGroupData = [];
        }

        return empty($userGroupData) ? [] : $userGroupData->toArray();
    }

    public function updateUserGroup(int $id, array $userGroup)
    {
        try {
            $updateNum = self::where('id', $id)->update($userGroup);
            $userGroupData = self::where('id', $id)->first();
        } catch (Exception $e) {
            $userGroupData = [];
        }

        return empty($userGroupData) ? [] : $userGroupData->toArray();
    }

    public function deleteUserGroup(array $ids)
    {
        return self::whereIn('id', $ids)->delete();
    }

    public function removeUserGroup(string $code)
    {
        return self::where('code', $code)->delete();
    }
}
