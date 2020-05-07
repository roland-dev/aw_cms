<?php
namespace Matrix\Http\Controllers;

use Illuminate\Http\Request;
use Matrix\Contracts\KitReportManager;
use Matrix\Contracts\OperateLogContract;
use Matrix\Exceptions\MatrixException;
use Exception;
use Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class KitReportController extends Controller
{
  private $request;
  private $kitReportManager;
  private $operateLog;

  public function __construct(Request $request, KitReportManager $kitReportManager, OperateLogContract $operateLog)
  {
    $this->request = $request;
    $this->kitReportManager = $kitReportManager;
    $this->operateLog = $operateLog;
  }

  public function getKits()
  {
    $kits = $this->kitReportManager->getKits();
    $ret = [
      'code' => SYS_STATUS_OK,
      'data' => [
        'kits' => $kits,
      ],
    ];
    return $ret;
  }

  public function getPublishStatus()
  {
    $publishStatus = $this->kitReportManager->getPublishStatus();

    $ret = [
      'code' => SYS_STATUS_OK,
      'data' => [
        'publish_status' => $publishStatus,
      ],
    ];
    return $ret;
  }

  public function getValidStatus()
  {
    $validStatus = $this->kitReportManager->getValidStatus();

    $ret = [
      'code' => SYS_STATUS_OK,
      'data' => [
        'valid_status' => $validStatus,
      ],
    ];
    return $ret;
  }

  public function search()
  {
    $credentials = $this->request->validate([
      'page_no' => 'nullable|integer',
      'page_size' => 'nullable|integer',
      'title' => 'nullable|string',
      'kit_code' => 'nullable|string',
      'valid_status' => 'nullable|integer',
      'publish' => 'nullable|integer',
    ]);

    try {
      $pageNo = array_get($credentials, 'page_no', 1);
      $pageSize = array_get($credentials, 'page_size', 10);

      $cond = [
        'title' => (string)array_get($credentials, 'title'),
        'kit_code' => (string)array_get($credentials, 'kit_code'),
        'valid_status' => array_get($credentials, 'valid_status'),
        'publish' => array_get($credentials, 'publish'),
      ];

      $kitReportList = $this->kitReportManager->getKitReportList($pageNo, $pageSize, $cond);
      $kitReportCnt = $this->kitReportManager->getKitReportCnt($cond);
      $isTeacher = $this->kitReportManager->isTeacher();
      $modifyPermission = $this->kitReportManager->getModifyPermission();

      $ret = [
        'code' => SYS_STATUS_OK,
        'msg' => 'success',
        'data' => [
          'kit_report_list' => $kitReportList,
          'kit_report_cnt' => $kitReportCnt,
          'is_teacher_stock_a' => $isTeacher,
          'modify_permission' => $modifyPermission,
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
      'title' => 'required|string',
      'kit_code' => 'required|string',
      'start_at' => 'required|string',
      'end_at' => 'required|string',
      'cover_url' => 'required|string',
      'summary' => 'required|string',
      'format' => 'required|integer',
      'content' => 'nullable|string',
      'url' => 'nullable|string',
    ]);

    try {
      $kitReport = $this->kitReportManager->createKitReport($credentials);
      $this->operateLog->record('create', 'kit_report', $kitReport->id, "用户 ".Auth::user()->name." 创建了一个锦囊报告 {$kitReport}", $this->request->ip(), Auth::user()->id);
      
      $ret = [
        'code' => SYS_STATUS_OK,
        'msg' => 'success',
        'data' => [
          'kit_report' => $kitReport,
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

  public function getKitReportInfo(int $id)
  {
    try {
      $kitReport = $this->kitReportManager->getKitReportInfo($id);

      $ret = [
        'code' => SYS_STATUS_OK,
        'msg' => 'success',
        'data' => [
          'kit_report' => $kitReport
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

  public function update(int $id)
  {
    $credentials = $this->request->validate([
      'title' => 'required|string',
      'kit_code' => 'required|string',
      'start_at' => 'required|string',
      'end_at' => 'required|string',
      'cover_url' => 'required|string',
      'summary' => 'required|string',
      'format' => 'required|integer',
      'content' => 'nullable|string',
      'url' => 'nullable|string',
    ]);

    try {
      $kitReport = $this->kitReportManager->updateKitReport($id, $credentials);
      $this->operateLog->record('update', 'kit_report', $kitReport->id, "用户 ".Auth::user()->name." 修改了一个锦囊报告 {$kitReport}", $this->request->ip(), Auth::user()->id);

      $ret = [
        'code' => SYS_STATUS_OK,
        'msg' => 'success',
        'data' => [
          'kit_report' => $kitReport,
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
      $kitReport = $this->kitReportManager->deleteKitReport($id);
      $this->operateLog->record('delete', 'kit_report', $kitReport->id, "用户 ".Auth::user()->name." 删除了一个锦囊报告 {$kitReport}", $this->request->ip(), Auth::user()->id);

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

  public function uploadCover()
  {
    if (!$this->request->hasFile('image')) {
      abort(400);
    }
    $path = $this->request->image->store('public/kit_report/'.date('Y-m-d'));

    $ret = [
      'code' => SYS_STATUS_OK,
      'data' => [
        'path' => config('app.cdn.base_url').Storage::url($path),
      ],
    ];

    return $ret;
  }

  public function uploadFile()
  {
    if (!$this->request->hasFile('file')) {
      abort(400);
    }
    $path = $this->request->file->store('public/kit_pdf');

    $ret = [
      'code' => SYS_STATUS_OK,
      'data' => [
        'path' => config('app.cdn.base_url').Storage::url($path),
      ],
    ];

    return $ret;
  }

  public function publish(int $id)
  {
    try {
      $scheme = $this->request->server('REQUEST_SCHEME');
      $resData = $this->kitReportManager->publishKitReport($id, $scheme);

      $feed = array_get($resData, 'feed');
      $this->operateLog->record('create', 'feed', $feed->feed_id, "用户 ".Auth::user()->name." 推送了一个锦囊报告", $this->request->ip(), Auth::user()->id);
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
}