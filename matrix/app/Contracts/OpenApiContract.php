<?php
namespace Matrix\Contracts;

interface OpenApiContract extends BaseInterface
{
    public function getCustomApp(string $code);
    public function generateCustomApp(string $name, string $remark);
    public function show();
    public function lock();
    public function unlock();
    public function updateSecret();
    public function generateToken(string $secret);
    public function checkToken(string $token);

    public function getCustomAppListOfPaging(int $pageNo, int $pageSize, array $credentials);
    public function getCustomAppCnt(array $credentials);
}
