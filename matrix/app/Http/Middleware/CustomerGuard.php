<?php

namespace Matrix\Http\Middleware;

use Closure;
use Cache;

class CustomerGuard
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $sessionId = $request->header('X-SessionId');
        if (empty($sessionId)) {
            abort(401);
        }
        $session = Cache::get($sessionId);
        if (empty($session)) {
            abort(401);
        }
        return $next($request);
    }
}
