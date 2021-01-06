<?php
namespace Generator\Generators\System;

use Cli\Tools\VO\SystemBuildConfig;
use Core\Environment;
use Core\Mqtt;

final class Dns
{
    function __construct()
    {
        if (!isset($_ENV['SYSTEM_ROOT'])) {
            exit('To run this command the environment variable SYSTEM_ROOT must be set.');
        }
    }

    /**
     * @param SystemBuildConfig $oSystemBuildVo
     * @throws \Throwable
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Syntax
     */
    function create(SystemBuildConfig $oSystemBuildVo)
    {
        if($oSystemBuildVo->getCreateHostsLocal() && Environment::isDevel())
        {
            Mqtt::setConfig('localhost', 'cloudmanager', 'Tsmakosrss2019!');
            echo "Add host " . $oSystemBuildVo->getDbName('dev') . PHP_EOL;
            Mqtt::publish('local/add_hosts', $oSystemBuildVo->getDbName('dev'));
            echo "Add host api." . $oSystemBuildVo->getDbName('dev') . PHP_EOL;
            Mqtt::publish('local/add_hosts', 'api.' . $oSystemBuildVo->getDbName('dev'));
            echo "Add host admin." . $oSystemBuildVo->getDbName('dev') . PHP_EOL;
            Mqtt::publish('local/add_hosts', 'admin.' . $oSystemBuildVo->getDbName('dev'));
        }
    }
}

