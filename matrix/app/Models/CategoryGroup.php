<?php

namespace Matrix\Models;

use Exception;

class CategoryGroup extends BaseModel
{
    //  
    protected $fillable = ['code', 'name', 'category_code', 'sort', 'description'];

    public function getCategoryGroupListByCode(string $categoryGroupCode)
    {
        $categoryGroupList = self::where('code', $categoryGroupCode)->orderBy('sort', 'desc')->get();

        return empty($categoryGroupList) ? [] : $categoryGroupList->toArray();
    }

    public function getCategoryGroupList()
    {
        $categoryGroupList = self::select('code', 'name')->groupBy('code')->get();
        return empty($categoryGroupList) ? [] : $categoryGroupList->toArray();
    }

    public function getCategoryGroupInfo(string $categoryGroupCode)
    {
        $categoryGroupInfo = self::select('code', 'name')->where('code', $categoryGroupCode)->groupBy('code')->first();
        return empty($categoryGroupInfo) ? [] : $categoryGroupInfo->toArray();
    }

    public function checkCategoryGroupCodeUnique(string $categoryGroupCode)
    {
        $checkResp = self::where('code', $categoryGroupCode)->get();
        return empty($checkResp) ? [] : $checkResp->toArray();
    }

    public function createCategoryGroup(array $newCategoryGroup)
    {
        try {
            $condition = [
                'code' => array_get($newCategoryGroup, 'code'),
                'category_code' => array_get($newCategoryGroup, 'category_code'),
            ];
            $categoryGroupObj = self::where($condition)->take(1)->first();
            if (empty($categoryGroupObj)) {
                $categoryGroupObj = self::create([
                    'code' => array_get($newCategoryGroup, 'code'),
                    'name' => array_get($newCategoryGroup, 'name'),
                    'category_code' => array_get($newCategoryGroup, 'category_code'),
                    'sort' => array_get($newCategoryGroup, 'sort') ? array_get($newCategoryGroup, 'sort') : 0,
                    'description' => array_get($newCategoryGroup, 'description') ? array_get($newCategoryGroup, 'description') : '',
                ]);
            } else {
                $params = ['name', 'sort', 'description'];

                foreach ($newCategoryGroup as $key => $value) {
                    if (in_array($key, $params)) {
                        $categoryGroupObj->{$key} = (string)$value;
                    }
                }

                $categoryGroupObj->save();
            }
        } catch(Exception $e) {
            $categoryGroupObj = NULL;
        }

        return empty($categoryGroupObj) ? [] : $categoryGroupObj->toArray();
    }
    
    public function updateCategoryGroup(int $id, array $updateCategoryGroup)
    {
        $categoryGroup = self::find($id);

        if ( empty($categoryGroup) ) {
            return [];
        }

        $params = ['name', 'category_code', 'sort', 'description'];

        foreach ($updateCategoryGroup as $key => $value) {
            if (in_array($key, $params)) {
                $categoryGroup->{$key} = (string)$value;
            }
        }

        $categoryGroup->save();

        return $categoryGroup->toArray();
    }

    public function deleteCategoryGroup(array $ids)
    {
        return self::whereIn('id', $ids)->delete();
    }

    public function removeCategoryGroup(string $categoryGroupCode)
    {
        return self::where('code', $categoryGroupCode)->delete();
    }
}
