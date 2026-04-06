<?php

namespace plugin\yflow\custom\adapter;

/**
 * 流程适配器接口
 *
 *
 * @since 2023/5/29
 */
interface WarmFlowAdapter
{
    /**
     * 判断是否适配指定的流程类型
     *
     * @param int $warmFlowType 流程类型
     * @return bool
     */
    public function isAdapter(int $warmFlowType): bool;

    /**
     * 处理流程交互
     *
     * @param array $obj 交互数据
     * @return bool
     */
    public function adapter(array $obj): bool;
}
