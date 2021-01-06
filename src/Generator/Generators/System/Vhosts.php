<?php
namespace Generator\Generators\System;

use Cli\Tools\VO\SystemBuildConfig;
use Core\Environment;
use Core\Mqtt;

final class Vhosts
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
        if($oSystemBuildVo->getCreateVhostLocal() && Environment::isDevel())
        {
            $aMessages = [
                [
                    'document_root' => $_ENV['SYSTEM_ROOT'] . '/public_html/' . $oSystemBuildVo->getSiteDomainName() . '/public_html',
                    'domain' => $oSystemBuildVo->getSiteDomainName('dev')
                ],
                [
                    'document_root' => $_ENV['SYSTEM_ROOT'] . '/admin_public_html/',
                    'domain' => 'admin.' . $oSystemBuildVo->getSiteDomainName('dev')
                ],
                [
                    'document_root' => $_ENV['SYSTEM_ROOT'] . '/public_html/api.' . $oSystemBuildVo->getSiteDomainName() . '/public_html',
                    'domain' => 'api.' . $oSystemBuildVo->getSiteDomainName('dev')
                ]
            ];


            Mqtt::setConfig('localhost', 'cloudmanager', 'Tsmakosrss2019!');
            foreach ($aMessages as $aMessage)
            {
                echo "Asking for local vhosts {$aMessage['domain']}" . PHP_EOL;
                $sMessage = json_encode($aMessage);
                Mqtt::publish('local/create_vhost', $sMessage);
            }




        }
        if($oSystemBuildVo->getCreateVhostProduction())
        {
            $this->makeVhosts('live', $oSystemBuildVo);
        }
        if($oSystemBuildVo->getCreateVhostTest())
        {
            $this->makeVhosts('test', $oSystemBuildVo);
        }

    }

    /**
     * @param $sEnv
     * @param SystemBuildConfig $oSystemBuildVo
     * @throws \Exception
     */
    private function makeVhosts($sEnv, SystemBuildConfig $oSystemBuildVo)
    {

        $sDomain = $oSystemBuildVo->getSiteDomainName($sEnv);
        $sServiceName = $oSystemBuildVo->getServiceName();
        $aMessages = [
            [
                'domain' => $sDomain,
                'document_root' => '/var/www/1overheid/' . $sServiceName . '/system/public_html/' . $sDomain . '/public_html',
                'server_admin' => $oSystemBuildVo->getTechnicalEmail(),
                'SYSTEM_ID' => $oSystemBuildVo->getSystemId(),
                'log_dir' => '/var/www/1overheid/' . $sServiceName . '/data/log',
                'custom_log' => '/var/www/1overheid/' . $sServiceName . '/data/log/access.log',
                'SYSTEM_ROOT' => '/home/' . $oSystemBuildVo->getLinuxUser($sEnv) . '/data/' . $sServiceName
            ],
            [
                'domain' => 'admin.' . $sDomain,
                'document_root' => '/var/www/1overheid/' . $sServiceName . '/system/admin_public_html',
                'server_admin' => $oSystemBuildVo->getTechnicalEmail(),
                'SYSTEM_ID' =>  $oSystemBuildVo->getSystemId(),
                'log_dir' => '/var/www/1overheid/' . $sServiceName . '/data/log',
                'SYSTEM_ROOT' => '/home/' . $oSystemBuildVo->getLinuxUser($sEnv) . '/data/' . $sServiceName
            ],
            [
                'domain' => 'api.' . $sDomain,
                'document_root' => '/var/www/1overheid/' . $sServiceName . '/system/public_html/api.' . $sDomain . '/public_html',
                'server_admin' => $oSystemBuildVo->getTechnicalEmail(),
                'SYSTEM_ID' =>  $oSystemBuildVo->getSystemId(),
                'log_dir' => '/var/www/1overheid/' . $sServiceName . '/data/log',
                'SYSTEM_ROOT' => '/home/' . $oSystemBuildVo->getLinuxUser($sEnv) . '/data/' . $sServiceName
            ],
        ];

        foreach ($aMessages as $aMessage)
        {
            $sMessage = json_encode($aMessage);
            Mqtt::setConfig($oSystemBuildVo->getServer($sEnv), 'vhost_requester', 'Ts!@#21osrss2019!');
            echo "Asking for online vhost {$aMessage['domain']} - {$aMessage['document_root']}" . PHP_EOL;
            echo "Sleeping for 3 seconds for certbot and apache".PHP_EOL;
            sleep(3);
            Mqtt::publish('system/vhost/create', $sMessage);
        }

    }
}

