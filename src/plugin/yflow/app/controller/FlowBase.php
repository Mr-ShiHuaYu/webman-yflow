<?php

namespace plugin\yflow\app\controller;

use Yflow\core\invoker\FrameInvoker;
use Yflow\core\service\ChartService;
use Yflow\core\service\DefService;
use Yflow\core\service\FormService;
use Yflow\core\service\HisTaskService;
use Yflow\core\service\InsService;
use Yflow\core\service\NodeService;
use Yflow\core\service\SkipService;
use Yflow\core\service\TaskService;
use Yflow\core\service\UserService;
use plugin\admin\app\controller\Base;
use support\Response;

class FlowBase extends Base
{

    protected ChartService $chartService;
    protected DefService $defService;
    protected FormService $formService;
    protected HisTaskService $hisTaskService;
    protected InsService $insService;
    protected NodeService $nodeService;
    protected SkipService $skipService;
    protected TaskService $taskService;
    protected UserService $userService;


    public function __construct()
    {
        // 初始化服务
        $this->chartService = FrameInvoker::getBean(ChartService::class);
        $this->defService = FrameInvoker::getBean(DefService::class);
        $this->formService = FrameInvoker::getBean(FormService::class);
        $this->hisTaskService = FrameInvoker::getBean(HisTaskService::class);
        $this->insService = FrameInvoker::getBean(InsService::class);
        $this->nodeService = FrameInvoker::getBean(NodeService::class);
        $this->skipService = FrameInvoker::getBean(SkipService::class);
        $this->taskService = FrameInvoker::getBean(TaskService::class);
        $this->userService = FrameInvoker::getBean(UserService::class);
    }


    /**
     * 表格数据格式化
     * @param $items
     * @param $total
     * @return Response
     */
    protected function getDataTable($items, $total): Response
    {
        return json(['code' => 0, 'msg' => 'ok', 'count' => $total, 'data' => $items]);
    }

}
