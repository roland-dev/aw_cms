<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

use Matrix\Contracts\PermissionManager;

class PermissionTest extends TestCase
{
    public function testGetPermissionTree(){
       $permissionManager = app(PermissionManager::class); 
       $getPermissionTreeResult = $permissionManager->getPermissionTree();
       $assert = (SYS_STATUS_OK === $getPermissionTreeResult['code']) && ('[{"id":1,"code":"admin","name":"1","pre_code":"root","active":1,"created_at":"2018-02-28 16:35:45","updated_at":"2018-02-28 16:35:51","child":[{"id":2,"code":"admin_1","name":"2","pre_code":"admin","active":1,"created_at":"2018-02-28 16:35:55","updated_at":"2018-02-28 16:36:01","child":[{"id":4,"code":"admin_3","name":"4","pre_code":"admin_1","active":1,"created_at":"2018-02-28 16:36:08","updated_at":"2018-02-28 16:36:24","child":[]},{"id":5,"code":"admin_4","name":"5","pre_code":"admin_1","active":1,"created_at":"2018-02-28 16:36:11","updated_at":"2018-02-28 16:36:27","child":[]},{"id":7,"code":"admin_6","name":"7","pre_code":"admin_1","active":1,"created_at":"2018-02-28 16:36:17","updated_at":"2018-02-28 16:36:33","child":[]}]},{"id":3,"code":"admin_2","name":"3","pre_code":"admin","active":1,"created_at":"2018-02-28 16:36:04","updated_at":"2018-02-28 16:36:20","child":[{"id":6,"code":"admin_5","name":"6","pre_code":"admin_2","active":1,"created_at":"2018-02-28 16:36:14","updated_at":"2018-02-28 16:36:30","child":[]}]}]}]' === json_encode($getPermissionTreeResult['data']['permission_tree']));
       $this->assertTrue($assert);
    }

     public function testGetUserPermissionTree(){
       $userId = 1;
       $permissionManager = app(PermissionManager::class);
       Auth::loginUsingId(1,true);
       $getUserPermissionTree = $permissionManager->getUserPermission($userId);
	   $assert = (SYS_STATUS_OK === $getUserPermissionTree['code']) && (json_encode($getUserPermissionTree['data']['grantedPermissionTree']) === '[{"id":1,"code":"admin","name":"1","pre_code":"root","active":1,"created_at":"2018-02-28 16:35:45","updated_at":"2018-02-28 16:35:51","child":[{"id":2,"code":"admin_1","name":"2","pre_code":"admin","active":1,"created_at":"2018-02-28 16:35:55","updated_at":"2018-02-28 16:36:01","child":[{"id":4,"code":"admin_3","name":"4","pre_code":"admin_1","active":1,"created_at":"2018-02-28 16:36:08","updated_at":"2018-02-28 16:36:24","child":[],"granted":false},{"id":5,"code":"admin_4","name":"5","pre_code":"admin_1","active":1,"created_at":"2018-02-28 16:36:11","updated_at":"2018-02-28 16:36:27","child":[],"granted":false},{"id":7,"code":"admin_6","name":"7","pre_code":"admin_1","active":1,"created_at":"2018-02-28 16:36:17","updated_at":"2018-02-28 16:36:33","child":[],"granted":false}],"granted":false},{"id":3,"code":"admin_2","name":"3","pre_code":"admin","active":1,"created_at":"2018-02-28 16:36:04","updated_at":"2018-02-28 16:36:20","child":[{"id":6,"code":"admin_5","name":"6","pre_code":"admin_2","active":1,"created_at":"2018-02-28 16:36:14","updated_at":"2018-02-28 16:36:30","child":[],"granted":false}],"granted":false}],"granted":false}]');
       $this->assertTrue($assert);
     }

    public function testGetGrantedPermissionTree(){
       $grantedCodeList = ['permission'];
       $getPermissionTree = '[{"id":1,"code":"admin","name":"1","pre_code":"root","active":1,"created_at":"2018-02-28 16:35:45","updated_at":"2018-02-28 16:35:51","child":[{"id":2,"code":"admin_1","name":"2","pre_code":"admin","active":1,"created_at":"2018-02-28 16:35:55","updated_at":"2018-02-28 16:36:01","child":[{"id":4,"code":"admin_3","name":"4","pre_code":"admin_1","active":1,"created_at":"2018-02-28 16:36:08","updated_at":"2018-02-28 16:36:24","child":[]},{"id":5,"code":"admin_4","name":"5","pre_code":"admin_1","active":1,"created_at":"2018-02-28 16:36:11","updated_at":"2018-02-28 16:36:27","child":[]},{"id":7,"code":"admin_6","name":"7","pre_code":"admin_1","active":1,"created_at":"2018-02-28 16:36:17","updated_at":"2018-02-28 16:36:33","child":[]}]},{"id":3,"code":"admin_2","name":"3","pre_code":"admin","active":1,"created_at":"2018-02-28 16:36:04","updated_at":"2018-02-28 16:36:20","child":[{"id":6,"code":"admin_5","name":"6","pre_code":"admin_2","active":1,"created_at":"2018-02-28 16:36:14","updated_at":"2018-02-28 16:36:30","child":[]}]}]}]';

       $arr = json_decode($getPermissionTree,TRUE); 
       $permissionManager = app(PermissionManager::class);
       $getGrantedPermissionTree = $permissionManager->getGrantedPermissionTree($arr,$grantedCodeList);
       $assert =json_encode($getGrantedPermissionTree) === '[{"id":1,"code":"admin","name":"1","pre_code":"root","active":1,"created_at":"2018-02-28 16:35:45","updated_at":"2018-02-28 16:35:51","child":[{"id":2,"code":"admin_1","name":"2","pre_code":"admin","active":1,"created_at":"2018-02-28 16:35:55","updated_at":"2018-02-28 16:36:01","child":[{"id":4,"code":"admin_3","name":"4","pre_code":"admin_1","active":1,"created_at":"2018-02-28 16:36:08","updated_at":"2018-02-28 16:36:24","child":[],"granted":false},{"id":5,"code":"admin_4","name":"5","pre_code":"admin_1","active":1,"created_at":"2018-02-28 16:36:11","updated_at":"2018-02-28 16:36:27","child":[],"granted":false},{"id":7,"code":"admin_6","name":"7","pre_code":"admin_1","active":1,"created_at":"2018-02-28 16:36:17","updated_at":"2018-02-28 16:36:33","child":[],"granted":false}],"granted":false},{"id":3,"code":"admin_2","name":"3","pre_code":"admin","active":1,"created_at":"2018-02-28 16:36:04","updated_at":"2018-02-28 16:36:20","child":[{"id":6,"code":"admin_5","name":"6","pre_code":"admin_2","active":1,"created_at":"2018-02-28 16:36:14","updated_at":"2018-02-28 16:36:30","child":[],"granted":false}],"granted":false}],"granted":false}]';
       $this->assertTrue($assert);
    }

   public function testGrantUserPermission(){
       $userId = 7;
       $permissions = ['master','master2'];
       $permissionManager = app(PermissionManager::class);
       $userGrantPermission = $permissionManager->grant($userId,$permissions);
       $assert = SYS_STATUS_OK == $userGrantPermission['code']; 
       $this->assertTrue($assert);
   }
}
