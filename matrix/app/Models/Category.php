<?php

namespace Matrix\Models;

use Exception;

class Category extends BaseModel
{
    const IS_SYSTEM_GENERATION = 1;

    protected $fillable = ['code', 'name', 'summary', 'description', 'cover_url', 'ad_image_url', 'active', 'service_key', 'is_system_generation'];

    //区分登记视频与课程视频
    public function getCategoryByCode($categoryCode)
    {
        $categoryData = self::where('active', 1)->whereIn('code', $categoryCode)->get();
        return  empty($categoryData) ? [] : $categoryData->toArray();
    }

    public function getCategories()
    {
        $categoryData = self::where('active', 1)->get();
        return  empty($categoryData) ? [] : $categoryData->toArray();
    }



    public function getOneCategoryInfo($categoryInfo)
    {
        $category = self::where('active', 1)->where(function ($query) use ($categoryInfo) {
            $query->where('code', $categoryInfo)
                ->orWhere('id', $categoryInfo);
        })->first();
        return empty($category) ? [] : $category->toArray();
    }

    public function getCategoryList()
    {
        $categoryList = self::get();

        return empty($categoryList) ? [] : $categoryList->toArray();
    }

    public function getCategoryListByCodeList(array $categoryCodeList, int $active = 0)
    {
        $model = self::whereIn('code', $categoryCodeList);

        if (!empty($active)) {
            $model = $model->where('active', 1);
        }

        $categoryList = $model->orderBy('id', 'asc')->get();

        return empty($categoryList) ? [] : $categoryList->toArray();
    }

    public function getCategoryInfo(string $code)
    {
        $category = self::where('code', $code)->take(1)->firstOrFail();
        return $category->toArray();
    }

    public function getCateoryListByCondition(array $condition = [])
    {
        if (empty($condition)) {
            $categoryList = self::where('is_system_generation', '<>', self::IS_SYSTEM_GENERATION)->orderBy('created_at', 'desc')->get();
        } else {
            $categoryList = self::where($condition)->where('is_system_generation', '<>', self::IS_SYSTEM_GENERATION)->orderBy('created_at', 'desc')->get();
        }

        return empty($categoryList) ? [] : $categoryList->toArray();
    }

    public function checkCategoryCodeUnique($categoryCode)
    {
        $checkResp = self::where('code', $categoryCode)->get();
        return empty($checkResp) ? [] : $checkResp->toArray();
    }

    public function createCategory(array $category)
    {
        try {
            $categoryObj = self::create([
                'name' => array_get($category, 'name'),
                'code' => array_get($category, 'code'),
                'summary' => empty(array_get($category, 'summary')) ? '' : array_get($category, 'summary'),
                'description' => array_get($category, 'description'),
                'cover_url' => array_get($category, 'cover_url'),
                'ad_image_url' => array_get($category, 'ad_image_url'),
                'service_key' => array_get($category, 'service_key'),
                'is_system_generation' => empty(array_get($category, 'is_system_generation')) ? 0 : array_get($category, 'is_system_generation'),
                'active' => 1,
            ]);
        } catch (Exception $e) {
            $categoryObj = NULL;
        }

        return empty($categoryObj) ? [] : $categoryObj->toArray();
    }

    public function getCategoryInfoByCategoryId(int $categoryId)
    {
        $category = self::find($categoryId);
        return empty($category) ? [] : $category->toArray();
    }

    public function updateCategory(int $categoryId, array $categoryInfo)
    {
        $category = self::find($categoryId);
        if ( empty($category) ) {
            return [];
        }

        $params = ['name', 'summary', 'description', 'cover_url', 'ad_image_url', 'service_key'];

        foreach ($categoryInfo as $key => $value) {
            if (in_array($key, $params)) {
                $category->{$key} = (string)$value;
            }
        }

        $category->save();

        return $category->toArray();
    }

    public function getCategoryListOredrByName()
    {
        $categoryList = self::orderBy('name', 'desc')->get();

        return empty($categoryList) ? [] : $categoryList->toArray();
    }
}
