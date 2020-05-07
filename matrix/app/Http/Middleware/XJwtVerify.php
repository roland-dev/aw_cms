<?php

namespace Matrix\Http\Middleware;

use Closure;
use Matrix\Contracts\UserManager;
use Exception;
use Log;
use Illuminate\Support\Facades\Auth;

class XJwtVerify
{
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
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
        $jwt = $request->header('x-jwt');
        if (empty($jwt)) {
            $jwt = $request->cookie('x-jwt');
        }

        if ($request->route()->uri == 'user') {
            return $next($request);
        } else if ($request->route()->uri == 'user/auth/uc') {
            return $next($request);
        } else if ($request->route()->uri == 'user/auth/uc/enterprise') {
            return $next($request);
        } else if ($request->route()->uri == 'promotion/moveqr/{qrGroupCode}/{report?}') {
            return $next($request);
        } else if (empty($jwt)) {
            Auth::logout();
            $frontUrl = config('front.url');
            $ret = [
                'code' => 401,
                'msg' => '登录状态失效了',
                'front_url' => $frontUrl,
            ];
            return response()->json($ret);
        }

        return $next($request);
    }
}
