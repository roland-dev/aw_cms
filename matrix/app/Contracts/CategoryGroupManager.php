<?php

namespace Matrix\Contracts;

interface CategoryGroupManager extends BaseInterface
{
  public function getCategoryList();
  public function getCategoryGroupList(int $pageNo, int $pageSize);
  public function getCategoryGroupCnt();

  public function checkCategoryGroupCodeUnique(string $categoryGroupCode);
  public function createCategoryGroup(array $newCategoryGroup);
  public function getCategoryGroupInfo(string $categoryGroupCode);
  public function updateCategoryGroup(array $updateCategoryGroup);
  public function deleteCategoryGroup(string $categoryGroupCode);
  public function createCategoryGroupMember(array $newCategoryGroupMember);
  public function getCategoryGroupMember(int $categoryGroupId);
  public function updateCategoryGroupMember(int $categoryGroupId, array $newCategoryGroupMember);
  public function deleteCategoryGroupMember(int $categoryGroupId);

  public function getCategoryGroupMemberList(int $pageNo, int $pageSize, string $categoryGroupCode);
  public function getCategoryGroupMemberCnt(string $categoryGroupCode);
}