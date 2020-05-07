<?php

namespace Matrix\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Matrix\Exceptions\OpenApiException;
use Illuminate\Validation\ValidationException;
use Matrix\Contracts\OpenApiContract;
use Matrix\Contracts\PermissionManager;
use Matrix\Exceptions\MatrixException;
use Log;
use Exception;

class OpenApiController extends Controller
{
    const FORCE_FLUSH = 1;
    const UN_FORCE_FLUSH = 0;
    //
    protected $request;
    protected $openApi;

    public function __construct(Request $request, OpenApiContract $openApi)
    {
        $this->request = $request;
        $this->openApi = $openApi;
    }

    /**
    *产生OpenApi code 和 secret
    *@param $name string
    *@param $remark string
    *@return array
    */
    public function create()
    {
        try{
            $reqData = $this->request->validate([
                'name' => 'required|string',
                'remark' => 'required|string',
            ]);

            $name = array_get($reqData, 'name');

            $remark = array_get($reqData, 'remark');

            $customAppInfo = $this->openApi->generateCustomApp($name, $remark)->show();

            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => "Custom App $name already created successful",
            ];
        }catch(ValidationException $e){
            Log::error($e->getMessage(), [$e]);

            $ret = [
                'code' => LOST_PARAMS_ERROR,
                'msg' => $e->errors(),
            ];
        } catch (MatrixException $e) {
            //Log::error("Custom App $name already exists.", [$e]);
            Log::error($e->getMessage(), [$e]);

            $ret = [
                'code' => OPEN_API_APP_EXISTS,
                'msg' => "Custom App $name already exists",
            ];
        } catch (Exception $e) {
            //Log::error("Custom App $name unknow error.", [$e]);
            Log::error($e->getMessage(), [$e]);

            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => "Custom App $name unknow error",
            ];
        }

        return $ret;
    }

    /**
    *更新customApp基本信息
    *@param $code string
    *@param $name string
    *@param $remark string
    *@return array
    */
    public function updateBasicInfo()
    {
        try{
            $reqData = $this->request->validate([
                'name' => 'required|string',
                'code' => 'required|string',
                'remark' => 'required|string',
            ]);

            $name = array_get($reqData, 'name');

            $code = array_get($reqData, 'code');

            $remark = array_get($reqData, 'remark');

            $customAppInfo = $this->openApi->getCustomApp($code)->updateCustomAppBasicInfo($name, $remark, $code);

            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => "Custom App $name update successfully",
            ];
        } catch (ValidationException $e){
            Log::error($e->getMessage(), [$e]);

            $ret = [
                'code' => LOST_PARAMS_ERROR,
                'msg' => $e->errors(),
            ];
        } catch (OpenApiException $e) {
            Log::error($e->getMessage(), [$e]);

            $ret = [
                'code' => $e->getCode(),
                'msg' => "Custom App $code not found.",
            ];
        } catch (Exception $e){
            Log::error($e->getMessage(), [$e]);

            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => "Custom App $name update fails",
            ];
        }

        return $ret;
    }

    /**
    *更新customApp基本信息
    *@param $code string
    *@return array
    */
    public function updateSecret()
    {
        try{
            $reqData = $this->request->validate([
                'code' => 'required|string',
            ]);

            $code = array_get($reqData, 'code');

            $customAppInfo = $this->openApi->getCustomApp($code)->updateSecret();

            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => "update $code's secret successfully",
            ];
        }catch(ValidationException $e){
            Log::error($e->getMessage(), [$e]);

            $ret = [
                'code' => LOST_PARAMS_ERROR,
                'msg' => $e->errors(),
            ];
        } catch (OpenApiException $e) {
            Log::error($e->getMessage(), [$e]);

            $ret = [
                'code' => OPEN_API_APP_NOT_FOUND,
                'msg' => "Custom App $code not found.",
            ];
        }catch(Exception $e){
            Log::error($e->getMessage(), [$e]);

            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => "update $code's secret fails",
            ];
        }

        return $ret;
    }

    /**
    *锁第三方账户
    *@param $code string 第三方code值
    *@return array
    */
    public function lock()
    {
        try{
            $reqData = $this->request->validate([
                'code' => 'required|string',
            ]);

            $code = array_get($reqData, 'code');

            $customAppInfo = $this->openApi->getCustomApp($code)->lock();

            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => "lock $code's successfully",
            ];
        }catch(ValidationException $e){
            Log::error($e->getMessage(), [$e]);

            $ret = [
                'code' => LOST_PARAMS_ERROR,
                'msg' => $e->errors(),
            ];
        } catch (OpenApiException $e) {
            Log::error($e->getMessage(), [$e]);

            $ret = [
                'code' => OPEN_API_APP_NOT_FOUND,
                'msg' => $e->getMessage(),
            ];
        }catch(Exception $e){
            Log::error($e->getMessage(), [$e]);

            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => "lock $code's fails",
            ];
        }

        return $ret;
    }

    /**
    *解锁第三方账户
    *@param $code string 第三方code值
    *@return array
    */
    public function unLock()
    {
        try{
            $reqData = $this->request->validate([
                'code' => 'required|string',
            ]);

            $code = array_get($reqData, 'code');

            $customAppInfo = $this->openApi->getCustomApp($code)->unlock();

            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => "unLock $code's successfully",
            ];
        }catch(ValidationException $e){
            Log::error($e->getMessage(), [$e]);

            $ret = [
                'code' => LOST_PARAMS_ERROR,
                'msg' => $e->errors(),
            ];
        } catch (OpenApiException $e) {
            Log::error($e->getMessage(), [$e]);

            $ret = [
                'code' => OPEN_API_APP_NOT_FOUND,
                'msg' => $e->getMessage(),
            ];
        }catch(Exception $e){
            Log::error($e->getMessage(), [$e]);

            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => "unLock $code's fails",
                //'msg' => $e->getMessage(),
            ];
        }

        return $ret;
    }

    /**
    *获取customApp列表
    *@return array
    **/
    public function getCustomAppList()
    {
        try{
            $reqData = $this->request->validate([
                'name' => 'string|nullable',
                'code' => 'string|nullable',
            ]);

            $name = array_get($reqData, 'name');

            $code = array_get($reqData, 'code');

            $customAppList = $this->openApi->getCustomAppList($name, $code);

            foreach($customAppList as $customAppInfo){
                $customAppInfo['secret'] = substr_replace($customAppInfo['secret'], '********', 5, 20);
            }

            foreach($customAppList as $customAppInfo){
                $getPermissionList = $this->openApi->getGuardedList($customAppInfo['code'], self::UN_FORCE_FLUSH);

                $customAppInfo['permission'] = empty($getPermissionList) ? '' : implode(',', $getPermissionList);
            }

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => $customAppList,
            ];
        }catch(Exception $e){
            Log::error($e->getMessage(), [$e]);

            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                //'msg' => $e->getMessage(),
                'msg' => '系统未知错误',
            ];
        }

        return $ret;
    }

    public function getCustomAppListOfPaging ()
    {

        $credentials = $this->request->validate([
            'page_no' => 'nullable|integer',
            'page_size' => 'nullable|integer',
            'name' => 'nullable|string',
            'code' => 'nullable|string',
        ]);

        try {
            $pageNo = (int)array_get($credentials, 'page_no', 1);
            $pageSize = (int)array_get($credentials, 'page_size', 10);

            $customAppList = $this->openApi->getCustomAppListOfPaging($pageNo, $pageSize, $credentials);
            $customAppCnt = $this->openApi->getCustomAppCnt($credentials);

            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => 'success',
                'data' => [
                    'custom_app_list' => $customAppList,
                    'custom_app_cnt' => $customAppCnt,
                ],
            ];
        } catch (MatrixException $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage(),
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '未知错误',
            ];
        }

        return $ret;
    }

    /**
    *获取customApp详情
    *@param $code string
    *@return array
    **/
    public function getCustomAppInfo($code)
    {
        try{
            //$route = Route::getRoutes()->match($this->request);
            //$uri = $route->uri();

            //$uri = $this->request->route()->uri();
            if(empty($code)){
                throw new OpenApiException("Custom App $code not found.");
            }

            $customAppInfo = $this->openApi->getCustomApp($code)->show();

            $customAppInfo['secret'] = substr_replace($customAppInfo['secret'], '********', 5, 20);
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => $customAppInfo,
            ];
        }catch(ValidationException $e){
            Log::error($e->getMessage(), [$e]);

            $ret = [
                'code' => LOST_PARAMS_ERROR,
                'msg' => $e->errors(),
            ];
        } catch (OpenApiException $e) {
            Log::error($e->getMessage(), [$e]);

            $ret = [
                'code' => OPEN_API_APP_NOT_FOUND,
                'msg' => $e->getMessage(),
            ];
        }catch(Exception $e){
            Log::error($e->getMessage(), [$e]);

            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                //'msg' => $e->getMessage(),
                'msg' => '系统未知错误',
            ];
        }

        return $ret;
    }

    /**
    *获取权限信息
    *@return array
    **/
    public function getPermissionList()
    {
        try{
            $permissionTree = $this->openApi->getPermissionTree();

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => $permissionTree,
                'msg' => 'success',
            ];
        }catch(Exception $e){
            Log::error($e->getMessage(), [$e]);

            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                //'msg' => $e->getMessage(),
                'msg' => '系统未知错误',
            ];
        }

        return $ret;
    }

    /**
    *获取权限信息
    *@return array
    **/
    public function getCodePermission($code)
    {
        try{
            //获取权限树
            $permissionTree = $this->openApi->getPermissionTree();
            //return $permissionTree;

            //获取被授予的权限
            $grantedCodeList = $this->openApi->getGuardedList($code, self::UN_FORCE_FLUSH);

            foreach ($permissionTree as $key => $permission) {
                $permissionTree[$key]['granted'] = in_array($permission['code'], $grantedCodeList);

                if (!empty($permissionTree[$key]['child'])) {
                    foreach ($permissionTree[$key]['child'] as $index => $permissionChild) {
                        $permissionChild['granted'] = in_array($permissionChild['code'], $grantedCodeList);
                    }

                    //通过child权限判定father权限是否存在
                    $childGrantedList = array_column($permissionTree[$key]['child'], 'granted');

                    if (count(array_unique($childGrantedList)) === 1) {
                        //如果全是false/true
                        $permissionTree[$key]['granted'] = $childGrantedList[0];
                    } else {
                        //child权限不全部被选中，同时存在授予与未被授予的权限
                        $permissionTree[$key]['granted'] = 1;
                    }
                }
            }

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => $permissionTree,
            ];
        }catch(Exception $e){
            Log::error($e->getMessage(), [$e]);

            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                //'msg' => $e->getMessage(),
                'msg' => '系统未知错误',
            ];
        }

        return $ret;
    }


    /**
    *openApi权限添加
    *@param $code string 第三方理code
    *@param $guard_list array 权限列表
    *@return array
    **/
    public function grant()
    {
        try{
            DB::beginTransaction();

            $guardList = [];

            $reqData = $this->request->validate([
                'code' => 'required|string',
                'guard_list' => 'array|nullable',
            ]);

            $code = array_get($reqData, 'code');

            $guardList = array_get($reqData, 'guard_list');

            $customAppInfo = $this->openApi->getCustomApp($code)->show();

            $getPermissionList = $this->openApi->getGuardedList($code, self::UN_FORCE_FLUSH);

            $addList = array_diff($guardList, $getPermissionList);

            $force = 0;//是否强制刷新标记

            if(!empty($addList)){
                foreach($addList as $add){
                    $this->openApi->addOpenapiGuard($code, $add);
                }

                $force = 1;
            }

            $removeList = array_diff($getPermissionList, $guardList);

            if(!empty($removeList)){
                foreach($removeList as $remove){
                    $this->openApi->removeOpenapiGuard($code, $remove);
                }

                $force = 1;
            }

            if(!empty($force)){
                $getPermissionList = $this->openApi->getGuardedList($code, self::FORCE_FLUSH);
            }

            DB::commit();

            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => 'success',
            ];
        }catch(ValidationException $e){
            DB::rollBack();

            Log::error($e->getMessage(), [$e]);

            $ret = [
                'code' => LOST_PARAMS_ERROR,
                'msg' => $e->errors(),
            ];
        } catch (OpenApiException $e) {
            DB::rollBack();

            Log::error($e->getMessage(), [$e]);

            $ret = [
                'code' => OPEN_API_APP_NOT_FOUND,
                'msg' => $e->getMessage(),
            ];
         } catch (Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage(), [$e]);

            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => "Grant Custom App $name unknow error",
            ];
        }

        return $ret;
    }
}
