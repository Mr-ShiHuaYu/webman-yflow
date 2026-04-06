<?php

namespace plugin\yflow\app\service;


use plugin\admin\app\model\Admin;
use plugin\admin\app\model\Role;
use plugin\yflow\custom\adapter\WarmFlowAdapter;
use support\Db;
use Yflow\core\invoker\FrameInvoker;
use Yflow\impl\orm\laravel\FlowHisTaskModel;
use Yflow\impl\orm\laravel\FlowTaskModel;
use Yflow\impl\orm\laravel\FlowUserModel;

/**
 * 流程执行serviceImpl
 *
 *
 * @since 2023/5/29 13:09
 */
class ExecuteService
{
    /**
     * 适配器列表
     * @var WarmFlowAdapter[]
     */
    private static array $warmFlowAdapters = [];

    /**
     * 初始化方法
     */
    public function __construct()
    {
        // 初始化适配器列表
        // 使用FrameInvoker获取所有WarmFlowAdapter接口的实现
        self::$warmFlowAdapters = FrameInvoker::getBeansOfType(WarmFlowAdapter::class);
    }

    /**
     * 分页查询待办任务
     */
    public function toDoPage($task, $limit = 10): array
    {
        // 构建查询
        $query = FlowTaskModel::with(['definition', 'instance', 'users'])
            ->select([
                'id',
                'node_code',
                'node_name',
                'node_type',
                'definition_id',
                'instance_id',
                'create_time',
                'update_time',
                'tenant_id',
                'flow_status',
                'form_custom',
                'form_path'
            ])
            ->where('node_type', '=', 1)
            ->distinct();

        // 应用条件过滤
        if (!empty($task->getPermissionList())) {
            $query->whereHas('users', function ($q) use ($task) {
                $q->whereIn('processed_by', $task->getPermissionList());
            });
        }

        if (!empty($task->getNodeCode())) {
            $query->where('node_code', $task->getNodeCode());
        }

        if (!empty($task->getNodeName())) {
            $query->where('node_name', 'like', '%' . $task->getNodeName() . '%');
        }

        if (!empty($task->getInstanceId())) {
            $query->where('instance_id', $task->getInstanceId());
        }

        // 排序
        $query->orderBy('create_time', 'desc');

        // 执行查询
        $paginator = $query->paginate($limit);
        $total = $paginator->total();
        $results = $paginator->items();

        // 转换为模型对象并设置关联属性
        $taskList = [];
        foreach ($results as $taskModel) {
            if ($taskModel->definition) {
                $taskModel->setFlowName($taskModel->definition->getFlowName());
            }
            if ($taskModel->instance) {
                $taskModel->setBusinessId($taskModel->instance->getBusinessId());
            }
            $taskList[] = $taskModel;
        }

        return [$taskList, $total];
    }

    /**
     * 获取已办任务
     * 对应 Java SQL: donePage
     */
    public function donePage($hisTask, $limit = 10): array
    {
        // 子查询：获取每个实例的最大ID
        $subQuery = FlowHisTaskModel::selectRaw('MAX(id) as id')
            ->when(!empty($hisTask->getApprover()), function ($q) use ($hisTask) {
                $q->where('approver', $hisTask->getApprover());
            })
            ->when(!empty($hisTask->getNodeCode()), function ($q) use ($hisTask) {
                $q->where('node_code', $hisTask->getNodeCode());
            })
            ->when(!empty($hisTask->getNodeName()), function ($q) use ($hisTask) {
                $q->where('node_name', 'like', '%' . $hisTask->getNodeName() . '%');
            })
            ->when(!empty($hisTask->getInstanceId()), function ($q) use ($hisTask) {
                $q->where('instance_id', $hisTask->getInstanceId());
            })
            ->groupBy('instance_id');

        // 主查询：关联flow_his_task、flow_definition、flow_instance
        $query = FlowHisTaskModel::fromSub($subQuery, 'tmp')
            ->join('flow_his_task as t', 't.id', '=', 'tmp.id')
            ->join('flow_definition as d', 'd.id', '=', 't.definition_id')
            ->join('flow_instance as i', 'i.id', '=', 't.instance_id')
            ->select([
                't.id',
                't.node_code',
                't.node_name',
                't.cooperate_type',
                't.approver',
                't.collaborator',
                't.node_type',
                't.target_node_code',
                't.target_node_name',
                't.definition_id',
                't.instance_id',
                'i.flow_status',
                't.message',
                't.ext',
                't.create_time',
                't.update_time',
                't.tenant_id',
                'i.business_id',
                't.form_path',
                'd.flow_name'
            ])
            ->orderBy('t.create_time', 'desc');

        $paginator = $query->paginate($limit);
        $total = $paginator->total();
        $results = $paginator->items();

        return [
            'data' => $results,
            'total' => $total
        ];
    }

    /**
     * 获取抄送任务
     * 对应 Java SQL: copyPage
     */
    public function copyPage($flowTask, $limit = 10): array
    {
        // 查询flow_user表，关联flow_instance、wa_admins(用户)、flow_definition
        $query = FlowUserModel::from('flow_user as a')
            ->leftJoin('flow_instance as b', 'a.associated', '=', 'b.id')
            ->leftJoin('wa_admins as c', 'b.create_by', '=', 'c.id')
            ->leftJoin('flow_definition as d', 'b.definition_id', '=', 'd.id')
            ->select([
                'c.nickname as approver',
                'b.flow_status',
                'b.business_id',
                'a.create_time',
                'b.node_name',
                Db::raw('CAST(b.id AS CHAR) as instance_id'),
                'd.flow_name'
            ])
            ->where('a.type', '=', '4')
            ->when(!empty($flowTask->getFlowName()), function ($q) use ($flowTask) {
                $q->where('c.nickname', 'like', '%' . $flowTask->getFlowName() . '%');
            })
            ->when(!empty($flowTask->getNodeName()), function ($q) use ($flowTask) {
                $q->where('b.node_name', 'like', '%' . $flowTask->getNodeName() . '%');
            })
            ->when($flowTask->getNodeType() !== null, function ($q) use ($flowTask) {
                $q->where('b.node_type', $flowTask->getNodeType());
            })
            ->orderBy('a.create_time', 'desc');

        $paginator = $query->paginate($limit);
        $total = $paginator->total();
        $results = $paginator->items();

        return [
            'data' => $results,
            'total' => $total
        ];
    }

    /**
     * 根据ID反显姓名
     */
    public function idReverseDisplayName($ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $users = Admin::whereIn('id', $ids)->get();

        return $users->map(function ($user) {
            return [
                'user_id' => $user->id,
                'username' => $user->username,
                'nickname' => $user->nickname
            ];
        })->toArray();
    }

    /**
     * 用于待办任务中 不是减签时选择用户,如 转办|加签|委派
     * 根据条件分页查询不等于用户列表的所有用户
     */
    public function selectNotUserList($data, $limit = 10): array
    {
        // 过滤掉 userIds 中的非数字ID（如 role:1 这样的值）
        $numericUserIds = $this->filterNumericUserIds($data['userIds'] ?? []);

        $query = Admin::query()
            ->when(!empty($numericUserIds), function ($q) use ($numericUserIds) {
                $q->whereNotIn('id', $numericUserIds);
            })
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '<>', 1);
            });

        return $this->doSelectUserPage($query, $limit);
    }

    /**
     * 用于待办任务中 减签时选择用户
     * 根据条件分页查询等于用户列表的所有用户
     */
    public function selectUserList($data, $limit = 10): array
    {
        // 过滤掉 userIds 中的非数字ID（如 role:1 这样的值）
        $numericUserIds = $this->filterNumericUserIds($data['userIds'] ?? []);

        $query = Admin::query()
            ->when(!empty($numericUserIds), function ($q) use ($numericUserIds) {
                $q->whereIn('id', $numericUserIds);
            })
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '<>', 1);
            });

        return $this->doSelectUserPage($query, $limit);
    }

    /**
     * 根据ID查询名称
     */
    public function getName($id): string
    {
        if (empty($id)) {
            return '';
        }

        // 获取用户、部门、角色映射
        $userMap = $this->getUserMap();
        $roleMap = $this->getRoleMap();

        if (str_contains($id, 'user:')) {
            $userId = str_replace('user:', '', $id);
            if (isset($userMap[$userId])) {
                return '用户:' . $userMap[$userId];
            }
        } elseif (str_contains($id, 'role:')) {
            $roleId = str_replace('role:', '', $id);
            if (isset($roleMap[$roleId])) {
                return '角色:' . $roleMap[$roleId];
            }
        } else {
            if (is_numeric($id) && isset($userMap[$id])) {
                return '用户:' . $userMap[$id];
            }
        }

        return '';
    }

    /**
     * 根据ID查询名称映射
     */
    public function getNameMap($ids): array
    {
        if (empty($ids)) {
            return [];
        }

        // 获取用户、部门、角色映射
        $userMap = $this->getUserMap();
        $roleMap = $this->getRoleMap();

        $result = [];
        foreach ($ids as $id) {
            if (str_contains($id, 'user:')) {
                $userId = str_replace('user:', '', $id);
                if (isset($userMap[$userId])) {
                    $result['用户'] = $userMap[$userId];
                }
            } elseif (str_contains($id, 'role:')) {
                $roleId = str_replace('role:', '', $id);
                if (isset($roleMap[$roleId])) {
                    $result['角色'] = $roleMap[$roleId];
                }
            } else {
                if (is_numeric($id) && isset($userMap[$id])) {
                    $result['用户'] = $userMap[$id];
                } else {
                    $result[$id] = $id;
                }
            }
        }

        return $result;
    }

    /**
     * 获取用户映射
     */
    public function getUserMap(): array
    {
        $users = Admin::get();
        $userMap = [];
        foreach ($users as $user) {
            $userMap[$user->id] = $user->nickname;
        }
        return $userMap;
    }

    /**
     * 获取角色映射
     */
    private function getRoleMap(): array
    {
        $roles = Role::get();
        $roleMap = [];
        foreach ($roles as $role) {
            $roleMap[$role->id] = $role->name;
        }
        return $roleMap;
    }

    /**
     * 从用户ID列表中过滤出纯数字ID
     * 用于排除 role:1、dept:1 等非用户ID
     */
    private function filterNumericUserIds(array $userIds): array
    {
        $numericUserIds = [];
        foreach ($userIds as $id) {
            if (is_numeric($id)) {
                $numericUserIds[] = $id;
            }
        }
        return $numericUserIds;
    }


    /**
     * 处理流程交互类型
     */
    public function interactiveType(array $warmFlowInteractiveTypeVo): bool
    {
        $operatorType = $warmFlowInteractiveTypeVo['operatorType'] ?? 0;
        foreach (self::$warmFlowAdapters as $warmFlowAdapter) {
            if ($warmFlowAdapter->isAdapter($operatorType)) {
                if (is_string($warmFlowInteractiveTypeVo['addHandlers'])) {
                    $warmFlowInteractiveTypeVo['addHandlers'] = explode(',', $warmFlowInteractiveTypeVo['addHandlers']);
                }
                return $warmFlowAdapter->adapter($warmFlowInteractiveTypeVo);
            }
        }
        return false;
    }

    /**
     * 分页选择用户
     * @param mixed $query
     * @param mixed $limit
     * @return array
     */
    public function doSelectUserPage(mixed $query, mixed $limit): array
    {
        $paginator = $query->paginate($limit);
        $total = $paginator->total();
        $results = $paginator->items();

        return [
            'data' => array_map(function ($user) {
                return [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'nickname' => $user->nickname,
                    'mobile' => $user->mobile,
                    'email' => $user->email,
                    'status' => $user->status,
                    'dept_id' => $user->dept_id ?? 0,
                ];
            }, $results),
            'total' => $total
        ];
    }
}
