<?php

namespace Matrix\Models;

use Exception;

class Teacher extends BaseModel
{

    const DISABLE = 0;
    const ENABLE = 1;

    protected $fillable = [
        'user_id', 'category_code', 'icon_url', 'visitor_video_url', 'customer_video_url', 'cover_url', 'description', 'active'
    ];

    //
    public function getTeacherListByCategoryCode(string $categoryCode)
    {
        $teacherList = self::where('category_code', $categoryCode)->where('active', 1)->orderBy('primary', 'desc')->get();

        return empty($teacherList) ? [] : $teacherList->toArray();
    }

    public function getAllTeacherListByCategoryCode(string $categoryCode)
    {
        $teacherList = self::where('category_code', $categoryCode)->orderBy('primary', 'desc')->get();

        return empty($teacherList) ? [] : $teacherList->toArray();
    }

    public function getTeacherInfo(int $teacherId)
    {
        $teacher = self::findOrFail($teacherId);
        return $teacher->toArray();
    }

    public function getTeacherInfoByUserIdAndCategoryCode(int $userId, string $categoryCode)
    {
        $teacher = self::where('user_id', $userId)
            ->where('category_code', $categoryCode)
            ->take(1)->firstOrFail();
        return $teacher->toArray();
    }

    public function getTeacherListByUserId(int $userId)
    {
        $teacherList = self::where('user_id', $userId)->get();

        return $teacherList->toArray();
    }

    public function getTeacherListByCategoryCodeList(array $categoryCodeList)
    {
        $teacherList = self::whereIn('category_code', $categoryCodeList)->orderBy('primary', 'desc')->get();

        return empty($teacherList) ? [] : $teacherList->toArray();
    }

    public function getTeacherListByIdList(array $teacherIdList)
    {
        $teacherList = self::whereIn('id', $teacherIdList)->get();

        return empty($teacherList) ? [] : $teacherList->toArray();
    }
    public function getTeacherListByUserIdList(array $teacherUserIdList)
    {
        $teacherList = self::whereIn('user_id', $teacherUserIdList)->where('active', 1)->get()->toArray();

        return $teacherList;

    }

    public function getPrimaryTeacher(string $categoryCode)
    {
        $teacherInfo = self::where('category_code', $categoryCode)->where('active', 1)->where('primary', 1)->first();
        return empty($teacherInfo) ? [] : $teacherInfo->toArray();
    }

    public function setPrimaryTeacher(string $categoryCode, int $parimaryTeacherId)
    {
        self::where('category_code', $categoryCode)->where('primary', 1)->update(['primary' => 0]);
        self::where('category_code', $categoryCode)->where('user_id', $parimaryTeacherId)->update([
            'primary' => 1,
            'active' => 1
            ]);
    }

    public function getTeacherListByCondition(array $condition = [])
    {
        if (empty($condition)) {
            $teacherList = self::orderBy('created_at', 'desc')->get();
        } else {
            $teacherList = self::where($condition)->orderBy('created_at', 'desc')->get();
        }

        return empty($teacherList) ? [] : $teacherList->toArray();
    }

    public function createTeacher(array $teacher)
    {
        try {
            $teacherObj = self::create([
                'user_id' => array_get($teacher, 'user_id'),
                'category_code' => array_get($teacher, 'category_code'),
                'icon_url' => empty(array_get($teacher, 'icon_url')) ? '' : array_get($teacher, 'icon_url'),
                'visitor_video_url' => array_get($teacher, 'visitor_video_url'),
                'customer_video_url' => array_get($teacher, 'customer_video_url'),
                'cover_url' => array_get($teacher, 'cover_url'),
                'description' => empty(array_get($teacher, 'description')) ? '' : array_get($teacher, 'description'),
                'primary' => 0,
                'active' => 1,
            ]);
        } catch (Exception $e) {
            $teacherObj = NULL;
        }

        return empty($teacherObj) ? [] : $teacherObj->toArray();
    }

    public function updateTeacher(int $teacherId, array $teacherInfo)
    {
        $teacher = self::find($teacherId);
        if ( empty($teacher) ) {
            return [];
        }

        foreach ($teacherInfo as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $teacher->{$key} = (string)$value;
            }
        }

        $teacher->save();

        return $teacher->toArray();
    }

    public function activeTeacher(int $teacherId, int $active)
    {
        $teacher = self::find($teacherId);
        if ( empty($teacher) ) {
            return [];
        }

        $teacher->active = $active;

        if ($active === self::DISABLE) {
            $teacher->primary = self::DISABLE;
        }

        $teacher->save();

        return $teacher->toArray();
    }
}
