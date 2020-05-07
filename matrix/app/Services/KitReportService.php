<?php
namespace Matrix\Services;

use DateTime;
use Matrix\Services\BaseService;
use Matrix\Contracts\KitReportManager;
use Matrix\Models\KitReport;
use Matrix\Models\Feed;
use Matrix\Models\Grant;
use Illuminate\Support\Facades\Auth;
use Matrix\Models\Kit;
use Matrix\Models\UserGroup;
use Matrix\Exceptions\MatrixException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Matrix\Exceptions\KitReportException;
use Matrix\Models\User;
use Illuminate\Support\Facades\DB;
use Matrix\Exceptions\KitException;
use Matrix\Models\Ucenter;

class KitReportService extends BaseService implements KitReportManager
{
  private $kitReport;
  private $feed;
  private $grant;

  public function __construct(KitReport $kitReport, Feed $feed, Grant $grant)
  {
    $this->kitReport = $kitReport;
    $this->feed = $feed;
    $this->grant = $grant;
  }

  
  public function getKits()
  {
    $cond = [];

    if (self::isTeacher()) {
      $cond[] = ['belong_user_id', Auth::user()->id];
    }

    $kits = Kit::select('code', 'name')->where($cond)->get()->toArray();
    
    return $kits;
  }

  public function getPublishStatus()
  {
    $pushlishStatus = $this->kitReport->getPublishStatus();
    return $pushlishStatus;
  }

  public function getValidStatus()
  {
    $validStatus = $this->kitReport->getValidStatus();
    return $validStatus;
  }

  public function isTeacher()
  {
    $result = false;

    $userId = Auth::user()->id;

    $userIdList = UserGroup::where('code', UserGroup::USER_GROUP_STOCK_A)->pluck('user_id')->toArray();

    if (in_array($userId, $userIdList)) {
      $result = true;
    }

    return $result;
  }

  public function getKitReportList(int $pageNo, int $pageSize, array $credentials)
  {
    $cond = [];

    foreach ($credentials as $k => $v) {
      if ($k !== 'title' && $k !== 'valid_status' && $v !== "" && $v !== null) {
        $cond[] = [$k, $v];
      }
    }

    $kitReportTitle = array_get($credentials, 'title');
    if (!empty($kitReportTitle)) {
      $cond[] = ['title', 'like', "%$kitReportTitle%"];
    }

    $kitReport = KitReport::where($cond);
    $kitReportValidStatus = array_get($credentials, 'valid_status');
    if ($kitReportValidStatus !== "" && $kitReportValidStatus !== null) {
      if ((int)$kitReportValidStatus === KitReport::KIT_REPORT_VALID) {
        $kitReport->where('start_at', '<=', date('Y-m-d H:i:s'))
                  ->where('end_at', '>=', date('Y-m-d H:i:s'));
      } else if ((int)$kitReportValidStatus === KitReport::KIT_REPORT_INVALID) {
        $kitReport->where(function ($query) {
          $query->where('start_at', '>', date('Y-m-d H:i:s'))
            ->orWhere('end_at', '<', date('Y-m-d H:i:s'));
        });
      }
    }

    if (self::isTeacher()) {
      $userId = Auth::user()->id;
      $kitList = Kit::where('belong_user_id', $userId)->get()->toArray();
      $kitCodeList = array_column($kitList, 'code');
      $kitReport->whereIn('kit_code', $kitCodeList);
    }

    $kitReportList = $kitReport->orderBy('updated_at', 'desc')
                                ->skip($pageSize * ($pageNo - 1))
                                ->take($pageSize)
                                ->get()
                                ->toArray();
    

    $kitList = Kit::get()->toArray();
    $kitArr = array_column($kitList, 'name', 'code');

    $publishStatusArr = array_column(KitReport::PUBLISH_STATUS, 'name', 'status');

    $userList = User::get()->toArray();
    $userArr = array_column($userList, 'name', 'id');

    $validStatusArr = array_column(KitReport::VALID_STATUS, 'name', 'status');

    foreach ($kitReportList as &$kitReport) {
      $kitReport['kit_name'] = array_get($kitArr, array_get($kitReport, 'kit_code'));
      $kitReport['publish_status_name'] = $publishStatusArr[array_get($kitReport, 'publish')];
      $kitReport['last_modify_user_name'] = $userArr[array_get($kitReport, 'last_modify_user_id')];
      if (strtotime(array_get($kitReport, 'start_at')) <= time() && time() <= strtotime(array_get($kitReport, 'end_at'))) {
        $kitReport['valid_status'] = KitReport::KIT_REPORT_VALID;
        $kitReport['valid_status_name'] = $validStatusArr[KitReport::KIT_REPORT_VALID];
      } else {
        $kitReport['valid_status'] = KitReport::KIT_REPORT_INVALID;
        $kitReport['valid_status_name'] = $validStatusArr[KitReport::KIT_REPORT_INVALID];
      }
    }

    return $kitReportList;
  }

  public function getKitReportCnt(array $credentials)
  {
    $cond = [];

    foreach ($credentials as $k => $v) {
      if ($k !== 'title' && $k !== 'valid_status' && $v !== "" && $v !== null) {
        $cond[] = [$k, $v];
      }
    }

    $kitReportTitle = array_get($credentials, 'title');
    if (!empty($kitReportTitle)) {
      $cond[] = ['title', 'like', "%$kitReportTitle%"];
    }

    $kitReport = KitReport::where($cond);
    $kitReportValidStatus = array_get($credentials, 'valid_status');
    if ($kitReportValidStatus !== "" && $kitReportValidStatus !== null) {
      if ((int)$kitReportValidStatus === KitReport::KIT_REPORT_VALID) {
        $kitReport->where('start_at', '<=', date('Y-m-d H:i:s'))
                  ->where('end_at', '>=', date('Y-m-d H:i:s'));
      } else if ((int)$kitReportValidStatus === KitReport::KIT_REPORT_INVALID) {
        $kitReport->where('start_at', '>', date('Y-m-d H:i:s'))
                  ->orWhere('end_at', '<', date('Y-m-d H:i:s'));
      }
    }

    if (self::isTeacher()) {
      $userId = Auth::user()->id;
      $kitList = Kit::where('belong_user_id', $userId)->get()->toArray();
      $kitCodeList = array_column($kitList, 'code');
      $kitReport->whereIn('kit_code', $kitCodeList);
    }

    $kitReportCnt = $kitReport->count();

    return $kitReportCnt;
  }

  public function getModifyPermission()
  {
    $result = false;

    $grantedList = $this->grant->getOneGrantedList(Auth::user()->id);
    $grantedCodeList = array_column($grantedList, 'permission_code');
    if (in_array(KitReport::PUBLISHED_UPDATE_PERMISSION_CODE, $grantedCodeList)) {
      $result = true;
    }

    return $result;
  }

  public function createKitReport(array $credentials)
  {
    if (!self::isTeacher()) {
      throw new KitReportException("当前用户不是牛人老师，不能进行这项操作", KIT_REPORT_AUTH_NOT_BELONG_USER_GROUP_STOCK_A);
    }

    $userId = Auth::user()->id;
    $kitCode = array_get($credentials, 'kit_code');
    try {
      $kit = Kit::where('code', $kitCode)->firstOrFail()->toArray();
      if (array_get($kit, 'belong_user_id') !== $userId) {
        throw new KitReportException("所选锦囊不属于当前操作人", KIT_REPORT_KIT_CODE_BELONG_USER_NOT_MATCHING);
      }
    } catch (ModelNotFoundException $e) {
      throw new KitReportException("锦囊Code不存在", KIT_REPORT_KIR_CODE_NOT_FOUND);
    }

    $credentials['content'] = (string)array_get($credentials, 'content');
    $credentials['report_id'] = md5(str_random(32));
    $credentials['creator_user_id'] = $userId;
    $credentials['last_modify_user_id'] = $userId;

    $kitReport = KitReport::create($credentials);
    
    return $kitReport;
  }

  public function getKitReportInfo(int $id)
  {
    try {
      $kitReport = KitReport::where('id', $id)->firstOrFail()->toArray();

      $creatorUser = User::where('id', array_get($kitReport, 'creator_user_id'))->first()->toArray();
      $kitReport['creator_user_name'] = array_get($creatorUser, 'name');
    } catch (ModelNotFoundException $e) {
      throw new KitReportException("{$id} 这个锦囊报告不存在", KIT_REPORT_NOT_FOUND);
    }

    try {
      $kitCode = array_get($kitReport, 'kit_code');
      $kit = Kit::where('code', $kitCode)->firstOrFail()->toArray();

      $belongUserId = array_get($kit, 'belong_user_id');
      $belongUser = User::where('id', $belongUserId)->first()->toArray();
      $kitReport['belong_user_id'] = $belongUserId;
      $kitReport['belong_user_name'] = array_get($belongUser, 'name');
      $kitReport['belong_user_icon'] = array_get($belongUser, 'icon_url');
    } catch (ModelNotFoundException $e) {
      throw new KitReportException("{$kitCode} 这个锦囊不存在", KIT_NOT_FOUND);
    }

    return $kitReport;
  }

  public function updateKitReport(int $id, array $credentials)
  {
    try {
      $kitReport = KitReport::where('id', $id)->firstOrFail();
    } catch (ModelNotFoundException $e) {
      throw new KitReportException("{$id} 这个锦囊报告不存在", KIT_REPORT_NOT_FOUND);
    }

    

    if ($kitReport->publish === KitReport::PUBLISH_SUCCESS) {
      $grantedList = $this->grant->getOneGrantedList(Auth::user()->id);
      $grantedCodeList = array_column($grantedList, 'permission_code');
      if (!in_array(KitReport::PUBLISHED_UPDATE_PERMISSION_CODE, $grantedCodeList)) {
        throw new KitReportException("{$id} 这个锦囊报告已发布，您无权限进行修改");
      }
    } else {
      if (!self::isTeacher()) {
        throw new KitReportException("当前用户不是牛人老师，不能进行这项操作", KIT_REPORT_AUTH_NOT_BELONG_USER_GROUP_STOCK_A);
      }

      $userId = Auth::user()->id;
      $kitCode = $kitReport->kit_code;
      try {
        $oldKit = Kit::where('code', $kitCode)->firstOrFail()->toArray();
        if (!empty($oldKit) && array_get($oldKit, 'belong_user_id') !== $userId) {
          throw new KitReportException("{$id} 这个锦囊报告不属于当前操作人，不允许操作", KIT_REPORT_NOT_BELONG_CURRENT_OPERATOR);
        }
      } catch (ModelNotFoundException $e) {
        throw new KitReportException("操作错误，锦囊不存在或者已删除", KIT_REPORT_KIR_CODE_NOT_FOUND);
      }
      

      $kitCode = array_get($credentials, 'kit_code');
      try {
        $kit = Kit::where('code', $kitCode)->firstOrFail()->toArray();
        if (array_get($kit, 'belong_user_id') !== $userId) {
          throw new KitReportException("所选锦囊不属于当前操作人", KIT_REPORT_KIT_CODE_BELONG_USER_NOT_MATCHING);
        }
      } catch (ModelNotFoundException $e) {
        throw new KitReportException("所选锦囊不存在", KIT_REPORT_KIR_CODE_NOT_FOUND);
      }
    }

    $credentials['last_modify_user_id'] = Auth::user()->id;
    foreach ($credentials as $k => $v) {
      $kitReport->{$k} = $v;
    }
    $kitReport->save();


    return $kitReport;
  }

  public function deleteKitReport(int $id)
  {
    if (!self::isTeacher()) {
      throw new KitReportException("当前用户不是牛人老师，不能进行这项操作", KIT_REPORT_AUTH_NOT_BELONG_USER_GROUP_STOCK_A);
    }

    try {
      $kitReport = KitReport::where('id', $id)->firstOrFail();

      if ($kitReport->publish === KitReport::PUBLISH_SUCCESS) {
        throw new KitReportException("{$id} 这个锦囊报告已发布， 不能进行删除操作", KIT_REPORT_PUBLISHED);
      }

      $userId = Auth::user()->id;
      $kitCode = $kitReport->kit_code;
      try {
        $kit = Kit::where('code', $kitCode)->firstOrFail()->toArray();
        if (array_get($kit, 'belong_user_id') !== $userId) {
          throw new MatrixException("{$id} 这个锦囊报告不属于当前操作人，不允许操作", KIT_REPORT_NOT_BELONG_CURRENT_OPERATOR);
        }
      } catch (ModelNotFoundException $e) {
        throw new MatrixException("锦囊Code不存在", KIT_REPORT_KIR_CODE_NOT_FOUND);
      }

      $kitReport->delete();
    } catch (ModelNotFoundException $e) {
      throw new KitReportException("{$id} 这个锦囊报告不存在", KIT_REPORT_NOT_FOUND);
    }

    return $kitReport;
  }

  public function publishKitReport(int $id, string $scheme = "http")
  {
    try {
      DB::beginTransaction();
      $kitReport = KitReport::where('id', $id)->firstOrFail();
      if ($kitReport->publish === KitReport::PUBLISH_SUCCESS) {
        throw new KitReportException("{$id} 这个锦囊报告已发布", KIT_REPORT_PUBLISHED);
      }

      try {
        $kit = Kit::where('code', $kitReport->kit_code)->firstOrFail();
      } catch (ModelNotFoundException $e) {
        throw new KitException("{$kitReport->kit_code} 这个锦囊不存在", KIT_NOT_FOUND);
      }

      if (new DateTime($kitReport->start_at) > new DateTime()) {
        throw new KitReportException("{$id} 这个锦囊报告未到有效开始时间，不允许进行发布操作", KIT_REPORT_NOT_CAN_PUBLISH);
      }

      $kitBelongUserUcenter = Ucenter::where('user_id', $kit->belong_user_id)->first();
      $creatorUcenter = Ucenter::where('user_id', $kitReport->creator_user_id)->first();
      $kitReportFeed = [
        'feed_owner' => $kit->name,
        'feed_type' => Feed::FEED_TYPR_KIT_REPORT,
        'category_key' => $kit->code,
        'msg_type' => KitReport::KIT_REPORT_FEED_MSG_TYPE,
        'owner_id' => $kitBelongUserUcenter->enterprise_userid,
        'source_id' => $kitReport->report_id,
        'title' => $kitReport->title,
        'summary' => $kitReport->summary,
        'source_url' => sprintf('/api/v2/client/kit_report/%s', array_get($kitReport, 'report_id')),
        'thumb_cdn_url' => strpos($kitReport->cover_url, '//') === 0 ? $scheme . ":$kitReport->cover_url" : $kitReport->cover_url,
        'access_level' => $kit->service_key,
        // 'is_elite' => 0,
        'push_status' => 0,
        'qywx_status' => 0,
        'add_time' => $kitReport->start_at,
        'creator' => $creatorUcenter->enterprise_userid,
      ];

      if (new DateTime($kitReport->start_at) < new DateTime(date('Y-m-d 00:00:00'))) {
        $kitReportFeed['push_status'] = 5;
      }

      $feed = Feed::create($kitReportFeed);
      $kitReport->publish = KitReport::PUBLISH_SUCCESS;
      $kitReport->save();
      DB::commit();
      $ret = [
        'feed' => $feed,
        'kit_report' => $kitReport,
      ];
    } catch (ModelNotFoundException $e) {
      DB::rollback();
      throw new KitReportException("{$id} 这个锦囊报告推送失败", KIT_REPORT_PUSH_ERROR);
    }

    return $ret;
  }

  public function getKitReportInfoByKitReportId(string $reportId)
  {
    try {
      $kitReport = KitReport::withTrashed()->where('report_id', $reportId)->firstOrFail()->toArray();

      $creatorUser = User::where('id', array_get($kitReport, 'creator_user_id'))->first()->toArray();
      $kitReport['creator_user_name'] = array_get($creatorUser, 'name');
    } catch (ModelNotFoundException $e) {
      throw new KitReportException("{$reportId} 这个锦囊报告不存在", KIT_REPORT_NOT_FOUND);
    }

    try {
      $kitCode = array_get($kitReport, 'kit_code');
      $kit = Kit::withTrashed()->where('code', $kitCode)->firstOrFail()->toArray();

      $belongUserId = array_get($kit, 'belong_user_id');
      $belongUser = User::where('id', $belongUserId)->first()->toArray();
      $kitReport['belong_user_id'] = $belongUserId;
      $kitReport['belong_user_name'] = array_get($belongUser, 'name');
      $kitReport['belong_user_icon'] = array_get($belongUser, 'icon_url');
      $kitReport['service_key'] = array_get($kit, 'service_key');
    } catch (ModelNotFoundException $e) {
      throw new KitReportException("{$kitCode} 这个锦囊不存在", KIT_NOT_FOUND);
    }

    return $kitReport;
  }
}