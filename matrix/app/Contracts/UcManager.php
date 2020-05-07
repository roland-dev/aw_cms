<?php

namespace Matrix\Contracts;

interface UcManager extends BaseInterface
{
    public function getEnterpriseLoginUrl(string $callback);
    public function getUserInfoByToken(string $token);
    public function getUserDetailByJwt(string $jwt);
    public function getUserDetail(string $parameter);
    public function getCustomerProductCodeList(string $openId);
    public function getCustomerProductCodeListBySessionId(string $sessionId);
    public function getH5EnterpriseLoginUrl(string $callback);
    public function getUserInfoBySessionId(string $sessionId, string $channel, bool $refresh);
    public function getUserInfoByOpenId(string $openId, string $channel, bool $refresh);
    public function getUserInfoByMobile(string $mobile, string $channel, bool $refresh);
    public function getUserInfoByQyUserid(string $qyUserid, string $channel, bool $refresh);
    public function getUserInfoByCustomerCode(string $customerCode, string $channel);

    public function fitAppWebviewUrl(string $url);

    public function getAccessCodeByOpenId(string $openId, string $channel, bool $refresh);
    public function getAccessCodeBySessionId(string $sessionId, string $channel, bool $refresh);

    public function batchFriend(array $friendList, string $channel);
    public function batchRemoveFriend(array $friendList, string $channel);
    public function modifyNickname(string $nickName, string $channel);
    public function getUserMoneys(array $openIds);
    public function getAnonymousUserInfo(string $channel);

    public function syncUserInfo(array $userInfo);
    public function getAccessCodeByToken(string $token);

    public function sendMessageToUc(array $formData, string $channel);
}
