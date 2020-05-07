<?php

namespace Matrix\Services;

use Matrix\Contracts\SubCategoryManager;
use Matrix\Models\SubCategory;
use Matrix\Models\Article;

use Exception;

class SubCategoryService extends BaseService implements SubCategoryManager
{
  private $subCategory;
  private $article;

  public function __construct(SubCategory $subCategory, Article $article)
  {
    $this->subCategory = $subCategory;
    $this->article = $article;
  }

  public function getSubCategoryList(int $pageNo, int $pageSize, string $categoryCode)
  {
    $subCategoryList = SubCategory::where('category_code', $categoryCode)
      ->orderBy('created_at', 'desc')
      ->skip($pageSize * ($pageNo - 1))
      ->take($pageSize)
      ->get()
      ->toArray();

    return $subCategoryList;
  }

  public function getSubCategoryCnt(string $categoryCode) {
    $subCategoryCnt = SubCategory::where('category_code', $categoryCode)->count();
    return $subCategoryCnt;
  }

  public function checkSubCategoryCodeUnique(string $categoryCode, string $subCategoryCode)
  {
    $checkRes = $this->subCategory->checkSubCategoryCodeUnique($categoryCode, $subCategoryCode);
    $ret = [
      'code' => SYS_STATUS_OK,
      'data' => [
        'sub_category_code_check_res' => $checkRes,
      ],
    ];
    return $ret;
  }

  public function createSubCategory(array $newSubCategory)
  {
    $subCategory = $this->subCategory->createSubCategory($newSubCategory);
    if (empty($subCategory)) {
      $ret = [
        'code' => COLUMN_SUB_CATEGORY_EXISTS
      ];
      return $ret;
    }

    $ret = [
      'code' => SYS_STATUS_OK,
      'data' => [
        'sub_category_info' => $subCategory,
      ],
    ];
    return $ret;
  }

  public function getSubCategoryInfoBySubCategoryId(int $subCategoryId)
  {
    $subCategoryInfo = $this->subCategory->getSubCategoryInfoBySubCategoryId($subCategoryId);
    if (empty($subCategoryInfo)) {
      $ret = [
        'code' => COLUMN_SUB_CATEGORY_NOT_FOUND,
      ];
    } else {
      $ret = [
        'code' => SYS_STATUS_OK,
        'data' => [
          'sub_category_info' => $subCategoryInfo,
        ],
      ];
    }
    return $ret;
  }

  public function updateSubCategory(int $subCategoryId, array $subCategoryInfo)
  {
    $oldSubCategoryInfo = $this->subCategory->getSubCategoryInfoBySubCategoryId($subCategoryId);
    if (empty($oldSubCategoryInfo)) {
      $ret = [
        'code' => COLUMN_SUB_CATEGORY_UPDATE_FAILED,
      ];
      return $ret;
    }

    try {
      $subCategory = $this->subCategory->updateSubCategory($subCategoryId, $subCategoryInfo);
    } catch (Exception $e) {
      $ret = [
        'code' => SYS_STATUS_ERROR_UNKNOW,
      ];
      return $ret;
    }

    $ret = [
      'code' => SYS_STATUS_OK,
      'data' => [
        'sub_category_info' => $subCategory,
      ],
    ];

    return $ret;
  }

  public function deleteSubCategory(int $subCategoryId)
  {
    $oldSubCategoryInfo = $this->subCategory->getSubCategoryInfoBySubCategoryId($subCategoryId);
    if (empty($oldSubCategoryInfo)) {
      $ret = [
        'code' => COLUMN_SUB_CATEGORY_NOT_FOUND,
      ];
      return $ret;
    }

    if (self::checkSubCategory($oldSubCategoryInfo)) {
      $ret = [
        'code' => COLUMN_SUB_CATEGORY_NOT_EMPTY,
      ];
      return $ret;
    }

    $deleteSubCategoryRet = $this->subCategory->deleteSubCategory($subCategoryId);
    if (empty($deleteSubCategoryRet) || $deleteSubCategoryRet === 0) {
      $ret = [
        'code' => SYS_STATUS_ERROR_UNKNOW,
        'msg' => '服务器错误',
      ];
      return $ret;
    }

    $ret = [
      'code' => SYS_STATUS_OK,
      'msg' => '删除成功',
    ];
    return $ret;
  }

  /**
   * 检查 当前subCategory栏目下是否为空
   */
  private function checkSubCategory(array $subCategoryInfo)
  {
    $result = false;

    $cond = [
      'category_code' => array_get($subCategoryInfo, 'category_code'),
      'sub_category_code' => array_get($subCategoryInfo, 'code')
    ];

    $articleList = $this->article->getArticleList($cond);

    if (count($articleList)) {
      $result = true;
    }

    return $result;
  }

  public function activeSubCategory(int $subCategoryId, int $active)
  {
    $oldSubCategoryInfo = $this->subCategory->getSubCategoryInfoBySubCategoryId($subCategoryId);
    if ( empty($oldSubCategoryInfo) ) {
      $ret = [
        'code' => COLUMN_SUB_CATEGORY_NOT_FOUND,
      ];

      return $ret;
    }

    try {
      $subCategory = $this->subCategory->activeSubCategory($subCategoryId, $active);

      $ret = [
        'code' => SYS_STATUS_OK,
        'data' => [
          'sub_category_info' => $subCategory,
        ],
      ];
    } catch (Exception $e) {
      $ret = [
        'code' => SYS_STATUS_ERROR_UNKNOW,
      ];
    }

    return $ret;
  }

}