<?php
namespace Matrix\Contracts;

interface KitReportManager extends BaseInterface
{
  public function getKits();
  public function getPublishStatus();
  public function getValidStatus();
  public function isTeacher();

  public function getKitReportList(int $pageNo, int $pageSize, array $credentials);
  public function getKitReportCnt(array $credentials);
  public function getModifyPermission();
  public function createKitReport(array $credentials);
  public function getKitReportInfo(int $id);
  public function updateKitReport(int $id, array $credentials);
  public function deleteKitReport(int $id);

  public function publishKitReport(int $id, string $scheme);

  public function getKitReportInfoByKitReportId(string $reportId);
}