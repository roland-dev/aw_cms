<?php

namespace Matrix\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

use Cache;
use Exception;

class TeacherTab extends BaseModel
{
    //
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    protected $fillable = ['code', 'name', 'teacher_user_id', 'sort'];

    public function getTabListByUserId(int $userId)
    {
        $tabList = self::select('code', 'name')
                     ->where('teacher_user_id', $userId)
                     ->orderBy('sort', 'desc')->get();

        return $tabList->toArray();
    }

    public function createTeacherTab(array $teacherTab)
    {
        try {
            $tab = Tab::where('code', array_get($teacherTab, 'code'))->where('active', 1)->first()->toArray();
            $tabData = array_merge([
                'sort' => array_get($tab, 'sort'),
                'name' => array_get($tab, 'name'),
            ], $teacherTab);

            $condition = [
                'code' => array_get($tabData, 'code'),
                'teacher_user_id' => array_get($tabData, 'teacher_user_id'),
            ];

            $teacherTabObj = self::onlyTrashed()->where($condition)->get()->toArray();
            if (empty($teacherTabObj)) {
                $teacherTabData = self::create($tabData);
            } else {
                $restoreNum = self::onlyTrashed()->where($condition)->restore();
                self::where($condition)->update($tabData);
                $teacherTabData = self::where($condition)->get();
            }
        } catch (Exception $e) {
            $teacherTabData = [];
        }

        return empty($teacherTabData) ? [] : $teacherTabData->toArray();
    }

    public function deleteTeacherTab(int $userId, array $teacherTab)
    {
        return self::where('teacher_user_id', $userId)->whereIn('code', $teacherTab)->delete();
    }
}
