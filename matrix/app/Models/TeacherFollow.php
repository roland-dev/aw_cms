<?php

namespace Matrix\Models;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use DB;

class TeacherFollow extends BaseModel
{
    //
    protected $fillable = ['user_id', 'open_id', 'business', 'active', 'sync_to_uc'];

    public function follow(int $userId, string $openId, string $business)
    {
        try {
            $teacherFollow = self::where('user_id', $userId)
                ->where('open_id', $openId)
                ->where('business', $business)
                ->take(1)->firstOrFail();
            if (empty($teacherFollow->active)) {
                $teacherFollow->active = 1;
                $teacherFollow->sync_to_uc = 0;
                $teacherFollow->save();
            }
        } catch (ModelNotFoundException $e) {
            $teacherFollow = self::create([
                'user_id' => $userId,
                'open_id' => $openId,
                'business' => $business,
                'active' => 1,
                'sync_to_uc' => 0,
            ]);
        }

        return $teacherFollow->toArray();
    }

    public function unfollow(int $userId, string $openId, string $business)
    {
        try {
            $teacherFollow = self::where('user_id', $userId)
                ->where('open_id', $openId)
                ->where('business', $business)
                ->take(1)->firstOrFail();
            if (!empty($teacherFollow->active)) {
                $teacherFollow->active = 0;
                $teacherFollow->sync_to_uc = 0;
                $teacherFollow->save();
            }
        } catch (ModelNotFoundException $e) {
        }
    }

    public function getFollowListByUserId(int $userId)
    {
        $followList = self::where('user_id', $userId)->get()->pluck('open_id');

        return $followList->toArray();
    }

    public function getFollowListByOpenId(string $openId, string $business)
    {
        $followList = self::where('open_id', $openId)
            ->where('business', $business)
            ->where('active', 1)
            ->get()->pluck('user_id');

        return $followList->toArray();
    }

    public function getFollow(int $userId, string $openId, string $business)
    {
        try {
            $teacherFollow = self::where('user_id', $userId)
                ->where('open_id', $openId)
                ->where('business', $business)
                ->where('active', 1)
                ->take(1)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return [];
        }

        return $teacherFollow->toArray();
    }

    public function getFollowCount(int $userId, string $business)
    {
        $followCount = self::where('user_id', $userId)
            ->where('business', $business)
            ->where('active', 1)->count();

        return $followCount;
    }

    public function getFollowCountList(string $business)
    {
        $followCountList = self::select('user_id', DB::raw('count(*) as cnt'))
            ->where('business', $business)
            ->where('active', 1)
            ->groupBy('user_id')->get();

        return $followCountList->toArray();
    }

    public function getUnsyncUcFollowList(string $business, int $size)
    {
        $followList = self::where('business', $business)
            ->where('sync_to_uc', 0)->take($size)->get();

        return $followList;
    }
}
