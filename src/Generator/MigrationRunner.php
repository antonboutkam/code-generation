<?php
namespace Generator;

use Model\System\MigrationScripts;
use Model\System\MigrationScriptsQuery;
use Propel\Runtime\Propel;

class MigrationRunner
{
    public static function runAll(string $sGlobPattern, $aFilesToSkip = [])
    {
        $aFiles = glob($sGlobPattern);
        sort($aFiles, SORT_NUMERIC);

        $oConnection = Propel::getWriteConnection('hurah');

        try
        {

            foreach($aFiles as $sFile)
            {
                echo "Executing $sFile" . PHP_EOL;
                require_once $sFile;
            }

            echo "All done, commit." . PHP_EOL;
        }
        catch (\Exception $exception)
        {
            echo "Got an exception, rolling back everything (origin: $sFile)" . PHP_EOL;
            var_dump($exception);
            echo $exception->getMessage() . PHP_EOL;
            $oConnection->rollBack();
        }
    }
    public static function runOnce(string $sGlobPattern, $aFilesToSkip = [])
    {
        $aFiles = glob($sGlobPattern);
        sort($aFiles, SORT_NUMERIC);

        $oConnection = Propel::getWriteConnection('hurah');

        $aMoveAfterFiles = [];
        try
        {

            foreach($aFiles as $sFile)
            {
                $oMigrationScript = MigrationScriptsQuery::create()->findOneByFileName($sFile);
                if($oMigrationScript instanceof MigrationScripts)
                {
                    echo "Skipping " . basename($sFile) . " already have it" .  PHP_EOL;
                    continue;
                }
                echo "Executing " . basename($sFile) .  PHP_EOL;

                $aMoveAfterFiles[] = $sFile;
                $sContents = file_get_contents($sFile);
                require_once $sFile;

                echo "Saving $sFile in migration_scripts table" . PHP_EOL;
                $oMigrationScripts = new MigrationScripts();
                $oMigrationScripts->setFileName($sFile);
                $oMigrationScripts->setFileContents($sContents);
                $oMigrationScripts->save();
            }

            echo "All done, commit." . PHP_EOL;
        }
        catch (\Exception $exception)
        {
            echo "Got an exception, rolling back everything (origin: $sFile)" . PHP_EOL;
            var_dump($exception);
            echo $exception->getMessage() . PHP_EOL;
            $oConnection->rollBack();
        }
    }
}
