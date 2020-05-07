<?php
namespace Matrix\Contracts;

interface MoveQrContract extends BaseInterface
{
    public function createQrGroup(string $title, int $maxFans, string $remark);
    public function updateQrGroup(string $groupCode, string $title, int $maxFans, string $remark);
    public function removeQrGroup(string $groupCode);
    public function getMoveQrGroup(string $groupCode);
    public function getMoveQrGroupList();

    public function createMoveQr(string $groupCode, string $title, string $filename, int $sort, string $remark);
    public function updateMoveQr(string $code, string $title, int $sort, string $remark);
    public function removeQr(string $code);
}
