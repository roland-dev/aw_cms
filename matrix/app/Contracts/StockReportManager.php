<?php

namespace Matrix\Contracts;

interface StockReportManager extends BaseInterface
{
  public function getReportCategories();
  public function getReportCategoryOfCategoryId($categoryId);
  public function getPublishStatus();

  public function createStockReport(array $credentials);
  public function getStockReportList(int $pageNo, int $pageSize, array $credentials);
  public function getStockReportCnt(array $credentials);
  public function getStockReportInfo(int $id);
  public function getStockReportInfoByStockReportId(string $reportId);
  public function updateStockReport(int $id, array $credentials);
  public function deleteStockReport(int $id);

  public function publishStockReport(int $id);
  
  public function getModifyPermission();

  public function getStockReportListOfApi(int $pageNo, int $pageSize, array $credentials, string $version = 'v2');
  public function getStockReportListOfClient(array $credentials);
}