<?php

namespace plugin\yflow\app\controller;

use Yflow\core\utils\DtoUtil;
use Yflow\core\utils\page\Page;
use Yflow\impl\orm\laravel\FlowDefinitionModel;
use support\annotation\route\Get;
use support\annotation\route\Post;
use support\annotation\route\RouteGroup;
use support\Request;
use support\Response;

#[RouteGroup('/app/yflow/definition')]
class DefinitionController extends FlowBase
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

    /**
     * 流程定义列表页面
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        return raw_view('definition/index');
    }

    /**
     * 查询流程定义列表
     * @param Request $request
     * @return Response
     */
    public function select(Request $request): Response
    {
        $pageNum = (int)$request->get('page', 1);
        $pageSize = (int)$request->get('limit', 10);
        $page = Page::pageOf($pageNum, $pageSize);
        $obj = DtoUtil::fromData($request->all(), FlowDefinitionModel::class);
        $page = $this->defService->orderByCreateTime()->desc()->page($obj, $page);

        return json([
            'code' => 0,
            'msg' => 'ok',
            'count' => $page->getTotal(),
            'data' => $page->getList()
        ]);
    }

    /**
     * 新增流程定义页面
     * @param Request $request
     * @return Response
     */
    public function insert(Request $request): Response
    {
        if ($request->isPost()) {
            // 处理新增请求
            $obj = DtoUtil::fromData($request->all(), FlowDefinitionModel::class);
            $res = $this->defService->checkAndSave($obj);
            return $this->success('新增成功', (array)$res);
        }
        return raw_view('definition/insert');
    }

    /**
     * 修改流程定义页面
     * @param Request $request
     * @return Response
     */
    public function update(Request $request): Response
    {
        if ($request->isPost()) {
            // 处理修改请求
            $data = $request->post();
            $obj = DtoUtil::fromData($data, FlowDefinitionModel::class);
            $res = $this->defService->updateById($obj);
            return $this->success('修改成功', (array)$res);
        }
        return raw_view('definition/update');
    }

    /**
     * 获取流程定义详情
     * @param Request $request
     * @param int $id
     * @return Response
     */
    #[Get('/get/{id}')]
    public function get(Request $request, int $id): Response
    {
        $data = $this->defService->getById($id);
        return $this->success('获取成功', $data->toArray());
    }

    /**
     * 删除流程定义
     * @param Request $request
     * @param int $id
     * @return Response
     */
    #[Post('/delete/{id}')]
    public function delete(Request $request, int $id): Response
    {
        $this->defService->removeDef([$id]);
        return $this->success('删除成功');
    }

    /**
     * 发布流程定义
     * @param Request $request
     * @param int $id
     * @return Response
     */
    #[Post('/publish/{id}')]
    public function publish(Request $request, int $id): Response
    {
        $publish = $this->defService->publish($id);
        return $this->success('发布成功', (array)$publish);
    }

    /**
     * 取消发布流程定义
     * @param Request $request
     * @param int $id
     * @return Response
     */
    #[Post('/unPublish/{id}')]
    public function unPublish(Request $request, int $id): Response
    {
        $unPublish = $this->defService->unPublish($id);
        return $this->success('取消发布成功', (array)$unPublish);
    }

    /**
     * 激活流程定义
     * @param Request $request
     * @param int $id
     * @return Response
     */
    #[Get('/active/{definitionId}')]
    public function active(Request $request, int $definitionId): Response
    {
        $active = $this->defService->active($definitionId);
        return $this->success('激活成功', (array)$active);
    }

    /**
     * 挂起流程定义
     * @param Request $request
     * @param int $id
     * @return Response
     */
    #[Get('/unActive/{definitionId}')]
    public function unActive(Request $request, int $definitionId): Response
    {
        $unActive = $this->defService->unActive($definitionId);
        return $this->success('挂起成功', (array)$unActive);
    }

    /**
     * 复制流程定义
     * @param Request $request
     * @param int $id
     * @return Response
     */
    #[Get('/copy/{id}')]
    public function copy(Request $request, int $id): Response
    {
        $copyDef = $this->defService->copyDef($id);
        return $this->success('复制成功', (array)$copyDef);
    }

    /**
     * 导入流程定义
     * @param Request $request
     * @return Response
     */
    #[Get('/importDefinition')]
    public function importDefinition(Request $request): Response
    {
        $file = $request->file('file');
        if ($file) {
            // 处理文件上传
            $this->defService->importIs($file);
        }
        return $this->success('导入成功');
    }

    /**
     * 导出流程定义
     * @param Request $request
     * @param int $id
     * @return Response
     */
    #[Get('/exportDefinition/{id}')]
    public function exportDefinition(Request $request, int $id): Response
    {
        $content = $this->defService->exportJson($id);
        $filename = 'flow_definition_' . $id . '.json';

        return response()
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Content-Disposition', "attachment; filename=\"$filename\"")
            ->withHeader('Content-Length', strlen($content))
            ->withBody($content);
    }

    /**
     * 获取流程图
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function getFlowChart(Request $request, int $id): Response
    {
        // TODO: 实现获取流程图逻辑，调用 chartService 相关方法
        // 这里返回一个示例图片
        $image = base64_encode(file_get_contents(base_path('plugin/admin/admin/images/logo.png')));
        return response(base64_decode($image), 200, [
            'Content-Type' => 'image/png'
        ]);
    }

    /**
     * 查询已发布表单定义列表
     * @param Request $request
     * @return Response
     */
    public function publishedList(Request $request): Response
    {
        // TODO: 实现查询已发布表单定义列表逻辑
        $list = [];
        return $this->success('获取成功', $list);
    }

    /**
     * 流程设计页面
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function design(Request $request): Response
    {
        return raw_view('definition/design');
    }

    public function chart(Request $request): Response
    {
        return raw_view('definition/chart');
    }
}
