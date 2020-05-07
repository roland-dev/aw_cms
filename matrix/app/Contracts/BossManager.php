<?php

namespace Matrix\Contracts;

interface BossManager extends BaseInterface
{
    public function getPackages();

    public function getPackagesOfServiceCode();

    public function getServices(string $moduleName);

    public function getCategoryList(string $moduleName);

    public function kgsMsgDelete(string $kgsId, string $uname);

    public function pushQywx(string $serviceCode, string $msgType, array $msgData);
}
