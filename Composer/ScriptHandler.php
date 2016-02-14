<?php

namespace Ola\RabbitMqAdminToolkitBundle\Composer;

use Composer\Script\CommandEvent;
use Sensio\Bundle\DistributionBundle\Composer\ScriptHandler as BaseScriptHandler;

class ScriptHandler extends BaseScriptHandler
{
    /**
     * Define rabbitmq vhost
     *
     * @param $event CommandEvent A instance
     */
    public static function vhostDefine(CommandEvent $event)
    {
        $consoleDir = static::getConsoleDir($event, 'define rabbitmq vhost');

        if (null === $consoleDir) {
            return;
        }

        static::executeCommand($event, $consoleDir, 'rabbitmq:vhost:define');
    }
}
