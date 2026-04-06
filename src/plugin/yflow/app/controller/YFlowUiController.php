<?php

namespace plugin\yflow\app\controller;

use Yflow\ui\service\WarmFlowService;
use plugin\admin\app\controller\Base;
use support\annotation\route\Get;
use support\annotation\route\RouteGroup;
use support\Request;
use support\Response;

//#[RouteGroup('/app/yflow/y-flow-ui')]
class YFlowUiController extends Base
{
    /**
     * 无需登录及鉴权的方法
     * @var array
     */
    protected $noNeedLogin = ['config'];

    /**
     * 用于获取 获取tokenName
     * @param Request $request
     * @return Response
     */
//    #[Get('/config')]
    public function config(Request $request): Response
    {
        $apiFlowVo = WarmFlowService::config();
        return json($apiFlowVo->toArray());
    }
}
