<?php
namespace Matrix\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Matrix\Contracts\ContentGuardContract;
use Matrix\Contracts\DynamicAdManager;
use Matrix\Models\DynamicAd;
use Matrix\Models\DynamicAdTerminal;
use Matrix\Models\Terminal;
use Matrix\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Matrix\Contracts\BossManager;
use Matrix\Exceptions\MatrixException;
use Matrix\Models\ContentGuard;
use Matrix\Models\Feed;
use Matrix\Models\Talkshow;

class DynamicAdService extends BaseService implements DynamicAdManager
{
  private $dynamicAd;
  private $dynamicAdTerminal;
  private $contentGuardContract;
  private $bossManager;

  public function __construct(
    DynamicAd $dynamicAd,
    DynamicAdTerminal $dynamicAdTerminal,
    ContentGuardContract $contentGuardContract,
    BossManager $bossManager
  )
  {
    $this->dynamicAd = $dynamicAd;
    $this->dynamicAdTerminal = $dynamicAdTerminal;
    $this->contentGuardContract = $contentGuardContract;
    $this->bossManager = $bossManager;
    $this->propagandaPageckage = config('packagetype.propaganda_pageckage');
  }

  public function getSourceTypes()
  {
    $sourceTypes = $this->dynamicAd->getSourceTypes();
    return $sourceTypes;
  }

  public function getDynamicAdTerminals()
  {
    $terminals = Terminal::select('code', 'name')->where('is_dynamic_ad', 1)->get()->toArray();
    return $terminals;
  }

  public function createDynamicAd(array $credentials)
  {
    DB::beginTransaction();
    try {
      $credentials['jump_type'] = (string)array_get($credentials, 'jump_type');
      $credentials['jump_params'] = (string)array_get($credentials, 'jump_parmas');
      $credentials['title'] = mb_strlen($credentials['title']) > 30 ? sprintf('%s...', mb_substr($credentials['title'], 0, 30)) : $credentials['title'];
      $dynamicAd = DynamicAd::create($credentials);
      $dynamicAdId = array_get($dynamicAd, 'id');

      $terminalCodes = array_get($credentials, 'terminal_codes');
      $dynamicAdTerminalData = $this->dynamicAdTerminal->createDynamicAdTerminal($dynamicAdId, $terminalCodes);

      $permissionCodes = array_get($credentials, 'permission_codes');
      $packageContentGuards = self::packageContentGuards($permissionCodes, DynamicAd::URI_DYNAMIC_AD_ACCESS, [$dynamicAdId]);
      foreach ($packageContentGuards as $packageContentGuard) {
        $contentGuardData = $this->contentGuardContract->grant($packageContentGuard);
        if (array_get($contentGuardData, 'code') === SYS_STATUS_ERROR_UNKNOW) {
          throw new MatrixException('权限更新失败', SYS_STATUS_ERROR_UNKNOW);
        }
      }
      DB::commit();
    } catch (MatrixException $e) {
      DB::rollback();
      throw new MatrixException($e->getMessage(), $e->getCode());
    } catch (Exception $e) {
      DB::rollback();
      throw new Exception($e->getMessage(), $e->getCode());
    }

    return $dynamicAd;
  }

  public function packageContentGuards(array $packages, string $uri, array $params)
  {
    $result = [];

    foreach ($packages as $package) {
      $item = [
        'service_code' => $package,
        'uri' => $uri,
      ];
      $i = 1;
      foreach ($params as $param) {
        $item['param' . $i] = $param;
        $i ++;
      }
      array_push($result, $item);
    }
    return $result;
  }

  public function getDynamicAdList(int $pageNo, int $pageSize, array $credentials)
  {
    $cond = [];

    foreach ($credentials as $k => $v) {
      if (in_array($k, ['source_type']) && $v !== "" && $v !== null) {
        $cond[] = [$k, $v];
      }
    }

    $dynamicAd = DynamicAd::where($cond);

    $startAt = array_get($credentials, 'start_at');
    if (!empty($startAt)) {
      $dynamicAd->where('start_at', '>=', $startAt);
    }
    $endAt = array_get($credentials, 'end_at');
    if (!empty($endAt)) {
      $dynamicAd->where('end_at', '<=', $endAt);
    }

    $dynamicAdList = $dynamicAd->orderBy('start_at', 'asc')
      ->skip($pageSize * ($pageNo - 1))
      ->take($pageSize)
      ->get()
      ->toArray();
    
    $sourceTypes = $this->dynamicAd->getSourceTypes();
    $sourceTypes = array_column($sourceTypes, 'name', 'code');

    $userIdList = array_column($dynamicAdList, 'last_modify_user_id');
    $userList = User::whereIn('id', $userIdList)->get()->toArray();
    $userList = array_column($userList, 'name', 'id');

    foreach ($dynamicAdList as &$dynamicAd) {
      $dynamicAd['source_type_name'] = array_get($sourceTypes, array_get($dynamicAd, 'source_type'));
      $dynamicAd['last_modify_user_name'] = empty(array_get($userList, array_get($dynamicAd, 'last_modify_user_id'))) ? '系统同步' : array_get($userList, array_get($dynamicAd, 'last_modify_user_id'));
    }

    return $dynamicAdList;
  }

  public function getDynamicAdCnt(array $credentials)
  {
    $cond = [];

    foreach ($credentials as $k => $v) {
      if (in_array($k, ['source_type']) && $v !== "" && $v !== null) {
        $cond[] = [$k, $v];
      }
    }

    $dynamicAd = DynamicAd::where($cond);

    $startAt = array_get($credentials, 'start_at');
    if (!empty($startAt)) {
      $dynamicAd->where('start_at', '>=', $startAt);
    }
    $endAt = array_get($credentials, 'end_at');
    if (!empty($endAt)) {
      $dynamicAd->where('end_at', '<=', $endAt);
    }

    $dynamicAdCnt = $dynamicAd->count();

    return $dynamicAdCnt;
  }

  public function changeActiveStatus(int $dynamicAdId, int $active)
  {
    try {
      $dynamicAd = DynamicAd::findOrFail($dynamicAdId);

      $dynamicAd->active = $active;
      $dynamicAd->last_modify_user_id = Auth::user()->id;
      $dynamicAd->save();
    } catch (ModelNotFoundException $e) {
      throw new MatrixException("{$dynamicAdId} 这个跑马灯不存在", DYNAMIC_AD_NOT_FOUND);
    }
    return $dynamicAd;
  }

  public function changeSignStatus(int $dynamicAdId,int $sign)
  {
    try {
      $dynamicAd = DynamicAd::findOrFail($dynamicAdId);

      $dynamicAd->sign = $sign;
      $dynamicAd->last_modify_user_id = Auth::user()->id;
      $dynamicAd->save();
    } catch (ModelNotFoundException $e) {
      throw new MatrixException("{$dynamicAdId} 这个跑马灯不存在", DYNAMIC_AD_NOT_FOUND);
    }
    return $dynamicAd;
  }

  public function getDynamicAdInfo(int $dynamicAdId)
  {
    try {
      $dynamicAd = DynamicAd::findOrFail($dynamicAdId)->toArray();

      $dynamicAd['terminal_codes'] = DynamicAdTerminal::where('dynamic_ad_id', $dynamicAdId)->pluck('terminal_code')->toArray();
      $dynamicAd['permission_codes'] = ContentGuard::where('uri', DynamicAd::URI_DYNAMIC_AD_ACCESS)->where('param1', $dynamicAdId)->pluck('service_code')->toArray();

      $packageData = $this->bossManager->getPackages();
      $packageTree = $this->formatDataOfPackages(array_get($packageData, 'data'));

      $permissionArray = $this->getPermissionCodesType($dynamicAd['permission_codes'], $packageTree);
      $dynamicAd['permission_array'] = $permissionArray;
    } catch (ModelNotFoundException $e) {
      throw new MatrixException("{$dynamicAdId} 这个跑马灯不存在", DYNAMIC_AD_NOT_FOUND);
    }
    return $dynamicAd;
  }


  private function formatDataOfPackages(array $arr)
    {
        $resultData = [];
        $packageList = $this->sortArr($arr, 'code', SORT_ASC, SORT_STRING);
        $otherPackageList = []; // 其他类型 例如： 免费版以及一些没有product_id的套餐
        foreach ($packageList as $packageItem) {
            $packageItem['granted'] = false;
            // 判断是否具有product_id
            if (empty(array_get($packageItem, 'product_id'))) {
                array_push($otherPackageList, $packageItem);
                continue;
            }
            // 判断当前package的product是否存在
            if (!in_array(array_get($packageItem, 'product_id'), array_column($resultData, 'product_id'))) {
                $productData = [];
                $productData['name'] = array_get($packageItem, 'product_name');
                $productData['product_id'] = array_get($packageItem, 'product_id');
                $productData['granted'] = false;
                $productData['child'] = [];
                foreach ($this->propagandaPageckage as $item) {
                    array_push($productData['child'], []);
                }
                array_push($resultData, $productData);
            }
            $productIndex = 0; // 当前套餐所属product索引
            foreach ($resultData as $k => $productItem) {
                if (array_get($productItem, 'product_id') === array_get($packageItem, 'product_id')) {
                    $productIndex = $k;
                }
            }
            
            foreach ($this->propagandaPageckage as $i => $orderName) {
                if (substr(strrchr(array_get($packageItem, 'code'), '_'), 1) === $orderName) {
                    $productChild = array_get($resultData[$productIndex], 'child');
                    if (empty($productChild[$i])) {
                        $productChild[$i] = [];
                    }
                    array_push($productChild[$i], $packageItem);
                    $resultData[$productIndex]['child'] = $productChild;
                }
            }
        }

        foreach ($resultData as $resultDataIndex => $resultDataItem) {
            foreach ($resultDataItem['child'] as $childIndex => $childItem) {
                if (sizeof($childItem) === 0) {
                    array_splice($resultData[$resultDataIndex]['child'], $childIndex, 1);
                }
            }
        }

        if ( !empty($otherPackageList) ) {
            $otherPackage = [
                'name' => '其他',
                'child' => [
                    $otherPackageList
                ],
                'granted' => false
            ];
            array_push($resultData, $otherPackage);
        } 

        return $resultData;
    }

    private function sortArr($arrays, $sort_key, $sort_order=SORT_ASC, $sort_type=SORT_NUMERIC)
    {
        if(is_array($arrays)){
            foreach ($arrays as $array){
                if(is_array($array)){
                    $key_arrays[] = $array[$sort_key];
                }else{
                    return false;
                }
            }
        }else{
            return false;
        }
        array_multisort( $key_arrays, $sort_order, $sort_type, $arrays);
        return $arrays;
    }

    private function getPermissionCodesType(array $permissionCodes, array $packageTree)
    {
        if (sizeof($permissionCodes) < 1) {
            return $packageTree;
        }
        $resultData = $packageTree;
        $k = 0;
        foreach ($packageTree as $item) {
            if (array_get($item, 'child')) {
                $j = 0;
                foreach ($item['child'] as $childItem) {
                    $i = 0;
                    foreach ($childItem as $packageCode) {
                        if (in_array($packageCode['code'], $permissionCodes)) {
                            $packageCode['granted'] = true;
                            $resultData[$k]['child'][$j][$i] = $packageCode;
                        }
                        $i ++;
                    }
                    $j ++;
                }
                $k ++;
            }
        }
        return $resultData;
    }

    public function updateDynamicAd(int $dynamicAdId, array $credentials)
    {
      DB::beginTransaction();
      try {
        $dynamicAd = DynamicAd::findOrFail($dynamicAdId);

        $credentials['last_modify_user_id'] = Auth::user()->id;
        $fillable = ['title', 'content_url', 'jump_type', 'jump_params', 'start_at', 'end_at', 'active', 'sign', 'source_type', 'source_id', 'last_modify_user_id'];
        foreach ($credentials as $k => $v) {
          if (in_array($k, $fillable)) {
            $dynamicAd->{$k} = $v;
          }
        }
        $dynamicAd->save();

        DynamicAdTerminal::where('dynamic_ad_id', $dynamicAdId)->delete();
        $terminalCodes = array_get($credentials, 'terminal_codes');
        $dynamicAdTerminalData = $this->dynamicAdTerminal->createDynamicAdTerminal($dynamicAdId, $terminalCodes);


        $delContentGuardData = $this->contentGuardContract->revoke([
          'uri' => DynamicAd::URI_DYNAMIC_AD_ACCESS,
          'param1' => $dynamicAdId,
        ]);
        if (array_get($delContentGuardData, 'code') === SYS_STATUS_ERROR_UNKNOW) {
            throw new MatrixException('权限更新失败', SYS_STATUS_ERROR_UNKNOW);
        }

        $permissionCodes = array_get($credentials, 'permission_codes');
        $packageContentGuards = self::packageContentGuards($permissionCodes, DynamicAd::URI_DYNAMIC_AD_ACCESS, [$dynamicAdId]);
        foreach ($packageContentGuards as $item) {
          $contentGuardData = $this->contentGuardContract->grant($item);
          if (array_get($contentGuardData, 'code') === SYS_STATUS_ERROR_UNKNOW) {
            throw new MatrixException('权限更新失败', SYS_STATUS_ERROR_UNKNOW);
          }
        }
        DB::commit();
      } catch (ModelNotFoundException $e) {
        DB::rollback();
        throw new MatrixException("{$dynamicAdId} 这个跑马灯不存在", DYNAMIC_AD_NOT_FOUND);
      } catch (MatrixException $e) {
        DB::rollback();
        throw new MatrixException($e->getMessage(), $e->getCode());
      } catch (Exception $e) {
        DB::rollback();
        throw new Exception($e->getMessage(), $e->getCode());
      }

      return $dynamicAd;
    }

    public function deleteDynamicAd(int $dynamicAdId)
    {
      DB::beginTransaction();
      try {
        $dynamicAd = DynamicAd::findOrFail($dynamicAdId);
        $dynamicAd->delete();

        $dynamicAd['terminal_codes'] = DynamicAdTerminal::where('dynamic_ad_id', $dynamicAdId)->pluck('terminal_code')->toArray();
        $dynamicAd['permission_codes'] = ContentGuard::where('uri', DynamicAd::URI_DYNAMIC_AD_ACCESS)->where('param1', $dynamicAdId)->pluck('service_code')->toArray();

        DynamicAdTerminal::where('dynamic_ad_id', $dynamicAdId)->delete();

        $delContentGuardData = $this->contentGuardContract->revoke([
          'uri' => DynamicAd::URI_DYNAMIC_AD_ACCESS,
          'param1' => $dynamicAdId,
        ]);
        if (array_get($delContentGuardData, 'code') === SYS_STATUS_ERROR_UNKNOW) {
            throw new MatrixException('权限更新失败', SYS_STATUS_ERROR_UNKNOW);
        }

        DB::commit();
      } catch (ModelNotFoundException $e) {
        DB::rollback();
        throw new MatrixException("{$dynamicAdId} 这个跑马灯不存在", DYNAMIC_AD_NOT_FOUND);
      } catch (MatrixException $e) {
        DB::rollback();
        throw new MatrixException($e->getMessage(), $e->getCode());
      } catch (Exception $e) {
        DB::rollback();
        throw new Exception($e->getMessage(), $e->getCode());
      }
      return $dynamicAd;
    }

    public function getDynamicAdListOfClient(array $dynamicAdId)
    {
      $nowDate = date('Y-m-d H:i:s', time());
      $dynamicAdList = DynamicAd::select('id', 'title', 'start_at', 'end_at', 'content_url', 'sign', 'jump_type', 'jump_params', 'source_type')
        ->whereIn('id', $dynamicAdId)
        ->where('start_at', '<', $nowDate)
        ->where('end_at', '>', $nowDate)
        ->where('active', 1)
        ->orderBy('start_at', 'asc')
        ->get()
        ->toArray();
      
      foreach ($dynamicAdList as &$dynamicAd) {
        if (Talkshow::NOTICE_SYNC_TO_DYNAMIC_AD_TYPE === (string)array_get($dynamicAd, 'source_type')) {
          $dynamicAd['title'] = '直播预告：' . $dynamicAd['title'];
        }
        if (Talkshow::SYNC_TO_DYNAMIC_AD_TYPE === (string)array_get($dynamicAd, 'source_type')) {
          $dynamicAd['title'] = '正在直播：' . $dynamicAd['title'];
        }
        if (Feed::SYNC_TO_DYNAMIC_AD_TYPE === (string)array_get($dynamicAd, 'source_type')) {
          $feedId = array_get($dynamicAd, 'source_id');
          $feed = Feed::where('feed_id', $feedId)->get()->toArray();
          if (!empty($feed['owner_id'])) {
            $authorUserData = $this->userManager->getUserByEnterpriseUserId($feed['owner_id']);
            $authorUser = array_get($authorUserData, 'data');

            $dynamicAd['title'] = array_get($authorUser, 'name') . "：" . $dynamicAd['title'];
          }
        }
        unset($dynamicAd['source_type']);
      }

      return $dynamicAdList;
    }
}