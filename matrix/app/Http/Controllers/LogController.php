<?php
 
namespace Matrix\Http\Controllers;

use Illuminate\Http\Request;
use Matrix\Contracts\LogManager;

class LogController extends Controller
{
    private $logManager;
    private $request;

    public function __construct(Request $request, LogManager $logManager)
    {
       $this->logManager = $logManager; 
       $this->request = $request;
    }

    public function show()
    {
        $logRes = $this->logManager->getOperationLogList();
        $this->checkServiceResult($logRes, 'Log');        
        $logList = array_get($logRes, 'logList');
        return [
            'code' => SYS_STATUS_OK,
            'logList' => $logList
        ];
    }
}
