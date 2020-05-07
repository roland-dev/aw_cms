<?php

namespace Matrix\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Exception;

class User extends Authenticatable
{
    use Notifiable;

    const DISABLE = 0;
    const ENABLE = 1;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'type', 'icon_url', 'active', 'selected', 'cert_no', 'description',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function getUserInfo($id)
    {
        $user = self::findOrFail($id);
        return $user->toArray();
    }

    public function getTeacherInfo()
    {
        $teacher = self::where('type', 'teacher')
            ->where('active', 1)->get();
        return empty($teacher) ? [] : $teacher->toArray();
    }

    public function getUserList()
    {
        $userList = self::where('active', 1)->get();
        return empty($userList) ? [] : $userList->toArray();
    }

    public function getAllUserList(array $condition = [])
    {
        if (empty($condition)) {
            $userList = self::orderBy('created_at', 'desc')->get();
        } else {
            $userList = self::where($condition)->orderBy('created_at', 'desc')->get();
        }

        return empty($userList) ? [] : $userList->toArray();
    } 

    public function createUser(array $user)
    {
        try {
            $userObj = self::create([
                'name' => array_get($user, 'name'),
                'email' => array_get($user, 'email'),
                'password' => (string)array_get($user, 'password'),
                'type' => array_get($user, 'type'),
                'icon_url' => (string)array_get($user, 'icon_url'),
                'active' => self::ENABLE,
                'selected' => self::DISABLE,
                'cert_no' => (string)array_get($user, 'cert_no'),
                'description' => (string)array_get($user, 'description'),
            ]);
        } catch (Exception $e) {
            $userObj = NULL;
        }

        return empty($userObj) ? [] : $userObj->toArray();
    }

    public function updateUser(int $userId, array $userInfo)
    {
        $user = self::find($userId);
        if (empty($user)) {
            return [];
        }

        foreach ($userInfo as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $user->{$key} = (string)$value;
            }
        }

        $user->save();

        return $user->toArray();
    }

    public function getUserListByUserIdList(array $userIdList)
    {
        $userList = self::whereIn('id', $userIdList)->get();

        return empty($userList) ? [] : $userList->toArray();
    }
}
