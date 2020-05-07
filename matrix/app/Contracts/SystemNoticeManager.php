<?php

namespace Matrix\Contracts;

interface SystemNoticeManager extends BaseInterface
{
    public function getCustomerSystemNoticeList(string $openId);
    public function readCustomerSystemNotice(int $systemNoticeId, string $openId);

    public function getManageSystemNoticeList(int $userId);
    public function readManageSystemNotice(int $systemNoticeId);

}

