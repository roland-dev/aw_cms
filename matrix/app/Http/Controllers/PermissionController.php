<?php

namespace Matrix\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Matrix\Contracts\PermissionManager;

class PermissionController extends Controller
{
    //
    private $request;
    private $permissionManager;

    public function __construct(Request $request, PermissionManager $permissionManager)
    {
        $this->request = $request;
        $this->permissionManager = $permissionManager;
    }

    public function show($userId = 0)
    {
        $userId = 0 === $userId ? Auth::id() : $userId;

        $permissionData = $this->permissionManager->getUserPermission($userId);
        $this->checkServiceResult($permissionData, 'Permission');
        $grantedPermissionTree = array_get($permissionData, 'data.grantedPermissionTree', []);

        return [
            'code' => SYS_STATUS_OK,
            'data' => [
                'menu' => $grantedPermissionTree,
            ],
        ];
    }
  
}
