<?php

namespace Generator\Generators\Admin\Module\Config;

use AdminModules\ModuleConfig;
use Cli\Tools\CommandUtils;
use Core\Translate;
use Helper\Schema\Table;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use Propel\Runtime\Exception\PropelException;

final class ModuleConfigfile
{
    /**
     * @param Table $oTable
     * @throws PropelException
     */
    function create(Table $oTable)
    {
        if ((string)$oTable->getDatabase()->getCustom() != '') {
            $sConfigFilePath = CommandUtils::getRoot() . '/admin_modules/Custom/' . $oTable->getDatabase()->getCustom() . '/' . $oTable->getModule()->getName() . '/Config.php';
            $sConfigNamespace = 'AdminModules\\Custom\\' . $oTable->getDatabase()->getCustom() . '\\' . $oTable->getModule()->getName();
        } else {
            $sConfigFilePath = CommandUtils::getRoot() . '/admin_modules/' . $oTable->getModule()->getName() . '/Config.php';
            $sConfigNamespace = 'AdminModules\\' . $oTable->getModule()->getName();
        }


        $sClassName = 'Config';
        $oNamespace = new PhpNamespace($sConfigNamespace);
        $oNamespace->addUse(ModuleConfig::class);
        $oNamespace->addUse(Translate::class);

        $oClass = new ClassType($sClassName);
        $oClass->setExtends(ModuleConfig::class);
        $oClass->setFinal();
        $oClass->addMethod('isEnabelable')->setBody('return true;')->setReturnType('bool');
        $oClass->addMethod('getModuleTitle')
            ->setBody('return Translate::fromCode("' . $oTable->getModuleName() . '");')
            ->setReturnType('string');

        $oNamespace->add($oClass);
        echo "Write file " . $sConfigFilePath . PHP_EOL;
        file_put_contents($sConfigFilePath, '<?php' . PHP_EOL . (string)$oNamespace);
    }
}
