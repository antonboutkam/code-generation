<?php

namespace Generator\Admin\Module\Controller\Overview;

use AdminModules\GenericOverviewController;
use Core\LogActivity;
use Core\StatusMessage;
use Core\StatusMessageButton;
use Core\StatusModal;
use Core\Translate;
use Crud\FormManager;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Symfony\Component\Console\Output\OutputInterface;

class BaseGenerator {

    private GeneratorConfigInterface $config;
    private OutputInterface $output;

    public function __construct(GeneratorConfigInterface $config, OutputInterface $oOutput) {
        $this->config = $config;
        $this->output = $oOutput;
    }

    /**
     * @return string
     */
    public function generate(): string {
        $oNamespace = new PhpNamespace($this->config->getBaseNamespace());
        $oClass = new ClassType('OverviewController');

        $oNamespace->addUse(GenericOverviewController::class);
        $oClass->addExtend(GenericOverviewController::class);
        $oClass->setAbstract(true);

        $oClass->addComment("This class is automatically generated, do not modify manually.");
        $oClass->addComment("Modify " . $this->config->getNamespace() . " instead if you need to override or add functionality.");

        $oConstructorMethod = $oClass->addMethod('__construct');
        $oConstructorMethod->addParameter('aGet');
        $oConstructorMethod->addParameter('aPost');

        $sBody = '$this->setEnablePaginate(50);';
        $sBody .= 'parent::__construct($aGet, $aPost);';
        $oConstructorMethod->setBody($sBody);

        $oGetTitleMethod = $oClass->addMethod('getTitle');
        $oGetTitleMethod->setReturnType('string');
        $oGetTitleMethod->setBody('return "' . $this->config->getTitle() . '";');

        $sManagerClassName = 'Crud' . $this->config->getPhpName() . 'Manager';
        $sGeneratedCrudNamespace = $this->config->getCrudNamespace() . '\\' . $this->config->getPhpName();
        $oNamespace->addUse($sGeneratedCrudNamespace . '\\' . $sManagerClassName);
        $oNamespace->addUse(FormManager::class);

        $oGetManagerMethod = $oClass->addMethod('getManager');
        $oGetManagerMethod->setReturnType(FormManager::class);
        $oGetManagerMethod->setBody('return new ' . $sManagerClassName . '();');

        $oGetManagerMethod = $oClass->addMethod('getQueryObject');
        $oGetManagerMethod->setReturnType(ModelCriteria::class);
        $oGetManagerMethod->setBody('return ' . $this->config->getQueryClass() . '::create();');
//
        $oDoDelete = $oClass->addMethod('doDelete');
        $oDoDelete->setReturnType('void');
        $aDoDeleteBody = [
            '$iId = $this->get(\'id\', null, true, \'numeric\');',
            '$oQueryObject = $this->getQueryObject();',
            '$oDataObject = $oQueryObject->findOneById($iId);',
            'if($oDataObject instanceof ' . $this->config->getModelClass() . '){',
            '    LogActivity::register("' . $this->config->getModuleName() . '", "' . $this->config->getTitle() . ' verwijderen", $oDataObject->toArray());',
            '    $oDataObject->delete();',
            '    StatusMessage::success("' . $this->config->getTitle() . ' verwijderd.");',
            '}',
            'else',
            '{',
            '       StatusMessage::warning("' . $this->config->getTitle() . ' niet gevonden.");',
            '}',
            '$this->redirect($this->getManager()->getOverviewUrl());',
        ];

        $oDoDelete->setBody(join(PHP_EOL, $aDoDeleteBody));
        $oNamespace->addUse($this->config->getModelClass(true));

        $oDoConfirmDelete = $oClass->addMethod('doConfirmDelete');

        $oDoConfirmDelete->setFinal(true);
        $oDoConfirmDelete->setReturnType('void');
        $oDoConfirmDelete->setVisibility('public');
        $aDoConfirmDeleteBody = [
            '$iId = $this->get(\'id\', null, true, \'numeric\');',
            '$sMessage = Translate::fromCode("Weet je zeker dat je dit ' . $this->config->getTitle() . ' item wilt verwijderen?");',
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
        $oNamespace->addUse($this->config->getModelNamespace() . '\\' . $this->config->getQueryClass());
        $oNamespace->addUse(ModelCriteria::class);
        $oNamespace->add($oClass);
        return '<?php' . PHP_EOL . (string)$oNamespace;
    }
    /*
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
    */
}
