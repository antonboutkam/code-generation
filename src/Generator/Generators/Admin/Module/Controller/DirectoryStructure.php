<?php
namespace Generator\Generators\Admin\Module\Controller;

use Cli\Tools\CommandUtils;
use Helper\Schema\Table;

final class DirectoryStructure
{
    function create(Table $oTable)
    {
        if((string)$oTable->getDatabase()->getCustom() !== '')
        {
            $sModuleRoot = CommandUtils::getRoot() . '/admin_modules/Custom/' . $oTable->getDatabase()->getCustom() . '/' . $oTable->getModule()->getModuleDir();
        }
        else
        {
            $sModuleRoot = CommandUtils::getRoot() . '/admin_modules/' . $oTable->getModule()->getModuleDir();
        }

        $aDirs = [
            $sModuleRoot . '/Locales',
            $sModuleRoot . '/' . ucfirst($oTable->getName()),
            $sModuleRoot . '/' . ucfirst($oTable->getName()) . '/Locales',
            $sModuleRoot . '/' . ucfirst($oTable->getName()) . '/Base',
        ];

        foreach ($aDirs as $sDirPath)
        {
            echo $sDirPath . PHP_EOL;
            if(!is_dir($sDirPath))
            {
                mkdir($sDirPath, 0777, true);
            }
        }

    }
}
