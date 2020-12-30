<?php

namespace Generator\Generators\Server\Database;

use Cli\Tools\VO\SystemBuildConfig;
use Core\Environment;
use Core\InlineTemplate;

final class Databases
{
    public function __construct()
    {
        if (!isset($_ENV['SYSTEM_ROOT'])) {
            exit('To run this command the environment variable SYSTEM_ROOT must be set.');
        }
    }

    public function create(SystemBuildConfig $oSystemBuildVo)
    {
        if ($oSystemBuildVo->getCreateDbLocal() && Environment::isDevel()) {
            $sUser = 'root';
            $sPass = 'Bttl66!!77!!';
            $sServer = 'localhost';
            $sNewUserPass = $oSystemBuildVo->generatePass('dev');
            $sNewUserDbName = $oSystemBuildVo->getDbName('dev');
            $this->runCreateQueries($sUser, $sPass, $sServer, $sNewUserDbName, $sNewUserPass);
        }

        if ($oSystemBuildVo->getCreateDbTest()) {
            $sUser = $oSystemBuildVo->getTestMysqlRootUser();
            $sPass = $oSystemBuildVo->getTestMysqlRootPass();
            $sServer = $oSystemBuildVo->getProductionServer();
            $sNewUserPass = $oSystemBuildVo->generatePass('test');
            $sNewUserDbName = $oSystemBuildVo->getDbName('test');
            $this->runCreateQueries($sUser, $sPass, $sServer, $sNewUserDbName, $sNewUserPass, Environment::isDevel());
        }

        if ($oSystemBuildVo->getCreateDbProduction()) {
            $sUser = $oSystemBuildVo->getProductionMysqlRootUser();
            $sPass = $oSystemBuildVo->getProductionMysqlRootPass();
            $sServer = $oSystemBuildVo->getProductionServer();
            $sNewUserPass = $oSystemBuildVo->generatePass('live');
            $sNewUserDbName = $oSystemBuildVo->getDbName('live');
            $this->runCreateQueries($sUser, $sPass, $sServer, $sNewUserDbName, $sNewUserPass, Environment::isDevel());
        }
    }

    public function createDatabase($sUser, $sPass, $sServer, $sDbToCreate, bool $bOverSsh = false)
    {
        $sQuery = "CREATE DATABASE IF NOT EXISTS " . $sDbToCreate . ";";
        $this->runQuery($sQuery, $sUser, $sPass, $sServer, $bOverSsh);
    }

    public function createUser($sUser, $sPass, $sServer, $sUserToCreate, $sDbToGiveAccessTo, $sPassToCreate, bool $bOverSsh = false)
    {
        $aQueries = [
            "CREATE USER IF NOT EXISTS '$sUserToCreate'@'localhost' IDENTIFIED BY '$sPassToCreate';",
            "GRANT ALL PRIVILEGES ON $sUserToCreate.* TO $sDbToGiveAccessTo@localhost;",
            "ALTER USER '$sUserToCreate'@'localhost' IDENTIFIED BY '$sPassToCreate';",
            "FLUSH PRIVILEGES;",
        ];

        foreach ($aQueries as $sQuery) {
            $this->runQuery($sQuery, $sUser, $sPass, $sServer, $bOverSsh);
        }
    }

    private function runQuery($sQuery, $sUser, $sPass, $sServer, bool $bOverSsh = false)
    {
        if ($bOverSsh) {
            echo $sQuery . PHP_EOL;
            $sCombinedQuery = 'ssh novum@' . $sServer . ' "mysql -u' . $sUser . ' -p\"' . $sPass . '\" -e \"' . $sQuery . '\""';
            $sShellResponse = shell_exec($sCombinedQuery);
            echo "Shell output:" . $sShellResponse . PHP_EOL;
        } else {
            $link = mysqli_connect($sServer, $sUser, $sPass);
            echo $sQuery . PHP_EOL;
            mysqli_query($link, $sQuery);
        }
    }

    private function runCreateQueries($sUser, $sPass, $sServer, $dbToCreate, $sPassToCreate, bool $bOverSsh = false): void
    {
        $this->createUser($sUser, $sPass, $sServer, $dbToCreate, $sPassToCreate, $bOverSsh);
        $this->createDatabase($sUser, $sPass, $sServer, $dbToCreate, $bOverSsh);
        $aQueries = [
            "CREATE USER IF NOT EXISTS '$dbToCreate'@'localhost' IDENTIFIED BY '$sPassToCreate';",
            "GRANT ALL PRIVILEGES ON $dbToCreate.* TO $dbToCreate@localhost;",
            "ALTER USER '$dbToCreate'@'localhost' IDENTIFIED BY '$sPassToCreate';",
            "FLUSH PRIVILEGES;",
        ];

        foreach ($aQueries as $sQuery) {
            $this->runQuery($sQuery, $sUser, $sPass, $sServer);

            echo $sQuery . PHP_EOL;
            $sCombinedQuery = 'ssh novum@' . $sServer . ' "mysql -u' . $sUser . ' -p\"' . $sPass . '\" -e \"' . $sQuery . '\""';
            $sShellResponse = shell_exec($sCombinedQuery);
            echo "Shell output:" . $sShellResponse . PHP_EOL;
        }
    }
}
