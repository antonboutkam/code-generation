<?php
namespace Generator\Generators\System;

use Generator\Vo\SystemBuildConfig;
use Core\InlineTemplate;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

class RegisterNamespace
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
        echo "Adding new namespaces to the autoloader" . PHP_EOL;
        $sComposerFileName = $_ENV['SYSTEM_ROOT'] . '/composer.json';
        $sComposerJson = file_get_contents($sComposerFileName);
        $aComposerJson = json_decode($sComposerJson, true);

        $aComposerJson['autoload']['psr-4'][$oSystemBuildVo->getNamespace() . '\\'] = 'public_html/' . $oSystemBuildVo->getSiteDomainName() . '/modules';
        $aComposerJson['autoload']['psr-4']['Test' . $oSystemBuildVo->getNamespace() . '\\'] = 'public_html/' . $oSystemBuildVo->getSiteDomainName('test') . '/modules';

        if($oSystemBuildVo->getHasApi())
        {
            $aComposerJson['autoload']['psr-4']['Api' . $oSystemBuildVo->getNamespace() . '\\'] = 'public_html/api.' . $oSystemBuildVo->getSiteDomainName() . '/modules';
            $aComposerJson['autoload']['psr-4']['ApiTest' . $oSystemBuildVo->getNamespace() . '\\'] = 'public_html/api.' . $oSystemBuildVo->getSiteDomainName('test') . '/modules';
        }


        $sNewComposerJson = json_encode($aComposerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($sComposerFileName, $sNewComposerJson);

        exec("composer dump-autoload -d " . $_ENV['SYSTEM_ROOT']);


        /*
        putenv('COMPOSER_HOME=' . $_ENV['SYSTEM_ROOT'] . '/vendor/bin/composer');

        echo "Running \"composer dump-autoload\"" . PHP_EOL;
        $input = new ArrayInput(array('command' => 'dump-autoload'));
        $application = new Application();
        $application->setAutoExit(false); // prevent `$application->run` method from exitting the script
        $application->run($input);
        */
    }

}



