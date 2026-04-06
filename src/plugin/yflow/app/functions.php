<?php

use plugin\admin\app\model\Role;
use support\Log;

/**
 * Here is your custom functions.
 */


if (!function_exists('getLoginUser')) {
    /**
     * 获取登录用户信息
     */
    function getLoginUser(): array
    {
        $roles_arr = [];
        $admin = admin();
        $role_ids = $admin['roles'];
        $roles = Role::whereIn('id', $role_ids)->select(['id', 'name'])->get()->all();
        foreach ($roles as $role) {
            $roles_arr[] = ['role_id' => $role['id'], 'role_name' => $role['name']];
        }
        return [
            'user_id' => admin_id(),
            'roles' => $roles_arr
        ];
    }
}


if (!function_exists('getPermissionList')) {
    /**
     * 获取权限列表
     *
     * @param string $userId 用户 ID
     * @param array $sysUser 用户信息
     * @return array
     */
    function getPermissionList(string $userId, array $sysUser): array
    {
        $permissionList = [];

        // 获取用户角色
        $roles = $sysUser['roles'] ?? [];
        if (!empty($roles)) {
            foreach ($roles as $role) {
                $permissionList[] = 'role:' . $role['role_id'];
            }
        }

        $permissionList[] = $userId;

        // 记录日志
        Log::info('当前用户所有权限：' . json_encode($permissionList));

        return $permissionList;
    }
}
