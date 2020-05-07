<?php

namespace Matrix\Http\Middleware;

use Closure;
use Matrix\Contracts\OpenApiContract;
use Matrix\Exceptions\MatrixException;
use Exception;
use Log;

class OpenApiProtect
{
    protected $openApi;

    public function __construct(OpenApiContract $openApi)
    {
        $this->openApi = $openApi;
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
        Log::debug('Open API Request Debug: ', [$request]);

        $credentials = $request->validate([
            'code' => 'required|size:8',
            'token' => 'required|size:32',
        ]);

        $code = array_get($credentials, 'code');
        $token = array_get($credentials, 'token');

        try {
            $uri = $request->route()->uri();

            $requestMethod = $request->method();

            $this->openApi->getCustomApp($code)->checkToken($token);

            $this->openApi->getCustomApp($code)->checkPermission($code, $uri, $requestMethod);
        } catch (MatrixException $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => $e->getCode(),
                'msg' => 'token校验失败',
            ];
            return response()->Json($ret);
        } catch (Exception $e) {
            Log::error('Unknow exception: ', [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '系统未知错误信息',
            ];
            return response()->Json($ret);
        }

        return $next($request);
    }
}
