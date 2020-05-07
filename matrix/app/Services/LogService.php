<?php

namespace Matrix\Services;

use Matrix\Contracts\LogManager;
use Matrix\Models\Log;
use Exception;

class LogService extends BaseService implements LogManager
{
    private $log;
    private $userManager;
    private $feedManager;
    private $articleManager;
    private $twitterManager;

    public function __construct(
        Log $log
    )
    {
        $this->log = $log;
    }

    public function getOperationLogList()
    {
        $logList = $this->log->show();
        return [
            'code' => SYS_STATUS_OK,
            'logList' => $logList
        ];
    }

    public function createOperationLog(string $sourceKey, int $userId, string $originalData, string $operate)
    {
        $logRes = $this->log->createLog($sourceKey, $userId, $originalData, $operate);
    }

    /**
     *文件动态日志记录(弃用)
     *
     *@param feedType integer 模块类型
     *@param operator integer 操作者Id
     *@param sourceId integer 操作记录Id
     *
     *@return array
     */
     //public function logRecord(int $feedType, int $operator, int $sourceId)
     //{
     //    try{
     //        $userInfo = $this->userManager->getUserInfo($operator);
     //        $uname = array_get($userInfo, 'userInfo.name');

     //        switch ($feedType) {
     //           case 2://大盘分析
     //           case 4://周战报
     //               $tjWxSendLogDetail = $this->feedManager->getTjWxSendLogDetail($sourceId);
     //               Log::info('删除大盘分析/周战报记录id:'.$sourceId.'删除大盘分析/周战报记录内容:'.json_encode($tjWxSendLogDetail).'操作人:'.$uname.'操作时间:'.date('Y-m-d H:i:s'));
     //               break;
     //           case 11://解盘
     //               $twitterInfo = $this->twitterManager->getTwitterInfo($sourceId);
     //               $kgsId = array_get($twitterInfo, 'source_id');

     //               Log::info('删除解盘记录id:'.$sourceId.'删除解盘记录内容:'.json_encode($twitterInfo).'操作人:'.$uname.'操作时间:'.date('Y-m-d H:i:s').'删除看高手Id:'.$kgsId);
     //               break;
     //           case 12://专栏文章
     //               $articleInfo = $this->articleManager->getArticleInfo($sourceId);
     //               Log::info('删除文章记录id:'.$sourceId.'删除文章记录内容:'.json_encode(array_get($articleInfo, 'data.article')).'操作人:'.$uname.'操作时间:'.date('Y-m-d H:i:s'));
     //               break;
     //           case 111://精选文章
     //               $feedInfo = $this->feedManager->getFeedInfo($sourceId);
     //               Log::info('删除精选记录id:'.$sourceId.'删除精选记录内容:'.json_encode($feedInfo).'操作人:'.$uname.'操作时间:'.date('Y-m-d H:i:s'));
     //               break;
     //           default:
     //               Log::info('传入类型有误');
     //               break;
     //        }
     //    }catch(Exception $e){
     //        Log::error($e->getMessage(), [$e]);
     //    }
     //}
}
