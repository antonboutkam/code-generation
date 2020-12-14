<?php/*** @unfixed**/

namespace Generator\Admin\Module\Controller\Overview;

use AdminModules\GenericOverviewController;
use Cli\Tools\CommandUtils;
use Core\LogActivity;
use Core\StatusMessage;
use Core\StatusMessageButton;
use Core\StatusModal;
use Core\Translate;
use Crud\FormManager;
use Helper\Schema\Table;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use Propel\Runtime\ActiveQuery\ModelCriteria;

final class REMOVEControllerOverviewGenerator
{

    public function create(Table $oTable)
    {
        echo "Write file " . $this->getBaseFileName($oTable) . PHP_EOL;
        $sBaseOverviewController = $this->createBaseOverviewController($oTable);
        file_put_contents($this->getBaseFileName($oTable), '<?php/*** @unfixed**/' . PHP_EOL . $sBaseOverviewController);

        if (!file_exists($this->getFileName($oTable))) {
            echo "Write file " . $this->getFileName($oTable) . PHP_EOL;
            $sOverviewController = $this->createOverviewController($oTable);
            file_put_contents($this->getFileName($oTable), '<?php/*** @unfixed**/' . PHP_EOL . $sOverviewController);
        } else {
            echo "Skipping write file " . $this->getFileName($oTable) . " already existed " . PHP_EOL;
        }
    }

    private function createOverviewController(Table $oTable)
    {
        $oNamespace = new PhpNamespace($this->getNamespace($oTable));
        $oNamespace->addUse($this->getBaseClassNamespace($oTable));

        $oClass = new ClassType('OverviewController');
        $oClass->setFinal(true);
        $oClass->addExtend($this->getBaseClassNamespace($oTable) . '\\OverviewController');

        $oClass->setComment("Skeleton subclass for drawing a list of " . $oTable->getPhpName() . " records.");
        $oClass->addComment(str_repeat(PHP_EOL, 2));
        $oClass->addComment("You should add additional methods to this class to meet the");
        $oClass->addComment("application requirements.  This class will only be generated as");
        $oClass->addComment("long as it does not already exist in the output directory.");

        $oNamespace->add($oClass);
        return $oNamespace;
    }

    private function createBaseOverviewController(Table $oTable): string {

        $oNamespace = new PhpNamespace($this->getBaseClassNamespace($oTable));
        $oClass = new ClassType('OverviewController');

        $oNamespace->addUse(GenericOverviewController::class);
        $oClass->addExtend(GenericOverviewController::class);
        $oClass->setAbstract(true);

        $oClass->addComment("This class is automatically generated, do not modify manually.");
        $oClass->addComment("Modify " . $this->getNamespace($oTable) . " instead if you need to override or add functionality.");
        // $oClass->setExtends(FormManager::class);

        $oConstructorMethod = $oClass->addMethod('__construct');
        $oConstructorMethod->addParameter('aGet');
        $oConstructorMethod->addParameter('aPost');

        $sBody = '$this->setEnablePaginate(50);';
        $sBody .= 'parent::__construct($aGet, $aPost);';
        $oConstructorMethod->setBody($sBody);

        $oGetTitleMethod = $oClass->addMethod('getTitle');
        $oGetTitleMethod->setReturnType('string');
        $oGetTitleMethod->setBody('return "' . $oTable->getTitle() . '";');

        $oGetModuleMethod = $oClass->addMethod('getModule');
        $oGetModuleMethod->setReturnType('string');
        $oGetModuleMethod->setBody('return "' . $oTable->getPhpName() . '";');

        $sManagerClassName = 'Crud' . $oTable->getPhpName() . 'Manager';
        $sGeneratedCrudNamespace = $oTable->getCrudNamespace() . '\\' . $oTable->getPhpName();
        $oNamespace->addUse($sGeneratedCrudNamespace . '\\' . $sManagerClassName);
        $oNamespace->addUse(FormManager::class);

        $oGetManagerMethod = $oClass->addMethod('getManager');
        $oGetManagerMethod->setReturnType(FormManager::class);
        $oGetManagerMethod->setBody('return new ' . $sManagerClassName . '();');

        $oGetManagerMethod = $oClass->addMethod('getQueryObject');
        $oGetManagerMethod->setReturnType(ModelCriteria::class);
        $oGetManagerMethod->setBody('return ' . $oTable->getQueryClass() . '::create();');

        $oDoDelete = $oClass->addMethod('doDelete');
        $oDoDelete->setReturnType('void');
        $aDoDeleteBody = [
            '$iId = $this->get(\'id\', null, true, \'numeric\');',
            '$oQueryObject = $this->getQueryObject();',
            '$oDataObject = $oQueryObject->findOneById($iId);',
            'if($oDataObject instanceof ' . $oTable->getModelClass() . '){',
            '    LogActivity::register("' . $oTable->getModule()->getName() . '", "' . $oTable->getTitle() . ' verwijderen", $oDataObject->toArray());',
            '    $oDataObject->delete();',
            '    StatusMessage::success("' . $oTable->getTitle() . ' verwijderd.");',
            '}',
            'else',
            '{',
            '       StatusMessage::warning("' . $oTable->getTitle() . ' niet gevonden.");',
            '}',
            '$this->redirect($this->getManager()->getOverviewUrl());',
        ];

        $oDoDelete->setBody(join(PHP_EOL, $aDoDeleteBody));
        $oNamespace->addUse($oTable->getModelClass(true));

        $oDoConfirmDelete = $oClass->addMethod('doConfirmDelete');

        $oDoConfirmDelete->setFinal(true);
        $oDoConfirmDelete->setReturnType('void');
        $oDoConfirmDelete->setVisibility('public');
        $aDoConfirmDeleteBody = [
            '$iId = $this->get(\'id\', null, true, \'numeric\');',
            '$sMessage = Translate::fromCode("Weet je zeker dat je dit ' . $oTable->getTitle() . ' item wilt verwijderen?");',
            '$sTitle = Translate::fromCode("Zeker weten?");',
            '$sOkUrl = $this->getManager()->getOverviewUrl() . "?id=" . $iId . "&_do=Delete";',
            '$sNOUrl = $this->getRequestUri();',
            '$sYes = Translate::fromCode("Ja");',
            '$sCancel = Translate::fromCode("Annuleren");',
            '$aButtons  = [',
            '   new StatusMessageButton($sYes, $sOkUrl, $sYes, "warning"),',
            '   new StatusMessageButton($sCancel, $sNOUrl, $sCancel, "info"),',
            '];',
            'StatusModal::warning($sMessage, $sTitle, $aButtons);',

        ];
        $oDoConfirmDelete->addBody(join(PHP_EOL, $aDoConfirmDeleteBody));

        $oNamespace->addUse(LogActivity::class);
        $oNamespace->addUse(StatusModal::class);
        $oNamespace->addUse(StatusMessage::class);
        $oNamespace->addUse(Translate::class);
        $oNamespace->addUse(StatusMessageButton::class);
        $oNamespace->addUse($oTable->getModelNamespace() . '\\' . $oTable->getQueryClass());
        $oNamespace->addUse(ModelCriteria::class);
        $oNamespace->add($oClass);
        return (string)$oNamespace;
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
            return CommandUtils::getRoot() . '/admin_modules/Custom/' . $oTable->getDatabase()->getCustom() . '/' . $oTable->getModule()->getModuleDir() . '/' . ucfirst($oTable->getName()) . '/Base/OverviewController.php';
        }

        return CommandUtils::getRoot() . '/admin_modules/' . $oTable->getModule()->getModuleDir() . '/' . ucfirst($oTable->getName()) . '/Base/OverviewController.php';
    }

    private function getFileName(Table $oTable): string
    {
        if ((string)$oTable->getDatabase()->getCustom() !== '') {
            return CommandUtils::getRoot() . '/admin_modules/Custom/' . $oTable->getDatabase()->getCustom() . '/' . $oTable->getModule()->getModuleDir() . '/' . ucfirst($oTable->getName()) . '/OverviewController.php';
        }
        return CommandUtils::getRoot() . '/admin_modules/' . $oTable->getModule()->getModuleDir() . '/' . ucfirst($oTable->getName()) . '/OverviewController.php';
    }
}
