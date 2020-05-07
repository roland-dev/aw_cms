<?php
namespace Matrix\Http\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Matrix\Contracts\KitManager;
use Matrix\Contracts\UserGroupManager;
use Matrix\Exceptions\MatrixException;
use Exception;
use Log;
use Matrix\Contracts\TeacherManager;
use Matrix\Contracts\OperateLogContract;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class KitController extends Controller
{
  private $request;
  private $kitManager;
  private $operateLog;
  private $userGroupManager;
  private $teacherManager;

  public function __construct(Request $request, KitManager $kitManager, OperateLogContract $operateLog, UserGroupManager $userGroupManager, TeacherManager $teacherManager)
  {
    $this->request = $request;
    $this->kitManager = $kitManager;
    $this->operateLog = $operateLog;
    $this->userGroupManager = $userGroupManager;
    $this->teacherManager = $teacherManager;
  }

  public function getBuyTypes()
  {
    $buyTypes = $this->kitManager->getBuyTypes();
    $ret = [
      'code' => SYS_STATUS_OK,
      'data' => [
        'buy_types' => $buyTypes,
      ],
    ];
    return $ret;
  }

  public function getBuyStates()
  {
    $buyStates = $this->kitManager->getBuyStates();
    $ret = [
      'code' => SYS_STATUS_OK,
      'data' => [
        'buy_states' => $buyStates,
      ],
    ];
    return $ret;
  }

  /**
   * 获取牛人老师列表
   */
  public function getTeacherList()
  {
    try {
      $teacherList = $this->kitManager->getTeacherList();
      
      $ret = [
        'code' => SYS_STATUS_OK,
        'data' => [
          'teacher_list' => $teacherList,
        ]
      ];
    } catch (MatrixException $e) {
      Log::error($e->getMessage(), [$e]);
      $ret = [
        'code' => $e->getCode(),
        'msg' => $e->getMessage(),
      ];
    } catch (Exception $e) {
      Log::error($e->getMessage(), [$e]);
      $ret =[
        'code' => SYS_STATUS_ERROR_UNKNOW,
        'msg' => '未知错误',
      ];
    }

    return $ret;
  }

  public function search()
  {
    $credentials = $this->request->validate([
      'page_no' => 'nullable|integer',
      'page_size' => 'nullable|integer',
      'name' => 'nullable|string',
      'belong_user_id' => 'nullable|integer',
      'buy_type' => 'nullable|integer',
      'buy_state' => 'nullable|integer',
    ]);

    try {
      $pageNo = array_get($credentials, 'page_no', 1);
      $pageSize = array_get($credentials, 'page_size', 10);

      $cond = [
        'name' => (string)array_get($credentials, 'name'),
        'belong_user_id' => (int)array_get($credentials, 'belong_user_id'),
        'buy_type' => array_get($credentials, 'buy_type'),
        'buy_state' => array_get($credentials, 'buy_state'),
      ];

      $kitList = $this->kitManager->getKitList($pageNo, $pageSize, $cond);
      $kitCnt = $this->kitManager->getKitCnt($cond);
      $isTeacher = $this->kitManager->isTeacher();

      $ret = [
        'code' => SYS_STATUS_OK,
        'msg' => 'success',
        'data' => [
          'kit_list' => $kitList,
          'kit_cnt' => $kitCnt,
          'is_teacher_stock_a' => $isTeacher,
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

  public function create()
  {
    $credentials = $this->request->validate([
      'name' => 'required|string',
      'cover_url' => 'required|string',
      'descript' => 'required|string',
      'buy_type' => 'required|int',
      'buy_state' => 'required|int',
      'sort_num' => 'nullable'
    ]);

    try {
      $respData = $this->kitManager->createKit($credentials);
      $kit = array_get($respData, 'kit');
      $this->operateLog->record('create', 'kit', $kit->id, "用户 ".Auth::user()->name." 创建了一个锦囊 {$kit}", $this->request->ip(), Auth::user()->id);

      $kitCategory = array_get($respData, 'kit_category');
      $this->operateLog->record('create', 'category', array_get($kitCategory, 'id'), "用户 ".Auth::user()->name." 同步创建了一个锦囊栏目 ".json_encode($kitCategory), $this->request->ip(), Auth::user()->id);

      $ret = [
        'code' => SYS_STATUS_OK,
        'msg' => 'success',
        'data' => [
          'kit' => $kit,
          'kit_category' => $kitCategory,
        ]
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
        'msg' => '未知错误'
      ];
    }

    return $ret;
  }

  public function getKitInfo(int $id)
  {
    try {
      $kit = $this->kitManager->getKitInfo($id);

      $ret = [
        'code' => SYS_STATUS_OK,
        'msg' => 'success',
        'data' => [
          'kit' => $kit,
        ]
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

  public function update(int $id)
  {
    $credentials = $this->request->validate([
      'name' => 'required|string',
      'cover_url' => 'required|string',
      'descript' => 'required|string',
      'buy_type' => 'required|int',
      'buy_state' => 'required|int',
      'sort_num' => 'nullable',
    ]);

    try {
      $respData = $this->kitManager->updateKit($id, $credentials);
      $kit = array_get($respData, 'kit');
      $this->operateLog->record('update', 'kit', $kit->id, "用户 ".Auth::user()->name." 修改了一个锦囊 {$kit}", $this->request->ip(), Auth::user()->id);
      
      $kitCategory = array_get($respData, 'kit_category');
      $this->operateLog->record('update', 'category', array_get($kitCategory, 'id'), "用户 ".Auth::user()->name." 同步修改了一个锦囊栏目 ".json_encode($kitCategory), $this->request->ip(), Auth::user()->id);

      $ret = [
        'code' => SYS_STATUS_OK,
        'msg' => 'success',
        'data' => [
          'kit' => $kit,
          'kit_category' => $kitCategory,
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

  public function delete(int $id)
  {
    try {
      $kit = $this->kitManager->deleteKit($id);
      $this->operateLog->record('delete', 'kit', $kit->id, "用户 ".Auth::user()->name." 删除了一个锦囊 {$kit}", $this->request->ip(), Auth::user()->id);

      $ret = [
        'code' => SYS_STATUS_OK,
        'msg' => 'success',
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

  public function uploadCoverImg()
  {
    if (!$this->request->hasFile('image')) {
      abort(400);
    }
    $path = $this->request->image->store('public/kit');

    $ret = [
      'code' => SYS_STATUS_OK,
      'data' => [
        'path' => config('app.cdn.base_url').Storage::url($path),
      ],
    ];

    return $ret;
  }
}