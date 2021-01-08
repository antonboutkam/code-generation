<?php

namespace Generator\Generators\Crud\Manager;

use Cli\Tools\CommandUtils;
use Core\Utils;
use Crud\FormManager;
use Crud\IApiExposable;
use Crud\IConfigurableCrud;
use Exception\LogicException;
use Generator\Generators\Crud\FieldIterator\CrudFieldIteratorGenerator;
use Helper\ApiXsd\Schema\Api;
use Helper\Schema\Module;
use Helper\Schema\Table;
use Hurah\Types\Type\PhpNamespace;
use Nette\PhpGenerator;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\Map\TableMap;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

final class CrudManagerGenerator
{
    private OutputInterface $output;

    public function __construct(OutputInterface $oOutput = null)
    {
        if ($oOutput) {
            $this->output = $oOutput;
        } else {
            $this->output = new ConsoleOutput();
        }

    }

    public function create(Table $oTable, Api $oApi = null)
    {
        $this->makeManagerPlaceholder($oTable);
        $this->makeBaseManager($oTable, $oApi);

        if ((string)$oTable->getCrudNamespace() !== 'Crud') {
            // These are generic cruds / the same for every system.
            // The traits are ment to be allow you to modify the behavior of a certain system as a whole.
            // If you want to modify all systems then just modify the classes directly.
            $this->makeCrudTrait($oTable);

            if ($oApi instanceof Api) {
                $this->makeCrudApiTrait($oTable, $oApi);
            }
        }
    }

    private function makeManagerPlaceholder(Table $oTable)
    {
        $sClassName = 'Crud' . $oTable->getPhpName() . 'Manager';
        $sFilePath = CommandUtils::getRoot() . '/classes/Crud/' . $oTable->getCrudDir() . '/' . $oTable->getPhpName() . '/' . $sClassName . '.php';

        if (file_exists($sFilePath)) {
            $this->output('Skipped creating the placeholder version of <info>' . $sClassName . '</info> it already exists');
            return null;
        }

        $sGeneratedCrudNamespace = $oTable->getCrudNamespace() . '\\' . $oTable->getPhpName();
        $sGeneratedCrudBaseNamespace = $sGeneratedCrudNamespace . '\\Base';

        $oNamespace = new PhpGenerator\PhpNamespace($sGeneratedCrudNamespace);
        $oClass = new PhpGenerator\ClassType($sClassName);
        $oClass->setExtends($sGeneratedCrudBaseNamespace . '\\' . $sClassName);
        $oClass->setFinal(true);
        $oClass->setComment("Skeleton subclass for representing a " . $oTable->getPhpName() . ".");
        $oClass->addComment(str_repeat(PHP_EOL, 6));
        $oClass->addComment("You should add additional methods to this class to meet the");
        $oClass->addComment("application requirements.  This class will only be generated as");
        $oClass->addComment("long as it does not already exist in the output directory.");
        $oNamespace->add($oClass);
        file_put_contents($sFilePath, '<?php ' . PHP_EOL . (string)$oNamespace);
    }

    private function output(string $sMessage)
    {
        $this->output->writeln($sMessage);
    }

    private function makeBaseManager(Table $oTable, Api $oApi = null)
    {
        $sClassName = 'Crud' . $oTable->getPhpName() . 'Manager';

        $sGeneratedCrudBaseNamespace = $oTable->getCrudNamespace() . '\\' . $oTable->getPhpName() . '\\Base';

        $sQueryClass = $oTable->getQueryClass();
        $sModelClass = $oTable->getModelClass();

        $oNamespace = new PhpGenerator\PhpNamespace($sGeneratedCrudBaseNamespace);
        $oNamespace->addUse(Utils::class);
        $oGeneratedManager = new PhpGenerator\ClassType($sClassName);
        $oGeneratedManager->setAbstract();
        $oGeneratedManager->addComment("This class is automatically generated, do not modify manually.");
        $oGeneratedManager->addComment("Modify $sModelClass instead if you need to override or add functionality.");

        $oGeneratedManager->setExtends(FormManager::class);

        if ((string)$oTable->getCrudNamespace() !== 'Crud') {
            $aTraits = [
                $oTable->getCrudNamespace() . '\\CrudTrait',
            ];
            if ($oApi instanceof Api) {
                $aTraits[] = $oTable->getCrudNamespace() . '\\CrudApiTrait';
            }
            $oGeneratedManager->setTraits($aTraits);
        }

        $aInterfaces = [IConfigurableCrud::class];

        if ($oTable->getApiExposed()) {
            $aInterfaces[] = IApiExposable::class;
        }
        $oGeneratedManager->setImplements($aInterfaces);

        // getQueryObject
        $oGetQueryObjectMethod = $oGeneratedManager->addMethod('getQueryObject');
        $oGetQueryObjectMethod->setVisibility('public');
        $oGetQueryObjectMethod->setBody('return ' . $oTable->getQueryClass() . '::create();');
        $oGetQueryObjectMethod->setReturnType(ModelCriteria::class);

        // getTableMap
        $mapClass = new PhpNamespace($oTable->getMapClass(true));
        $oNamespace->addUse((string)$mapClass);
        $oGetQueryObjectMethod = $oGeneratedManager->addMethod('getTableMap');
        $oGetQueryObjectMethod->setVisibility('public');
        $oGetQueryObjectMethod->setBody('return new ' . ($mapClass->getShortName()) . '();');
        $oGetQueryObjectMethod->setReturnType((string)$mapClass);

        // getShortDescription
        $oGetQueryObjectMethod = $oGeneratedManager->addMethod('getShortDescription');
        $oGetQueryObjectMethod->setVisibility('public');
        if ($oTable->getApiDescription() && str_replace(' ', '', $oTable->getApiDescription()) != '{{description}}') {
            $oGetQueryObjectMethod->setBody('return ' . '"' . addslashes($oTable->getApiDescription()) . '";');
        } else {
            $oGetQueryObjectMethod->setBody('return ' . '"' . addslashes($oTable->getShortDescription()) . '";');
        }
        $oGetQueryObjectMethod->setReturnType('string');

        // getEntityTitle
        $oGetEntityTitle = $oGeneratedManager->addMethod('getEntityTitle');
        $oGetEntityTitle->setVisibility('public');
        $oGetEntityTitle->setBody('return ' . '"' . addslashes($oTable->getPhpName()) . '";');
        $oGetEntityTitle->setReturnType('string');

        // getOverviewUrl
        $oGetOverviewUrl = $oGeneratedManager->addMethod('getOverviewUrl');
        $oGetOverviewUrl->setVisibility('public');
        if ($oTable->getModule() === null) {
            $oGetOverviewUrl->setBody('return "";');
        } else {
            if (strtolower($oTable->getCrudDir())) {
                $oGetOverviewUrl->setBody('return "/' . strtolower($oTable->getCrudDir()) . '/' . strtolower($oTable->getModule()->getName()) . '/' . strtolower($oTable->getName()) . '/overview";');
            } else {
                $oGetOverviewUrl->setBody('return "/' . strtolower($oTable->getModule()->getName()) . '/' . strtolower($oTable->getName()) . '/overview";');
            }
        }
        $oGetOverviewUrl->setReturnType('string');

        // getEditUrl
        $oGetEditUrl = $oGeneratedManager->addMethod('getEditUrl');
        $oGetEditUrl->setVisibility('public');
        if ($oTable->getModule() === null) {
            $oGetEditUrl->setBody('return "";');
        } else {
            if ($oTable->getCrudDir()) {
                $oGetEditUrl->setBody('return "/' . strtolower($oTable->getCrudDir()) . '/' . strtolower($oTable->getModule()->getName()) . '/' . strtolower($oTable->getName()) . '/edit";');
            } else {
                $oGetEditUrl->setBody('return "/' . strtolower($oTable->getModule()->getName()) . '/' . strtolower($oTable->getName()) . '/edit";');
            }
        }
        $oGetEditUrl->setReturnType('string');

        $oGetCreateNewUrl = $oGeneratedManager->addMethod('getCreateNewUrl');
        $oGetCreateNewUrl->setVisibility('public');
        $oGetCreateNewUrl->setBody('return $this->getEditUrl();');
        $oGetCreateNewUrl->setReturnType('string');

        // getNewFormTitle
        $oGetNewFormTitle = $oGeneratedManager->addMethod('getNewFormTitle');
        $oGetNewFormTitle->setVisibility('public');
        $oGetNewFormTitle->setBody('return ' . '"' . $oTable->getTitle() . ' toevoegen";');
        $oGetNewFormTitle->setReturnType('string');

        // getEditFormTitle
        $oGetEditFormTitle = $oGeneratedManager->addMethod('getEditFormTitle');
        $oGetEditFormTitle->setVisibility('public');
        $oGetEditFormTitle->setBody('return ' . '"' . $oTable->getTitle() . ' aanpassen";');
        $oGetEditFormTitle->setReturnType('string');

        // getDefaultOverviewFields
        $oGetEditFormTitle = $oGeneratedManager->addMethod('getDefaultOverviewFields');
        $oGetEditFormTitle->setVisibility('public');
        $oGetEditFormTitle->setBody('return ' . '"' . $oTable->getTitle() . ' aanpassen";');
        $oGetEditFormTitle->setReturnType('string');

        $aColumns = $oTable->getColumnArray([
            'Id',
            'id',
        ]);
        $aOverviewColumns = $aColumns;
        if ($oTable->getModule() instanceof Module) {
            $aOverviewColumns = array_merge($aColumns, [
                'Delete',
                'Edit',
            ]);
        }

        // getDefaultOverviewFields
        $oGetDefaultOverviewFields = $oGeneratedManager->addMethod('getDefaultOverviewFields');
        $oGetDefaultOverviewFields->setVisibility('public');
        $oGetDefaultOverviewAddNsParameter = $oGetDefaultOverviewFields->addParameter('bAddNs', new PhpGenerator\Literal('false'));
        $oGetDefaultOverviewAddNsParameter->setType('bool');
        $aBody = [
            "\$aOverviewColumns = ['" . join("', '", $aOverviewColumns) . "'];",
            "if(\$bAddNs){",
            "   array_walk(\$aOverviewColumns, function(&\$item) {",
            "      \$item = Utils::makeNamespace(\$this, \$item);",
            "   });",
            "}",
            "return \$aOverviewColumns;",
        ];

        $oGetDefaultOverviewFields->setBody(join(PHP_EOL, $aBody));
        $oGetDefaultOverviewFields->setReturnType('array');

        // getDefaultEditFields
        $oGetDefaultEditFields = $oGeneratedManager->addMethod('getDefaultEditFields');
        $oGetDefaultEditFields->setVisibility('public');
        $oGetDefaultEditFieldsAddNsParameter = $oGetDefaultEditFields->addParameter('bAddNs', new PhpGenerator\Literal('false'));
        $oGetDefaultEditFieldsAddNsParameter->setType('bool');
        $aBody = [
            "\$aOverviewColumns = ['" . join("', '", $aOverviewColumns) . "'];",
            "if(\$bAddNs){",
            "   array_walk(\$aOverviewColumns, function(&\$item) {",
            "       \$item = Utils::makeNamespace(\$this, \$item);",
            "   });",
            "}",
            "return \$aOverviewColumns;",
        ];
        $oGetDefaultEditFields->setBody(join(PHP_EOL, $aBody));
        $oGetDefaultEditFields->setReturnType('array');

        // getModel
        $oGetModel = $oGeneratedManager->addMethod('getModel');
        $oGetModel->setComment('Returns a model object of the type that this CrudManager represents.');
        $oAData = $oGetModel->addParameter('aData', null);
        $oAData->setTypeHint('array');
        $oGetModel->setVisibility('public');
        $oGetModel->addComment('@param array|null $aData');
        $oGetModel->addComment('@return ' . $sModelClass);
        $oGetModel->setBody($this->getModelBody($sModelClass, $sQueryClass, $oTable));

        $oGetModel->setReturnType($oTable->getModelClass(true));

        // getSave
        $oSave = $oGeneratedManager->addMethod('store');
        $oSave->addComment('This method is ment to be called by save so any pre and post events are triggered also.');
        $oSave->addComment('Store form data, please first perform validation by calling validate');
        $oSave->addComment('@param array $aData an array of fields that belong to this type of data');
        $oSave->addComment('@return ' . $sModelClass);
        $oSave->addComment('@throws \Propel\Runtime\Exception\PropelException');

        $oSaveAData = $oSave->addParameter('aData', null);
        $oSaveAData->setTypeHint('array');
        $oSave->setVisibility('public');
        $oSave->setBody($this->getSaveBody($sModelClass));
        $oSave->setReturnType($oTable->getModelClass(true));

        // fillVo
        $oFillVo = $oGeneratedManager->addMethod('fillVo');
        $oFillVo->addComment('Fills the model object with data coming from a client.');
        $oFillVo->addComment('@param array $aData');
        $oFillVo->addComment('@param ' . $sModelClass . ' $oModel');
        $oFillVo->addComment('@return ' . $sModelClass);

        $oFillVoAData = $oFillVo->addParameter('aData');
        $oFillVoAData->setTypeHint('array');

        $oFillVooModel = $oFillVo->addParameter("oModel");
        $oFillVooModel->setTypeHint($oTable->getModelClass(true));

        $oGetFieldIteratorMethod = $oGeneratedManager->addMethod('getFieldIterator');
        $oFilterParameter = $oGetFieldIteratorMethod->addParameter('filter', null);
        $oFilterParameter->setType(new PhpGenerator\Literal('callable'));
        $oIteratorName = CrudFieldIteratorGenerator::makeInterfaceName($oTable);
        $oNamespace->addUse((string)$oIteratorName);
        $oGetFieldIteratorMethod->setReturnType((string)$oIteratorName);
        $oGetFieldIteratorMethod->setBody(join(PHP_EOL, [
            '$aArray = $this->getAllFieldObjectsArray($filter);',
            'return new ' . $oIteratorName->getShortName() . '($aArray);',
        ]));
        $oGetFieldIteratorMethod->addComment(join(PHP_EOL, [
            '@param callable|null $filter',
            '@return ' . $oIteratorName->getShortName(),
        ]));

        $oFillVo->setVisibility('protected');
        $oFillVo->setBody($this->getFillVoBody($oTable, $oNamespace));
        $oFillVo->setReturnType($oTable->getModelClass(true));

        // addInterfacesAndModels
        foreach ($aInterfaces as $sInterfaceName) {
            $oNamespace->addUse($sInterfaceName);
        }

        $oNamespace->addUse(FormManager::class);
        $oNamespace->addUse($oTable->getModelClass(true));
        $oNamespace->addUse($oTable->getQueryClass(true));
        $oNamespace->addUse($oTable->getMapClass(true));

        $oNamespace->addUse(LogicException::class);
        $oNamespace->addUse(TableMap::class);
        $oNamespace->addUse($oTable->getCrudNamespace());
        $oNamespace->addUse(ModelCriteria::class);
        $oNamespace->add($oGeneratedManager);

        $sDirPath = CommandUtils::getRoot() . '/classes/Crud/' . $oTable->getCrudDir() . '/' . $oTable->getPhpName() . '/Base/' . $sClassName . '.php';

        file_put_contents($sDirPath, '<?php' . PHP_EOL . (string)$oNamespace);
    }

    private function getModelBody(string $sModelClass, string $sQueryClass, Table $oTable): string
    {
        $aOut = [];

        $aOut[] = "if (isset(\$aData['id']) && \$aData['id']) {";
        $aOut[] = "     \$o{$sQueryClass} = {$sQueryClass}::create();";
        $aOut[] = "     \$o{$sModelClass} = \$o{$sQueryClass}->findOneById(\$aData['id']);";
        $aOut[] = "     if (!\$o{$sModelClass} instanceof {$sModelClass}) {";
        $aOut[] = "         throw new LogicException(\"{$sModelClass} should be an instance of {$sModelClass} but got something else.\" . __METHOD__);";
        $aOut[] = "     }";
        $aOut[] = "     \$o{$sModelClass} = \$this->fillVo(\$aData, \$o{$sModelClass});";
        $aOut[] = "}";

        $aOut[] = "else {";
        $aOut[] = "     \$o{$sModelClass} = new {$sModelClass}();";
        $aOut[] = "     if (!empty(\$aData)) {";
        $aOut[] = "         \$o{$sModelClass} = \$this->fillVo(\$aData, \$o{$sModelClass});";
        $aOut[] = "     }";
        $aOut[] = "}";
        $aOut[] = "return \$o{$sModelClass};";

        return join(PHP_EOL, $aOut);
    }

    private function getSaveBody(string $sModelClass): string
    {
        $aOut = [];
        $aOut[] = "\$o{$sModelClass} = \$this->getModel(\$aData);";
        $aOut[] = PHP_EOL;
        $aOut[] = " if(!empty(\$o{$sModelClass}))";
        $aOut[] = " {";
        $aOut[] = "     \$o{$sModelClass} = \$this->fillVo(\$aData, \$o{$sModelClass});";
        $aOut[] = "     \$o{$sModelClass}->save();";
        $aOut[] = " }";
        $aOut[] = "return \$o{$sModelClass};";

        return join(PHP_EOL, $aOut);
    }

    private function getFillVoBody(Table $oTable, PhpGenerator\PhpNamespace $oManagerNamespace)
    {
        $aOut = [];

        foreach ($oTable->getColumns() as $oColumn) {
            if ($oColumn->getName() == 'id') {
                continue;
            }
            if ($oColumn->getPhpName()) {
                $sPhpName = $oColumn->getPhpName();
            } else {
                $sPhpName = Utils::camelCase($oColumn->getName());
            }

            $sFieldNamespace = Utils::makeNamespace((string)$oTable->getCrudNamespace(), (string)$oTable->getPhpName(), 'Field', $sPhpName);
            $oFieldNamespace = new PhpNamespace($sFieldNamespace);
            $oManagerNamespace->addUse($sFieldNamespace);

            $aOut[] = "if(isset(\$aData['{$oColumn->getName()}'])) {";
            $aOut[] = "     \$oField = new {$oFieldNamespace->getShortName()}();";
            $aOut[] = "     \$mValue = \$oField->sanitize(\$aData['{$oColumn->getName()}']);";
            $aOut[] = "     \$oModel->set{$sPhpName}(\$mValue);";
            $aOut[] = "}";
        }

        $aOut[] = "return \$oModel;";
        return join(PHP_EOL, $aOut);
    }

    private function makeCrudTrait(Table $oTable)
    {
        $sNamespace = $oTable->getCrudNamespace();
        $oNamespace = new PhpGenerator\PhpNamespace($sNamespace);
        $oGeneratedTrait = new PhpGenerator\ClassType('CrudTrait');
        $oGeneratedTrait->setType(PhpGenerator\ClassType::TYPE_TRAIT);

        $method = $oGeneratedTrait->addMethod('getTags');
        $aParts = explode('\\', $sNamespace);
        unset($aParts[0], $aParts[1]);
        $method->setBody('return ["' . join('", "', $aParts) . '"];');

        $oNamespace->add($oGeneratedTrait);

        $sFilePath = CommandUtils::getRoot() . '/classes/Crud/' . $oTable->getCrudDir() . '/CrudTrait.php';

        if (!file_exists($sFilePath)) {
            $this->output("Creating crud trait " . $sFilePath);

            file_put_contents($sFilePath, '<?php' . PHP_EOL . (string)$oNamespace);
        } else {
            $this->output("Skipping crud trait, already existed " . $sFilePath);
        }
    }

    private function makeCrudApiTrait(Table $oTable, Api $oApi = null)
    {
        $sNamespace = $oTable->getCrudNamespace();
        $oNamespace = new PhpGenerator\PhpNamespace($sNamespace);
        $oGeneratedTrait = new PhpGenerator\ClassType('CrudApiTrait');

        $oGeneratedTrait->addComment("This trait is automatically generated, do not modify manually.");
        $oGeneratedTrait->addComment("Add custom code to the model class or add extra traits if you need to override or add functionality.");
        $oGeneratedTrait->setType(PhpGenerator\ClassType::TYPE_TRAIT);

        $oGetDocumentationUrl = $oGeneratedTrait->addMethod('getDocumentationUrl');
        $oGetDocumentationUrl->setReturnType('string');
        $oGetDocumentationUrl->setBody('return "' . $oApi->getDocumentation_url() . '";');

        $oGetDocumentationUrl = $oGeneratedTrait->addMethod('getApiVersion');
        $oGetDocumentationUrl->setReturnType('string');
        $oGetDocumentationUrl->setBody('return "' . $oApi->getApiVersion() . '";');

        $oGetApiUrl = $oGeneratedTrait->addMethod('getApiUrl');
        $oGetApiUrl->setReturnType('string');
        $oGetApiUrl->setBody('return "' . $oApi->getEndpoint_url() . '";');

        $oGetApiUrl = $oGeneratedTrait->addMethod('getApiNamespace');
        $oGetApiUrl->setReturnType('string');
        $oGetApiUrl->setBody('return "' . $oApi->getApiNamespace() . '";');

        $oNamespace->add($oGeneratedTrait);

        $sFilePath = CommandUtils::getRoot() . '/classes/Crud/' . $oTable->getCrudDir() . '/CrudApiTrait.php';

        $this->output("Creating crud api trait <info>$sFilePath</info>");

        file_put_contents($sFilePath, '<?php' . PHP_EOL . (string)$oNamespace);
    }
}
