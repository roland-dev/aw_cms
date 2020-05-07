<?php

namespace Matrix\Http\Controllers\Client;

use Matrix\Contracts\KitReportManager;
use Matrix\Contracts\UcManager;
use Exception;
use Log;
use Matrix\Exceptions\UcException;
use Matrix\Exceptions\MatrixException;
use Matrix\Contracts\UserManager;
use Matrix\Contracts\UserGroupManager;
use Matrix\Models\UserGroup;
use Matrix\Contracts\InteractionContract;
use Illuminate\Http\Request;
use Matrix\Contracts\KitManager;

class KitReportController extends Controller
{
  const KIT_REPORT_TYPE = 'kit_report';

  private $kitReportManager;
  private $ucManager;
  private $userManager;
  private $userGroupManager;
  private $interactionContract;
  private $request;
  private $kitManager;

  public function __construct(KitReportManager $kitReportManager, UcManager $ucManager, UserManager $userManager, UserGroupManager $userGroupManager, InteractionContract $interactionContract, Request $request, KitManager $kitManager)
  {
    $this->kitReportManager = $kitReportManager;
    $this->ucManager = $ucManager;
    $this->userManager = $userManager;
    $this->userGroupManager = $userGroupManager;
    $this->interactionContract = $interactionContract;
    $this->request = $request;
    $this->kitManager = $kitManager;
  }

  protected function fitUrl(string $url)
  {
    if (empty($url)) {
      return '';
    }
    if (strpos($url, 'http') === 0) { // http or https
        return $url;
    } elseif (strpos($url, '//') === 0) { // //www.zhongyingtougu.com/
        return $this->request->server('REQUEST_SCHEME').":$url";
    } elseif (strpos($url, '/files/') === 0) { // //www.zhongyingtougu.com/
        return substr_replace($url, config('cdn.cdn_url'), 0, 6);
    } else {
        return sprintf('%s%s', config('app.h5_api_url'), $url);
    }
  }

  public function getKitReportInfo($reportId)
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
      $accessCodeList = $this->ucManager->getAccessCodeBySessionId($sessionId, 'default', true);
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
        'code' => $e->getCode(),
        'msg' => $e->getMessage()
      ];
      return $ret;
    }

    try {
      $kitReport = $this->kitReportManager->getKitReportInfoByKitReportId($reportId);
    } catch (MatrixException $e) {
      Log::error($e->getMessage(), [$e]);
      $ret = [
        'code' => $e->getCode(),
        'msg' => $e->getMessage()
      ];
      return $ret;
    } catch (Exception $e) {
      Log::error($e->getMessage(), [$e]);
      $ret = [
        'code' => $e->getCode(),
        'msg' => $e->getMessage()
      ];
      return $ret;
    }

    if (!in_array(array_get($kitReport, 'service_key'), $accessCodeList)) {
      $ret = [
        'code' => 403,
      ];
      return view('errors.403', $ret);
    }

    try {
      $currentUserInfo = $this->ucManager->getUserInfoBySessionId($sessionId, 'default', true);
      $currentOpenId = (string)array_get($currentUserInfo, 'data.user.openId');
    } catch (MatrixException $e) {
      Log::error($e->getMessage(), [$e]);

      $ret = [
        'code' => $e->getCode(),
        'msg' => $e->getMessage()
      ];

      if ($ret['code'] === CMS_API_X_SESSIONID_NOT_FOUND) {
        $ret['callback_url'] = $loginUrl;
      }

      return view('errors.403', $ret);
    } catch (Exception $e) {
      Log::error($e->getMessage(), [$e]);
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
        Log::info($e->getMessage(), [$e]);
        $ret = [
          'code' => $e->getCode(),
          'msg' => $e->getMessage()
        ];
        return $ret;
      } catch (Exception $e) {
        Log::error($e->getMessage(), [$e]);
        $ret = [
          'code' => SYS_STATUS_ERROR_UNKNOW,
          'msg' => $e->getMessage()
        ];
        return $ret;
      }
    }

    try {

      $kitReportId = array_get($kitReport, 'report_id', '');

      $isLike = $this->interactionContract->getLikeRecord($kitReportId, self::KIT_REPORT_TYPE, $currentOpenId, $udid);

      $likeSum = $this->interactionContract->getLikeSum($kitReportId, self::KIT_REPORT_TYPE);

      $kitReport['session_id'] = empty($sessionId) ? '' : $sessionId;
      $kitReport['is_forward_teacher'] = $isTeacher;
      $kitReport['type'] = self::KIT_REPORT_TYPE;
      $kitReport['forward_teacher_id'] = empty($isTeacher) ? 0 : $teacherUserId;
      $kitReport['is_reply'] = isset($userMobile) && !empty($userMobile) ? 1 : 0;
      $kitReport['forward_open_id'] = empty($isTeacher) ? 0 : $currentOpenId;
      $kitReport['is_like'] = array_get($isLike, 'data.like');
      $likeSum = (int)array_get($likeSum, 'data.statisticInfo.like_sum');
      $likeSum = $likeSum > 999 ? '999+' : $likeSum;
      $kitReport['like_sum'] = $likeSum;

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
        'msg' => $e->getMessage()
      ];
    } catch (Exception $e) {
      Log::error($e->getMessage(), [$e]);
      $ret = [
        'code' => SYS_STATUS_ERROR_UNKNOW,
        'msg' => '未知错误'
      ];
      return $ret;
    }
    // return$ret;
    return view('kitreport.detail', $ret);
  }

  /**
   * @SWG\GET(
   *    path="/api/v2/client/kit/report",
   *    tags={"Kit"},
   *    description="请求该接口需要X-SessionId",
   *    operationId="getkits",
   *    produces={"application/json"},
   *    consumes={"application/json"},
   *    summary="获取 锦囊列表数据 接口",
   *    @SWG\Parameter(
   *      in="query",
   *      name="teacher_userid",
   *      type="string",
   *      description="牛人老师qy_userid",
   *      required=true
   *    ),
   *    @SWG\Response(
   *      response=200,
   *      description="OK",
   *      @SWG\Schema(
   *        ref="#/definitions/ApiResponseVo<Kit>"
   *      )
   *    )
   * )
   */
  public function getkits()
  {
    $credentials = $this->request->validate([
      'teacher_userid' => 'required|string'
    ]);

    $sessionId = $this->request->header('X-SessionId');
    if (empty($sessionId)) {
      abort(401);
    }

    $accessCodeList = [];

    try {
      $accessCodeList = $this->ucManager->getAccessCodeBySessionId($sessionId, 'default', true);
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
        'msg' => $e->getMessage()
      ];
      return $ret;
    }

    try {
      $teacherUserInfoData = $this->userManager->getUserByEnterpriseUserId(array_get($credentials, 'teacher_userid'));
      $code = array_get($teacherUserInfoData, 'code');
      if ($code === USER_NOT_FOUND) {
        $ret = [
          'code' => CLIENT_TEACHER_NOT
        ];
        return $ret;
      } elseif($code != SYS_STATUS_OK) {
        $ret = [
          'code' => SYS_STATUS_ERROR_UNKNOW
        ];

        return $ret;
      }
      $teacherUserId = array_get($teacherUserInfoData, 'data.id');

      $kits = $this->kitManager->getKitsOfClient($teacherUserId);

      $kitsOfOutBuy = $this->kitManager->getKitOfOutBuy($teacherUserId);

      foreach ($kitsOfOutBuy as $kitOfOutBuy) {
        $kitServiceKey = array_get($kitOfOutBuy, 'service_key');
        if (in_array($kitServiceKey, $accessCodeList)) {
          $kits[] = $kitOfOutBuy;
        }
      }

      foreach ($kits as &$kit) {
        $kitServiceKey = array_get($kit, 'service_key');
        $isBought = 0;
        if (in_array($kitServiceKey, $accessCodeList)) {
          $isBought = 1;
        }
        $kit['is_bought'] = $isBought;

        $kit['cover_url'] = $this->fitUrl($kit['cover_url']);
        $reports = array_get($kit, 'reports');
        foreach ($reports as &$report) {
          $report['cover_url'] = $this->fitUrl($report['cover_url']);
        }
        $kit['reports'] = $reports;
      }

      $ret = [
        'code' => SYS_STATUS_OK,
        'msg' => 'success',
        'data' => [
          'kits' => $kits,
        ]
      ];
    } catch (MatrixException $e) {
      Log::error($e->getMessage(), [$e]);
      $ret = [
        'code' => $e->getCode(),
        'msg' => $e->getMessage()
      ];
    }  catch (Exception $e) {
      Log::error($e->getMessage(), [$e]);
      $ret = [
        'code' => SYS_STATUS_ERROR_UNKNOW,
        'msg' => '未知错误'
      ];
    }
    
    return $ret;
  }

  public function getKitInfo($kitCode)
  {
    try {
      $kitInfo = $this->kitManager->getKitInfoByKitCode($kitCode);

      $kitInfo['cover_url'] = $this->fitUrl($kitInfo['cover_url']);

      $ret = [
        'code' => SYS_STATUS_OK,
        'msg' => 'success',
        'data' => $kitInfo
      ];
    } catch(MatrixException $e) {
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

  public function getKitDetailInfo($kitCode)
  {
    try {
      $kitInfo = $this->kitManager->getKitInfoByKitCode($kitCode);

      $ret = [
        'code' => SYS_STATUS_OK,
        'msg' => 'success',
        'data' => $kitInfo
      ];
    } catch (MatrixException $e) {
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
        'data' => '未知错误'
      ];
      return $ret;
    }

    return view('kit.detail', $ret);
  }
}