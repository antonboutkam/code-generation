<?php

namespace Generator\Site\Builder;

use Cli\CodeGen\System\Helper\Skeleton;
use Cli\CodeGen\System\Helper\Target;
use Generator\ISiteStructureConfig;
use Throwable;
use Twig_Error_Loader;
use Twig_Error_Syntax;

final class Structure
{
    function __construct()
    {
        if (!isset($_ENV['SYSTEM_ROOT']))
        {
            exit('To run this command the environment variable SYSTEM_ROOT must be set.');
        }
    }

    /**
     * @param ISiteStructureConfig $oSystemBuildVo
     * @throws Throwable
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Syntax
     */
    function create(ISiteStructureConfig $oSystemBuildVo)
    {
        foreach ([
                     'live',
                     'test',
                 ] as $sEnv)
        {
            $this->createEnvironment($oSystemBuildVo, $sEnv);
        }
    }

    /**
     * @param ISiteStructureConfig $oSiteStructure
     * @param string $sEnv
     * @throws Throwable
     */
    function createEnvironment(ISiteStructureConfig $oSiteStructure, string $sEnv)
    {
        $this->makeSiteDirectories($oSiteStructure, $sEnv);
        $this->makeSiteSettings($oSiteStructure, $sEnv);
        $this->makeSiteJson($oSiteStructure, $sEnv);

        $this->makeIndex($oSiteStructure, $sEnv);
        $this->makeHtaccess($oSiteStructure, $sEnv);
        $this->makeLayout($oSiteStructure, $sEnv);
        $this->makeHomeController($oSiteStructure, $sEnv);
        $this->makeControllerFactory($oSiteStructure, $sEnv);
        $this->makeRevisionFile($oSiteStructure, $sEnv);
    }

    /**
     * @param ISiteStructureConfig $oSiteStructureVo
     * @param string $sEnv
     */
    private function makeSiteJson(ISiteStructureConfig $oSiteStructureVo, string $sEnv): void
    {
        $this->copyParseTemplate('site.json', ['system' => $oSiteStructureVo], $oSiteStructureVo, $sEnv);
    }

    private function makeRevisionFile(ISiteStructureConfig $oSiteStructureVo, string $sEnv)
    {
        touch($oSiteStructureVo->getInstallDir($sEnv) . '/revision');
    }

    /**
     * @param ISiteStructureConfig $oSiteStructureVo
     * @param $sEnv
     * @throws Throwable
     */
    private function makeHomeController(ISiteStructureConfig $oSiteStructureVo, $sEnv): void
    {
        $this->copyParseTemplate('modules/Home/Controller.php', ['system' => $oSiteStructureVo], $oSiteStructureVo, $sEnv);
        $this->copyParseTemplate('modules/Home/home.twig', ['system' => $oSiteStructureVo], $oSiteStructureVo, $sEnv);
    }

    /**
     * @param ISiteStructureConfig $oSiteStructureVo
     * @param $sEnv
     * @throws Throwable
     */
    private function makeLayout(ISiteStructureConfig $oSiteStructureVo, $sEnv): void
    {
        $this->copyParseTemplate('modules/layout.twig', ['system' => $oSiteStructureVo], $oSiteStructureVo, $sEnv);
    }

    /**
     * @param ISiteStructureConfig $oSiteStructure
     * @param $sEnv
     * @throws Throwable
     */
    private function makeControllerFactory(ISiteStructureConfig $oSiteStructure, $sEnv): void
    {
        $this->copyParseTemplate('modules/ControllerFactory.php', ['system' => $oSiteStructure], $oSiteStructure, $sEnv);
    }

    /**
     * @param ISiteStructureConfig $oSiteStructureVo
     * @param $sEnv
     * @throws Throwable
     */
    private function makeHtaccess(ISiteStructureConfig $oSiteStructureVo, $sEnv): void
    {
        $this->copyParseTemplate('public_html/.htaccess', [], $oSiteStructureVo, $sEnv);
    }

    /**
     * @param ISiteStructureConfig $oSiteStructureVo
     * @param $sEnv
     * @throws Throwable
     */
    private function makeIndex(ISiteStructureConfig $oSiteStructureVo, $sEnv): void
    {
        $this->copyParseTemplate('public_html/index.php', [], $oSiteStructureVo, $sEnv);
    }

    /**
     * @param ISiteStructureConfig $oSiteStructureVo
     * @param $sEnv
     * @throws Throwable
     */
    private function makeSiteSettings(ISiteStructureConfig $oSiteStructureVo, $sEnv): void
    {
        $this->copyParseTemplate('site-settings.php', ['system' => $oSiteStructureVo], $oSiteStructureVo, $sEnv);
    }

    /**
     * @param $sFile
     * @param $aVars
     * @param ISiteStructureConfig $oSiteStructure
     * @param string $sEnv
     */
    private function copyParseTemplate($sFile, $aVars, ISiteStructureConfig $oSiteStructure, string $sEnv)
    {
        $sParsedTemplate = Skeleton::parseTemplate('site_structure', $sFile, $aVars);
        $sDestFile = $oSiteStructure->getInstallDir($sEnv) . '/' . $sFile;
        echo "Write: $sDestFile." . PHP_EOL;
        file_put_contents($sDestFile, $sParsedTemplate);
    }

    private function makeSiteDirectories(ISiteStructureConfig $oSiteStructure, $sEnv): void
    {


        $aDirectories = [
            $oSiteStructure->getInstallDir($sEnv),
            $oSiteStructure->getInstallDir($sEnv) . '/modules',
            $oSiteStructure->getInstallDir($sEnv) . '/modules/Home/',
            $oSiteStructure->getInstallDir($sEnv) . '/public_html',
            $oSiteStructure->getInstallDir($sEnv) . '/cron',
            $oSiteStructure->getInstallDir($sEnv) . '/deamon',
        ];

        Target::makeDirectoryStructure($aDirectories);
    }
}
