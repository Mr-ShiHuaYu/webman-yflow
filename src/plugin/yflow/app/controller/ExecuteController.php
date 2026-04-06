<?php

namespace plugin\yflow\app\controller;

use Yflow\core\entity\TaskEntity;
use Yflow\core\enums\CooperateType;
use Yflow\core\utils\DtoUtil;
use Yflow\impl\orm\laravel\FlowHisTaskModel;
use Exception;
use plugin\yflow\app\service\ExecuteService;
use support\annotation\route\Get;
use support\annotation\route\Post;
use support\annotation\route\RouteGroup;
use support\Request;
use support\Response;

/**
 * 流程实例Controller
 *
 * @author hh
 * @date 2023-04-18
 */
#[RouteGroup('/app/yflow/execute')]
class ExecuteController extends FlowBase
{
    /**
     * 无需登录的方法
     * @var string[]
     */
    protected $noNeedLogin = [];

    /**
     * 不需要鉴权的方法
     * @var string[]
     */
    protected $noNeedAuth = [];

    private ExecuteService $executeService;

    public function __construct()
    {
        parent::__construct();
        $this->executeService = new ExecuteService();
    }

    /**
     * 分页待办任务列表
     */
    #[Get('/toDoPage')]
    public function toDoPage(Request $request): Response
    {
        if (!$request->isAjax()) {
            return raw_view('task/todo/index');
        }
        try {
            // 获取当前登录用户
            $sysUser = getLoginUser();
            $flowTask = new TaskEntity();

            // 构建权限列表
            $permissionList = getPermissionList((string)$sysUser['user_id'], $sysUser);
            $flowTask->setPermissionList($permissionList);

            // 分页处理
            $limit = $request->get('limit', 10);

            // 调用服务方法获取待办任务列表
            [$list, $total] = $this->executeService->toDoPage($flowTask, $limit);

            // 获取任务ID列表
            $taskIds = array_column($list, 'id');

            // 获取用户列表
            $userList = $this->userService->getByAssociateds($taskIds);

            // 按任务ID分组
            $map = [];
            foreach ($userList as $user) {
                $map[$user->getAssociated()][] = $user;
            }

            // 处理用户信息
            foreach ($list as &$taskVo) {
                if (!empty($taskVo)) {
                    $users = $map[$taskVo['id']] ?? [];
                    if (!empty($users)) {
                        foreach ($users as $user) {
                            if ($user->getType() == CooperateType::APPROVAL->value) { // APPROVAL
                                if (empty($taskVo['approver'])) {
                                    $taskVo['approver'] = '';
                                }
                                $name = $this->executeService->getName($user->getProcessedBy());
                                if (!empty($name)) {
                                    $taskVo['approver'] .= $name . ';';
                                }
                            } elseif ($user->getType() == CooperateType::TRANSFER->value) { // TRANSFER
                                if (empty($taskVo['transferred_by'])) {
                                    $taskVo['transferred_by'] = '';
                                }
                                $name = $this->executeService->getName($user->getProcessedBy());
                                if (!empty($name)) {
                                    $taskVo['transferred_by'] .= $name . ';';
                                }
                            } elseif ($user->getType() == CooperateType::DEPUTE->value) { // DEPUTE
                                if (empty($taskVo['delegate'])) {
                                    $taskVo['delegate'] = '';
                                }
                                $name = $this->executeService->getName($user->getProcessedBy());
                                if (!empty($name)) {
                                    $taskVo['delegate'] .= $name . ';';
                                }
                            }
                        }
                    }
                }
            }
            return $this->getDataTable($list, $total);
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 分页抄送任务列表
     */
    #[Get('/copyPage')]
    public function copyPage(Request $request): Response
    {
        if (!$request->isAjax()) {
            return raw_view('notice/index');
        }

        try {
            // 获取当前登录用户
            $sysUser = getLoginUser();
            $flowTask = new TaskEntity();

            // 构建权限列表
            $permissionList = getPermissionList((string)$sysUser['user_id'], $sysUser);
            $flowTask->setPermissionList($permissionList);

            // 分页处理
            $limit = $request->get('limit', 10);

            // 调用服务方法获取抄送任务列表
            $result = $this->executeService->copyPage($flowTask, $limit);
            $list = $result['data'];
            $total = $result['total'];

            return $this->getDataTable($list, $total);
        } catch (Exception $e) {
            return $this->json(500, $e->getMessage());
        }
    }

    /**
     * 分页已办任务列表
     */
    #[Get('/donePage')]
    public function donePage(Request $request): Response
    {
        if (!$request->isAjax()) {
            return raw_view('task/done/index');
        }

        try {
            // 获取当前登录用户
            $sysUser = getLoginUser();
            $flowHisTask = DtoUtil::fromData($request->all(), FlowHisTaskModel::class);
            $flowHisTask->setApprover((string)$sysUser['user_id']);

            // 分页处理
            $limit = $request->get('limit', 10);

            // 调用服务方法获取已办任务列表
            $result = $this->executeService->donePage($flowHisTask, $limit);
            $list = $result['data'];
            $total = $result['total'];

            // 获取用户映射
            $userMap = $this->executeService->getUserMap();

            // 处理用户信息
            if (!empty($list)) {
                foreach ($list as &$hisTask) {
                    $hisTask = $this->handleApproverUserName($hisTask, $userMap);
                }
            }

            return $this->getDataTable($list, $total);
        } catch (Exception $e) {
            return $this->json(500, $e->getMessage());
        }
    }

    /**
     * 查询已办任务历史记录,审批记录
     */
    #[Get('/doneList')]
    public function doneList(Request $request): Response
    {
        if (!$request->isAjax()) {
            return raw_view('task/done/doneList');
        }

        try {
            $instance_id = $request->get('instance_id');
            if (empty($instance_id)) {
                return $this->fail('实例ID不能为空');
            }
            // 调用服务方法获取历史任务列表
            $flowHisTasks = $this->hisTaskService->orderById()->desc()->list(['instance_id' => $instance_id]);

            // 获取用户映射
            $userMap = $this->executeService->getUserMap();

            $flowHisTaskList = [];
            if (!empty($flowHisTasks)) {
                foreach ($flowHisTasks as $hisTask) {
                    $hisTask = $this->handleApproverUserName($hisTask, $userMap);
                    $flowHisTaskList[] = $hisTask;
                }
            }

            return $this->json(0, '', $flowHisTaskList);
        } catch (Exception $e) {
            return $this->json(500, $e->getMessage());
        }
    }

    /**
     * 根据taskId查询代表任务
     */
    #[Get('/getTaskById/{taskId}')]
    public function getTaskById(Request $request, $taskId): Response
    {
        try {
            $task = $this->taskService->getById($taskId);
            return $this->json(0, '', $task->toArray());
        } catch (Exception $e) {
            return $this->json(500, $e->getMessage());
        }
    }

    /**
     * 查询跳转任意节点列表
     */
    #[Get('/anyNodeList/{instanceId}')]
    public function anyNodeList(Request $request, $instanceId): Response
    {
        try {
            // 获取流程实例
            $instance = $this->insService->getById($instanceId);

            // 获取开始节点
            $startNode = $this->nodeService->getStartNode($instance['definition_id']);

            // 获取后续节点列表
            $nodeList = $this->nodeService->suffixNodeList($startNode['id']);

            return $this->json(0, '', $nodeList);
        } catch (Exception $e) {
            return $this->json(500, $e->getMessage());
        }
    }

    /**
     * 处理非办理的流程交互类型
     */
    #[Post('/interactiveType')]
    public function interactiveType(Request $request): Response
    {
        try {
            $data = [
                'taskId' => $request->post('taskId', 0),
                'addHandlers' => $request->post('addHandlers', ''),
                'operatorType' => $request->post('operatorType', 0),
            ];
            $result = $this->executeService->interactiveType($data);
            return $this->json(0, '', (array)$result);
        } catch (Exception $e) {
            return $this->json(500, $e->getMessage());
        }
    }

    /**
     * 交互类型可以选择的用户
     */
    #[Get('/interactiveTypeSysUser')]
    public function interactiveTypeSysUser(Request $request): Response
    {
        try {
            // 获取当前登录用户
            $currentUser = getLoginUser();
            $userId = $currentUser['user_id'];

            $data = $request->all();
            $operatorType = $data['operatorType'] ?? 0;
            $taskId = $data['taskId'] ?? 0;

            // 获取用户列表
            $users = $this->userService->listByAssociatedAndTypes($taskId);

            $userIds = [];
            foreach ($users as $user) {
                $userIds[] = $user->getProcessedBy();
            }
            $data['userIds'] = $userIds;

            // 分页处理
            $limit = $request->get('limit', 10);

            if ($operatorType != CooperateType::REDUCTION_SIGNATURE->value) {
                // 不是减签
                $result = $this->executeService->selectNotUserList($data, $limit);
                $list = $result['data'];
                $total = $result['total'];
            } else {
                $result = $this->executeService->selectUserList($data, $limit);
                $list = $result['data'];
                $total = $result['total'];
                // 过滤掉当前用户
                $list = array_filter($list, function ($sysUser) use ($userId, &$total) {
                    $not_current_user = $sysUser['user_id'] != $userId;
                    if (!$not_current_user) {
                        // 这里是当前用户,总数减少1
                        $total--;
                    }
                    return $not_current_user;
                });
            }
            return $this->getDataTable(array_values($list), $total);
        } catch (Exception $e) {
            return $this->json(500, $e->getMessage());
        }
    }

    /**
     * 激活流程
     */
    #[Get('/active/{instanceId}')]
    public function active(Request $request, $instanceId): Response
    {
        try {
            $result = $this->insService->active($instanceId);
            return $this->json(0, '', (array)$result);
        } catch (Exception $e) {
            return $this->json(500, $e->getMessage());
        }
    }

    /**
     * 挂起流程
     */
    #[Get('/unActive/{instanceId}')]
    public function unActive(Request $request, $instanceId): Response
    {
        try {
            $result = $this->insService->unActive($instanceId);
            return $this->json(0, '', (array)$result);
        } catch (Exception $e) {
            return $this->json(500, $e->getMessage());
        }
    }

    /**
     * 根据 ID 反显姓名
     */
    #[Get('/idReverseDisplayName/{ids}')]
    public function idReverseDisplayName(Request $request, $ids): Response
    {
        try {
            $idArray = explode(',', $ids);
            $result = $this->executeService->idReverseDisplayName($idArray);
            return $this->json(0, '', $result);
        } catch (Exception $e) {
            return $this->json(500, $e->getMessage());
        }
    }

    /**
     * 待办任务-点击办理时显示页面
     */
    #[Get('/handle')]
    public function handle(Request $request): Response
    {
        return raw_view('task/todo/handle');
    }

    public function selectUser(): Response
    {
        return raw_view('task/todo/selectUser');
    }

    /**
     * 处理审批人,协作人姓名
     * @param mixed $hisTask
     * @param array $userMap
     * @return mixed
     */
    private function handleApproverUserName(mixed $hisTask, array $userMap): mixed
    {
        if (!empty($hisTask['approver'])) {
            $name = $this->executeService->getName($hisTask['approver']);
            $hisTask['approver'] = $name;
        }
        if (!empty($hisTask['collaborator'])) {
            $split = explode(',', $hisTask['collaborator']);
            if (!empty($split)) {
                $names = [];
                foreach ($split as $s) {
                    $names[] = $userMap[$s] ?? $s;
                }
                $hisTask['collaborator'] = implode(',', $names);
            }
        }
        return $hisTask;
    }
}
