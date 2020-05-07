<?php

namespace Matrix\Models;

use Illuminate\Support\Facades\Cache;

class Permission extends BaseModel
{
    //
    public function createPermissionTree(array $permissionList, string $preCode = 'root')
    {
        $permissionTree = [];
        foreach ($permissionList as $permission) {
            if ($preCode === $permission['pre_code']) {
                $permissionTree[] = $permission;
            }
        }
        if (empty($permissionTree)) {
            return [];
        }
        foreach ($permissionTree as &$permission) {
            $permission['child'] = $this->createPermissionTree($permissionList, $permission['code']);
        }

        return $permissionTree;
    }

    public function getPermissionTree()
    {
        $permissionTreeKey = 'permission_tree';
        $permissionTree = Cache::get($permissionTreeKey);
        if (NULL !== $permissionTree) {
            return $permissionTree;
        }

        $permissionList = self::where('active', 1)->get();

        if (empty($permissionList)) {
            return [];
        }

        $permissionList = $permissionList->toArray();
        $permissionTree = $this->createPermissionTree($permissionList);
        Cache::put($permissionTreeKey, $permissionTree, 30);
        return $permissionTree;
    }
}
