<?php

namespace Matrix\Http\Controllers\Client;

use Illuminate\Routing\Controller as BaseController;
use Matrix\Exceptions\UcException;
use Exception;
use Log;

class Controller extends BaseController
{
    protected function makeClientContent(string $content)
    {
        $content = strip_tags($content, '<image>');
        $content = htmlspecialchars_decode($content);

        return $content;
    }

    protected function respAdapter(array $ret)
    {
        $result = $ret;
    
        $code_key = 'response_filt.cms_api.'.array_get($ret,'code');
    
        $result['code'] = config($code_key);
    
        return $result;
    }

    protected function fitClientUrl(string $url)
    {
        if (empty($url)) {
            return '';
        }
        if (strpos($url, '//') === 0) {
            $url = "http:$url";
        }

        return $url;
    }

    protected function h5WechatAutoLogin($request, $ucenter)
    {
        try{
            $sessionId = $request->cookie('X-SessionId');

            $loginUrl = $this->checkLogin($request, $ucenter, $sessionId);
        } catch (UcException $e) {
            $loginUrl = $this->checkLogin($request, $ucenter, $sessionId);
        }

        return $loginUrl;
    }

    protected function checkLogin($request, $ucenter, $sessionId)
    {
        $ua = $request->userAgent();
        $currentUrl = $request->server('REQUEST_SCHEME') . substr($request->fullUrl(), strpos($request->fullUrl(), "://"));
        
        $ucEnterpriseLoginUrl = NULL;

        if (strpos(strtoupper($ua), 'MICROMESSENGER') !== false) { // 在微信浏览器内
            if (empty($sessionId)) {
                // 去UC联合登录
                $ucEnterpriseLoginUrlData = $ucenter->getH5EnterpriseLoginUrl($currentUrl);
                $ucEnterpriseLoginUrl = array_get($ucEnterpriseLoginUrlData, 'data.callback');
            } else {
                try{
                    $userInfo = $ucenter->getUserInfoBySessionId($sessionId);
                } catch (UcException $e) {
                    $ucEnterpriseLoginUrlData = $ucenter->getH5EnterpriseLoginUrl($currentUrl);
                    $ucEnterpriseLoginUrl = array_get($ucEnterpriseLoginUrlData, 'data.callback');
                }
            }
        }

        return $ucEnterpriseLoginUrl;
    }

    /**
     * 获取 APP 版本号
     */
    protected function getAPPVersion(string $userAgent)
    {
        $pattern = "/[\s\/]+/";
        $keywords = preg_split($pattern, $userAgent);
        $result = (string)array_get($keywords, 1);

        return $result;
    }
}

