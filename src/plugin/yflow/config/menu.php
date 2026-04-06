<?php

return [
    [
        'title' => '流程管理',
        'key' => 'yflow',
        'icon' => '',
        'weight' => 1234,
        'type' => 0,
        'children' => [
            [
                'title' => '流程定义',
                'key' => '\\plugin\\yflow\\app\\controller\\DefinitionController',
                'href' => '/app/yflow/definition',
                'type' => 1,
                'weight' => 0,
            ],
            [
                'title' => '待办任务',
                'key' => '\\plugin\\yflow\\app\\controller\\ExecuteController',
                'href' => '/app/yflow/execute/todoPage',
                'type' => 1,
                'weight' => 0,
            ],
            [
                'title' => '已办任务',
                'key' => '/app/yflow/execute/donePage',
                'href' => '/app/yflow/execute/donePage',
                'type' => 1,
                'weight' => 0,
            ],
            [
                'title' => '抄送任务',
                'key' => '/app/yflow/execute/copyPage',
                'href' => '/app/yflow/execute/copyPage',
                'type' => 1,
                'weight' => 0,
            ],
            [
                'title' => '测试菜单',
                'key' => 'yflow_demo',
                'icon' => '',
                'weight' => 0,
                'type' => 0,
                'children' => [
                    [
                        'title' => 'OA请假申请',
                        'key' => '\\plugin\\yflow\\app\\controller\\TestLeaveController',
                        'href' => '/app/yflow/leave/index',
                        'type' => 1,
                        'weight' => 0,
                    ]
                ]
            ]
        ]
    ]
];
