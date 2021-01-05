<?php
namespace Generator\Env;

use Core\Utils;
use Hi\Helpers\DirectoryStructure;
use Generator\IEnvConfig;
use Symfony\Component\Console\Command\Command;

final class Builder
{
    function __construct() {
        if (!isset($_ENV['SYSTEM_ROOT'])) {
            exit('To run this command the environment variable SYSTEM_ROOT must be set.');
        }
    }

    function create(IEnvConfig $oEnvConfig) {

        $oDirectoryStructure = new DirectoryStructure();

        $aEnvContents = [
            "SYSTEM_ID=" . (string) $oEnvConfig->getSystemId(),
            "DATA_DIR=" . (string) $oDirectoryStructure->getDataDir(true),
            "SYSTEM_ROOT=" . (string) $oDirectoryStructure->getSystemRoot(),
            "DB_USER=" . (string) Utils::slugify($oEnvConfig->getDbUser(), '_'),
            "DB_HOST=" . (string) $oEnvConfig->getDbHost(),
            "DB_PASS=" . (string) $oEnvConfig->getDbPass(),
        ];
        $sEnvFilePath =  Utils::makePath($oDirectoryStructure->getDomainDir(true), $oEnvConfig->getSystemId());
        $sEnvFileLocation = Utils::makePath($sEnvFilePath, '.env');
        if(!is_dir($sEnvFilePath)){
            Utils::makeDir($sEnvFilePath);
        }
        echo "Storing env file at $sEnvFileLocation" . PHP_EOL;
        file_put_contents($sEnvFileLocation, join(PHP_EOL, $aEnvContents));
    }

}
