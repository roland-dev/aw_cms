<?php

namespace Matrix\Http\Controllers\Client;

use Matrix\Contracts\StockReportManager;
use Matrix\Exceptions\StockReportException;
use Log;
use Exception;
use Matrix\Models\StockReportCategory;
use Illuminate\Http\Request;
use Matrix\Exceptions\MatrixException;
use Matrix\Contracts\UcManager;
use Matrix\Contracts\UserManager;
use Matrix\Contracts\UserGroupManager;
use Matrix\Models\UserGroup;
use Matrix\Contracts\InteractionContract;
use Matrix\Models\StockReport;

class StockReportController extends Controller
{

  const STOCK_REPORT_TYPE = 'stock_report';

  private $stockReportManager;
  private $ucManager;
  private $userManager;
  private $userGroupManager;
  private $interactionContract;
  private $request;


  public function __construct(StockReportManager $stockReportManager, UcManager $ucManager, UserManager $userManager, UserGroupManager $userGroupManager, InteractionContract $interactionContract, Request $request)
  {
    $this->stockReportManager = $stockReportManager;
    $this->ucManager = $ucManager;
    $this->userManager = $userManager;
    $this->userGroupManager = $userGroupManager;
    $this->interactionContract = $interactionContract;
    $this->request = $request;
  }
  public function getStockReportInfo($reportId)
  {
    $sessionId = '';
    $isTeacher = 0;
    $currentOpenId = '';
    $udid = '';

    $loginUrl = $this->h5WechatAutoLogin($this->request, $this->ucManager);
    if (!empty($loginUrl)) {
      return redirect()->away($loginUrl);
    }

    $sessionId = $this->request->header('X-SessionId');
    if (empty($sessionId)) {
      $sessionId = $this->request->cookie('X-SessionId');
    }

    if (empty($sessionId)) {
      $ret['code'] = CMS_API_X_SESSIONID_NOT_FOUND;
      $ret['data'] = 'Expired X-SessionId';
      return view('errors.401', $ret);
    }

    $accessCodeList = [];

    try {
      $accessCodeList = $this->ucManager->getAccessCodeBySessionId($sessionId);
    } catch (UcException $e) {
      Log::error($e->getMessage(), [$e]);
      $ret = [
        'code' => $e->getCode(),
        'msg' => $e->getMessage()
      ];
      return $ret;
    } catch (Exception $e) {
      Log::error($e->getMessage(), [$e]);
      $ret = [
        'code' => SYS_STATUS_ERROR_UNKNOW,
        'msg' => '未知错误'
      ];
      return $ret;
    }

    if (!in_array(StockReport::STOCK_REPORT_ACCESS_LEVEL, $accessCodeList)) {
      $ret = [
        'code' => 403,
      ];
      return view('errors.403', $ret);
    }

    try {
      $currentUserInfo = $this->ucManager->getUserInfoBySessionId($sessionId);
      $currentOpenId = (string)array_get($currentUserInfo, 'data.user.openId');
    } catch (MatrixException $e) {
      Log::error($e->getMessage(), [$e]);

      $ret = [
        'code' => $e->getCode(),
        'msg' => $e->getMessage(),
      ];

      if ($ret['code'] == CMS_API_X_SESSIONID_NOT_FOUND) {
        $ret['callback_url'] = $loginUrl;
      }

      return view('errors.403', $ret);
    } catch (Exception $e) {
      $ret = [
        'code' => SYS_STATUS_ERROR_UNKNOW,
        'msg' => $e->getMessage(),
      ];

      return $ret;
    }

    $enterpriseUserId = array_get($currentUserInfo, 'data.user.qyUserId');
    $userMobile = array_get($currentUserInfo, 'data.user.mobile');
    
    if (!empty($enterpriseUserId)) {
      try {
        $teacherUserData = $this->userManager->getUserByEnterpriseUserId($enterpriseUserId);

        $teacherUserId = array_get($teacherUserData, 'data.id');

        $teacherUserActive = array_get($teacherUserData, 'data.active');

        if (!empty($teacherUserId) && !empty($teacherUserActive)) {
          $teacherUserListData = $this->userGroupManager->getUserListByUserGroupCode(UserGroup::USER_GROUP_CODE_APPROVED_REPLY);

          $teacherUserList = array_get($teacherUserListData, 'user_list');

          if (!empty($teacherUserList)) {
            
            $userIdList = array_column($teacherUserList, 'id');

            if (in_array($teacherUserId, $userIdList)) {
              $isTeacher = 1;
            }
          }
        }
      } catch (MatrixException $e) {
        Log::info($e->getMessage());
        $ret = [
          'code' => $e->getCode(),
          'msg' => $e->getMessage(),
        ];
        return $ret;
      } catch (Exception $e) {
        $ret = [
          'code' => SYS_STATUS_ERROR_UNKNOW,
          'msg' => $e->getMessage(),
        ];

        return $ret;
      }
    }

    try {
      $stockReport = $this->stockReportManager->getStockReportInfoByStockReportId($reportId);

      $stockReportId = array_get($stockReport, 'report_id', '');

      $isLike = $this->interactionContract->getLikeRecord($stockReportId, self::STOCK_REPORT_TYPE, $currentOpenId, $udid);

      $likeSum = $this->interactionContract->getLikeSum($stockReportId, self::STOCK_REPORT_TYPE);

      $stockReport['session_id'] = empty($sessionId) ? '' : $sessionId;
      $stockReport['is_forward_teacher'] = $isTeacher;
      $stockReport['type'] = self::STOCK_REPORT_TYPE;
      $stockReport['forward_teacher_id'] = empty($isTeacher) ? 0 : $teacherUserId;
      $stockReport['is_reply'] = isset($userMobile) && !empty($userMobile) ? 1 : 0;
      $stockReport['forward_open_id'] = empty($isTeacher) ? 0 : $currentOpenId;
      $stockReport['is_like'] = array_get($isLike, 'data.like');
      $likeSum = (int)array_get($likeSum, 'data.statisticInfo.like_sum');
      $likeSum = $likeSum > 999 ? '999+' : $likeSum;
      $stockReport['like_sum'] = $likeSum;

      $ret = [
        'code' => SYS_STATUS_OK,
        'msg' => 'success',
        'data' => [
          'stock_report' => $stockReport
        ],
      ];
    } catch(MatrixException $e) {
      Log::error($e->getMessage(), [$e]);
      $ret = [
        'code' => $e->getCode(),
        'msg' => $e->getMessage(),
      ];
      return $ret;
    } catch (Exception $e) {
      $ret = [
        'code' => SYS_STATUS_ERROR_UNKNOW,
        'msg' => $e->getMessage(),
      ];
      return $ret;
    }
    
    return view('stockreport.detail', $ret);
  }

  public function getStockReportListByDate()
  {
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

  public function getStockReportList($stockCode, $categoryId)
  {
    $sessionId = $this->request->header('X-SessionId');
    if (empty($sessionId)) {
      $sessionId = $this->request->cookie('X-SessionId');
    }

    if (empty($sessionId)) {
      abort(401);
    }
    $accessCodeList = [];

    try {
      $accessCodeList = $this->ucManager->getAccessCodeBySessionId($sessionId);
    } catch (UcException $e) {
      Log::error($e->getMessage(), [$e]);
      $ret = [
        'code' => $e->getCode(),
        'msg' => $e->getMessage()
      ];
      return $ret;
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
        $reportList = $this->stockReportManager->getStockReportListOfApi($pageNo, $pageSize, $cond);
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
}
