<?php

namespace Matrix\Contracts;

interface SubCategoryManager extends BaseInterface
{
  public function getSubCategoryList(int $pageNo, int $pageSize, string $categoryCode);
  public function getSubCategoryCnt(string $categoryCode);

  public function checkSubCategoryCodeUnique(string $categoryCode, string $subCategoryCode);
  public function createSubCategory(array $newSubCategory);
  public function getSubCategoryInfoBySubCategoryId(int $subCategoryId);
  public function updateSubCategory(int $subCategoryId, array $subCategoryInfo);
  public function deleteSubCategory(int $subCategoryId);
  public function activeSubCategory(int $subCategoryId, int $active);
}