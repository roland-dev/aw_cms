<?php
namespace Matrix\Http\Controllers\Client;

use Illuminate\Http\Request;
use Log;
use Exception;
use Matrix\Contracts\ContentGuardContract;
use Matrix\Contracts\DynamicAdManager;
use Matrix\Contracts\UcManager;
use Matrix\Exceptions\MatrixException;
use Matrix\Models\DynamicAd;

use function GuzzleHttp\json_decode;

class DynamicAdController extends Controller
{

  private $request;
  private $dynamicAdManager;
  private $ucManager;
  private $contentGuardContract;

  private $basicPackage;

  public function __construct(
    Request $request,
    DynamicAdManager $dynamicAdManager,
    UcManager $ucManager,
    ContentGuardContract $contentGuardContract
  )
  {
    $this->request = $request;
    $this->dynamicAdManager = $dynamicAdManager;
    $this->ucManager = $ucManager;
    $this->contentGuardContract = $contentGuardContract;

    $this->basicPackage = config('packagetype.basic_package');
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

  public function getDynamicAdList()
  {
    try {
      $sessionId = $this->request->header('X-SessionId');
      if (empty($sessionId)) {
        $sessionId = $this->request->cookie('X-SessionId');
      }

      if (empty($sessionId)) {
        $productKeyList = [$this->basicPackage];
      } else {
        $customerProductCode = $this->ucManager->getCustomerProductCodeListBySessionId($sessionId);
        $productKeyList = array_get($customerProductCode, 'data.product_key_list');
      }
      $dynamicAdIdList = $this->contentGuardContract->getOnesAccessIdList(DynamicAd::URI_DYNAMIC_AD_ACCESS, $productKeyList);
      $dynamicAdsData = $this->dynamicAdManager->getDynamicAdListOfClient($dynamicAdIdList);

      foreach ($dynamicAdsData as &$dynamicAdData) {
        if (!empty(array_get($dynamicAdData, 'jump_params'))) {
          $dynamicAdData['jump_params'] = json_decode(array_get($dynamicAdData, 'jump_params'), true);
        }
        $dynamicAdData['content_url'] = $this->fitDetailUrl($dynamicAdData['content_url']);
      }

      $ret = [
        'code' => SYS_STATUS_OK,
        'msg' => 'success',
        'data' => $dynamicAdsData,
        'errors' => null,
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