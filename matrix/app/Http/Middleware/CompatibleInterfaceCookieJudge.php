<?php

namespace Matrix\Http\Middleware;

use Closure;
use Matrix\Contracts\UcManager;

// 兼容性接口
class CompatibleInterfaceCookieJudge
{
    private $ucManager;

    public function __construct(UcManager $ucManager)
    {
        $this->ucManager = $ucManager;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $jwt = $request->cookie('x-jwt');
        $token = $request->cookie('token');
        if (empty($jwt) && empty($token)) {
            $code_key = 'response_filt.cms_api.'.CMS_API_COOKIE_PARAMETER_NOT_FOUND;
            $callback = $this->ucManager->getH5EnterpriseLoginUrl();
            $msg = "";
            if (array_get($callback, 'code') === SYS_STATUS_OK) {
                $callbackUrl = array_get($callback, 'data.callback');
            } else {
                $callbackUrl = array_get($callback, 'data.callback');
                $msg = " and get rollback is fail";
            }

            return response()->json([
                "code" => config($code_key),
                "msg" => "$._COOKIE.x_jwt and $._COOKIE.token not found in request" . $msg,
                "callback_url" => $callbackUrl,
            ]);
        }
        return $next($request);
    }
}
