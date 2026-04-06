<?php

namespace plugin\yflow\custom\bean;

use Yflow\core\annotation\Bean;

/**
 * 用户类
 */
#[Bean(name: "user")]
class User
{
    /**
     * spel条件表达：判断大于等4
     * @param string $flag 待判断的字符串
     * @return bool
     */
    public function eval(string $flag): bool
    {
        $a = (float)$flag;
        $b = 4;
        return $a > $b;
    }

    /**
     * spel办理人表达式
     * @param mixed $handler 办理人
     * @return mixed
     */
    public function evalVar($handler)
    {
        // 如果是对象，尝试获取其id属性
        if (is_object($handler)) {
            if (method_exists($handler, 'getId')) {
                return $handler->getId();
            } elseif (property_exists($handler, 'id')) {
                return $handler->id;
            }
        }
        return $handler;
    }

    /**
     * spel办理人表达式
     * @param object $handler 办理人
     * @return int
     */
    public function evalVarEntity(object $handler): int
    {
        return $handler->getId();
    }

    /**
     * spel监听器表达式
     * @param object $listenerVariable 监听器变量
     * @return bool
     */
    public function notify(object $listenerVariable): bool
    {
        dump("监听器表达式:", $listenerVariable);
        return true;
    }
}
