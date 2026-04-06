<?php

namespace plugin\yflow\app\bootstrap;

use Webman\Bootstrap;
use Yflow\core\config\YFlowConfig;
use Yflow\core\invoker\FrameInvoker;
use Yflow\YFlowBootstrap;

class FlowInit implements Bootstrap
{

    public static function start($worker)
    {
        // Is it console environment ?
        $is_console = !$worker;
        if ($is_console) {
            // If you do not want to execute this in console, just return.
            return;
        }

        // 只在webman的0号进程执行
        if ($worker->name != 'webman' || $worker->id != 0) {
            return;
        }

        self::initFlow();
    }

    public static function initFlow()
    {
        YFlowBootstrap::registerBeforeCallback(function (YFlowConfig $yFlow) {
            $yFlow->setBanner(getenv('PRINT_BANNER') === 'true');
            $yFlow->setTopTextShow(false); // 设置 topTextShow为 false
            $yFlow->setBeanScanDir([
                app_path(),
            ]);
            FrameInvoker::addDependences(config('plugin.yflow.yflowConfig.dependence') ?? []);
        });

        YFlowBootstrap::registerAfterCallback(function () {
//            $jsonConvert = FrameInvoker::getBean(\Yflow\core\json\JsonConvert::class);
//            dump($jsonConvert);
        });

        YFlowBootstrap::init();
    }
}
