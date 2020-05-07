<?php

namespace Matrix\Http\Controllers\OpenApi;

use Illuminate\Http\Request;
use Matrix\Contracts\OpenApiContract;
use Matrix\Exceptions\MatrixException;
use Illuminate\Validation\ValidationException;
use Log;

class CustomAppController extends BaseController
{
    //
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function applyToken(OpenApiContract $openApi)
    {
        $credentials = $this->request->validate([
            'code' => 'required|string|size:8',
            'secret' => 'required|string|size:32',
        ]);

        try {
            $appCode = array_get($credentials, 'code');
            $appSecret = array_get($credentials, 'secret');
            $openApiInfo = $openApi->getCustomApp($appCode)->generateToken($appSecret)->show();

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'token' => array_get($openApiInfo, 'token'),
                    'expired' => time() + 3600,
                ],
            ];
        } catch ( ValidationException $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => $e->getCode(),
                'msg' => $e->errors(),
            ];
        } catch (Exception $e) {
            Log::error('Unknow exception: ', [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '系统未知错误',
            ];
        }

        return $ret;
    }
}
