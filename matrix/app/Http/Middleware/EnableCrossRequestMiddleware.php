<?php

namespace Matrix\Http\Middleware;

use Closure;

class EnableCrossRequestMiddleware
{

    protected function getAllowOrigin()
    {
        return (array)config('app.allow_origin');
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
        $allowOrigin = $this->getAllowOrigin();
        $response = $next($request);
        $origin = $request->server('HTTP_ORIGIN') ?? '';

        if (in_array($origin, $allowOrigin)) {
            $response->headers->add(['Access-Control-Allow-Origin' => $origin]);
            $response->headers->add(['Access-Control-Allow-Headers' => 'Origin, Content-Type, Cookie, X-CSRF-TOKEN, Accept, Authorization, X-XSRF-TOKEN, X-SessionId']);
            $response->headers->add(['Access-Control-Expose-Headers' => 'Authorization, authenticated']);
            $response->headers->add(['Access-Control-Allow-Methods' => 'GET, POST, PATCH, PUT, OPTIONS']);
            $response->headers->add(['Access-Control-Allow-Credentials' => 'true']);
        }

        return $response;
    }
}
