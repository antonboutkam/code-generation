<?php
namespace Generator\Api\Builder;

use Cli\CodeGen\System\Helper\Skeleton;
use Generator\IApiStructureConfig;
use Throwable;
use Twig_Error_Loader;
use Twig_Error_Syntax;

final class Structure
{
    function __construct()
    {
        if (!isset($_ENV['SYSTEM_ROOT'])) {
            exit('To run this command the environment variable SYSTEM_ROOT must be set.');
        }
    }

    /**
     * @param IApiStructureConfig $oApiStructureConfig
     * @throws Throwable
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Syntax
     */
    function create(IApiStructureConfig $oApiStructureConfig)
    {
        foreach (['live', 'test'] as $sEnv)
        {
            $this->createStructure($oApiStructureConfig, $sEnv);
        }
    }

    /**
     * @param IApiStructureConfig $oApiStructureVo
     * @param string $sEnv
     * @throws Throwable
     * @throws Twig_Error_Syntax
     */
    function createStructure(IApiStructureConfig $oApiStructureVo, string $sEnv)
    {
        $this->makeSiteDirectories($oApiStructureVo, $sEnv);
        $this->makeSiteSettings($oApiStructureVo, $sEnv);
        $this->makeSiteJson($oApiStructureVo, $sEnv);
        $this->makeIndex($oApiStructureVo, $sEnv);
        $this->makeHtaccess($oApiStructureVo, $sEnv);
        $this->makeEndpointFilter($oApiStructureVo, $sEnv);
        $this->makeControllerFactory($oApiStructureVo, $sEnv);
    }

    /**
     * @param IApiStructureConfig $oApiStructureVo
     * @param $sEnv
     * @throws Throwable
     */
    private function makeSiteSettings(IApiStructureConfig $oApiStructureVo, $sEnv): void
    {
        $this->copyParseTemplate('site-settings.php', ['system' => $oApiStructureVo], $oApiStructureVo, $sEnv);
    }

    /**
     * @param IApiStructureConfig $oApiStructureConfig
     * @param string $sEnv
     * @throws Throwable
     */
    private function makeSiteJson(IApiStructureConfig $oApiStructureConfig, string $sEnv): void
    {
        $this->copyParseTemplate('site.json', ['system' => $oApiStructureConfig], $oApiStructureConfig, $sEnv);
    }


    /**
     * @param IApiStructureConfig $oApiStructureVo
     * @param string $sEnv
     * @throws Throwable
     */
    private function makeEndpointFilter(IApiStructureConfig $oApiStructureVo, string $sEnv): void
    {
        $this->copyParseTemplate('modules/EndpointFilter.php', ['system' => $oApiStructureVo], $oApiStructureVo, $sEnv);
    }

    /**
     * @param IApiStructureConfig $oApiStructureVo
     * @param string $sEnv
     * @throws Throwable
     */
    private function makeControllerFactory(IApiStructureConfig $oApiStructureVo, string $sEnv): void
    {
        $this->copyParseTemplate('modules/ControllerFactory.php', ['system' => $oApiStructureVo], $oApiStructureVo, $sEnv);
    }

    /**
     * @param IApiStructureConfig $oApiStructureVo
     * @param string $sEnv
     * @throws Throwable
     */
    private function makeHtaccess(IApiStructureConfig $oApiStructureVo, string $sEnv): void
    {
        $this->copyParseTemplate('public_html/.htaccess', ['system' => []], $oApiStructureVo, $sEnv);
    }

    /**
     * @param IApiStructureConfig $oApiStructureVo
     * @param string $sEnv
     * @throws Throwable
     */
    private function makeIndex(IApiStructureConfig $oApiStructureVo, string $sEnv): void
    {
        $this->copyParseTemplate('public_html/index.php', [], $oApiStructureVo, $sEnv);
    }

    /**
     * @param $sFile
     * @param $aVars
     * @param IApiStructureConfig $oApiStructureVo
     * @param $sEnv
     * @throws Throwable
     */
    private function copyParseTemplate($sFile, $aVars, IApiStructureConfig $oApiStructureVo, $sEnv)
    {
        $sParsedTemplate = Skeleton::parseTemplate('api_structure', $sFile, $aVars);
        $sDestFile = $oApiStructureVo->getInstallDir($sEnv) . '/' . $sFile;
        echo "Write: $sDestFile." . PHP_EOL;
        file_put_contents($sDestFile, $sParsedTemplate);
    }

    private function makeSiteDirectories(IApiStructureConfig $oApiStructureVo, $sEnv):void
    {
        $aDirectories = [
            $oApiStructureVo->getInstallDir($sEnv),
            $oApiStructureVo->getInstallDir($sEnv) .'/modules',
            $oApiStructureVo->getInstallDir($sEnv) .'/public_html',
        ];

        foreach ($aDirectories as $sDirectory)
        {
            if(!is_dir($sDirectory))
            {
                echo "Creating directory " . $sDirectory . PHP_EOL;
                mkdir($sDirectory, 0777, true);
            }
        }
    }
}
