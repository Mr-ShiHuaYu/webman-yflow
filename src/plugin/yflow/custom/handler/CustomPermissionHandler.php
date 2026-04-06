<?php

namespace plugin\yflow\custom\handler;

use Yflow\core\handler\PermissionHandler;

/**
 * 办理人权限处理器--实现类
 * 用户获取工作流中用到的 permissionFlag 和 handler
 * permissionFlag: 办理人权限标识，比如用户，角色，部门等，用于校验是否有权限办理任务
 * handler: 当前办理人唯一标识，就是确定唯一用的，如用户 id，通常用来入库，记录流程实例创建人，办理人,如create_by,update_by
 *
 */
class CustomPermissionHandler implements PermissionHandler
{
    public function permissions(): array
    {
        if (!$admin = admin()) {
            return [];
        }
        $role_ids = $admin['roles'];
        return array_map(fn ($role_id) => "role:{$role_id}", $role_ids);
    }

    public function getHandler(): ?string
    {
        return admin_id();
    }

    public function convertPermissions($permissions): array
    {
        return $permissions;
    }
}
