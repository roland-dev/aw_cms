<?php

namespace Matrix\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use Matrix\Exceptions\ServiceException;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function checkServiceResult(array $result, string $serviceName)
    {
        $code = array_get($result, 'code', SYS_STATUS_ERROR_UNKNOW);
        if (SYS_STATUS_OK !== $code) {
            throw new ServiceException("$serviceName Error: $code.");
        }
    }

    protected function respAdapter(array $ret)
    {
        $result = $ret;
    
        $code_key = 'response_filt.cms_api.'.array_get($ret,'code');
    
        $result['code'] = config($code_key);
    
        return $result;
    }
}
