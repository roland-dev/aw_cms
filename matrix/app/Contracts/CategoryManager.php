<?php

namespace Matrix\Contracts;

interface CategoryManager extends BaseInterface
{
    //public function getCategoryList($service);
    public function getCategoryInfoByCode(string $code);
    public function getCategoryList();
    public function getMyCategoryList();
    public function getSubCategoryList();
    public function getSubCategoryListByCategoryCode(string $categoryCode);
    public function getTeacherListByCategoryCode(string $categoryCode, string $openId);
    public function getActiveSubCategoryListByCategoryCode(string $categoryCode);
    public function getCategoryInfo(string $categoryCode, $openId);
    public function getTeacherById(int $teacherId);
    public function getCategoryListByGroupCode(string $categoryGroupCode, int $active);
    public function getCategoryListByCodeList(array $codeList);

    public function getTeacherListByCategoryCodeList(array $categoryCodeList);
    public function getTeacherListByIdList(array $teacherIdList);
    public function getCategoryListByUserId(int $userId);
    public function getTeacherListByUserIdList(array $teacherUserIdList);

    public function createCategory(array $newCategory);
    public function getCategoryInfoByCategoryId(int $categoryId);
    public function updateCategory(int $categoryId, array $categoryInfo);
    public function getTeacherList(string $categoryCode);

    public function getCategoryListOfPaging(int $pageNo, int $pageSize, array $credentials);
    public function getCategoryCnt(array $credentials);
}
