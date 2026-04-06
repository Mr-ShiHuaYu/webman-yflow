<?php

namespace plugin\yflow\app\service;

use Yflow\core\dto\Tree;

/**
 * 自定义表单路径服务
 *
 */
class FormPathService
{
    /**
     * 查询表单路径
     */
    public function queryFormPath()
    {
        $trees = [];
        $trees[] = new Tree("1", "表单1", null, null);
        $trees[] = new Tree("1-1", "表单1-1", "1", null);
        $trees[] = new Tree("2", "表单2", null, null);
        $trees[] = new Tree("2-1", "表单2-1", "2", null);
        $trees[] = new Tree("3", "表单3", null, null);

        return $trees;
    }
}
