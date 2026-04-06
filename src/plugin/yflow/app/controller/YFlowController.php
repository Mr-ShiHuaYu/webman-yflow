<?php

namespace plugin\yflow\app\controller;

use Exception;
use plugin\admin\app\controller\Base;
use ReflectionException;
use support\annotation\route\Get;
use support\annotation\route\Post;
use support\annotation\route\RouteGroup;
use support\Request;
use support\Response;
use Yflow\core\dto\DefJson;
use Yflow\core\dto\FlowDto;
use Yflow\core\exception\FlowException;
use Yflow\core\utils\DtoUtil;
use Yflow\ui\dto\HandlerFeedBackDto;
use Yflow\ui\dto\HandlerQuery;
use Yflow\ui\service\WarmFlowService;

#[RouteGroup('/app/yflow/y-flow')]
class YFlowController extends Base
{
    /**
     * 无需登录及鉴权的方法
     * @var array
     */
//    protected $noNeedLogin = ['index', 'saveJson', 'queryDef', 'queryFlowChart', 'handlerType', 'handlerResult', 'handlerFeedback', 'handlerDict', 'publishedForm', 'getFormContent', 'saveFormContent', 'load', 'hisLoad', 'handle', 'nodeExt'];
    protected $noNeedLogin = ['index'];

    /**
     *    这个 方法几乎无用,用于防止直接访问 /app/yflow/y-flow/, 重定向到 /app/yflow/y-flow-ui/index.html
     * @param Request $request
     * @return Response
     */
    #[Get]
    public function index(Request $request): Response
    {
        // 获取所有请求参数
        $params = $request->all();
        // 构建查询字符串
        $queryString = http_build_query($params);
        // 构建重定向 URL
        $redirectUrl = "/app/yflow/y-flow-ui/index.html";
        if (!empty($queryString)) {
            $redirectUrl .= "?" . $queryString;
        }
        return redirect($redirectUrl);
    }

    /**
     * 保存流程json字符串
     *
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    #[Post('/save-json')]
    public function saveJson(Request $request): Response
    {
        /**
         * @var DefJson $defJson
         */
        $defJson = DtoUtil::fromData($request->post(), DefJson::class);
        $onlyNodeSkip = $request->header('onlyNodeSkip', false);
        $result = WarmFlowService::saveJson($defJson, $onlyNodeSkip);
        return json($result->toArray());
    }

    /**
     * 获取流程定义数据(包含节点和跳转)
     *
     * @param Request $request
     * @param null $id
     * @return Response
     * @throws FlowException
     */
    #[Get('/query-def/[{id}]')]
    public function queryDef(Request $request, $id = null): Response
    {
        $result = WarmFlowService::queryDef($id);
        return json($result->toArray());
    }

    /**
     * 获取流程图
     *
     * @param Request $request
     * @param $id
     * @return Response
     * @throws FlowException
     */
    #[Get('/query-flow-chart/{id}')]
    public function queryFlowChart(Request $request, $id): Response
    {
        $result = WarmFlowService::queryFlowChart($id);
        return json($result->toArray());
    }

    /**
     * 办理人权限设置列表tabs页签
     *
     * @param Request $request
     * @return Response
     * @throws FlowException
     */
    #[Get('/handler-type')]
    public function handlerType(Request $request): Response
    {
        $result = WarmFlowService::handlerType();
        return json($result->toArray());
    }

    /**
     * 办理人权限设置列表结果
     *
     * @param Request $request
     * @return Response
     * @throws FlowException
     * @throws ReflectionException
     */
    #[Get('/handler-result')]
    public function handlerResult(Request $request): Response
    {
        /**
         * @var HandlerQuery $query
         */
        $query = DtoUtil::fromData($request->get(), HandlerQuery::class);
        $result = WarmFlowService::handlerResult($query);
        return json($result->toArray());
    }

    /**
     * 办理人权限名称回显
     *
     * @param Request $request
     * @return Response
     * @throws FlowException
     * @throws ReflectionException
     */
    #[Get('/handler-feedback')]
    public function handlerFeedback(Request $request): Response
    {
        /**
         * @var HandlerFeedBackDto $handlerFeedBackDto
         */
        $handlerFeedBackDto = DtoUtil::fromData($request->get(), HandlerFeedBackDto::class);
        $result = WarmFlowService::handlerFeedback($handlerFeedBackDto);
        return json($result->toArray());
    }

    /**
     * 办理人选择项
     *
     * @param Request $request
     * @return Response
     * @throws FlowException
     */
    #[Get('/handler-dict')]
    public function handlerDict(Request $request): Response
    {
        $result = WarmFlowService::handlerDict();
        return json($result->toArray());
    }

    /**
     * 已发布表单列表 该接口不需要业务系统实现
     *
     * @param Request $request
     * @return Response
     * @throws FlowException
     */
    #[Get('/published-form')]
    public function publishedForm(Request $request): Response
    {
        $result = WarmFlowService::publishedForm();
        return json($result->toArray());
    }

    /**
     * 读取表单内容
     *
     * @param Request $request
     * @param $id
     * @return Response
     * @throws FlowException
     */
    #[Get('/form-content/{id}')]
    public function getFormContent(Request $request, $id): Response
    {
        $result = WarmFlowService::getFormContent($id);
        return json($result->toArray());
    }

    /**
     * 保存表单内容,该接口不需要系统实现
     *
     * @param Request $request
     * @return Response
     */
    #[Post('/form-content')]
    public function saveFormContent(Request $request): Response
    {
        $flowDto = new FlowDto();
        $flowDto->setId($request->input('id'));
        $flowDto->setFormContent($request->input('formContent'));
        $result = WarmFlowService::saveFormContent($flowDto);
        return json($result->toArray());
    }

    /**
     * 根据任务id获取待办任务表单及数据
     *
     * @param Request $request
     * @param $taskId
     * @return Response
     */
    #[Get('/execute/load/{taskId}')]
    public function load(Request $request, $taskId): Response
    {
        $result = WarmFlowService::load($taskId);
        return json($result->toArray());
    }

    /**
     * 根据任务id获取已办任务表单及数据
     *
     * @param Request $request
     * @param $taskId
     * @return Response
     */
    #[Get('/execute/hisLoad/{taskId}')]
    public function hisLoad(Request $request, $taskId): Response
    {
        $result = WarmFlowService::hisLoad($taskId);
        return json($result->toArray());
    }

    /**
     * 通用表单流程审批接口
     *
     * @param Request $request
     * @return Response
     */
    #[Post('/execute/handle')]
    public function handle(Request $request): Response
    {
        $formData = $request->post();
        $taskId = $request->input('taskId');
        $skipType = $request->input('skipType');
        $message = $request->input('message');
        $nodeCode = $request->input('nodeCode');
        $result = WarmFlowService::handle($formData, $taskId, $skipType, $message, $nodeCode);
        return json($result->toArray());
    }

    /**
     * 获取节点扩展属性
     *
     * @param Request $request
     * @return Response
     * @throws FlowException
     */
    #[Get('/node-ext')]
    public function nodeExt(Request $request): Response
    {
        $result = WarmFlowService::nodeExt();
        return json($result->toArray());
    }
}
