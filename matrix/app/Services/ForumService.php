<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/4
 * Time: 15:41
 */

namespace Matrix\Services;

use Illuminate\Support\Facades\DB;
use Matrix\Contracts\ForumManager;
use Matrix\Models\Ad;
use Matrix\Models\Forum;
use Matrix\Models\User;
use Matrix\Models\AdTerminal;

class ForumService extends BaseService implements ForumManager
{
    const FORUM_OPERATION_CODE = 'forum';

    private $forum;
    private $ad;
    private $adTerminal;
    private $user;

    public function __construct(Forum $forum, Ad $ad, AdTerminal $adTerminal, User $user)
    {
        $this->forum = $forum;
        $this->ad = $ad;
        $this->adTerminal = $adTerminal;
        $this->user = $user;
    }

    public function createForum(array $forumData)
    {
        $forum = $this->forum->createForum($forumData);
        if (empty($forum)) {
            return [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => "服务器错误"
            ];
        }

        return [
            'code' => SYS_STATUS_OK,
            'data' => $forum,
            'msg' => "添加成功"
        ];
    }

    public function updateForum(int $forumId, array $forumData)
    {
        $forum = $this->forum->updateForum($forumId, $forumData);
        if (empty($forum)) {
            return [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => "服务器错误"
            ];
        }
        
        return [
            'code' => SYS_STATUS_OK,
            'msg' => "更新成功"
        ];
    }

    public function detail(int $forumId)
    {
        $forum = $this->forum->detail($forumId);
        if (empty($forum)) {
            return [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => "查询失败，服务器错误"
            ];
        }

        return [
            'code' => SYS_STATUS_OK,
            'data' => $forum,
            'msg' => ''
        ] ;
    }

    /**
     * @param int $forumId
     * @return array
     */
    public function destoryForum(int $forumId)
    {
        $result = $this->forum->destoryForum($forumId);
        if ( !$result ) {
            return [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => "服务器错误"
            ];
        }

        return [
            'code' => SYS_STATUS_OK,
            'msg' => "删除成功"
        ];
    }

    public function getAdListDataOfForumId(int $forumId, bool $bool = false)
    {
        $adsOfForumsData = $this->ad->getAdListDataOfOperationId($forumId, self::FORUM_OPERATION_CODE, $bool);
        
        $adsOfForum = array_get($adsOfForumsData, 'data');

        foreach ($adsOfForum as &$ad) {
            $adTerminals = $this->adTerminal->getAdTerminals(array_get($ad, 'id'));
            $ad['terminal_codes'] = $adTerminals;
        }

        $adsOfForumsData['data'] = $adsOfForum;

        return $adsOfForumsData;
    }

    public function destoryAdOfOtherModules(int $forumId)
    {
        return $this->ad->destoryAdOfOtherModules($forumId, self::FORUM_OPERATION_CODE);
    }

    public function getForumsData(array $forumIdsOfPermission)
    {
        return $this->forum->getForumsData($forumIdsOfPermission);
    }

    public function getForumsDataById($forumId)
    {
        return $this->forum->getForumsDataById($forumId);
    }

    public function getTeachers()
    {
        $result = [];
        $result['code'] = SYS_STATUS_OK;
        $result['data'] = $this->user->getTeacherInfo();
        return $result;
    }

    public function getForumList(int $pageNo, int $pageSize, array $credentials)
    {
        $cond = [];

        $theme = array_get($credentials, 'theme');
        if (!empty($theme)) {
            $cond[] = ['theme', 'like', '%'.$theme.'%'];
        }

        $visibleAt = array_get($credentials, 'visible_at');
        if (!empty($visibleAt)) {
            $cond[] = ['visible_at', '>=', $visibleAt];
        }

        $forumAt = array_get($credentials, 'forum_at');
        if (!empty($forumAt)) {
            $cond[] = [DB::raw('date_add(forum_at, interval 2 day)'), '<=', $forumAt];
        }

        $forumList = Forum::where($cond)
            ->orderBy('created_at', 'desc')
            ->skip($pageSize * ($pageNo - 1))
            ->take($pageSize)
            ->get()
            ->toArray();

        $userIdList = array_column($forumList, 'updated_user_id');
        $userList = $this->user->getUserListByUserIdList($userIdList);
        $userList = array_column($userList, NULL, 'id');
        $userIdList = array_column($userList, 'id');

        foreach ($forumList as &$forum) {
            if (in_array(array_get($forum, 'updated_user_id'), $userIdList)) {
                $forum['updated_user_name'] = $userList[$forum['updated_user_id']]['name'];
            }
        }
        
        return $forumList;
    }

    public function getForumCnt(array $credentials)
    {
        $cond = [];
        
        $theme = array_get($credentials, 'theme');
        if (!empty($theme)) {
            $cond[] = ['theme', 'like', '%'.$theme.'%'];
        }

        $visibleAt = array_get($credentials, 'visible_at');
        if (!empty($visibleAt)) {
            $cond[] = ['visible_at', '>=', $visibleAt];
        }

        $forumAt = array_get($credentials, 'forum_at');
        if (!empty($forumAt)) {
            $cond[] = [DB::raw('date_add(forum_at, interval 2 day)'), '<=', $forumAt];
        }

        $forumCnt = Forum::where($cond)->count();

        return $forumCnt;
    }
}