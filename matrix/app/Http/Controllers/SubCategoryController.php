<?php

namespace Matrix\Http\Controllers;

use Illuminate\Http\Request;
use Matrix\Contracts\SubCategoryManager;

use Matrix\Exceptions\MatrixException;
use Exception;
use Log;

class SubCategoryController extends Controller
{
  private $request;
  private $subCategoryManager;
  
  public function __construct(Request $request, SubCategoryManager $subCategoryManager)
  {
    $this->request = $request;
    $this->subCategoryManager = $subCategoryManager;
  }

  public function getSubCategoryList(string $categoryCode)
  {
    $credentials = $this->request->validate([
      'page_no' => 'nullable|integer',
      'page_size' => 'nullable|integer'
    ]);
    
    try {
      $pageNo = array_get($credentials, 'page_no', 1);
      $pageSize = array_get($credentials, 'page_size', 10);

      $subCategoryList = $this->subCategoryManager->getSubCategoryList($pageNo, $pageSize, $categoryCode);
      $subCategoryCnt = $this->subCategoryManager->getSubCategoryCnt($categoryCode);

      $ret = [
        'code' => SYS_STATUS_OK,
        'msg' => 'success',
        'data' => [
          'sub_category_list' => $subCategoryList,
          'sub_category_cnt' => $subCategoryCnt,
        ],
      ];
    } catch (MatrixException $e) {
      Log::error($e->getMessage(), [$e]);
      $ret = [
        'code' => $e->getCode(),
        'msg' => $e->getMessage(),
      ];
    } catch (Exception $e) {
      Log::error($e->getMessage(), [$e]);
      $ret = [
        'code' => SYS_STATUS_ERROR_UNKNOW,
        'msg' => '未知错误'
      ];
    }

    return $ret;
  }

  public function checkSubCategoryCodeUnique(string $categoryCode, string $subCategoryCode)
  {
    $repData = $this->subCategoryManager->checkSubCategoryCodeUnique($categoryCode, $subCategoryCode);
    $this->checkServiceResult($repData, 'SubCategoryService');
    $oneSubCategoryInfo = array_get($repData, 'data.sub_category_code_check_res');
    $ret = [
      'code' => SYS_STATUS_OK,
      'data' => [
        'check_res' => $oneSubCategoryInfo,
      ],
    ];
    return $ret;
  }

  public function create()
  {
    $reqData = $this->request->validate([
      'name' => 'required|string',
      'code' => 'required|string',
      'category_code' => 'required|string'
    ]);

    $subCategoryInfoRes = $this->subCategoryManager->createSubCategory($reqData);

    try {
      $this->checkServiceResult($subCategoryInfoRes, 'SubCategoryService');
      $subCategoryInfo = array_get($subCategoryInfoRes, 'data.sub_category_info');
      $ret = [
        'code' => SYS_STATUS_OK,
        'data' => [
          'sub_category_info' => $subCategoryInfo,
        ],
      ];
    } catch (Exception $e) {
      $ret = [
        'code' => array_get($subCategoryInfoRes, 'code', SYS_STATUS_ERROR_UNKNOW),
        'msg' => array_get($subCategoryInfoRes, 'msg', '服务器错误'),
      ];
    }

    return $ret;
  }

  public function getSubCategoryInfoBySubCategoryId(int $subCategoryId)
  {
    $subCategoryInfoRes = $this->subCategoryManager->getSubCategoryInfoBySubCategoryId($subCategoryId);
    try {
      $this->checkServiceResult($subCategoryInfoRes, 'SubCategoryService');
      $subCategoryInfo = array_get($subCategoryInfoRes, 'data.sub_category_info');
      $ret = [
        'code' => SYS_STATUS_OK,
        'data' => [
          'sub_category_info' => $subCategoryInfo,
        ],
      ];
    } catch (Exception $e) {
      Log::error($e->getMessage(), [$e]);
      $ret = [
        'code' => array_get($subCategoryInfoRes, 'code', SYS_STATUS_ERROR_UNKNOW),
        'msg' => array_get($subCategoryInfoRes, 'msg', '服务器错误'),
      ];
    }

    return $ret;
  }

  public function update($subCategoryId)
  {
    $reqData = $this->request->validate([
      'name' => 'required|string',
      'code' => 'required|string'
    ]);

    $subCategoryInfoRes = $this->subCategoryManager->updateSubCategory($subCategoryId, $reqData);

    try {
      $this->checkServiceResult($subCategoryInfoRes, 'SubCategoryService');
      $subCategoryInfo = array_get($subCategoryInfoRes, 'data.sub_category_info');
      $ret = [
        'code' => SYS_STATUS_OK,
        'data' => [
          'sub_category_info' => $subCategoryInfo,
        ],
      ];
    } catch (Exception $e) {
      $ret = [
        'code' => array_get($subCategoryInfoRes, 'code', SYS_STATUS_ERROR_UNKNOW),
        'msg' => array_get($subCategoryInfoRes, 'msg', '服务器错误'),
      ];
    }

    return $ret;
  }

  public function delete(int $subCategoryId)
  {
    $delSubCategoryRet = $this->subCategoryManager->deleteSubCategory($subCategoryId);
    try {
      $this->checkServiceResult($delSubCategoryRet, 'SubCategoryService');
      $ret = [
        'code' => SYS_STATUS_OK,
        'msg' => '删除成功',
      ];
    } catch (Exception $e) {
      $ret = [
        'code' => array_get($delSubCategoryRet, 'code', SYS_STATUS_ERROR_UNKNOW),
        'msg' => array_get($delSubCategoryRet, 'msg', '服务器错误'),
      ];
    }

    return $ret;
  }

  public function activeSubCategory($subCategoryId, $active)
  {
    $activeSubCategoryData = $this->subCategoryManager->activeSubCategory($subCategoryId, $active);
    try {
      $this->checkServiceResult($activeSubCategoryData, 'SubCategoryService');
      $subCategoryInfo = array_get($activeSubCategoryData, 'data.sub_category_info');
      $ret = [
        'code' => SYS_STATUS_OK,
        'data' => [
          'sub_category_info' => $subCategoryInfo,
        ],
      ];
    } catch (Exception $e) {
      $ret = [
        'code' => array_get($activeSubCategoryData, 'code', SYS_STATUS_ERROR_UNKNOW),
      ];
    }

    return $ret;
  }

}