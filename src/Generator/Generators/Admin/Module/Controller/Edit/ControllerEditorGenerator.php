<?php/*** @unfixed**/

namespace Generator\Admin\Module\Controller\Edit\ControllerEditorGenerator;

use AdminModules\GenericEditController;
use Cli\Tools\CommandUtils;
use Crud\FormManager;
use Helper\Schema\Table;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;

final class ControllerEditorGenerator
{
    public function create(Table $oTable)
    {
        echo "Write file " . $this->getBaseFileName($oTable) . PHP_EOL;

        $sBaseOverviewController = $this->createBaseOverviewController($oTable);

        file_put_contents($this->getBaseFileName($oTable), '<?php/*** @unfixed**/' . PHP_EOL . $sBaseOverviewController);
        if (!file_exists($this->getFileName($oTable))) {
            echo "Write file " . $this->getFileName($oTable) . PHP_EOL;
            $sEditController = $this->createOverviewController($oTable);
            file_put_contents($this->getFileName($oTable), '<?php/*** @unfixed**/' . PHP_EOL . $sEditController);
        } else {
            echo "Skipping Write file " . $this->getFileName($oTable) . " already existed" . PHP_EOL;
        }
    }

    private function createOverviewController(Table $oTable)
    {
        $oNamespace = new PhpNamespace($this->getNamespace($oTable));
        $oNamespace->addUse($this->getBaseClassNamespace($oTable));

        $oClass = new ClassType('EditController');
        $oClass->setFinal(true);
        $oClass->addExtend($this->getBaseClassNamespace($oTable) . '\\EditController');

        $oClass->setComment("Skeleton subclass for drawing a list of " . $oTable->getPhpName() . " records.");
        $oClass->addComment(str_repeat(PHP_EOL, 2));
        $oClass->addComment("You should add additional methods to this class to meet the");
        $oClass->addComment("application requirements.  This class will only be generated as");
        $oClass->addComment("long as it does not already exist in the output directory.");

        $oNamespace->add($oClass);
        return $oNamespace;
    }

    private function createBaseOverviewController(Table $oTable): string
    {
        $oNamespace = new PhpNamespace($this->getBaseClassNamespace($oTable));
        $oClass = new ClassType('EditController');

        $oNamespace->addUse(GenericEditController::class);
        $oClass->addExtend(GenericEditController::class);
        $oClass->setAbstract(true);

        $oClass->addComment("This class is automatically generated, do not modify manually.");
        $oClass->addComment("Modify " . $this->getNamespace($oTable) . " instead if you need to override or add functionality.");

        $sManagerClassName = 'Crud' . $oTable->getPhpName() . 'Manager';
        $sGeneratedCrudNamespace = $oTable->getCrudNamespace() . '\\' . $oTable->getPhpName();
        $oNamespace->addUse($sGeneratedCrudNamespace . '\\' . $sManagerClassName);
        $oNamespace->addUse(FormManager::class);

        $oGetManagerMethod = $oClass->addMethod('getCrudManager');
        $oGetManagerMethod->setReturnType(FormManager::class);
        $oGetManagerMethod->setBody('return new ' . $sManagerClassName . '();');

        $oGetTitleMethod = $oClass->addMethod('getPageTitle');
        $oGetTitleMethod->setReturnType('string');
        $oGetTitleMethod->setBody('return "' . $oTable->getTitle() . '";');
        $oNamespace->add($oClass);
        return (string)$oNamespace;
    }

    private function getBaseClassNamespace(Table $oTable): string
    {
        if ((string)$oTable->getDatabase()->getCustom() !== '') {
            return 'AdminModules\\Custom\\' . $oTable->getDatabase()->getCustom() . '\\' . $oTable->getModule()->getModuleDir() . '\\' . ucfirst($oTable->getName()) . '\\Base';
        }
        return 'AdminModules\\' . $oTable->getModule()->getModuleDir() . '\\' . ucfirst($oTable->getName()) . '\\Base';
    }

    private function getNamespace(Table $oTable): string
    {
        if ((string)$oTable->getDatabase()->getCustom() !== '') {
            return 'AdminModules\\Custom\\' . $oTable->getDatabase()->getCustom() . '\\' . $oTable->getModule()->getModuleDir() . '\\' . ucfirst($oTable->getName());
        }
        return 'AdminModules\\' . $oTable->getModule()->getModuleDir() . '\\' . ucfirst($oTable->getName());
    }

    private function getBaseFileName(Table $oTable): string
    {
        if ((string)$oTable->getDatabase()->getCustom() !== '') {
            return CommandUtils::getRoot() . '/admin_modules/Custom/' . $oTable->getDatabase()->getCustom() . '/' . $oTable->getModule()->getModuleDir() . '/' . ucfirst($oTable->getName()) . '/Base/EditController.php';
        }

        return CommandUtils::getRoot() . '/admin_modules/' . $oTable->getModule()->getModuleDir() . '/' . ucfirst($oTable->getName()) . '/Base/EditController.php';
    }

    private function getFileName(Table $oTable): string
    {
        if ((string)$oTable->getDatabase()->getCustom() !== '') {
            return CommandUtils::getRoot() . '/admin_modules/Custom/' . $oTable->getDatabase()->getCustom() . '/' . $oTable->getModule()->getModuleDir() . '/' . ucfirst($oTable->getName()) . '/EditController.php';
        }
        return CommandUtils::getRoot() . '/admin_modules/' . $oTable->getModule()->getModuleDir() . '/' . ucfirst($oTable->getName()) . '/EditController.php';
    }
}