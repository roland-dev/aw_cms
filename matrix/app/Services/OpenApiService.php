<?php
namespace Matrix\Services;

use Matrix\Contracts\OpenApiContract;
use Matrix\Models\CustomApp;
use Matrix\Models\OpenapiPermission;
use Matrix\Models\OpenapiGuard;
use Matrix\Exceptions\OpenApiException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Cache;
use Log;

class OpenApiService extends BaseService implements OpenApiContract
{
    const FORMAT_OPEN_API_TOKEN = "openapi_token_%s"; // %s = customApp.code

    const FORMAT_OPEN_API_CODE = "openapi_code_permissions_%s"; //%s = customApp.code

    protected $customApp;

    protected function checkCustomApp()
    {
        if (empty($this->customApp)) {
            throw new OpenApiException("Must be get or generate a custom app first.", OPEN_API_MEMBER_NOT_FOUND);
        }
    }

    protected function codeGenerator(int $length = 32)
    {
        return str_random($length);
    }

    public function getCustomApp(string $code)
    {
        try {
            $customApp = CustomApp::where('code', $code)->take(1)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new OpenApiException("Custom App $code not found.", OPEN_API_APP_NOT_FOUND);
        }

        $this->customApp = $customApp;

        return $this;
    }

    public function generateCustomApp(string $name, string $remark)
    {
        try {
            $this->customApp = CustomApp::create([
                'code' => $this->codeGenerator(8),
                'name' => $name,
                'secret' => $this->codeGenerator(),
                'remark' => $remark,
                'active' => 1,
            ]);
        } catch (QueryException $e) {
            Log::error("Create custom app $name failed.", [$e]);
            throw new OpenApiException("Custom App create failed.", OPEN_API_APP_EXISTS);
        }

        return $this;
    }

    public function updateCustomAppBasicInfo(string $name, string $remark, string $code)
    {
        try {
            $this->customApp = CustomApp::where('code', $code)->update([
                'name' => $name,
                'remark' => $remark,
            ]);
        } catch (QueryException $e) {
            Log::error("Update custom app basic info $name failed.", [$e]);
            throw new OpenApiException("Custom App update failed", OPEN_API_APP_NOT_FOUND);
        }

        return $this;
    }

    public function show()
    {
        $this->checkCustomApp();
        $openApiTokenCacheKey = sprintf(self::FORMAT_OPEN_API_TOKEN, $this->customApp->code);
        $customApp = $this->customApp->toArray();
        $customApp['token'] = Cache::get($openApiTokenCacheKey);

        return $customApp;
    }

    public function lock()
    {
        $this->checkCustomApp();

        if ($this->customApp->active !== 1) {
            throw new OpenApiException("Custom App {$this->customApp->code} locked.", OPEN_API_APP_LOCKED);
        }

        $this->customApp->active = 0;
        $this->customApp->save();

        return $this;
    }

    public function unlock()
    {
        $this->checkCustomApp();

        if ($this->customApp->active !== 0) {
            throw new OpenApiException("Custom App {$this->customApp->code} unlocked.", OPEN_API_APP_LOCKED);
        }

        $this->customApp->active = 1;
        $this->customApp->save();

        return $this;
    }

    public function updateSecret()
    {
        $this->checkCustomApp();
        $this->customApp->secret = $this->codeGenerator();
        $this->customApp->save();

        return $this;
    }

    public function generateToken(string $secret)
    {
        $this->checkCustomApp();

        if ($this->customApp->active !== 1) {
            throw new OpenApiException("Custom App {$this->customApp->code} locked.", OPEN_API_APP_LOCKED);
        }

        if ($this->customApp->secret === $secret) {
            $openApiTokenCacheKey = sprintf(self::FORMAT_OPEN_API_TOKEN, $this->customApp->code);
            $token = $this->codeGenerator();
            Cache::put($openApiTokenCacheKey, $token, 60);
        } else {
            throw new OpenApiException("App {$this->customApp->code} bad secret.", OPEN_API_SECRET_ERROR);
        }

        return $this;
    }

    public function checkToken(string $tryToken)
    {
        $this->checkCustomApp();

        if ($this->customApp->active !== 1) {
            throw new OpenApiException("Custom App {$this->customApp->code} locked.", OPEN_API_APP_LOCKED);
        }

        $openApiTokenCacheKey = sprintf(self::FORMAT_OPEN_API_TOKEN, $this->customApp->code);
        $token = Cache::get($openApiTokenCacheKey);
        if ($tryToken !== $token) {
            throw new OpenApiException("App {$this->customApp->code} bad token.", OPEN_API_TOKEN_ERROR);
        }

        return $this;
    }

    public function getCustomAppList($name, $code)
    {
        $condition = [];

        if(!empty($name)){
            $condition[] = ['name', 'like', "%$name%"];
        }

        if(!empty($code)){
            $condition[] = ['code', 'like', "%$code%"];
        }

        $list = CustomApp::where($condition)->orderBy('updated_at', 'desc')->get();

        return $list;
    }

    public function getPermissionTree()
    {
        $permissionTreeKey = 'openapi_permission_tree';

        $permissionTree = [];

        $permissionTree = Cache::get($permissionTreeKey);

        if(!empty($permissionTree)){
            return $permissionTree;
        }

        $list = OpenapiPermission::where('active', 1)->orderBy('created_at', 'asc')->get();

        foreach($list as $permissionInfo){
            if(!array_key_exists($permissionInfo['group_code'], $list)){
                $permissionTree[$permissionInfo['group_code']]['child'][] = $permissionInfo;

                $permissionTree[$permissionInfo['group_code']]['code'] = $permissionInfo['group_code'];

                $permissionTree[$permissionInfo['group_code']]['name'] = $permissionInfo['group_name'];

                continue;
            }

            $permissionTree[$permissionInfo['group_code']]['child'][] = $permissionInfo;
        }

        //去掉group_code索引
        $permissionTree = array_column($permissionTree, NULL);

        Cache::put($permissionTreeKey, $permissionTree, 30);

        return $permissionTree;
    }

    public function getGuardedList(string $code, int $force = 0)
    {
        $list = [];

        $guardedCodeKey  = sprintf(self::FORMAT_OPEN_API_CODE, $code);

        if( 1 == $force ){//force 强制刷新 0 否 1 是
            Cache::forget($guardedCodeKey);
        }

        $guardedList = Cache::get($guardedCodeKey);

        if(!empty($guardedList)){
            return $guardedList;
        }

        //$guardedList = OpenapiGuard::where('openapi_code', $code)->where('active', 1)->get();
        $guardedList = OpenapiGuard::where('openapi_code', $code)->get();

        foreach($guardedList as $guarded){
            $list[] = $guarded['permission_code'];
        }

        Cache::put($guardedCodeKey, $list, 30);

        return $list;
    }

    public function addOpenapiGuard(string $customAppCode, string $permissionCode)
    {
        try{
            $guardedList = OpenapiGuard::create([
                'openapi_code' => $customAppCode,
                'permission_code' => $permissionCode,
                //'active' => 1,
            ]);
        } catch (QueryException $e) {
            Log::error("guard openapi failed.", [$e]);
            throw new OpenApiException("guard openapi failed.");
        }

        return $this;
    }

    public function removeOpenapiGuard(string $customAppCode, string $permissionCode)
    {
        try{
            $guardedList = OpenapiGuard::where(['permission_code' => $permissionCode])->where(['openapi_code' => $customAppCode])->delete();
        } catch (QueryException $e) {
            Log::error("delete openapi guarded failed.", [$e]);
            throw new OpenApiException("delete openapi guarded failed.");
        }

        return $this;
    }

    public function checkPermission(string $code, string $uri, string $requestMethod)
    {
        $this->checkCustomApp();

        $permissionInfo = OpenapiPermission::where('uri', $uri)->where('request_method', $requestMethod)->where('active', 1)->first();

        if(empty($permissionInfo)){
            throw new OpenApiException("uri not found.", OPEN_API_URI_NOT_FOUND);
        }

        $permissionList = $this->getGuardedList($code);

        if(!in_array($permissionInfo['code'], $permissionList)){
            throw new OpenApiException("Custom App have no permission.", OPEN_API_MEMBER_NOT_PERMISSION);
        }

        return $this;
    }

    public function getCustomAppListOfPaging(int $pageNo, int $pageSize, array $credentials)
    {
        $cond = [];

        $name = array_get($credentials, 'name');
        if (!empty($name)) {
            $cond[] = ['name', 'like', "%$name%"];
        }

        $code = array_get($credentials, 'code');
        if (!empty($code)) {
            $cond[] = ['code', 'like', "%$code%"];
        }

        $customAppList = CustomApp::where($cond)
            ->orderBy('updated_at', 'desc')
            ->skip($pageSize * ($pageNo - 1))
            ->take($pageSize)
            ->get()
            ->toArray();
        
        foreach ($customAppList as &$customApp) {
            $customApp['secret'] = substr_replace($customApp['secret'], '********', 5, 20);

            $getPermissionList = $this->getGuardedList($customApp['code'], 0);
            $customApp['permission'] = empty($getPermissionList) ? '' : implode(',', $getPermissionList);
        }
        
        return $customAppList;
    }

    public function getCustomAppCnt(array $credentials)
    {
        $cond = [];

        $name = array_get($credentials, 'name');
        if (!empty($name)) {
            $cond[] = ['name', 'like', "%$name%"];
        }

        $code = array_get($credentials, 'code');
        if (!empty($code)) {
            $cond[] = ['code', 'like', "%$code%"];
        }

        $customAppCnt = CustomApp::where($cond)->count();

        return $customAppCnt;
    }
}
