<?php

namespace Matrix\Http\Middleware;

use Closure;
use Matrix\Contracts\UcManager;
use Matrix\Exceptions\UcException;
use Exception;
use Log;

class ClientMiddleware
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

        $host = $request->server('HTTP_HOST');

        $sessionId = $request->header('X-SessionId');
        if (empty($sessionId)) {
            $sessionId = $request->cookie('X-SessionId');
        }

        if (!empty($sessionId)) {
            $sessionIdExpired = time() + 60 * 60 * 10;
            setcookie('X-SessionId', $sessionId, time() - 3600, config('session.path'), $host, false, true);
            setcookie('X-SessionId', $sessionId, $sessionIdExpired, config('session.path'), config('session.domain'), false, true);
        }

        $jwt = $request->header('X-jwt');
        if (empty($jwt)) {
            $jwt = $request->cookie('X-jwt');
        }

        if (!empty($jwt)) {
            $jwtExpired = time() + 60 * 60 * 10;
            setcookie('X-jwt', $jwt, time() - 3600, config('session.path'), $host, false, true);
            setcookie('X-jwt', $jwt, $jwtExpired, config('session.path'), config('session.domain'), false, true);
        }

        if ($request->route()->uri == 'api/v2/client/article/{article_id}') {
            return $next($request);
            $sessionId = $request->cookie('X-SessionId');
        } elseif ($request->route()->uri == 'api/v2/client/ksg/twitter') {
            return $next($request);
        } elseif ($request->route()->uri == 'api/v2/clint/content/reply') {
            return $next($request);
        } elseif ($request->route()->uri == 'api/v2/client/news') {
            return $next($request);
        } elseif ($request->route()->uri == 'api/v2/client/course/list/{course_code}') {
            return $next($request);
        } elseif ($request->route()->uri == 'api/v2/client/course/detail/{video_key}') {
            return $next($request);
        } elseif ($request->route()->uri == 'api/v2/client/talkshow/{video_key}') {
            return $next($request);
        } elseif ($request->route()->uri == 'api/v2/client/rechristen') {
            return $next($request);
        } elseif ($request->route()->uri == 'api/v2/client/cache/{session_id}') {
            return $next($request);
        } elseif ($request->route()->uri == 'api/v2/client/history') {
            return $next($request);
        } elseif ($request->route()->uri == 'api/v2/client/course/{courseCode}/description') {
            return $next($request);
        } elseif ($request->route()->uri == 'api/v2/client/stock_report/{report_id}') {
            return $next($request);
        } elseif ($request->route()->uri == 'api/v2/client/kit_report/{report_id}') {
            return $next($request);
        } elseif ($request->route()->uri == 'api/v2/client/kit/detail/{kit_code}') {
            return $next($request);
        } elseif ($request->route()->uri == 'api/v2/client/dynamic/ad') {
            return $next($request);
        } elseif ($request->route()->uri == 'api/v2/client/live/talkshow/{talkshow_code}') {
            return $next($request);
        } elseif ($request->route()->uri == 'api/v2/client/params') {
            return $next($request);
        } else {
            $sessionId = $request->header('X-SessionId');
            if (empty($sessionId)) {
                $sessionId = $request->cookie('X-SessionId');
            }
        }
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

        return $next($request);
    }
}
