<?php

namespace plugin\yflow\custom\adapter;

use Yflow\core\dto\FlowParams;
use Yflow\core\enums\CooperateType;

/**
 * 加签适配器
 *
 *
 * @since 2023/5/29
 */
class AddSignatureAdapter extends AbstractWarmFlowAdapter implements WarmFlowAdapter
{
    /**
     * 判断是否适配指定的流程类型
     *
     * @param int $warmFlowType 流程类型
     * @return bool
     */
    public function isAdapter(int $warmFlowType): bool
    {
        return $warmFlowType == CooperateType::ADD_SIGNATURE->value;
    }

    /**
     * 处理流程交互
     *
     * @param array $obj 交互数据
     * @return bool
     */
    public function adapter(array $obj): bool
    {
        $taskId = $obj['taskId'] ?? 0;
        // 获取登录用户信息，这里需要根据实际情况实现
        $sysUser = getLoginUser();
        $userId = (string)($sysUser['user_id'] ?? 0);

        $flowParams = new FlowParams();
        $flowParams->handler($userId)
            ->permissionFlag($this->permissionList($sysUser, $userId))
            ->addHandlers($obj['addHandlers'] ?? [])
            ->message($this->type($obj['operatorType'] ?? 0));

        return $this->taskService->addSignature($taskId, $flowParams);
    }
}
