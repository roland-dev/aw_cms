<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/4
 * Time: 15:38
 */

namespace Matrix\Contracts;


use Matrix\Models\User;

interface ForumManager extends BaseInterface
{
    public function createForum(array $forumData);

    public function updateForum(int $forumId, array $forumData);

    public function detail(int $forumId);

    public function destoryForum(int $forum);

    public function getAdListDataOfForumId(int $forumId, bool $bool);

    public function destoryAdOfOtherModules(int $forumId);

    public function getForumsData(array $forumIdsOfPermission);

    public function getForumsDataById($forumId);

    public function getTeachers();

    public function getForumList(int $pageNo, int $pageSize, array $credentials);

    public function getForumCnt(array $credentials);
}