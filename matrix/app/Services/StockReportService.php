<?php
namespace Matrix\Services;

use Matrix\Contracts\StockReportManager;
use Matrix\Models\StockReportCategory;
use Matrix\Models\StockReport;
use Matrix\Exceptions\StockReportException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Matrix\Models\Feed;
use Matrix\Models\Teacher;
use Matrix\Models\User;
use Matrix\Models\Ucenter;
use Matrix\Models\Grant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Matrix\Exceptions\MatrixException;

class StockReportService extends BaseService implements StockReportManager
{
  private $stockReport;
  private $feed;
  private $grant;

  public function __construct(StockReport $stockReport, Feed $feed, Grant $grant)
  {
    $this->stockReport = $stockReport;
    $this->feed = $feed;
    $this->grant = $grant;
  }

  public function getReportCategories()
  {
    $reportCategories = StockReportCategory::where('visible', '1')->get()->toArray();
    return $reportCategories;
  }

  public function getReportCategoryOfCategoryId($categoryId)
  {
    $reportCategory = StockReportCategory::select('category_id', 'category_name')->where('category_id', $categoryId)->where('visible', '1')->get()->toArray();
    return $reportCategory;
  }

  public function getPublishStatus()
  {
    $publistStatus = $this->stockReport->getPublishStatus();
    return $publistStatus;
  }

  public function createStockReport(array $credentials)
  {
    // 跟踪报告类型 一天只能发送一个报告
    $categoryId = array_get($credentials, 'category_id');
    if ($categoryId === StockReportCategory::GENZONGBAOGAO) {
      $stockCode = array_get($credentials, 'stock_code');
      $reportDate = array_get($credentials, 'report_date');
      $stockReports = StockReport::where("stock_code", $stockCode)->where("category_id", $categoryId)->where("report_date", $reportDate)->get()->toArray();
      if (count($stockReports) > 0) {
        throw new MatrixException("当前所选日期已有跟踪报告，不允许重复添加", STOCK_REPORT_GENZONGBAOGAO_EXCEED);
      }
    }

    $credentials['report_short_title'] = (string)array_get($credentials, 'report_short_title');
    $credentials['report_content'] = (string)array_get($credentials, 'report_content');
    $credentials['report_url'] = (string)array_get($credentials, 'report_url');
    $credentials['report_summary'] = (string)array_get($credentials, 'report_summary');
    $credentials['report_id'] = md5(str_random(32));
    $credentials['creator'] = Auth::user()->id;
    $credentials['last_modify_user_id'] = Auth::user()->id;

    $stockReport = StockReport::create($credentials);
    
    return $stockReport;
  }

  public function getStockReportList(int $pageNo, int $pageSize, array $credentials)
  {
    $cond = [];

    foreach ($credentials as $k => $v) {
      if ($k !== 'report_title' && $k !== 'stock_code' && $v !== "" && $v !== null) {
        $cond[] = [$k, $v];
      }
    }

    $reportTitle = array_get($credentials, 'report_title');
    if (!empty($reportTitle)) {
      $cond[] = ['report_title', 'like', "%$reportTitle%"];
    }

    $reportStockCode = array_get($credentials, 'stock_code');
    if (!empty($reportStockCode)) {
      if (strlen($reportStockCode) >= 6) {
        $cond[] = ['stock_code', 'like', "%$reportStockCode%"];
      } else {
        $cond[] = ['stock_code', $reportStockCode];
      }
    }

    $stockReportList = StockReport::where($cond)
                                    ->orderBy('updated_at', 'desc')
                                    ->skip($pageSize * ($pageNo - 1))
                                    ->take($pageSize)
                                    ->get()
                                    ->toArray();

    $publishStatusArr = array_column(StockReport::PUBLISH_STATUS, 'name', 'status');

    $reportCategories = StockReportCategory::where('visible', '1')->get()->toArray();
    $reportCategoryArr = array_column($reportCategories, 'category_name', 'category_id');

    $userList = User::get()->toArray();
    $UserArr = array_column($userList, 'name', 'id');

    foreach ($stockReportList as &$stockReport) {
      $publishStatus = array_get($stockReport, 'publish');
      $stockReport['publish_status_name'] = $publishStatusArr[$publishStatus];
      $stockReport['category_name'] = $reportCategoryArr[array_get($stockReport, 'category_id')];
      $stockReport['last_modify_user_name'] = $UserArr[array_get($stockReport, 'last_modify_user_id')];
    }

    return $stockReportList;
  }

  public function getStockReportCnt(array $credentials)
  {
    $cond = [];

    foreach ($credentials as $k => $v) {
      if ($k !== 'report_title' && $v !== "" && $v !== null) {
        $cond[] = [$k, $v];
      }
    }

    $reportTitle = array_get($credentials, 'report_title');
    if (!empty($reportTitle)) {
      $cond[] = ['report_title', 'like', "%$reportTitle%"];
    }

    $stockReportCnt = StockReport::where($cond)->count();

    return $stockReportCnt;
  }

  public function getStockReportInfo(int $id)
  {
    try {
      $stockReport = StockReport::where('id', $id)->firstOrFail()->toArray();
      $stockReport['report_url'] = empty($stockReport['report_url']) ? '' : $stockReport['report_url'];
      $stockReport['report_url'] = self::getFileUrl($stockReport['report_url']);

      $authorTeacherId = array_get($stockReport, 'author_teacher_id');
      if (!empty($authorTeacherId)) {
        $teacher = Teacher::where('id', $authorTeacherId)->first()->toArray();
        $user = User::where('id', array_get($teacher, 'user_id'))->first()->toArray();
        $stockReport['author_teacher_name'] = array_get($user, 'name');
        $stockReport['author_teacher_icon'] = array_get($teacher, 'icon_url');
      } else {
        $stockReport['author_teacher_name'] = '';
        $stockReport['author_teacher_icon'] = '';
      }
    } catch (ModelNotFoundException $e) {
      throw new StockReportException("{$id} 这个报告不存在", STOCK_REPORT_NOT_FOUND);
    }

    return $stockReport;
  }

  public function getStockReportInfoByStockReportId(string $reportId)
  {
    try {
      $stockReport = StockReport::where('report_id', $reportId)->firstOrFail()->toArray();

      // TODO 兼容老数据 PDF 地址
      $stockReport['report_url'] = empty($stockReport['report_url']) ? '' : $stockReport['report_url'];
      $stockReport['report_url'] = self::getFileUrl($stockReport['report_url']);

      $authorTeacherId = array_get($stockReport, 'author_teacher_id');
      if (!empty($authorTeacherId)) {
        $teacher = Teacher::where('id', $authorTeacherId)->first()->toArray();
        $user = User::where('id', array_get($teacher, 'user_id'))->first()->toArray();
        $stockReport['author_teacher_name'] = array_get($user, 'name');
        $stockReport['author_teacher_icon'] = empty(array_get($teacher, 'icon_url')) ? array_get($user, 'icon_url') : array_get($teacher, 'icon_url');
      } else {
        $stockReport['author_teacher_name'] = '';
        $stockReport['author_teacher_icon'] = '';
      }
    } catch (ModelNotFoundException $e) {
      throw new StockReportException("这个报告不存在", STOCK_REPORT_NOT_FOUND);
    }
    return $stockReport;
  }

  private function getFileUrl($path)
  {
    $result = '';

    if (empty($path)) {
      $result = '';
    } else if (substr($path, 0, strlen('http')) === 'http') {
      $result = $path;
    } else if (substr($path, 0, strlen(config('app.cdn.base_url'))) === config('app.cdn.base_url')) {
      $result = $path;
    } else {
      $result = config('app.cdn.base_url') . substr($path, strpos($path, 'files') - 1);
    }

    return $result;
  }
  
  public function updateStockReport(int $id, array $credentials)
  {
    try {
      $stockReport = StockReport::where('id', $id)->firstOrFail();
      if ($stockReport->publish === StockReport::PUBLISH_SUCCESS) {
        $grantedList = $this->grant->getOneGrantedList(Auth::id());
        $grantedCodeList = array_column($grantedList, 'permission_code');
        if (!in_array(StockReport::PUBLISHED_UPDATE_PERMISSION_CODE, $grantedCodeList)) {
          throw new StockReportException("{$id} 这个报告已发布，您无权限进行修改", STOCK_REPORT_PUBLISHED);
        }
      }
      // 跟踪报告类型 一天只能发送一个报告
      $categoryId = array_get($credentials, 'category_id');
      if ($categoryId === StockReportCategory::GENZONGBAOGAO) {
        $stockCode = array_get($credentials, 'stock_code');
        $reportDate = array_get($credentials, 'report_date');
        $stockReports = StockReport::where("stock_code", $stockCode)->where("category_id", $categoryId)->where("report_date", $reportDate)->where('id', '<>', $id)->get()->toArray();
        if (count($stockReports) > 0) {
          throw new MatrixException("当前所选日期已有跟踪报告，不允许重复添加", STOCK_REPORT_GENZONGBAOGAO_EXCEED);
        }
      }

      $credentials['last_modify_user_id'] = Auth::user()->id;
      foreach ($credentials as $k => $v) {
        $stockReport->{$k} = $v;
      }
      $stockReport->save();
    } catch (ModelNotFoundException $e) {
      throw new StockReportException("{$id} 这个报告不存在", STOCK_REPORT_NOT_FOUND);
    }

    return $stockReport;
  }

  public function deleteStockReport(int $id)
  {
    try {
      $stockReport = StockReport::where('id', $id)->firstOrFail();
      if ($stockReport->publish === StockReport::PUBLISH_SUCCESS) {
        throw new StockReportException("{$id} 这个报告已发布，不能进行删除操作", STOCK_REPORT_PUBLISHED);
      }
      $stockReport = StockReport::where('id', $id)->firstOrFail()->delete();
    } catch(ModelNotFoundException $e) {
      throw new StockReportException("{$id} 这个报告不存在", STOCK_REPORT_NOT_FOUND);
    }
  }

  public function publishStockReport(int $id)
  {
    try {
      DB::beginTransaction();
      $stockReport = StockReport::where('id', $id)->firstOrFail();
      if ($stockReport->publish === StockReport::PUBLISH_SUCCESS) {
        throw new StockReportException("{$id} 这个报告已发布", STOCK_REPORT_PUBLISHED);
      }
      if (strtotime($stockReport->report_date) > strtotime(date('Y-m-d').' 23:59:59')) {
        throw new StockReportException("{$id} 这个报告的报告日期为未来日期，不允许进行发布操作", STOCK_REPORT_NOT_CAN_PUBLISH);
      }

      $creatorUcenter = Ucenter::where('user_id', $stockReport->creator)->first();
      $stockReportFeed = [
        'feed_owner' => $stockReport->stock_name.' '.substr($stockReport->stock_code, 0, 6),
        'feed_type' => FEED::FEED_TYPE_STOCK_REPORT,
        'category_key' => StockReport::STOCK_REPORT_CATEGORY_KEY,
        'msg_type' => StockReport::STOCK_REPORT_FEED_MSG_TYPE,
        'source_id' => $stockReport->report_id,
        'title' => $stockReport->report_title,
        'summary' => $stockReport->report_summary,
        'access_level' => StockReport::STOCK_REPORT_ACCESS_LEVEL,
        'source_url' => sprintf('/api/v2/client/stock_report/%s', array_get($stockReport, 'report_id')),
        // 'is_elite' => 0,
        'push_status' => 0,
        'qywx_status' => 1,
        'add_time' => $stockReport->report_date . ' ' . (string)date('H:i:s'),
        'creator' => $creatorUcenter->enterprise_userid,
      ];

      if (strtotime($stockReport->report_date) < strtotime(date('Y-m-d').' 00:00:00')) {
        $stockReportFeed['push_status'] = 5;
        $stockReportFeed['qywx_status'] = 0;
      }

      $feed = Feed::create($stockReportFeed);
      $stockReport->publish = StockReport::PUBLISH_SUCCESS;
      $stockReport->save();
      DB::commit();
      $ret = [
        'feed' => $feed,
        'stock_report' => $stockReport,
      ];
    } catch (ModelNotFoundException $e) {
      DB::rollBack();
      throw new StockReportException("{$id} 这个报告推送失败", STOCK_REPORT_PUSH_ERROR);
    }

    return $ret;
  }

  public function getModifyPermission()
  {
    $result = false;

    $grantedList = $this->grant->getOneGrantedList(Auth::id());
    $grantedCodeList = array_column($grantedList, 'permission_code');
    if (in_array(StockReport::PUBLISHED_UPDATE_PERMISSION_CODE, $grantedCodeList)) {
      $result = true;
    }

    return $result;
  }

  public function getStockReportListOfApi(int $pageNo, int $pageSize, array $credentials, string $version = 'v2')
  {
    $cond = [];

    if ($version === 'v1') {
      foreach ($credentials as $k => $v) {
        if (!in_array($k, ['stock_code']) && $v !== "" && $v !== null) {
          $cond[] = [$k, $v];
        }
      }
      $reportStockCode = array_get($credentials, 'stock_code');
      if (!empty($reportStockCode)) {
        if (strlen($reportStockCode) === 6) {
          $cond[] = ['stock_code', 'like', "%$reportStockCode%"];
        } else {
          $cond[] = ['stock_code', $reportStockCode];
        }
      }
    } else {
      foreach ($credentials as $k => $v) {
        if ($v !== "" && $v !== null) {
          $cond[] = [$k, $v];
        }
      }
    }
    
    $cond[] = ['publish', StockReport::PUBLISH_SUCCESS];
    $cond[] = ['report_date', '<=', date('Y-m-d')];

    $stockReportList = StockReport::select('report_id', 'stock_code', 'report_title', 'category_id', 'report_summary', 'report_url', 'report_date', 'created_at as add_time')
                                    ->where($cond)
                                    ->orderBy('report_date', 'desc')
                                    ->orderBy('id', 'desc')
                                    ->skip($pageSize * $pageNo)
                                    ->take($pageSize)
                                    ->get()
                                    ->toArray();

    return $stockReportList;
  }

  public function getStockReportListOfClient(array $credentials)
  {
    $cond = [];

    foreach ($credentials as $k => $v) {
      if (!in_array($k, ['start_date', 'end_date']) && $v !== "" && $v !== null) {
        $cond[] = [$k, $v];
      }
    }

    $startDate = array_get($credentials, 'start_date');
    if (empty($startDate)) {
      $cond[] = ['report_date', '>=', date('Y-m-d')];
    } else {
      $cond[] = ['report_date', '>=', $startDate];
    }

    $endDate = array_get($credentials, 'end_date');
    if (empty($endDate)) {
      $cond[] = ['report_date', '<=', date('Y-m-d')];
    } else {
      $cond[] = ['report_date', '<=', $endDate];
    }

    $cond[] = ['publish', StockReport::PUBLISH_SUCCESS];
    $cond[] = ['report_date', '<=', date('Y-m-d')];

    $stockReportList = StockReport::select('report_id', 'stock_code', 'report_title', 'report_short_title', 'category_id', 'report_summary', 'report_url', 'report_date', 'created_at as add_time')
                                    ->where($cond)
                                    ->orderBy('report_date', 'asc')
                                    ->orderBy('id', 'desc')
                                    ->get()
                                    ->toArray();

    return $stockReportList;
  }
}