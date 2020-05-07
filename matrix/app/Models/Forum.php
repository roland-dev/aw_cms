<?php

namespace Matrix\Models;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\SoftDeletes;

use Exception;
use Illuminate\Support\Facades\DB;

class Forum extends BaseModel
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'theme',
        'img_src',
        'url_key',
        'url_link',
        'forum_at',
        'visible_at',
        'duration',
        'teacher',
        'abstract',
        'updated_user_id',
        'creator_id'
    ];

    const FORUM_OPERATION_CODE = 'forum';
    const CONTENT_GUARD_FOREIGN_KEY = 'param1';
    const URI_FORUM_ACCESS = '/api/v2/propaganda/forum/{forumId}';

    protected $showImgFilePathMapping;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->showImgFilePathMapping = [
            '/files' => config('cdn.cdn_url'),
        ];
        $dbType = config('database.default');
    }

    private function prefixImgSrc($imgSrc)
    {
        $result = "";
        foreach ($this->showImgFilePathMapping as $key => $value) {
            if (substr($imgSrc, 0, strlen($key)) === $key) {
                $result = $value . substr($imgSrc, strlen($key));
            }
        }
        return $result;
    }

    public function createForum(array $forumData)
    {
        try {
            $forumObj = self::create($forumData);
        } catch (Exception $e) {
            $e->getMessage();
            $forumObj = NULL;
        }

        return empty($forumObj) ? [] : $forumObj->toArray();
    }

    public function updateForum(int $forumId, array $forumData)
    {
        try {
            $forumObj = self::where('id', $forumId)
                ->update($forumData);
        } catch (Exception $e) {
            $e->getMessage();
            return NULL;
        }

        return $forumObj;
    }

    public function detail(int $forumId)
    {
        $condition = [
            self::CONTENT_GUARD_FOREIGN_KEY => $forumId,
            'uri' => self::URI_FORUM_ACCESS
        ];

        try {
            $forumObj = Forum::select('id', 'theme', 'img_src', 'url_key', 'url_link', 'forum_at', 'visible_at', 'duration', 'teacher', 'abstract')
                ->findOrFail($forumId);
            $packages = DB::table('content_guards')
                ->where($condition)
                ->pluck('service_code')
                ->toArray();
        } catch (Exception $e) {
            $e->getMessage();
            return [];
        }

        $forumObj->img_url = self::prefixImgSrc($forumObj->img_src);
        $forumObj->permission_codes = $packages;

        return empty($forumObj) ? [] : $forumObj->toArray();
    }

    public function destoryForum(int $forumId)
    {
        $result = false;

        try {
            $result = self::where('id', $forumId)->delete();
        } catch (Exception $e) {
            $e->getMessage();
        }

        return $result;
    }

    public function searchForumList($theme, $firstTime, $lastTime)
    {
        $condition = [];

        if (!empty($theme)) {
            $condition[] = ['theme', 'like', '%'.$theme.'%'];
        }
        if (!empty($firstTime)) {
            $condition[] = ['visible_at', '>=', $firstTime];
        }
        if (!empty($lastTime)) {
            $condition[] = [DB::raw('date_add(forum_at, interval 2 day)'), '<=', $lastTime];
        }

        $forums = self::select('id', 'theme', 'forum_at', 'visible_at', 'teacher', 'updated_user_id', 'updated_at')
            ->where( $condition )
            ->orderBy('created_at', 'desc')
            ->get();

        if (sizeof($forums) > 0) {
            foreach ($forums as $i => $forum) {
                $forums[$i]['updated_at'] = date_format($forum->updated_at, 'Y-m-d H:i:s');
            }
        }
        return empty($forums) ? [] : $forums->toArray();
    }

    public function getForumsData(array $forumIdsOfPermission)
    {
        $resultData = [
            'code' => '',
            'data' => []
        ];

        $nowDate = date('Y-m-d H:i:s', time());

        try{
            $forums = self::select('theme', 'abstract', 'teacher', 'forum_at', 'visible_at', 'duration', 'url_key', 'url_link', 'img_src', 'creator_id', 'id', 'created_at')
                ->whereIn('id', $forumIdsOfPermission)
                ->where(DB::raw('date_add(forum_at, interval 2 day)'), '>', $nowDate)
                ->where('visible_at', '<', $nowDate)
                ->orderBy('forum_at', 'asc')
                ->get();

            foreach ($forums as &$forum) {
                $packages = DB::table('content_guards')
                    ->where(self::CONTENT_GUARD_FOREIGN_KEY, $forum->id)
                    ->where('uri', self::URI_FORUM_ACCESS)
                    ->pluck('service_code')
                    ->toArray();
                $forum->package_code = $packages;
                $ucenters = DB::table('ucenters')
                    ->where('user_id', $forum->creator_id)
                    ->first();
                $forum->creator = array_get($ucenters, 'enterprise_userid');
            }
       } catch (Exception $e) {
            $e->getMessage();
            $resultData['code'] = SYS_STATUS_ERROR_UNKNOW;
            $resultData['msg'] = "论坛列表获取失败，服务器错误";
            return $resultData;
        }
        $resultData['code'] = SYS_STATUS_OK;
        $resultData['data'] = self::forumDataMapping($forums);
        return $resultData;
    }

    public function getForumsDataByID($forumId)
    {
        $resultData = [
            'code' => '',
            'data' => []
        ];

        $nowDate = date('Y-m-d H:i:s', time());

        try{
            $forums = self::select('theme', 'abstract', 'teacher', 'forum_at', 'visible_at', 'duration', 'url_key', 'url_link', 'img_src', 'creator_id', 'id', 'created_at')
                ->where('id', $forumId)
                ->where(DB::raw('date_add(forum_at, interval 2 day)'), '>', $nowDate)
                ->where('visible_at', '<', $nowDate)
                ->get();
            
            foreach ($forums as &$forum) {
                $packages = DB::table('content_guards')
                    ->where(self::CONTENT_GUARD_FOREIGN_KEY, $forumId)
                    ->where('uri', self::URI_FORUM_ACCESS)
                    ->pluck('service_code')
                    ->toArray();
                $forum->package_code = $packages;
                $ucenters = DB::table('ucenters')
                    ->select('enterprise_userid')
                    ->where('user_id', $forum->creator_id)
                    ->first();
                $forum->creator = array_get($ucenters, 'enterprise_userid');
            }
       } catch (Exception $e) {
            $e->getMessage();
            $resultData['code'] = SYS_STATUS_ERROR_UNKNOW;
            $resultData['msg'] = "论坛列表获取失败，服务器错误";
            return $resultData;
        }
        $resultData['code'] = SYS_STATUS_OK;
        $resultData['data'] = self::forumDataMapping($forums);
        return $resultData;
    }

    private function forumDataMapping($forums)
    {
        if (sizeof($forums) > 0) {
            $i = 0;
            $data = [];
            foreach ($forums as $forum) {
                if (empty($forum->id)) {
                    continue;
                }
                $forumDate=strtotime($forum->forum_at);
                $now=time();
                $hours = floor(($forumDate-$now)/3600);
                $minutes = ceil(($forumDate-$now)/60);
                $data[$i] = [
                    config('param_mapping.forum.theme') => $forum->theme,
                    config('param_mapping.forum.abstract') => $forum->abstract,
                    config('param_mapping.forum.teacher') => $forum->teacher,
                    config('param_mapping.forum.forum_at') => $forum->forum_at,
                    config('param_mapping.forum.forum_at_short') => substr($forum->forum_at, 0,16),
                    config('param_mapping.forum.hours') => $hours,
                    config('param_mapping.forum.minutes') => $minutes,
                    config('param_mapping.forum.visible_at') => substr($forum->visible_at, 0, 10),
                    config('param_mapping.forum.duration') => $forum->duration,
                    config('param_mapping.forum.url_key') => $forum->url_key,
                    config('param_mapping.forum.url_link') => $forum->url_link,
                    config('param_mapping.forum.img_src') => self::prefixImgSrc($forum->img_src),
                    config('param_mapping.forum.creator') => $forum->creator,
                    config('param_mapping.forum.package_code') => $forum->package_code,
                    config('param_mapping.forum.id') => $forum->id,
                    config('param_mapping.forum.created_at') => substr($forum->created_at, 0,16)
                ];
                $i ++;
            }
            return $data;
        }
        return [];
    }
}
