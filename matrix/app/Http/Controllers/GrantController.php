<?php                                                                                                                                          
namespace Matrix\Http\Controllers;

use Illuminate\Http\Request;
use Exception;
use Log;
use Matrix\Exceptions\MatrixException;

use Matrix\Contracts\PermissionManager;

class GrantController extends Controller
{
    private $request;
    private $permissionManager;

    public function __construct(Request $request, PermissionManager $permissionManager)
    {
        $this->request = $request;
        $this->permissionManager = $permissionManager;
    }


    public function grant()
    {
        $ret = ['code' => SYS_STATUS_OK];

        $reqData = $this->request->validate([
            'user_id' => 'required|integer|min:1',
            'permission_list' => 'nullable|array',
        ]);
        $userId = array_get($reqData, 'user_id');
        $permissionList = array_get($reqData, 'permission_list');
        $grantData =  $this->permissionManager->grant($userId,$permissionList);

        try {
            $this->checkServiceResult($grantData,'Grant');
        } catch(Exception $e) {
            $ret['code'] = array_get($grantData, 'code', SYS_STATUS_ERROR_UNKNOW);
        }

        return $ret;
    }
 
    public function getMoreUserGrantedList()
    {
        $reqData = $this->request->validate([
            'page_no' => 'nullable|integer',
            'paeg_size' => 'nullable|integer',
            'name' => 'nullable|string',
            'type' => 'nullable|string',
            'permission_code' => 'nullable|string',
        ]);

        
        try {
            $pageNo = array_get($reqData, 'page_no', 1);
            $pageSize = array_get($reqData, 'page_size', 10);

            $userGrantedList = $this->permissionManager->getMoreUserGrantedList($pageNo, $pageSize, $reqData);
            $userGrantedCnt = $this->permissionManager->getMoreUserGrantedCnt($reqData);

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'user_granted_list' => $userGrantedList,
                    'user_granted_cnt' => $userGrantedCnt,
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
}

