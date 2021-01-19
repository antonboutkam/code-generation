<?php
namespace Generator\Config;

use Generator\Generators\System\Helper\Skeleton;
use Generator\IConfigBuildVo;
use Core\Utils;
use Hi\Helpers\DirectoryStructure;
use Throwable;
use Twig_Error_Loader;
use Twig_Error_Syntax;

final class Builder
{
    function __construct() {
        if (!isset($_ENV['SYSTEM_ROOT'])) {
            exit('To run this command the environment variable SYSTEM_ROOT must be set.');
        }
    }
    /**
     * @param IConfigBuildVo $oConfigBuildVo
     * @throws Throwable
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Syntax
     */
    function create(IConfigBuildVo $oConfigBuildVo) {
        $this->makeConfigDirectories($oConfigBuildVo);
        $this->makeConfig($oConfigBuildVo);
    }
    /**
     * @param IConfigBuildVo $oConfigBuildVo
     * @throws Throwable
     */
    public function makeConfig(IConfigBuildVo $oConfigBuildVo): void {
        $this->copyParseTemplate('config.php', ['system' => $oConfigBuildVo], $oConfigBuildVo);
    }
    /**
     * @param $sFile
     * @param $aVars
     * @param IConfigBuildVo $oConfigBuildVo
     */
    private function copyParseTemplate($sFile, $aVars, IConfigBuildVo $oConfigBuildVo) {
        $sParsedFile = Skeleton::parseTemplate('config_structure', $sFile, $aVars);
        $sDestFile = $this->getConfigRoot($oConfigBuildVo->getSystemId()) . '/' . $sFile;
        echo "Write: $sDestFile." . PHP_EOL;
        file_put_contents($sDestFile, $sParsedFile);
    }
    function getConfigRoot(string $sSystemId) : string {
        $oDirectoryStructure = new DirectoryStructure();
        return Utils::makePath($oDirectoryStructure->getDomainDir(true), $sSystemId);
    }
    public function makeMainConfigDir(string $sSystemId):void {
        $sDir = Utils::makePath($this->getConfigRoot($sSystemId));
        $this->makeDir($sDir);
    }
    public function makePropelConfigDir(string $sSystemId):void {
        $sDir = Utils::makePath($this->getConfigRoot($sSystemId), 'propel');
        $this->makeDir($sDir);
    }
    private function makeDir(string $sDir) {
        echo "Creating directory $sDir" . PHP_EOL;
        Utils::makeDir($sDir);
    }
    private function makeConfigDirectories(string $sSystemId):void {
        $this->makeMainConfigDir($sSystemId);
        $this->makePropelConfigDir($sSystemId);
    }
}
