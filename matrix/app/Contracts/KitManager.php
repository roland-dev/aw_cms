<?php
namespace Matrix\Contracts;

interface KitManager extends BaseInterface
{
  public function getBuyTypes();
  public function getBuyStates();
  public function getTeacherList();

  public function getKitList(int $pageNo, int $pageSize, array $credentials);
  public function getKitCnt(array $credentials);
  public function isTeacher();
  public function createKit(array $credentials);
  public function getKitInfo(int $id);
  public function updateKit(int $id, array $credentials);
  public function deleteKit(int $id);

  public function getKitsOfClient(int $teacherUserId);
  public function getKitInfoByKitCode(string $kitCode);
  public function getKitOfOutBuy(int $teacherUserId);
}