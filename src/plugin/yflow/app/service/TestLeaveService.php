<?php

namespace plugin\yflow\app\service;

use Yflow\core\dto\FlowParams;
use Yflow\core\enums\SkipType;
use Yflow\core\FlowEngine;
use Yflow\core\orm\dao\IFlowInstanceDao;
use Yflow\core\utils\IdUtils;
use Yflow\impl\orm\laravel\FlowInstanceModel;
use Yflow\impl\orm\laravel\FlowUserModel;
use Exception;
use plugin\admin\app\model\Role;
use plugin\yflow\app\model\TestLeave;
use support\Db;

/**
 * OA 请假申请 Service 实现类
 */
class TestLeaveService
{
    /**
     * 查询 OA 请假申请
     *
     * @param string $id OA 请假申请主键
     * @return TestLeave|null
     */
    public function selectTestLeaveById(string $id): ?TestLeave
    {
        $testLeave = TestLeave::find($id);
        if (!$testLeave) {
            return null;
        }
        // 获取抄送人权限,因为存入时使用的实例ID,这里也可以实例ID查询
        $permission = FlowEngine::userService()->getPermission($testLeave->instance_id, "4");
        // 将额外处理人添加到对象（使用修改器）
        $testLeave->additional_handler = $permission;

        return $testLeave;
    }

    /**
     * 查询 OA 请假申请列表（带分页）
     *
     * @param array $conditions 查询条件
     * @param int $page 页码
     * @param int $limit 每页数量
     * @return array [total, list]
     */
    public function selectTestLeaveList(array $conditions, int $page = 1, int $limit = 10): array
    {
        $query = TestLeave::query();

        // 按类型筛选
        if (isset($conditions['type']) && $conditions['type'] !== '') {
            $query->where('type', $conditions['type']);
        }

        // 按流程状态筛选
        if (isset($conditions['flow_status']) && $conditions['flow_status'] !== '') {
            $query->where('flow_status', $conditions['flow_status']);
        }

        // 按创建者筛选
        if (isset($conditions['create_by']) && $conditions['create_by'] !== '') {
            $query->where('create_by', $conditions['create_by']);
        }

        // 按实例 ID 筛选
        if (isset($conditions['instance_id']) && $conditions['instance_id'] !== '') {
            $query->where('instance_id', $conditions['instance_id']);
        }

        // 计算总数
        $total = $query->count();

        // 分页查询
        $list = $query->orderBy('create_time', 'desc')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get()
            ->toArray();

        return [
            'total' => $total,
            'list' => $list
        ];
    }

    /**
     * 新增 OA 请假申请
     *
     * @param array $data 请假申请数据
     * @param string|null $flowStatus 流程状态
     * @return TestLeave
     */
    public function insertTestLeave(array $data, ?string $flowStatus = null): TestLeave
    {
        return Db::transaction(function () use ($data, $flowStatus) {

            // 生成主键 ID
            $businessId = IdUtils::nextIdStr();
            $data['id'] = $businessId; // 业务表的ID就是流程实例的 businessId

            // 获取当前登录用户
            $currentUser = getLoginUser();

            // 从字典表中获取流程编码
            $flowCode = $this->getFlowType($data['type'] ?? '');

            // 构建流程参数
            $flowParams = FlowParams::build()->flowCode($flowCode);
            // 设置办理人唯一标识，保存为流程实例的创建人 【如果实现了 @see \Yflow\core\handler\PermissionHandler 接口，则可以不传,否则必传】
//        $flowParams->handler((string)$currentUser['user_id']);

            // 流程变量
            $variable = [];
            $variable['businessData'] = $data;
            $variable['businessType'] = 'testLeave';

            // 条件表达式替换
            if (isset($data['day'])) {
                $variable['flag'] = $data['day'];
            }

            // 办理人表达式替换示例
            // $variable['handler1'] = [4, "5", 100];
            // $variable['handler2'] = 12;

            $flowParams->variable($variable);

            // 自定义流程状态扩展
            if (!empty($flowStatus)) {
                $flowParams->flowStatus($flowStatus)->hisStatus($flowStatus);
            }

            // 启动流程
            /** @var FlowInstanceModel $instance */
            $instance = FlowEngine::insService()->start($businessId, $flowParams);

            // 设置流程相关字段
            $data['instance_id'] = $instance->getId();
            $data['node_code'] = $instance->getNodeCode();
            $data['node_name'] = $instance->getNodeName();
            $data['node_type'] = $instance->getNodeType();
            $data['flow_status'] = $instance->getFlowStatus();
            $data['create_by'] = (string)$currentUser['user_id'];
            $data['create_time'] = date('Y-m-d H:i:s');
            $data['del_flag'] = '0';

            // 保存请假申请
            $testLeave = new TestLeave();
            $testLeave->fill($data);
            $testLeave->save();

            // 新增抄送人方法  【按需】
            if (!empty($data['additional_handler']) && is_string($data['additional_handler'])) {
                $data['additional_handler'] = explode(',', trim($data['additional_handler']));

                /**
                 * 没问题,这里使用实例ID,写入flow_user表的 associated 字段,并且type为4,最终在 copyPage 中也是使用的流程实例ID进行查询的
                 * @see \plugin\yflow\app\service\ExecuteService::copyPage
                 */
                $users = FlowEngine::userService()->structureUser(
                    $instance->id,
                    $data['additional_handler'],
                    "4"
                );
                FlowEngine::userService()->saveBatch($users);
            }
            // 此处可以发送消息通知，比如短信通知，邮件通知等，代码自己实现

            return $testLeave;

        });
    }

    /**
     * 修改 OA 请假申请
     *
     * @param array $data 请假申请数据
     * @return int
     * @throws Exception
     */
    public function updateTestLeave(array $data): int
    {
        $id = $data['id'] ?? 0;
        $testLeave = TestLeave::find($id);

        if (!$testLeave) {
            throw new Exception('请假申请不存在');
        }

        $data['update_time'] = date('Y-m-d H:i:s');

        $testLeave->fill($data);
        return $testLeave->save();
    }

    /**
     * 批量删除 OA 请假申请
     *
     * @param array $ids 主键 ID 数组
     * @return int
     */
    public function deleteTestLeaveByIds(array $ids): int
    {
        return Db::transaction(function () use ($ids) {
            // 查询要删除的记录
            $testLeaveList = TestLeave::whereIn('id', $ids)->get();

            // 删除
            $count = TestLeave::whereIn('id', $ids)->delete();

            if ($count > 0) {
                // 删除关联的流程实例
                $instanceIds = array_column($testLeaveList->toArray(), 'instance_id');
                if (!empty($instanceIds)) {
                    FlowEngine::insService()->removeWithTasks($instanceIds);

                    // 删除抄送
                    FlowUserModel::whereIn('associated', $instanceIds)
                        ->where('type', '=', '4')
                        ->delete();
                }
            }

            return $count;
        });
    }

    /**
     * 删除 OA 请假申请信息
     *
     * @param string $id 主键 ID
     * @return int
     */
    public function deleteTestLeaveById(string $id): int
    {
        return $this->deleteTestLeaveByIds([$id]);
    }

    /**
     * 提交审批
     *
     * @param string $id 请假申请 ID
     * @param string|null $flowStatus 流程状态
     * @return int
     */
    public function submit(string $id, ?string $flowStatus = null): int
    {
        return Db::transaction(function () use ($id, $flowStatus) {
            $testLeave = $this->selectTestLeaveById($id);
            if (!$testLeave) {
                throw new Exception('请假申请不存在');
            }

            // 获取当前登录用户
            $currentUser = getLoginUser();

            // 构建流程参数
            $flowParams = new FlowParams();
            $flowParams->skipType(SkipType::PASS->value);
            $flowParams->handler((string)$currentUser['user_id']);

            // 获取用户权限
            $permissionList = getPermissionList((string)$currentUser['user_id'], $currentUser);
            $flowParams->permissionFlag($permissionList);

            // 自定义流程状态
            if (!empty($flowStatus)) {
                $flowParams->flowStatus($flowStatus)->hisStatus($flowStatus);
            }

            // 流程变量
            $variable = [];
            $variable['businessType'] = 'testLeave';
            if (isset($testLeave->day)) {
                $variable['flag'] = $testLeave->day;
            }
            $flowParams->variable($variable);

            // 更新请假表
            $instance = FlowEngine::taskService()->skipByInsId($testLeave->instance_id, $flowParams);

            $testLeave->node_code = $instance->node_code;
            $testLeave->node_name = $instance->node_name;
            $testLeave->node_type = $instance->node_type;
            $testLeave->flow_status = $instance->flow_status;
            $testLeave->update_time = date('Y-m-d H:i:s');

            return $testLeave->save() ? 1 : 0;
        });
    }

    /**
     * 办理
     *
     * @param array $iframeData 表单数据
     * @param int $taskId 任务 ID
     * @param string $skipType 跳转类型
     * @param string $message 审批意见
     * @param string|null $nodeCode 节点编码
     * @param string|null $flowStatus 流程状态
     * @return void
     */
    public function handle(array $iframeData, int $taskId, string $skipType, string $message, ?string $nodeCode = null, ?string $flowStatus = null): void
    {
        Db::transaction(function () use ($iframeData, $taskId, $skipType, $message, $nodeCode, $flowStatus) {

            $flowParams = $this->buildFlowParams($message, $flowStatus, $iframeData);
            $flowParams->skipType($skipType);
            if ($nodeCode) {
                $flowParams->nodeCode($nodeCode);
            }

            // 调用流程引擎办理任务
            $instance = FlowEngine::taskService()->skip($taskId, $flowParams);

            // 更新请假表（如果有对应的业务数据）
            $this->updateLeaveTableInfo($iframeData, $instance);
        });
    }

    /**
     * 驳回到上一个任务
     *
     * @param array $iframeData 表单数据
     * @param int $taskId 任务 ID
     * @param string $message 审批意见
     * @param string|null $flowStatus 流程状态
     * @return void
     */
    public function rejectLast(array $iframeData, int $taskId, string $message, ?string $flowStatus = null): void
    {
        Db::transaction(function () use ($iframeData, $taskId, $message, $flowStatus) {
            $flowParams = $this->buildFlowParams($message, $flowStatus, $iframeData);

            // 调用流程引擎驳回
            $instance = FlowEngine::taskService()->rejectLast($taskId, $flowParams);
            $this->updateLeaveTableInfo($iframeData, $instance);
        });
    }

    /**
     * 拿回到最近办理的任务
     *
     * @param array $iframeData 表单数据
     * @param int $taskId 任务 ID
     * @param string $message 审批意见
     * @param string|null $flowStatus 流程状态
     * @return void
     */
    public function taskBack(array $iframeData, int $taskId, string $message, ?string $flowStatus = null): void
    {
        Db::transaction(function () use ($iframeData, $taskId, $message, $flowStatus) {
            $flowParams = $this->buildFlowParams($message, $flowStatus, $iframeData);
            // 调用流程引擎拿回
            $instance = FlowEngine::taskService()->taskBack($taskId, $flowParams);
            // 更新请假表
            $this->updateLeaveTableInfo($iframeData, $instance);
        });
    }

    /**
     * 撤销流程
     *
     * @param string $id 请假申请 ID
     * @return int
     */
    public function revoke(string $id): int
    {
        return Db::transaction(function () use ($id) {
            list($testLeave, $flowParams) = $this->buildFlowParamsForTaskBackOrRevoke($id);

            // 调用流程引擎撤销
            $instance = FlowEngine::taskService()->revoke($testLeave->instance_id, $flowParams);

            // 更新请假表
            $testLeave->node_code = $instance->node_code;
            $testLeave->node_name = $instance->node_name;
            $testLeave->node_type = $instance->node_type;
            $testLeave->flow_status = $instance->flow_status;
            $testLeave->update_time = date('Y-m-d H:i:s');

            return $testLeave->save() ? 1 : 0;
        });
    }

    /**
     * 拿回到最近办理的任务（按实例 ID）
     *
     * @param string $id 请假申请 ID
     * @return int
     */
    public function taskBackByInsId(string $id): int
    {
        return Db::transaction(function () use ($id) {
            list($testLeave, $flowParams) = $this->buildFlowParamsForTaskBackOrRevoke($id);

            // 调用流程引擎拿回
            $instance = FlowEngine::taskService()->taskBackByInsId($testLeave->instance_id, $flowParams);

            // 更新请假表
            $testLeave->node_code = $instance->node_code;
            $testLeave->node_name = $instance->node_name;
            $testLeave->node_type = $instance->node_type;
            $testLeave->flow_status = $instance->flow_status;
            $testLeave->update_time = date('Y-m-d H:i:s');

            return $testLeave->save() ? 1 : 0;
        });
    }

    /**
     * 终止流程，提前结束
     *
     * @param array $data 请假申请数据
     * @return int
     * @throws Exception
     */
    public function termination(array $data): int
    {
        $id = $data['id'] ?? 0;
        $testLeave = TestLeave::find($id);

        if (!$testLeave || !$testLeave->instance_id) {
            throw new Exception('流程实例不存在');
        }

        // 获取当前登录用户
        $currentUser = getLoginUser();

        // 构建流程参数
        $flowParams = new FlowParams();
        $flowParams->message('终止流程');
        $flowParams->handler((string)$currentUser['user_id']);

        // 流程变量
        $variable = [];
        $variable['businessType'] = 'testLeave';
        $flowParams->variable($variable);

        // 调用流程引擎终止
        $instance = FlowEngine::taskService()->terminationByInsId($testLeave->instance_id, $flowParams);

        if (!$instance) {
            throw new Exception('流程实例不存在');
        }

        // 更新请假表
        $testLeave->node_code = $instance->node_code;
        $testLeave->node_name = $instance->node_name;
        $testLeave->node_type = $instance->node_type;
        $testLeave->flow_status = $instance->flow_status;
        $testLeave->update_time = date('Y-m-d H:i:s');

        return $testLeave->save() ? 1 : 0;
    }

    /**
     * 从字典表中获取流程编码
     *
     * @param string $type 请假类型
     * @return string 流程编码
     */
    private function getFlowType(string $type): string
    {
        $flowCodeMap = [
            '0' => 'leaveFlow-serial1',      // 串行-简单
            '1' => 'leaveFlow-serial2',      // 串行-通过互斥
            '2' => 'leaveFlow-parallel1',    // 并行-汇聚
            '4' => 'leaveFlow-serial3',      // 串行-退回互斥
            '5' => 'leaveFlow-meet-sign',    // 会签
            '6' => 'leaveFlow-vote-sign',    // 票签
            '7' => 'leaveFlow-serial4',      // 串行-复杂互斥
            '8' => 'leaveFlow-parallel3',    // 并行-复杂
            '9' => 'leaveFlow-serial5',      // 办理人表达式
            '10' => 'leaveFlow-serial6',     // 监听器
        ];

        return $flowCodeMap[$type] ?? 'leave_default';
    }

    /**
     * @param string $message
     * @param string|null $flowStatus
     * @param array $iframeData
     * @return FlowParams
     */
    private function buildFlowParams(string $message, ?string $flowStatus, array $iframeData): FlowParams
    {
        // 获取当前登录用户
        $currentUser = getLoginUser();

        // 构建流程参数
        $flowParams = new FlowParams();
        $flowParams->handler((string)$currentUser['user_id']);
        $flowParams->message($message);

        // 获取用户权限
        $permissionList = getPermissionList((string)$currentUser['user_id'], $currentUser);
        $flowParams->permissionFlag($permissionList);

        // 流程变量
        $variable = [];
        $variable['businessType'] = 'testLeave';
        if (isset($iframeData['day'])) {
            $variable['flag'] = $iframeData['day'];
        }
        $flowParams->variable($variable);

        // 自定义流程状态
        if (!empty($flowStatus)) {
            $flowParams->flowStatus($flowStatus)->hisStatus($flowStatus);
        }

        // 请假信息存入 flowParams
        $flowParams->hisTaskExt(json_encode($iframeData));
        return $flowParams;
    }

    /**
     * 构建流程变量, 用于撤销流程或拿回
     * @param string $id
     * @return array
     * @throws Exception
     */
    private function buildFlowParamsForTaskBackOrRevoke(string $id): array
    {
        $testLeave = $this->selectTestLeaveById($id);
        if (!$testLeave) {
            throw new Exception('请假申请不存在');
        }

        // 获取当前登录用户
        $currentUser = getLoginUser();

        // 构建流程参数
        $flowParams = new FlowParams();
        $flowParams->handler((string)$currentUser['user_id']);
        $flowParams->message('撤销流程');

        // 流程变量
        $variable = [];
        $variable['businessType'] = 'testLeave';
        if (isset($testLeave->day)) {
            $variable['flag'] = $testLeave->day;
        }
        $flowParams->variable($variable);

        // 请假信息存入 flowParams
        $flowParams->hisTaskExt(json_encode($testLeave->toArray()));
        return array($testLeave, $flowParams);
    }

    /**
     * 获取当前登录用户信息
     *
     * @return array
     * @throws Exception
     */
    private function getCurrentUser(): array
    {
        $roles_arr = [];
        $admin = admin();
        $role_ids = $admin['roles'] ?? [];

        if (!empty($role_ids)) {
            $roles = Role::whereIn('id', $role_ids)->select(['id', 'name'])->get()->all();
            foreach ($roles as $role) {
                $roles_arr[] = ['role_id' => $role['id'], 'role_name' => $role['name']];
            }
        }

        return [
            'user_id' => admin_id(),
            'roles' => $roles_arr
        ];
    }

    /**
     * @param array|null $iframeData
     * @param IFlowInstanceDao|null $instance
     * @return void
     */
    private function updateLeaveTableInfo(?array $iframeData, ?IFlowInstanceDao $instance): void
    {
        // 更新请假表
        if (isset($iframeData['id']) || isset($iframeData['business_id'])) {
            $id = $iframeData['id'] ?: $iframeData['business_id']; // ?:是判断是否empty,??是判断是否null
            $testLeave = TestLeave::find($id);
            if ($testLeave) {
                $testLeave->node_code = $instance->node_code;
                $testLeave->node_name = $instance->node_name;
                $testLeave->node_type = $instance->node_type;
                $testLeave->flow_status = $instance->flow_status;
                $testLeave->update_time = date('Y-m-d H:i:s');
                $testLeave->save();
            }
        }
    }
}
