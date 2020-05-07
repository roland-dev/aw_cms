<?php

namespace Matrix\Http\Middleware;

use Closure;
use Matrix\Contracts\UcManager;
use Matrix\Exceptions\UcException;
use Exception;
use Log;

class InteractionMiddleware
{
    private $ucenter;

    public function __construct (UcManager $ucenter)
    {
        $this->ucenter = $ucenter;
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
        Log::debug('App Client Request Debug: ', [$request]);

        $mustLoginUriList = [
            'api/v2/interaction/forward/twitter',
            'api/v2/interaction/reply',
            //'api/v2/interaction/vote',
        ];

        if (in_array($request->route()->uri, $mustLoginUriList)) {
            $sessionId = $request->header('X-SessionId');
            if (empty($sessionId)) {
                abort(401);
            }
            try {
                $ucUserInfo = $this->ucenter->getUserInfoBySessionId($sessionId);
            } catch (UcException $e) {
                Log::error($e->getMessage(), [$e]);
                abort(401);
            } catch (Exception $e) {
                Log::error($e->getMessage(), [$e]);
                abort(401);
            }
        }

        return $next($request);
    }
}
