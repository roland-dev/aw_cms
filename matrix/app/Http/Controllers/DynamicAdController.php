<?php
namespace Matrix\Http\Controllers;

use Illuminate\Http\Request;
use Matrix\Contracts\DynamicAdManager;
use Matrix\Contracts\OperateLogContract;
use Matrix\Exceptions\MatrixException;
use Log;
use Exception;
use Illuminate\Support\Facades\Auth;

class DynamicAdController extends Controller
{
  private $request;
  private $dynamicAdManager;
  private $operateLog;

  public function __construct(
    Request $request,
    DynamicAdManager $dynamicAdManager,
    OperateLogContract $operateLog
  )
  {
    $this->request = $request;
    $this->dynamicAdManager = $dynamicAdManager;
    $this->operateLog = $operateLog;
  }

  protected function fitDetailUrl(string $url)
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


  public function getSourceTypes()
  {
    $sourceTypes = $this->dynamicAdManager->getSourceTypes();
    $ret = [
      'code' => SYS_STATUS_OK,
      'data' => [
        'source_types' => $sourceTypes
      ]
    ];
    return $ret;
  }

  public function getDynamicAdTerminals()
  {
    $terminals = $this->dynamicAdManager->getDynamicAdTerminals();
    $ret = [
      'code' => SYS_STATUS_OK,
      'data' => [
        'terminals' => $terminals,
      ],
    ];
    return $ret;
  }

  public function create()
  {
    $credentials = $this->request->validate([
      'title' => 'required|string',
      'content_url' => 'required|string',
      'jump_type' => 'nullable|string',
      'jump_params' => 'nullable|string',
      'start_at' => 'required|string',
      'end_at' => 'required|string',
      'terminal_codes' => 'required|array',
      'permission_codes' => 'required|array',
      'active' => 'required|integer:0,1',
      'sign' => 'required|integer:0,1'
    ]);

    try {
      $credentials['last_modify_user_id'] = Auth::user()->id;
      $dynamicAd = $this->dynamicAdManager->createDynamicAd($credentials);
      $this->operateLog->record('create', 'dynamic_ad', $dynamicAd->id, "用户 ".Auth::user()->name." 创建了一个跑马灯 {$dynamicAd}", $this->request->ip(), Auth::user()->id);

      $ret = [
        'code' => SYS_STATUS_OK,
        'data' => [
          'dynamic_ad' => $dynamicAd
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

  public function search()
  {
    $credentials = $this->request->validate([
      'page_no' => 'nullable|integer',
      'page_size' => 'nullable|integer',
      'start_at' => 'nullable|string',
      'end_at' => 'nullable|string',
      'source_type' => 'nullable|string'
    ]);

    try {
      $pageNo = array_get($credentials, 'page_no', 1);
      $pageSize = array_get($credentials, 'page_size', 10);

      $cond = [
        'start_at' => (string)array_get($credentials, 'start_at'),
        'end_at' => (string)array_get($credentials, 'end_at'),
        'source_type' => (string)array_get($credentials, 'source_type'),
      ];

      $dynamicAdList = $this->dynamicAdManager->getDynamicAdList($pageNo, $pageSize, $cond);
      $dynamicAdCnt = $this->dynamicAdManager->getDynamicAdCnt($cond);

      $ret = [
        'code' => SYS_STATUS_OK,
        'msg' => 'success',
        'data' => [
          'dynamic_ad_list' => $dynamicAdList,
          'dynamic_ad_cnt' => $dynamicAdCnt,
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
        'msg' => '未知错误'
      ];
    }
    return $ret;
  }

  public function changeActiveStatus($dynamicAdId, $active)
  {
    try {
      $dynamicAd = $this->dynamicAdManager->changeActiveStatus($dynamicAdId, $active);

      $ret = [
        'code' => SYS_STATUS_OK,
        'data' => [
          'dynamic_ad' => $dynamicAd,
        ],
      ];
    } catch (MatrixException $e) {
      Log::error($e->getMessage(), [$e]);
      $ret = [
        'code' => $e->getCode(),
        'msg' => $e->getMesage(),
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

  public function changeSignStatus($dynamicAdId, $sign)
  {
    try {
      $dynamicAd = $this->dynamicAdManager->changeSignStatus($dynamicAdId, $sign);

      $ret = [
        'code' => SYS_STATUS_OK,
        'data' => [
          'dynamic_ad' => $dynamicAd
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
        'msg' => '未知错误'
      ];
    }
    return $ret;
  }

  public function getDynamicAdInfo(int $dynamicAdId)
  {
    try {
      $dynamicAd = $this->dynamicAdManager->getDynamicAdInfo($dynamicAdId);

      $dynamicAd['content_url'] = $this->fitDetailUrl($dynamicAd['content_url']);

      $ret = [
        'code' => SYS_STATUS_OK,
        'data' => [
          'dynamic_ad' => $dynamicAd,
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
        'msg' => '未知错误'
      ];
    }

    return $ret;
  }

  public function update(int $dynamicAdId)
  {
    $credentials = $this->request->validate([
      'title' => 'required|string',
      'content_url' => 'required|string',
      'jump_type' => 'nullable|string',
      'jump_params' => 'nullable|string',
      'start_at' => 'required|string',
      'end_at' => 'required|string',
      'terminal_codes' => 'required|array',
      'permission_codes' => 'required|array',
      'active' => 'required|integer:0,1',
      'sign' => 'required|integer:0,1'
    ]);

    try {
      $dynamicAd = $this->dynamicAdManager->updateDynamicAd($dynamicAdId, $credentials);
      $this->operateLog->record('update', 'dynamic_ad', $dynamicAd->id, "用户 ".Auth::user()->name." 修改了一个跑马灯 {$dynamicAd}", $this->request->ip(), Auth::user()->id);
      
      $ret = [
        'code' => SYS_STATUS_OK,
        'data' => [
          'dynamic_ad' => $dynamicAd,
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
        'code' => SYS_STATUS_ERROR_UNKNOw,
        'msg' => '未知错误'
      ];
    }

    return $ret;
  }

  public function deltete(int $dynamicAdId)
  {
    try {
      $dynamicAd = $this->dynamicAdManager->deleteDynamicAd($dynamicAdId);
      $this->operateLog->record('delete', 'dynamic_ad', $dynamicAd->id, "用户 ".Auth::user()->name." 删除了一个跑马灯 {$dynamicAd}", $this->request->ip(), Auth::user()->id);

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
        'msg' => '未知错误'
      ];
    }

    return $ret;
  }
}