<?php

namespace plugin\yflow\custom\bean;

use Yflow\core\annotation\Bean;

/**
 * 票签spel表达式计算
 *
 *
 * @since 2025/11/7
 */
#[Bean(name: "voteSignService")]
class VoteSignService
{
    /**
     * 票签通过率计算
     *
     * @param string $skipType 跳转类型
     * @param int $passNum 审批通过人数
     * @param int $rejectNum 审批驳回人数
     * @param int $todoNum 待处理人数
     * @param int $allNum 总人数
     * @param array $passList 通过历史任务列表，HisTask中approver字段是审批人的唯一标识
     * @param array $rejectList 拒绝历史任务列表，HisTask中approver字段是审批人的唯一标识
     * @param array $todoList 待处理用户列表
     * @return bool
     */
    public function eval(string $skipType, int $passNum, int $rejectNum, int $todoNum, int $allNum,
                         array  $passList, array $rejectList, array $todoList): bool
    {
        // 记录日志
        dump("跳过类型: {$skipType}");
        dump("通过数量: {$passNum}");
        dump("拒绝数量: {$rejectNum}");
        dump("待处理数量: {$todoNum}");
        dump("总人数: {$allNum}");
        dump("通过历史任务列表: ", $passList);
        dump("拒绝历史任务列表: ", $rejectList);
        dump("待处理用户列表: ", $todoList);
        dump("开始票签通过率计算......");

        return true;
    }
}
