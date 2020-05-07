<?php
namespace Matrix\Services;

use Matrix\Contracts\KitManager;
use Illuminate\Support\Facades\Auth;
use Matrix\Models\UserGroup;
use Matrix\Models\User;
use Matrix\Models\Kit;
use Matrix\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Matrix\Exceptions\KitException;
use Exception;
use Matrix\Exceptions\MatrixException;
use Matrix\Models\KitReport;

class KitService extends BaseService implements KitManager
{
  private $kit;
  private $category;

  public function __construct(Kit $kit, Category $category)
  {
    $this->kit = $kit;
    $this->category = $category;
  }

  public function getBuyTypes()
  {
    return $this->kit->getBuyTypes();
  }

  public function getBuyStates()
  {
    return $this->kit->getBuyStates();
  }

  public function getTeacherList()
  {
    $userId = Auth::user()->id;

    $userIdList = UserGroup::where('code', UserGroup::USER_GROUP_STOCK_A)->pluck('user_id')->toArray();

    if (in_array($userId, $userIdList)) {
      $userList = User::where('id', $userId)->where('active', 1)->get();
    } else {
      $userList = User::whereIn('id', $userIdList)->where('active', 1)->get();
    }

    return $userList;
  }

  public function getKitList(int $pageNo, int $pageSize, array $credentials)
  {
    $cond = [];
    
    foreach ($credentials as $k => $v) {
      if ($k != 'name' && $k != 'belong_user_id' && $v !== "" && $v !== null) {
        $cond[] = [$k, $v];
      }
    }

    $name = array_get($credentials, 'name');
    if (!empty($name)) {
      $cond[] = ['name', 'like', "%$name%"];
    }

    $userId = array_get($credentials, 'belong_user_id');
    if (self::isTeacher()) {
      if (!empty($userId) && $userId !== Auth::user()->id) {
        return [];
      } else {
        $cond[] = ['belong_user_id', Auth::user()->id];
      }
    } else {
      if (!empty($userId)) {
        $cond[] = ['belong_user_id', $userId];
      }
    }

    $kitList = Kit::where($cond)
                    ->orderBy('updated_at', 'desc')
                    ->skip($pageSize * ($pageNo - 1))
                    ->take($pageSize)
                    ->get()
                    ->toArray();

    // 购买类型
    $buyTypesArr = array_column(Kit::BUY_TYPES, 'name', 'status');

    // 锦囊购买状态
    $buyStatesArr = array_column(Kit::BUY_STATE, 'name', 'status');
    
    $userList = User::get()->toArray();
    $userArr = array_column($userList, 'name', 'id');

    foreach ($kitList as &$kit) {
      $kit['belong_user_name'] = $userArr[array_get($kit, 'belong_user_id')];
      $kit['buy_type_name'] = $buyTypesArr[array_get($kit, 'buy_type')];
      $kit['buy_state_name'] = $buyStatesArr[array_get($kit, 'buy_state')];
      $kit['last_modify_user_name'] = $userArr[array_get($kit, 'last_modify_user_id')];
    }

    return $kitList;
  }

  public function getKitCnt(array $credentials)
  {
    $cond = [];

    foreach ($credentials as $k => $v) {
      if ($k != 'name'  && $k != 'belong_user_id' && $v !== "" && $v !== null) {
        $cond[] = [$k, $v];
      }
    }

    $name = array_get($credentials, 'name');
    if (!empty($name)) {
      $cond[] = ['name', 'like', "%$name%"];
    }

    $userId = array_get($credentials, 'belong_user_id');
    if (self::isTeacher()) {
      if (!empty($userId) && $userId !== Auth::user()->id) {
        return 0;
      } else {
        $cond[] = ['belong_user_id', Auth::user()->id];
      }
    } else {
      if (!empty($userId)) {
        $cond[] = ['belong_user_id', $userId];
      }
    }

    $kitCnt = Kit::where($cond)->count();

    return $kitCnt;
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

  public function createKit(array $credentials)
  {
    if (!self::isTeacher()) {
      throw new KitException("当前用户不是牛人老师，不能进行这项操作", KIT_AUTH_NOT_BELONG_USER_GROUP_STOCK_A);
    }

    $connection = config('database.default');
    $prefix = config('database.connections.' . $connection . ".prefix");
    $autoIncrement = DB::select("select auto_increment from information_schema.`TABLES` where table_name = '" . $prefix . "kits'");
    $autoIncrementId = $autoIncrement[0]->auto_increment;

    // 锦囊 code 和 service_code 保持一致 (以及同步创建的category)
    $kitCode = Kit::GENERATE_CODE_PREFIX . $autoIncrementId;

    try {
      DB::beginTransaction();

      $credentials['code'] = $kitCode;
      $credentials['report_id'] = md5(str_random(32));
      $credentials['sort_num'] = (int)array_get($credentials, 'sort_num');
      $credentials['belong_user_id'] = Auth::user()->id;
      $credentials['creator_user_id'] = Auth::user()->id;
      $credentials['last_modify_user_id'] = Auth::user()->id;
      $credentials['service_key'] = $kitCode;
  
      $kit = Kit::create($credentials);
  
      $kitCategoryData = [
        'code' => $kitCode,
        'name' => array_get($credentials, 'name'),
        'summary' => '',
        'service_key' => $kitCode,
        'is_system_generation' => Category::IS_SYSTEM_GENERATION,
      ];
      
      $kitCategory = $this->category->createCategory($kitCategoryData);
  
      if (empty($kitCategory)) {
        throw new MatrixException('创建锦囊对应的栏目失败', SYS_STATUS_ERROR_UNKNOW);
      }

      DB::commit();

      $ret = [
        'kit' => $kit,
        'kit_category' => $kitCategory,
      ];
    } catch (MatrixException $e) {
      DB::rollback();
      throw new MatrixException($e->getMessage(), $e->getCode());
    } catch (Exception $e) {
      DB::rollback();
      throw new Exception('创建锦囊失败', SYS_STATUS_ERROR_UNKNOW);
    }

    return $ret;
  }

  public function getKitInfo(int $id)
  {
    try {
      $kit = Kit::where('id', $id)->firstOrFail()->toArray();

      $belongUser = User::where('id', array_get($kit, 'belong_user_id'))->first()->toArray();
      $kit['belong_teacher_name'] = array_get($belongUser, 'name');
    } catch (ModelNotFoundException $e) {
      throw new KitException("{$id} 这个锦囊不存在", KIT_NOT_FOUND);
    }

    return $kit;
  }

  public function updateKit(int $id, array $credentials)
  {
    if (!self::isTeacher()) {
      throw new KitException("当前用户不是牛人老师，不能进行这项操作", KIT_AUTH_NOT_BELONG_USER_GROUP_STOCK_A);
    }

    DB::beginTransaction();
    try {
      $kit = Kit::where('id', $id)->firstOrFail();

      $userId = Auth::user()->id;
      if ($kit->belong_user_id !== $userId) {
        throw new MatrixException("{$id} 这个锦囊不属于当前操作人，不允许操作", KIT_NOT_BELONG_CURRENT_OPERATOR);
      }

      $credentials['last_modify_user_id'] = Auth::user()->id;

      $params = ['name', 'cover_url', 'descript', 'buy_type', 'buy_state', 'sort_num'];
      $credentials['sort_num'] = (int)array_get($credentials, 'sort_num');
      foreach ($credentials as $k => $v) {
        if (in_array($k, $params)) {
          $kit->{$k} = $v;
        }
      }
      $kit->save();
    } catch (ModelNotFoundException $e) {
      DB::rollback();
      throw new KitException("{$id} 这个锦囊不存在", KIT_NOT_FOUND);
    } catch (MatrixException $e) {
      DB::rollback();
      throw new MatrixException($e->getMessage(), $e->getCode());
    } catch (Exception $e) {
      DB::rollback();
      throw new Exception($e->getMessage(), $e->getCode());
    }

    try {
      $kitCategory = Category::where('code', $kit->code)->firstOrFail();

      $kitCategory->name = array_get($credentials, 'name');

      $kitCategory->save();
    } catch (ModelNotFoundException $e) {
      DB::rollback();
      throw new MatrixException("{$kit->code} 这个锦囊对应的栏目不存在", COLUMN_CATEGORY_NOT_FOUND);
    } catch (Exception $e) {
      DB::rollback();
      throw new Exception($e->getMessage(), $e->getCode());
    }

    DB::commit();
    $ret = [
      'kit' => $kit,
      'kit_category' => $kitCategory,
    ];

    return $ret;
  }

  public function deleteKit(int $id)
  {
    if (!self::isTeacher()) {
      throw new KitException("当前用户不是牛人老师，不能进行这项操作", KIT_AUTH_NOT_BELONG_USER_GROUP_STOCK_A);
    }

    try {
      DB::beginTransaction();
      $kit = Kit::where('id', $id)->firstOrFail();

      $userId = Auth::user()->id;
      if ($kit->belong_user_id !== $userId) {
        throw new MartrixException("{$id} 这个锦囊不属于当前操作人，不允许操作", KIT_NOT_BELONG_CURRENT_OPERATOR);
      }

      $kit->delete();
      KitReport::where('kit_code', $kit->code)->delete();
      DB::commit();
    } catch (ModelNotFoundException $e) {
      DB::rollback();
      throw new KitException("{$id} 这个锦囊不存在", KIT_NOT_FOUND);
    } catch (Exception $e) {
      DB::rollback();
      throw new Exception("未知错误", SYS_STATUS_ERROR_UNKNOW);
    }

    return $kit;
  }

  public function getKitsOfClient(int $teacherUserId)
  {
    $kitList = Kit::select('code', 'name', 'cover_url', 'descript', 'service_key')
                  ->where('buy_type', Kit::BUY_TYPER_APP_CAN_BUY)
                  ->where('buy_state', Kit::BUY_STATE_CAN_BUY)
                  ->where('belong_user_id', $teacherUserId)
                  ->orderBy('sort_num', 'desc')
                  ->orderBy('created_at', 'asc')
                  ->get()
                  ->toArray();
    foreach ($kitList as &$kit) {
      $kit['kit_detail_url'] = config("app.url") . sprintf('/api/v2/client/kit/detail/%s', array_get($kit, 'code'));
    }

    $kitCodeList = array_column($kitList, 'code');

    $kitReportList = KitReport::select('report_id', 'title', 'kit_code', 'start_at', 'end_at', 'cover_url', 'summary', 'url')
                    ->whereIn('kit_code', $kitCodeList)
                    ->where('start_at', '<=', date('Y-m-d H:i:s'))
                    ->where('end_at', '>=', date('Y-m-d H:i:s'))
                    ->where('publish', KitReport::PUBLISH_SUCCESS)
                    ->orderBy('start_at', 'desc')
                    ->orderBy('created_at', 'asc')
                    ->get()
                    ->toArray();

    foreach ($kitReportList as &$kitReport) {
      $kitReport['detail_id'] = array_get($kitReport, 'report_id');
      $kitReport['category_key'] = array_get($kitReport, 'kit_code');
      $kitReport['url'] = config("app.url") . sprintf('/api/v2/client/kit_report/%s', array_get($kitReport, 'report_id'));
    }

    $kitReportArray = self::formatKitReport($kitReportList, 'kit_code', $kitCodeList);

    foreach ($kitList as &$kit) {
      $kit['category_key'] = array_get($kit, 'code');
      $kitCode = array_get($kit, 'code');
      $kit['reports'] = array_get($kitReportArray, $kitCode);
    }

    return $kitList;
  }

  private function formatKitReport(array $array, string $paramsName , array $paramsValueArray)
  {
    $result = [];

    foreach ($paramsValueArray as $value) {
      $result[$value ] = [];
    }

    foreach ($array as $item) {
      $itemParamsValue = array_get($item, $paramsName);
      $result[$itemParamsValue][] = $item;
    }

    return $result;
  }

  public function getKitInfoByKitCode(string $kitCode)
  {
    try {
      $kit = Kit::select('id', 'code', 'name', 'cover_url', 'descript', 'service_key', 'belong_user_id', 'buy_type', 'buy_state')
            ->where('code', $kitCode)
            ->firstOrFail()
            ->toArray();
    } catch (ModelNotFoundException $e) {
      throw new KitException("{$kitCode} 这个锦囊不存在", KIT_NOT_FOUND);
    }

    return $kit;
  }

  public function getKitOfOutBuy(int $teacherUserId)
  {
    $kitList = Kit::select('code', 'name', 'cover_url', 'descript', 'service_key')
                  ->where('buy_type', Kit::BUY_TYPER_APP_NOT_CAN_BUY)
                  ->where('buy_state', Kit::BUY_STATE_CAN_BUY)
                  ->where('belong_user_id', $teacherUserId)
                  ->orderBy('sort_num', 'desc')
                  ->orderBy('created_at', 'asc')
                  ->get()
                  ->toArray();

    $kitCodeList = array_column($kitList, 'code');

    foreach ($kitList as &$kit) {
      $kit['kit_detail_url'] = config("app.url") . sprintf('/api/v2/client/kit/detail/%s', array_get($kit, 'code'));
    }

    $kitReportList = KitReport::select('report_id', 'title', 'kit_code', 'start_at', 'end_at', 'cover_url', 'summary', 'url')
                    ->whereIn('kit_code', $kitCodeList)
                    ->where('start_at', '<=', date('Y-m-d H:i:s'))
                    ->where('end_at', '>=', date('Y-m-d H:i:s'))
                    ->where('publish', KitReport::PUBLISH_SUCCESS)
                    ->orderBy('start_at', 'desc')
                    ->orderBy('created_at', 'asc')
                    ->get()
                    ->toArray();

    foreach ($kitReportList as &$kitReport) {
      $kitReport['detail_id'] = array_get($kitReport, 'report_id');
      $kitReport['category_key'] = array_get($kitReport, 'kit_code');
      $kitReport['url'] = config("app.url") . sprintf('/api/v2/client/kit_report/%s', array_get($kitReport, 'report_id'));
    }

    $kitReportArray = self::formatKitReport($kitReportList, 'kit_code', $kitCodeList);

    foreach ($kitList as &$kit) {
      $kit['category_key'] = array_get($kit, 'code');
      $kitCode = array_get($kit, 'code');
      $kit['reports'] = array_get($kitReportArray, $kitCode);
    }

    return $kitList;
  }
}