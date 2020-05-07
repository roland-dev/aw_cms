<?php

namespace Matrix\Models;

use Exception;

class SubCategory extends BaseModel
{
    //
    protected $fillable = ['code', 'name', 'category_code', 'active'];

    public function getSubCategoryListByCategoryCode(string $categoryCode)
    {
        $subCategoryList = self::where('category_code', $categoryCode)->get();

        return empty($subCategoryList) ? [] : $subCategoryList->toArray();
    }

    public function getSubCategoryList()
    {
        $subCategoryList = self::get();
        return empty($subCategoryList) ? [] : $subCategoryList->toArray();
    }

    public function getSubCategoryInfo(string $code)
    {
        $subCategory = self::where('code', $code)->take(1)->firstOrFail();
        return $subCategory->toArray();
    }

    public function getActiveSubCategoryListByCategoryCode(string $categoryCode)
    {
        $subCategoryList = self::where('category_code', $categoryCode)->where('active', 1)->get();

        return empty($subCategoryList) ? [] : $subCategoryList->toArray();
    }

    public function checkSubCategoryCodeUnique(string $categoryCode, string $subCategoryCode)
    {
        $checkResp = self::where('category_code', $categoryCode)->where('code', $subCategoryCode)->get();

        return empty($checkResp) ? [] : $checkResp->toArray();
    }

    public function createSubCategory(array $subCategory)
    {
        try {
            $subCategoryObj = self::create([
                'name' => array_get($subCategory, 'name'),
                'code' => array_get($subCategory, 'code'),
                'category_code' => array_get($subCategory, 'category_code'),
                'active' => 1,
            ]);
        } catch (Exception $e) {
            $subCategoryObj = NULL;
        }

        return empty($subCategoryObj) ? [] : $subCategoryObj->toArray();
    }

    public function getSubCategoryInfoBySubCategoryId(int $subCategoryId)
    {
        $subCategory = self::find($subCategoryId);
        return empty($subCategory) ? [] : $subCategory->toArray();
    }

    public function updateSubCategory(int $subCategoryId, array $subCategoryInfo)
    {
        $subCategory = self::find($subCategoryId);
        if ( empty($subCategory) ) {
            return [];
        }

        $subCategory->name = array_get($subCategoryInfo, 'name');

        $subCategory->save();

        return $subCategory->toArray();
    }
    
    public function deleteSubCategory(int $subCategoryId)
    {
        $deleteSubCategoryRet = self::where('id', $subCategoryId)->delete();
        return $deleteSubCategoryRet;
    }

    public function activeSubCategory(int $subCategoryId, int $active)
    {
        $subCategory = self::find($subCategoryId);
        if ( empty($subCategory) ) {
            return [];
        }

        $subCategory->active = $active;

        $subCategory->save();

        return $subCategory->toArray();
    }
}
