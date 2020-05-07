<?php

namespace Matrix\Contracts;

interface ContentGuardContract extends BaseInterface
{
//    public function getCategoryInfoByCode(string $code);
    public function checkCourseAccess(array $grantedCodeList, string $uri, array $parameter);
    public function getOnesAdAccessIdList(array $serviceKeyList);
    public function getOneCourseAccessCodeTree(array $serviceKeyList);
    public function getOneForumAccessIdList(array $serviceKeyList);
    public function grant(array $newData);
    public function revoke(array $condition);
    public function update(array $condition, array $newData);
    public function getOneArticleAccessIdList(array $serviceKeyList);
    public function getCourseAccessCodeList();
    public function getOnesAccessIdList(string $uri, array $serviceKeyList, string $key);
}

