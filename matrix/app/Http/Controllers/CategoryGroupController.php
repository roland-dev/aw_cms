<?php

namespace Matrix\Http\Controllers;

use Illuminate\Http\Request;

use Exception;
use Log;
use Matrix\Contracts\CategoryGroupManager;
use Matrix\Contracts\CategoryManager;
use Matrix\Exceptions\MatrixException;

class CategoryGroupController extends Controller
{
  private $request;
  private $categoryGroupManager;

  public function __construct(Request $request, CategoryGroupManager $categoryGroupManager)
  {
    $this->request = $request;
    $this->categoryGroupManager = $categoryGroupManager;
  }

  public function getCategoryList()
  {
    $categoryListRes = $this->categoryGroupManager->getCategoryList();
    $this->checkServiceResult($categoryListRes, 'CategoryGroupService');
    $categoryList = array_get($categoryListRes, 'data.category_list');
    $ret = [
      'code' => SYS_STATUS_OK,
      'data' => [
        'category_list' => $categoryList,
      ],
    ];

    return $ret;
  }

  public function getCategoryGroupList()
  {
    $credentials = $this->request->validate([
      'page_no' => 'nullable|integer',
      'page_size' => 'nullable|integer',
    ]);

    try {
      $pageNo = array_get($credentials, 'page_no', 1);
      $pageSize = array_get($credentials, 'page_size', 10);

      $categoryGroupList = $this->categoryGroupManager->getCategoryGroupList($pageNo, $pageSize);
      $categoryGroupCnt = $this->categoryGroupManager->getCategoryGroupCnt();

      $ret = [
        'code' => SYS_STATUS_OK,
        'msg' => 'success',
        'data' => [
          'category_group_list' => $categoryGroupList,
          'category_group_cnt' => $categoryGroupCnt,
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
        'msg' => '未知错误',
      ];
    }

    return $ret;
  }

  public function checkCategoryGroupCodeUnique(string $categoryGroupCode)
  {
    $repData = $this->categoryGroupManager->checkCategoryGroupCodeUnique($categoryGroupCode);
    $this->checkServiceResult($repData, 'CategoryGroupService');
    $oneCategoryGroupInfo = array_get($repData, 'data.category_group_code_check_res');
    $ret = [
      'code' => SYS_STATUS_OK,
      'data' => [
        'check_res' => $oneCategoryGroupInfo,
      ],
    ];

    return $ret;
  }

  public function create()
  {
    $reqData = $this->request->validate([
      'code' => 'required|string',
      'name' => 'required|string',
      'descript' => 'nullable|string',
    ]);

    try {
      $categoryGroupData = $this->categoryGroupManager->createCategoryGroup($reqData);
      $categoryGroup = array_get($categoryGroupData, 'data.category_group');
      $categoryList = array_get($categoryGroupData, 'data.category_list');
      $ret = [
        'code' => SYS_STATUS_OK,
        'data' => [
          'category_group' => $categoryGroup,
          'category_list' => $categoryList,
        ],
        'msg' => '添加成功',
      ];
    } catch (MatrixException $e) {
      Log::error("创建栏目组错误: {$e->getMessage()}", [$e]);
      $ret = [
        'code' => $e->getCode(),
        'msg' => $e->getMessage()
      ];
    } catch (Exception $e) {
      Log::error("创建栏目组错误：{$e->getMessage()}", [$e]);
      $ret = [
        'code' => SYS_STATUS_ERROR_UNKNOW,
        'msg' => '未知错误',
      ];
    }
    
    return $ret;
  }

  public function getCategoryGroupInfo($categoryGroupCode)
  {
    try {
      $categoryGroupInfoRes = $this->categoryGroupManager->getCategoryGroupInfo($categoryGroupCode);
      $ret = [
        'code' => SYS_STATUS_OK,
        'data' => [
          'category_group' => array_get($categoryGroupInfoRes, 'data.category_group'),
        ],
      ];
    } catch (MatrixException $e) {
      Log::error("获取栏目组信息错误：{$e->getMessage()}", [$e]);
      $ret = [
        'code' => $e->getCode(),
        'msg' => $e->getMessage(),
      ];
    } catch (Exception $e) {
      Log::error("获取栏目组信息错误：{$e->getMessage()}", [$e]);
      $ret = [
        'code' => SYS_STATUS_ERROR_UNKNOW,
        'msg' => '未知错误',
      ];
    }

    return $ret;
  }

  public function update()
  {
    $reqData = $this->request->validate([
      'code' => 'required|string',
      'name' => 'required|string',
      'descript' => 'nullable|string'
    ]);

    try {
      $categoryGroupData = $this->categoryGroupManager->updateCategoryGroup($reqData);
      $categoryGroup = array_get($categoryGroupData, 'data.category_group');
      $categoryList = array_get($categoryGroupData, 'data.category_list');
      $ret = [
        'code' => SYS_STATUS_OK,
        'data' => [
          'category_group' => $categoryGroup,
          'category_list' => $categoryList,
        ],
        'msg' => '编辑成功',
      ];
    } catch (MatrixException $e) {
      Log::error("更新栏目组错误：{$e->getMessage()}", [$e]);
      $ret = [
        'code' => $e->getCode(),
        'msg' => $e->getMessage(),
      ];
    } catch (Exception $e) {
      Log::error("更新栏目错误：{$e->getMessage()}", [$e]);
      $ret = [
        'code' => SYS_STATUS_ERROR_UNKNOW,
        'msg' => '未知错误',
      ];
    }

    return $ret;
  }

  public function delete($categoryGroupCode)
  {
    $delCategoryGroup = $this->categoryGroupManager->deleteCategoryGroup($categoryGroupCode);
    try {
      $this->checkServiceResult($delCategoryGroup, 'CategoryGroupService');
      $ret = [
        'code' => SYS_STATUS_OK,
        'msg' => '删除成功',
      ];
    } catch (Exception $e) {
      $ret = [
        'code' => array_get($delCategoryGroup, 'code', SYS_STATUS_ERROR_UNKNOW),
        'msg' => array_get($delCategoryGroup, 'msg', '服务器错误'),
      ];
    }
    
    return $ret;
  }

  public function getCategoryGroupMemberList()
  {
    $credentials = $this->request->validate([
      'page_no' => 'nullable|integer',
      'page_size' => 'nullable|integer',
      'category_group_code' => 'required|string'
    ]);

    try {
      $pageNo = array_get($credentials, 'page_no', 1);
      $pageSize = array_get($credentials, 'page_size', 10);

      $categoryGroupCode = (string)array_get($credentials, 'category_group_code');

      $categoryGroupMemberList = $this->categoryGroupManager->getCategoryGroupMemberList($pageNo, $pageSize, $categoryGroupCode);
      $categoryGroupMemberCnt = $this->categoryGroupManager->getCategoryGroupMemberCnt($categoryGroupCode);

      $ret = [
        'code' => SYS_STATUS_OK,
        'msg' => 'success',
        'data' => [
          'category_group_member_list' => $categoryGroupMemberList,
          'category_group_member_cnt' => $categoryGroupMemberCnt,
        ],
      ];
    } catch (MatrixException $e) {
      Log::error($e->getMessage(), [$e]);
      $ret = [
        'code' => $e->getCode(),
        'msg' => $e->getMessage()
      ];
    } catch (Exception $e) {
      Log::error($e->getMessage(), [$e]);
      $ret = [
        'code' => SYS_STATUS_ERROR_UNKNOW,
        'msg' => '未知错误',
      ];
    }

    return $ret;
  }

  public function createCategoryGroupMember()
  {
    $reqData = $this->request->validate([
      'code' => 'required|string',
      'name' => 'required|string',
      'category_code' => 'required|string',
      'sort' => 'nullable|integer',
      'description' => 'nullable|string'
    ]);

    try {
      $categoryGroupMember = $this->categoryGroupManager->createCategoryGroupMember($reqData);
      $ret = [
        'code' => SYS_STATUS_OK,
        'data' => [
          'category_group_member' => $categoryGroupMember
        ],
        'msg' => '添加成功',
      ];
    } catch (MatrixException $e) {
      Log::error("添加组成员错误：{$e->getMessage()}", [$e]);
      $ret = [
        'code' => $e->getCode(),
        'msg' => $e->getMessage()
      ];
    } catch (Exception $e) {
      Log::error("添加组成员错误：{$e->getMessage()}", [$e]);
      $ret = [
        'code' => SYS_STATUS_ERROR_UNKNOW,
        'msg' => '未知错误'
      ];
    }

    return $ret;
  }

  public function getCategoryGroupMember($categoryGroupId)
  {
    try {
      $categoryGroupMember = $this->categoryGroupManager->getCategoryGroupMember($categoryGroupId);

      $ret = [
        'code' => SYS_STATUS_OK,
        'data' => [
          'category_group_member' => $categoryGroupMember
        ],
        'msg' => 'success'
      ];
    } catch (MatrixException $e) {
      Log::error("查询组成员信息错误：{$e->getMessage()}", [$e]);
      $ret = [
        'code' => $e->getCode(),
        'msg' => $e->getMessage()
      ];
    } catch (Exception $e) {
      Log::error("查询组成员信息错误：{$e->getMessage()}", [$e]);
      $ret = [
        'code' => SYS_STATUS_ERROR_UNKNOW,
        'msg' => '未知错误'
      ];
    }

    return $ret;
  }

  public function updateCategoryGroupMember($categoryGroupId)
  {
    $reqData = $this->request->validate([
      'code' => 'required|string',
      'name' => 'required|string',
      'category_code' => 'required|string',
      'sort' => 'nullable|integer',
      'description' => 'nullable|string'
    ]);
    try {
      $categoryGroupMember = $this->categoryGroupManager->updateCategoryGroupMember($categoryGroupId, $reqData);
      $ret = [
        'code' => SYS_STATUS_OK,
        'data' => [
          'category_group_member' => $categoryGroupMember
        ],
        'msg' => '更新成功'
      ];
    } catch (MatrixException $e) {
      Log::error("编辑组成员错误：{$e->getMessage()}", [$e]);
      $ret = [
        'code' => $e->getCode(),
        'msg' => $e->getMessage(),
      ];
    } catch (Exception $e) {
      Log::error("编辑组成员错误：{$e->getMessage()}", [$e]);
      $ret = [
        'code' => SYS_STATUS_ERROR_UNKNOW,
        'msg' => '未知错误'
      ];
    }

    return $ret;
  }

  public function deleteCategoryGroupMember($categoryGroupId)
  {
    try {
      $categoryGroupMember = $this->categoryGroupManager->deleteCategoryGroupMember($categoryGroupId);

      $ret = [
        'code' => SYS_STATUS_OK,
        'msg' => '删除成功'
      ];
    } catch (MatrixException $e) {
      Log::error("删除组成员错误：{$e->getMessage()}", [$e]);
      $ret = [
        'code' => $e->getCode(),
        'msg' => $e->getMessage()
      ];
    } catch (Exception $e) {
      Log::error("删除组成员错误：{$e->getMessage()}", [$e]);
      $ret = [
        'code' => SYS_STATUS_ERROR_UNKNOW,
        'msg' => '未知错误'
      ];
    }

    return $ret;
  }
}