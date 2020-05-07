<?php
namespace Matrix\Contracts;

interface DynamicAdManager extends BaseInterface
{
  public function getSourceTypes();
  public function getDynamicAdTerminals();

  public function createDynamicAd(array $credentials);
  public function getDynamicAdList(int $pageNo, int $pageSize, array $credentials);
  public function getDynamicAdCnt(array $credentials);
  public function changeActiveStatus(int $dynamicAdId, int $active);
  public function changeSignStatus(int $dynamicAdId, int $sign);
  public function getDynamicAdInfo(int $dynamicAdId);
  public function updateDynamicAd(int $dynamicAdId, array $credentials);
  public function deleteDynamicAd(int $dynamicAdId);

  public function getDynamicAdListOfClient(array $dynamicAdId);
}