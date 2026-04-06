<?php

namespace plugin\yflow\custom\adapter;

use Yflow\core\enums\CooperateType;
use Yflow\core\invoker\FrameInvoker;
use Yflow\core\service\TaskService;

/**
 * 流程适配器抽象类
 *
 *
 * @since 2023/5/29
 */
abstract class AbstractWarmFlowAdapter
{
    /**
     * 任务服务
     * @var TaskService|null
     */
    protected ?TaskService $taskService;

    /**
     * 构造函数
     */
    public function __construct()
    {
        // 初始化服务
        $this->taskService = FrameInvoker::getBean(TaskService::class);
    }

    /**
     * 获取权限
     *
     * @param array $sysUser 用户信息
     * @param string|int $userId 用户ID
     * @return array 权限列表
     */
    protected function permissionList(array $sysUser, string|int $userId): array
    {
        $roles = $sysUser['roles'] ?? [];
        $permissionList = [];
        foreach ($roles as $role) {
            $permissionList[] = 'role:' . $role['role_id'];
        }
        $permissionList[] = $userId;
        return $permissionList;
    }

    /**
     * 根据类型获取描述
     *
     * @param int $type 流程类型
     * @return string 描述
     */
    protected function type(int $type): string
    {
        return CooperateType::getValueByKey($type);
    }
}
