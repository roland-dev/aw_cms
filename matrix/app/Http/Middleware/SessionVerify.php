<?php

namespace Matrix\Http\Middleware;

use Closure;
use Matrix\Contracts\UcManager;

class SessionVerify 
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
        $sessionId = $request->cookie('X-SessionId');
        if (empty($sessionId)) {
            $configKey = 'response_filt.cms_api.'.CMS_API_X_SESSIONID_NOT_FOUND;
            $callback = $this->ucManager->getH5EnterpriseLoginUrl();

            $callbackUrl = array_get($callback, 'data.callback');

            return response()->json([
                "code" => config($configKey),
                "msg" => "SESSIONID NOT FOUND IN REQUEST",
                "callback_url" => $callbackUrl,
            ]);
        }
        return $next($request);
    }
}
