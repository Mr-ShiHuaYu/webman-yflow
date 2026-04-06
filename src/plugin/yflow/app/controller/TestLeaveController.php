<?php

namespace plugin\yflow\app\controller;

use plugin\yflow\app\service\TestLeaveService;
use support\annotation\route\Get;
use support\annotation\route\Post;
use support\annotation\route\RouteGroup;
use support\Request;
use support\Response;

/**
 * OA 请假申请 Controller
 *
 * @package plugin\yflow\app\controller
 */
#[RouteGroup('/app/yflow/leave')]
class TestLeaveController extends FlowBase
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

    private TestLeaveService $testLeaveService;

    public function __construct()
    {
        parent::__construct();
        $this->testLeaveService = new TestLeaveService();
    }

    /**
     * 查询 OA 请假申请列表页面
     * @param Request $request
     * @return Response
     */
    #[Get('/index')]
    public function index(Request $request): Response
    {
        return raw_view('leave/index');
    }

    /**
     * 查询 OA 请假申请列表
     * @param Request $request
     * @return Response
     */
    #[Get('/list')]
    public function list(Request $request): Response
    {
        try {
            // 分页处理
            $page = (int)$request->get('page', 1);
            $limit = (int)$request->get('limit', 10);

            // 构建查询条件
            $conditions = [
                'type' => $request->get('type'),
                'flow_status' => $request->get('flow_status'),
                'create_by' => $request->get('create_by'),
                'instance_id' => $request->get('instance_id'),
            ];

            // 调用 Service 层获取列表（带分页）
            $result = $this->testLeaveService->selectTestLeaveList($conditions, $page, $limit);

            // 获取分页数据
            $total = $result['total'];
            $list = $result['list'];

            return json([
                'code' => 0,
                'msg' => 'ok',
                'count' => $total,
                'data' => $list
            ]);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 获取 OA 请假申请详细信息
     * @param Request $request
     * @param int $id
     * @return Response
     */
    #[Get('/get/{id}')]
    public function get(Request $request, int $id): Response
    {
        try {
            $data = $this->testLeaveService->selectTestLeaveById((string)$id);
            if (!$data) {
                return $this->fail('请假申请不存在');
            }
            return $this->success('获取成功', $data->toArray());
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 新增 OA 请假申请页面
     * @param Request $request
     * @return Response
     */
    #[Get('/insert')]
    public function insert(Request $request): Response
    {
        return raw_view('leave/insert');
    }

    /**
     * 新增 OA 请假申请
     * @param Request $request
     * @return Response
     */
    #[Post('/add')]
    public function doAdd(Request $request): Response
    {
        try {
            $data = [
                'type' => $request->post('type'),
                'reason' => $request->post('reason'),
                'start_time' => $request->post('start_time'),
                'end_time' => $request->post('end_time'),
                'day' => $request->post('day'),
                'additional_handler' => $request->post('additional_handler'),
            ];
            $flowStatus = $request->post('flow_status');

            // 调用 Service 层方法
            $testLeave = $this->testLeaveService->insertTestLeave($data, $flowStatus);

            return $this->success('新增成功', $testLeave->toArray());
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 修改 OA 请假申请页面
     * @param Request $request
     * @return Response
     */
    #[Get('/update')]
    public function update(Request $request): Response
    {
        return raw_view('leave/update');
    }

    /**
     * 修改 OA 请假申请
     * @param Request $request
     * @return Response
     */
    #[Post('/edit')]
    public function doEdit(Request $request): Response
    {
        try {
            $data = $request->post();

            // 调用 Service 层方法
            $result = $this->testLeaveService->updateTestLeave($data);

            return $this->success('修改成功', ['affected' => $result]);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 删除 OA 请假申请 OK
     * @param Request $request
     * @return Response
     */
    #[Post('/remove')]
    public function remove(Request $request): Response
    {
        try {
            $ids = $request->post('ids', []);
            if (empty($ids)) {
                return $this->fail('请选择要删除的数据');
            }

            // 调用 Service 层方法
            $count = $this->testLeaveService->deleteTestLeaveByIds($ids);

            return $this->success('删除成功', ['count' => $count]);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 提交审批
     * @param Request $request
     * @return Response
     */
    #[Post('/submit')]
    public function submit(Request $request): Response
    {
        try {
            $data = $request->post();
            $id = $data['id'] ?? 0;
            $flowStatus = $data['flow_status'] ?? null;

            // 调用 Service 层方法
            $result = $this->testLeaveService->submit((string)$id, $flowStatus);

            return $this->success('提交审批成功', ['affected' => $result]);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 办理
     * @param Request $request
     * @return Response
     */
    #[Post('/handle')]
    public function handle(Request $request): Response
    {
        try {
            $data = $request->post();
            $taskId = (int)($data['task_id'] ?? 0);
            $skipType = $data['skip_type'] ?? '';
            $message = $data['message'] ?? '';
            $nodeCode = $data['node_code'] ?? null;
            $flowStatus = $data['flow_status'] ?? null;

            // 直接获取iframe_data字段作为表单数据
            $iframeData = $data['iframe_data'] ?? [];

            // 调用 Service 层方法
            $this->testLeaveService->handle(
                $iframeData,
                $taskId,
                $skipType,
                $message,
                $nodeCode,
                $flowStatus
            );

            return $this->success('办理成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 驳回上一个任务
     * @param Request $request
     * @return Response
     */
    #[Post('/rejectLast')]
    public function rejectLast(Request $request): Response
    {
        try {
            $data = $request->post();
            $taskId = (int)($data['task_id'] ?? 0);
            $message = $data['message'] ?? '';
            $flowStatus = $data['flow_status'] ?? null;

            // 直接获取iframe_data字段作为表单数据
            $formData = $data['iframe_data'] ?? [];

            // 调用 Service 层方法
            $this->testLeaveService->rejectLast($formData, $taskId, $message, $flowStatus);

            return $this->success('驳回到上一个任务成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 拿回到最近办理的任务
     * @param Request $request
     * @return Response
     */
    #[Post('/taskBack')]
    public function taskBack(Request $request): Response
    {
        try {
            $data = $request->post();
            $taskId = (int)($data['task_id'] ?? 0);
            $message = $data['message'] ?? '';
            $flowStatus = $data['flow_status'] ?? null;

            // 直接获取iframe_data字段作为表单数据
            $formData = $data['iframe_data'] ?? [];

            // 调用 Service 层方法
            $this->testLeaveService->taskBack($formData, $taskId, $message, $flowStatus);

            return $this->success('拿回到最近办理的任务成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 撤销流程 OK
     * @param Request $request
     * @param int $id
     * @return Response
     */
    #[Post('/revoke/{id}')]
    public function revoke(Request $request, int $id): Response
    {
        try {
            // 调用 Service 层方法
            $result = $this->testLeaveService->revoke((string)$id);

            return $this->success('撤销流程成功', ['affected' => $result]);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 拿回到最近办理的任务（按实例 ID） OK
     * @param Request $request
     * @param int $id
     * @return Response
     */
    #[Get('/taskBackByInsId/{id}')]
    public function taskBackByInsId(Request $request, int $id): Response
    {
        try {
            // 调用 Service 层方法
            $result = $this->testLeaveService->taskBackByInsId((string)$id);

            return $this->success('拿回到最近办理的任务成功', ['affected' => $result]);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 终止流程，提前结束 OK
     * @param Request $request
     * @return Response
     */
    #[Post('/termination')]
    public function termination(Request $request): Response
    {
        try {
            $data = $request->post();

            // 调用 Service 层方法
            $result = $this->testLeaveService->termination($data);

            return $this->success('终止流程成功', ['affected' => $result]);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }
}
