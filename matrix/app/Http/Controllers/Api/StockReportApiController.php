<?php

namespace Matrix\Http\Controllers\Api;

use Matrix\Contracts\StockReportManager;
use Illuminate\Http\Request;
use Matrix\Exceptions\StockReportException;
use Exception;
use Log;
use Matrix\Contracts\UcManager;
use Matrix\Exceptions\MatrixException;
use Matrix\Exceptions\UcException;
use Matrix\Models\StockReport;
use Matrix\Models\StockReportCategory;

class StockReportApiController extends Controller
{
  private $request;
  private $stockReportManager;
  private $ucManager;

  public function __construct(Request $request, StockReportManager $stockReportManager, UcManager $ucManager)
  {
    $this->request = $request;
    $this->stockReportManager = $stockReportManager;
    $this->ucManager = $ucManager;
  }

  public function getStockReportList($stockCode, $categoryId)
  {
    $token = $this->request->cookie('token');
    if (empty($token)) {
      $ret = [
        'code' => CMS_API_COOKIE_PARAMETER_NOT_FOUND,
        'msg' => '$._COOKIE.token not found in request',
      ];
      return $this->respAdapter($ret);
    }
    $accessCodeList = [];

    try {
      $accessCodeList = $this->ucManager->getAccessCodeByToken($token);
    } catch (UcException $e) {
      Log::error($e->getMessage(), [$e]);
      $ret = [
        'code' => CMS_API_COOKIE_PARAMETER_INVALID,
        'msg' => 'Expired token',
        'errors' => null
      ];
      return $this->respAdapter($ret);
    } catch (Exception $e) {
      Log::error($e->getMessage(), [$e]);
      $ret = [
        'code' => SYS_STATUS_ERROR_UNKNOW,
        'msg' => '未知错误'
      ];
      return $ret;
    }

    try {
      $categories = $this->stockReportManager->getReportCategoryOfCategoryId($categoryId);
      if (in_array(StockReport::STOCK_REPORT_ACCESS_LEVEL, $accessCodeList)) {
        $credentials = $this->request->validate([
          'startID' => 'nullable',
          'limit' => 'nullable',
        ]);
        $pageNo = (int)array_get($credentials, 'startID', 0);
        $pageSize = (int)array_get($credentials, 'limit') === 0 ? 20 : (int)array_get($credentials, 'limit');
        $cond = [
          'stock_code' => $stockCode,
          'category_id' => $categoryId,
        ];
        $reportList = $this->stockReportManager->getStockReportListOfApi($pageNo, $pageSize, $cond, 'v1');
        foreach ($reportList as &$report) {
          $report['report_url'] = config("app.url") . sprintf('/api/v2/client/stock_report/%s', array_get($report, 'report_id'));
        }
      }
      $ret = [
        'code' => SYS_STATUS_OK,
        'msg' => 'success',
        'data' => [
          'categories' => $categories,
        ],
        'error' => null,
      ];
      if (!empty($reportList)) {
        $ret['data']['reports'] = $reportList;
      }
    } catch (StockReportException $e) {
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

  public function getStockReportListByDate()
  {
    $sessionId = $this->request->header('X-SessionId');
    if (empty($sessionId)) {
      $sessionId = $this->request->cookie('X-SessionId');
    }
    if (empty($sessionId)) {
      abort(401);
    }

    $credentials = $this->request->validate([
      'stock_code' => 'required',
      'start_date' => 'nullable',
      'end_date' => 'nullable',
      'category_id' => 'nullable',
    ]);

    $categoryId = array_get($credentials, 'category_id', StockReportCategory::GENZONGBAOGAO);
    $credentials['category_id'] = $categoryId;

    try {
      $categories = $this->stockReportManager->getReportCategoryOfCategoryId($categoryId);
      $reports = $this->stockReportManager->getStockReportListOfClient($credentials);
      foreach ($reports as &$report) {
        $report['report_url'] = config("app.url") . sprintf('/api/v2/client/stock_report/%s', array_get($report, 'report_id'));
      }
      $ret = [
        'code' => SYS_STATUS_OK,
        'msg' => 'success',
        'data' => [
          'categories' => $categories,
          'reports' => $reports,
        ],
      ];
    } catch (StockReportException $e) {
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
}