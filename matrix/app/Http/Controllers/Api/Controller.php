<?php

namespace Matrix\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    protected function respAdapter(array $ret)
    {
        $result = $ret;
    
        $code_key = 'response_filt.cms_api.'.array_get($ret,'code');
    
        $result['code'] = config($code_key);
    
        return $result;
    }

    protected function fitDetailUrl(string $url, Request $request)
    {
        if (empty($url)) {
            return '';
        }
        if (strpos($url, 'http') === 0) { // http or https
            return $url;
        } elseif (strpos($url, '//') === 0) { // //www.zhongyingtougu.com/
            return $request->server('REQUEST_SCHEME').":$url";
        } elseif (strpos($url, '/files/') === 0) { // //www.zhongyingtougu.com/
            return substr_replace($url, config('cdn.cdn_url'), 0, 6);
        } else {
            return sprintf('%s%s', config('app.h5_api_url'), $url);
        }
    }
}

