<?php

use plugin\yflow\custom\adapter\AddSignatureAdapter;
use plugin\yflow\custom\adapter\DeputeAdapter;
use plugin\yflow\custom\adapter\ReductionSignatureAdapter;
use plugin\yflow\custom\adapter\TransferAdapter;
use plugin\yflow\custom\json\CustomJsonConvertImpl;

return [
    'bean_scan_dir' => [
        app_path(),
        base_path('test')
    ],
    'dependence' => [
        Yflow\core\json\JsonConvert::class => DI\autowire(CustomJsonConvertImpl::class),

        // 注册 WarmFlowAdapter 适配器
        TransferAdapter::class => new TransferAdapter(),
        DeputeAdapter::class => new DeputeAdapter(),
        AddSignatureAdapter::class => new AddSignatureAdapter(),
        ReductionSignatureAdapter::class => new ReductionSignatureAdapter(),
        // 其他注入容器
        // 全局监听器
//        Yflow\core\listener\GlobalListener::class => DI\autowire(\plugin\yflow\custom\listener\CustomGlobalListener::class),

//        Yflow\core\handler\PermissionHandler::class => DI\autowire(\plugin\yflow\custom\handler\CustomPermissionHandler::class),

        // 前端-流程设计器-获取办理人权限设置列表接口
        \Yflow\ui\service\HandlerSelectService::class => DI\autowire(\plugin\yflow\custom\service\HandlerSelectServiceImpl::class),

        //    'user' => DI\autowire(\test\bean\User::class),
        //    'voteSignService' => DI\autowire(\test\bean\VoteSignService::class),
    ]
];
