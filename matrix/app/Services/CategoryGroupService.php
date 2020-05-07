<?php

namespace Matrix\Services;

use Exception;
use Matrix\Contracts\CategoryGroupManager;
use Matrix\Models\CategoryGroup;
use Matrix\Models\Category;
use Illuminate\Support\Facades\DB;
use Matrix\Exceptions\MatrixException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Matrix\Models\ColumnGroup;

class CategoryGroupService extends BaseService implements CategoryGroupManager
{
  private $categoryGroup;
  private $category;

  public function __construct(CategoryGroup $categoryGroup, Category $category)
  {
    $this->categoryGroup = $categoryGroup;
    $this->category = $category;
  }

  public function getCategoryList()
  {
    $categoryList = $this->category->getCategoryListOredrByName();

    $ret = [
      'code' => SYS_STATUS_OK,
      'data' => [
        'category_list' => $categoryList,
      ],
    ];

    return $ret;
  }

  public function getCategoryGroupList(int $pageNo, int $pageSize)
  {

    $categoryGroupList = ColumnGroup::select('code', 'name', 'descript')
      ->orderBy('created_at')
      ->skip($pageSize * ($pageNo - 1))
      ->take($pageSize)
      ->get()
      ->toArray();

    return $categoryGroupList;
  }

  public function getCategoryGroupCnt()
  {
    $categoryGroupCnt = ColumnGroup::count();
    return $categoryGroupCnt;
  }



  public function checkCategoryGroupCodeUnique(string $categoryGroupCode)
  {
    // $checkRes = $this->categoryGroup->checkCategoryGroupCodeUnique($categoryGroupCode);
    $checkRes = ColumnGroup::where('code', $categoryGroupCode)->get()->toArray();
    $ret = [
      'code' => SYS_STATUS_OK,
      'data' => [
        'category_group_code_check_res' => $checkRes,
      ],
    ];
    return $ret;
  }

  public function createCategoryGroup(array $newCategoryGroup)
  {
    $categoryGroupCode = array_get($newCategoryGroup, 'code');
    $categoryGroup = ColumnGroup::where('code', $categoryGroupCode)->first();
    if (!empty($categoryGroup)) {
      throw new MatrixException("$categoryGroupCode 当前栏目分组已存在", COLUMN_CATEGORY_GROUP_EXISTS);
    }


    $condition = [
      'code' => array_get($newCategoryGroup, 'code'),
      'name' => array_get($newCategoryGroup, 'name'),
      'descript' => (string)array_get($newCategoryGroup, 'descript'),
    ];

    ColumnGroup::create($condition);
    return self::getCategoryGroupInfo($categoryGroupCode);
  }

  public function getCategoryGroupInfo(string $categoryGroupCode)
  {
    try {
      $categoryGroup = ColumnGroup::where('code', $categoryGroupCode)->firstOrFail()->toArray();
    } catch (ModelNotFoundException $e) {
      throw new MatrixException("{$categoryGroupCode} 这个栏目分组不存在", COLUMN_CATEGORY_GROUP_NOT_FOUND);
    }

    $ret = [
      'code' => SYS_STATUS_OK,
      'data' => [
        'category_group' => $categoryGroup,
      ],
    ];

    return $ret;
  }

  public function updateCategoryGroup(array $updateCategoryGroup)
  {
    $categoryGroupCode = array_get($updateCategoryGroup, 'code');
    // $categoryGroupData = $this->categoryGroup->getCategoryGroupInfo($categoryGroupCode);
    $categoryGroupData = ColumnGroup::where('code', $categoryGroupCode)->first();
    if (empty($categoryGroupData)) {
      throw new MatrixException("当前栏目组不存在", COLUMN_CATEGORY_GROUP_NOT_FOUND);
    }

    $condition = [];
    $condition['name'] = array_get($updateCategoryGroup, 'name');

    CategoryGroup::where('code', $categoryGroupCode)->update($condition);

    // 增加更新 cms_column_groups 2019/09/17
    $condition['descript'] = (string)array_get($updateCategoryGroup, 'descript');
    ColumnGroup::where('code', $categoryGroupCode)->update($condition);
    
    return self::getCategoryGroupInfo($categoryGroupCode);
  }

  public function deleteCategoryGroup(string $categoryGroupCode)
  {
    
    DB::beginTransaction();
    try {
      // 增加删除 cms_column_groups 2019/09/17
      $columnGroup = ColumnGroup::where('code', $categoryGroupCode)->firstOrFail();
      $columnGroup->delete();

      $this->categoryGroup->removeCategoryGroup($categoryGroupCode);
      DB::commit();
    } catch(ModelNotFoundException $e) {
      DB::rollback();
      throw new MatrixException("{$categoryGroupCode} 这个栏目分组不存在", COLUMN_CATEGORY_GROUP_NOT_FOUND);
    } catch (Exception $e) {
      DB::rollback();
      throw new Exception("删除失败，未知错误", SYS_STATUS_ERROR_UNKNOW);
    }

    return [
      'code' => SYS_STATUS_OK,
      'msg' => '删除成功',
    ];
  }
  
  public function getCategoryCodeList(string $code)
  {
      $categoryGroupList = CategoryGroup::where('code', $code)->get()->toArray();
      if (empty($categoryGroupList)) {
          return [];
      }

      $categoryCodeList = array_column($categoryGroupList, 'category_code');

      return $categoryCodeList;
  }

  public function getCategoryGroupMemberList(int $pageNo, int $pageSize, string $categoryGroupCode)
  {
    $categoryGroupMemberList = CategoryGroup::where('code', $categoryGroupCode)
      ->orderBy('sort', 'desc')
      ->skip($pageSize * ($pageNo - 1))
      ->take($pageSize)
      ->get()
      ->toArray();
    
    $categoryCodeList = array_column($categoryGroupMemberList, 'category_code');
    $categoryList = $this->category->getCategoryListByCodeList($categoryCodeList);
    $categoryList = array_column($categoryList, NULL, 'code');
    $categoryCodeList = array_column($categoryList, 'code');

    foreach ($categoryGroupMemberList as &$categoryGroupMember) {
      if (in_array(array_get($categoryGroupMember, 'category_code'), $categoryCodeList)) {
        $categoryGroupMember['category_name'] = $categoryList[$categoryGroupMember['category_code']]['name'];
      }
    }

    return $categoryGroupMemberList;
  }

  public function getCategoryGroupMemberCnt(string $categoryGroupCode)
  {
    $categoryGroupMemberCnt = CategoryGroup::where('code', $categoryGroupCode)->count();
    return $categoryGroupMemberCnt;
  }


  public function createCategoryGroupMember(array $newCategoryGroupMember)
  {
    try {
      $categoryCode = array_get($newCategoryGroupMember, 'category_code');
      if (!empty($categoryCode)) {
        $category = Category::where('code', $categoryCode)->firstOrFail();
      } else {
        throw new MatrixException("所选栏目不存在", COLUMN_CATEGORY_NOT_FOUND);
      }
    } catch (ModelNotFoundException $e) {
      throw new MatrixException("所选栏目不存在", COLUMN_CATEGORY_NOT_FOUND);
    }

    try {
      $categoryGroupCode = array_get($newCategoryGroupMember, 'code');
      if (!empty($categoryGroupCode) && !empty($categoryCode)) {
        $categoryGroupMember = CategoryGroup::where('code', $categoryGroupCode)->where('category_code', $categoryCode)->take(1)->firstOrFail();
        throw new MatrixException("当前组中组成员已存在", COLUMN_CATEGORY_GROUP_MEMBER_EXISTS);
      } else {
        throw new MatrixException("所选用户组不存在", COLUMN_CATEGORY_GROUP_NOT_FOUND);
      }
    } catch (ModelNotFoundException $e) {
      $newCategoryGroupMember['description'] = (string)array_get($newCategoryGroupMember, 'description');
      $categoryGroupMember = CategoryGroup::create($newCategoryGroupMember);
    }

    return $categoryGroupMember;
  }

  public function getCategoryGroupMember(int $categoryGroupId)
  {
    try {
      $categoryGroupMember = CategoryGroup::where('id', $categoryGroupId)->firstOrFail();
    } catch (ModelNotFoundException $e) {
      throw new MatrixException("当前组成员数据不存在", COLUMN_CATEGORY_GROUP_MEMBER_NOT_FOUND);
    }

    return $categoryGroupMember;
  }

  public function updateCategoryGroupMember(int $categoryGroupId, array $newCategoryGroupMember)
  {
    try {
      $categoryCode = array_get($newCategoryGroupMember, 'category_code');
      if (!empty($categoryCode)) {
        $category = Category::where('code', $categoryCode)->firstOrFail();
      } else {
        throw new MatrixException("所选栏目不存在", COLUMN_CATEGORY_NOT_FOUND);
      }
    } catch (ModelNotFoundException $e) {
      throw new MatrixException("所选栏目不存在", COLUMN_CATEGORY_NOT_FOUND);
    }

    try {
      $categoryGroupCode = array_get($newCategoryGroupMember, 'code');
      if (!empty($categoryGroupCode) && !empty($categoryCode)) {
        $categoryGroupMember = CategoryGroup::where('code', $categoryGroupCode)->where('category_code', $categoryCode)->where('id', '<>', $categoryGroupId)->take(1)->firstOrFail();
        throw new MatrixException("当前组中组成员已存在", COLUMN_CATEGORY_GROUP_MEMBER_EXISTS);
      } else {
        throw new MatrixException("所选用户组不存在", COLUMN_CATEGORY_GROUP_NOT_FOUND);
      }
    } catch (ModelNotFoundException $e) {
      try {
        $categoryGroupMember = CategoryGroup::where('id', $categoryGroupId)->firstOrFail();
        foreach ($newCategoryGroupMember as $k => $v) {
          $categoryGroupMember->{$k} =$v;
        }
        $categoryGroupMember->save();
      } catch (ModelNotFoundException $e) {
        throw new MatrixException("当前组成员数据不存在", COLUMN_CATEGORY_GROUP_MEMBER_NOT_FOUND);
      }
    }

    return $categoryGroupMember;
  }

  public function deleteCategoryGroupMember(int $categoryGroupId)
  {
    try {
      $categoryGroupMember = CategoryGroup::where('id', $categoryGroupId)->firstOrFail()->delete();
    } catch (ModelNotFoundException $e) {
      throw new MatrixException("当前组成员数据不存在", COLUMN_CATEGORY_GROUP_MEMBER_NOT_FOUND);
    }
  }
}
