<?php

namespace Matrix\Http\Controllers;

use Illuminate\Http\Request;
use Matrix\Contracts\StockReportManager;
use Matrix\Exceptions\MatrixException;
use Log;
use Exception;
use Illuminate\Support\Facades\Auth;
use Matrix\Contracts\OperateLogContract;
use Illuminate\Support\Facades\Storage;

class StockReportController extends Controller
{
  //
  private $request;
  private $stockReportManager;
  private $operateLog;

  public function __construct(Request $request, StockReportManager $stockReportManager, OperateLogContract $operateLog)
  {
    $this->request = $request;
    $this->stockReportManager = $stockReportManager;
    $this->operateLog = $operateLog;
  }

  public function getReportCategories()
  {
    try {
      $reportCategories = $this->stockReportManager->getReportCategories();

      $ret = [
        'code' => SYS_STATUS_OK,
        'data' => [
          'report_categories' => $reportCategories,
        ],
        'msg' => '',
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

  public function getPublishStatus()
  {
    $publishStatus = $this->stockReportManager->getPublishStatus();
    $ret = [
      'code' => SYS_STATUS_OK,
      'data' => [
        'publish_status' => $publishStatus,
      ],
    ];
    return $ret;
  }

  public function create()
  {
    $credentials = $this->request->validate([
      'category_id' => 'required|integer',
      'stock_code' => 'required|string',
      'stock_name' => 'required|string',
      'report_title' => 'required|string',
      'report_short_title' => 'nullable|string|max:4',
      'report_date' => 'required|string',
      'report_format' => 'required|integer',
      'report_content' => 'nullable|string',
      'report_url' => 'nullable|string',
      'report_summary' => 'required|string'
    ]);

    try {
      $stockReport = $this->stockReportManager->createStockReport($credentials);
      $this->operateLog->record('create', 'stock_report', $stockReport->id, "用户 ".Auth::user()->name." 创建了一个个股报告 {$stockReport}", $this->request->ip(), Auth::user()->id);

      $ret = [
        'code' => SYS_STATUS_OK,
        'msg' => 'success',
        'data' => [
          'stock_report' => $stockReport,
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

  public function search()
  {
    $credentials = $this->request->validate([
      'page_no' => 'nullable|integer',
      'page_size' => 'nullable|integer',
      'report_title' => 'nullable|string',
      'stock_code' => 'nullable|string',
      'category_id' => 'nullable|integer',
      'publish' => 'nullable|integer',
    ]);

    try {
      $pageNo = array_get($credentials, 'page_no', 1);
      $pageSize = array_get($credentials, 'page_size', 10);

      $cond = [
        'report_title' => (string)array_get($credentials, 'report_title'),
        'stock_code' => (string)array_get($credentials, 'stock_code'),
        'category_id' => array_get($credentials, 'category_id'),
        'publish' => array_get($credentials, 'publish'),
      ];

      $stockReportList = $this->stockReportManager->getStockReportList($pageNo, $pageSize, $cond);
      $stockReportCnt = $this->stockReportManager->getStockReportCnt($cond);
      $modifyPermission = $this->stockReportManager->getModifyPermission();

      $ret = [
        'code' => SYS_STATUS_OK,
        'msg' => 'success',
        'data' => [
          'stock_report_list' => $stockReportList,
          'stock_report_cnt' => $stockReportCnt,
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

  public function getStockReportInfo(int $id)
  {
    try {
      $stockReport = $this->stockReportManager->getStockReportInfo($id);

      $ret = [
        'code' => SYS_STATUS_OK,
        'msg' => 'success',
        'data' => [
          'stock_report' => $stockReport
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
      'stock_code' => 'required|string',
      'stock_name' => 'required|string',
      'report_title' => 'required|string',
      'category_id' => 'required|integer',
      'report_short_title' => 'nullable|string|max:4',
      'report_date' => 'required|string',
      'report_format' => 'required|string',
      'report_content' => 'nullable|string',
      'report_url' => 'nullable|string',
      'report_summary' => 'required|string'
    ]);

    try {
      $credentials['creator'] = Auth::user()->id;
      $stockReport = $this->stockReportManager->updateStockReport($id, $credentials);
      $this->operateLog->record('update', 'stock_report', $id, "用户 ".Auth::user()->name." 修改了一个个股报告 {$stockReport}", $this->request->ip(), Auth::user()->id);
      
      $ret = [
        'code' => SYS_STATUS_OK,
        'msg' => 'success',
        'data' => [
          'stock_report' => $stockReport,
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
      $stockReport = $this->stockReportManager->deleteStockReport($id);

      $this->operateLog->record('delete', 'stock_report', $id, "用户 ".Auth::user()->name." 删除了一个个股报告", $this->request->ip(), Auth::user()->id);
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

  public  function publish(int $id)
  {
    try {
      $resData = $this->stockReportManager->publishStockReport($id);

      $feed = array_get($resData, 'feed');
      $this->operateLog->record('create', 'feed', $feed->feed_id, "用户 ".Auth::user()->name." 推送了一个个股报告", $this->request->ip(), Auth::user()->id);
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

  public function upload()
  {
    if (!$this->request->hasFile('file')) {
      abort(400);
    }
    $path = $this->request->file->store('public/stock_report/'.date("Y-m-d"));

    $ret = [
      'code' => SYS_STATUS_OK,
      'data' => [
        'path' => config('app.cdn.base_url').Storage::url($path),
      ],
    ];

    return $ret;
  }
}