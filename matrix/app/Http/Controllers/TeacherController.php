<?php

namespace Matrix\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Matrix\Contracts\TeacherManager;

use Exception;
use Log;
use Matrix\Exceptions\MatrixException;

class TeacherController extends Controller
{
  private $request;
  private $teacherManager;

  public function __construct(Request $request, TeacherManager $teacherManager)
  {
    $this->request = $request;
    $this->teacherManager = $teacherManager;
  }

  public function search()
  {
    $reqData = $this->request->validate([
      'category_code' => 'nullable|string',
    ]);
    
    $teacherListRes = $this->teacherManager->searchTeacherList($reqData);
    $this->checkServiceResult($teacherListRes, 'TeacherService');
    $teacherList = array_get($teacherListRes, 'data.teacher_list');

    $ret = [
      'code' => SYS_STATUS_OK,
      'data' => [
        'teacher_list' => $teacherList,
      ],
    ];

    return $ret;
  }

  public function getTeacherListOfPaging()
  {
    $credentials = $this->request->validate([
      'page_no' => 'nullable|integer',
      'page_size' => 'nullable|integer',
      'category_code' => 'nullable|string'
    ]);

    try {
      $pageNo = array_get($credentials, 'page_no', 1);
      $pageSize = array_get($credentials, 'page_size', 10);

      $categoryCode = (string)array_get($credentials, 'category_code');

      $teacherList = $this->teacherManager->getTeacherListOfPaging($pageNo, $pageSize, $categoryCode);
      $teacherCnt = $this->teacherManager->getTeacherCnt($categoryCode);

      $ret = [
        'code' => SYS_STATUS_OK,
        'msg' => 'success',
        'data' => [
          'teacher_list' => $teacherList,
          'teacher_cnt' => $teacherCnt,
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

  public function getUserList()
  {
    $reqData = $this->request->validate([
      'category_code' => 'required|string',
      'teacher_id' => 'nullable|integer',
    ]);

    $userListRes = $this->teacherManager->getUserList($reqData);
    $this->checkServiceResult($userListRes, 'TeacherService');
    $userList = array_get($userListRes, 'data.user_list');

    $ret = [
      'code' => SYS_STATUS_OK,
      'data' => [
        'user_list' => $userList,
      ],
    ];

    return $ret;
  }

  public function getTeacherInfo(int $teacherId)
  {
    $teacherInfoRes = $this->teacherManager->getTeacherInfo($teacherId);
    try {
      $this->checkServiceResult($teacherInfoRes, 'TeacherService');
      $teacherInfo = array_get($teacherInfoRes, 'data.teacher_info');
      $ret = [
        'code' => SYS_STATUS_OK,
        'data' => [
          'teacher_info' => $teacherInfo,
        ],
      ];
    } catch (Exception $e) {
      Log::error($e->getMessage(), [$e]);
      $ret = [
        'code' => array_get($teacherInfoRes, 'code', SYS_STATUS_ERROR_UNKNOW),
      ];
    }

    return $ret;
  }

  public function create()
  {
    $reqData = $this->request->validate([
      'user_id' => 'required|integer',
      'category_code' => 'required|string',
      'icon_url' => 'nullable|string',
      'visitor_video_url' => 'nullable|string',
      'customer_video_url' => 'nullable|string',
      'cover_url' => 'nullable|string',
      'description' => 'nullable|string'
    ]);

    $teacherInfoRes = $this->teacherManager->createTeacher($reqData);

    try {
      $this->checkServiceResult($teacherInfoRes, 'TeacherService');
      $teacherInfo = array_get($teacherInfoRes, 'data.teacher_info');
      $ret = [
        'code' => SYS_STATUS_OK,
        'data' => [
          'teacher_info' => $teacherInfo,
        ],
      ];
    } catch (Exception $e) {
      $ret = [
        'code' => array_get($teacherInfoRes, 'code', SYS_STATUS_ERROR_UNKNOW),
      ];
    }

    return $ret;
  }

  public function update($teacherId)
  {
    $reqData = $this->request->validate([
      'user_id' => 'required|integer',
      'category_code' => 'required|string',
      'icon_url' => 'nullable|string',
      'visitor_video_url' => 'nullable|string',
      'customer_video_url' => 'nullable|string',
      'cover_url' => 'nullable|string',
      'description' => 'nullable|string'
    ]);

    $teacherInfoRes = $this->teacherManager->updateTeacher($teacherId, $reqData);

    try {
      $this->checkServiceResult($teacherInfoRes, 'TeacherService');
      $teacherInfo = array_get($teacherInfoRes, 'data.teacher_info');
      $ret = [
        'code' => SYS_STATUS_OK,
        'data' => [
          'teacher_info' => $teacherInfo,
        ],
      ];
    } catch (Exception $e) {
      $ret = [
        'code' => array_get($teacherInfoRes, 'code', SYS_STATUS_ERROR_UNKNOW),
      ];
    }

    return $ret;
  }

  public function activeTeacher($teacherId, $active)
  {
    $activeTeacherData = $this->teacherManager->activeTeacher($teacherId, $active);
    try {
      $this->checkServiceResult($activeTeacherData, 'TeacherService');
      $teacherInfo = array_get($activeTeacherData, 'data.teacher_info');
      $ret = [
        'code' => SYS_STATUS_OK,
        'data' => [
          'teacher_info' => $teacherInfo,
        ],
      ];
    } catch (Exception $e) {
      $ret = [
        'code' => array_get($activeTeacherData, 'code', SYS_STATUS_ERROR_UNKNOW),
      ];
    }

    return $ret;
  }

  public function uploadIconImage()
  {
    if (!$this->request->hasFile('image')) {
      abort(400);
    }
    $path = $this->request->image->store('public/head_icon');

    $ret = [
      'code' => SYS_STATUS_OK,
      'data' => [
        'path' => config('app.cdn.base_url').Storage::url($path),
      ],
    ];

    return $ret;
  }

  public function uploadCoverImage()
  {
    if (!$this->request->hasFile('image')) {
      abort(400);
    }
    $path = $this->request->image->store('public/banner');

    $ret = [
      'code' => SYS_STATUS_OK,
      'data' => [
        'path' => config('app.cdn.base_url').Storage::url($path),
      ],
    ];

    return $ret;
  }
}