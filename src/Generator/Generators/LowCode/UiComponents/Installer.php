<?php

namespace Generator\Generators\LowCode\UiComponents;

use Cli\Composer\Database\Helper\Propel;
use Core\Cfg;
use Core\DataType\Path;
use Core\Utils;
use Exception\FileNotFoundException;
use Hi\Helpers\DirectoryStructure;
use LowCode\ComponentFactory;
use LowCode\Map\Property;
use Model\Module\ModuleQuery;
use Model\System\DataModel\Field\DataField;
use Model\System\DataModel\Field\DataFieldQuery;
use Model\System\DataModel\Model\DataModel;
use Model\System\DataModel\Model\DataModelQuery;
use Model\System\DataModel\Model\ModelConstraintQuery;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\Console\Output\OutputInterface;

class Installer
{

    /**
     * @param $aAnswers
     * @param OutputInterface $output
     * @throws PropelException
     * @throws FileNotFoundException;
     */
    public static function create($aAnswers, OutputInterface $output)
    {
        $oDirectoryStructure = new DirectoryStructure();

        Propel::includeConfigs($aAnswers['config_dir'], $output);
        require_once Utils::makePath($oDirectoryStructure->getVendorDir(), 'autoload.php');
        $oPropelConfigPath = new Path(Utils::makePath($oDirectoryStructure->getConfigRoot(), $aAnswers['config_dir'], 'propel', 'config.php'));

        if (!$oPropelConfigPath->exists()) {
            throw new FileNotFoundException("Propel config missing");
        }

        $aConfig = require Utils::makePath($oDirectoryStructure->getConfigRoot(), $aAnswers['config_dir'], 'config.php');
        Cfg::set($aConfig);

        $output->writeln("Creating table/model <info>ui_component_type</info>");
        ModuleQuery::getUiComponentModule();

        $aComponents = ComponentFactory::getAll();
        foreach ($aComponents as $oComponent) {
            $output->writeln("Registering <info>{$oComponent->getName()}</info> as a component type by inserting a row in <comment>ui_component_type</comment>.");
            $oComponent->addUiComponentType(true);

            // Add the component to the data_model table so we can generate schemas
            $output->writeln("Creating a model to store <comment>properties</comment> of each instance of <info>{$oComponent->getName()}</info>");
            $oDataModel = DataModel::fromUiComponent($oComponent);

            foreach ($oComponent->getComponentXml()->getProperties() as $oProperty) {
                if ($oProperty->getType() === 'enumeration') {
                    $output->writeln("<comment>property</comment> <info>{$oProperty->getKey()}</info> of component <info>{$oComponent->getName()}</info> contains enumerable values.");

                    $sLookupTableName = $oDataModel->getName() . '_' . $oProperty->getKey();
                    $output->writeln("Creating <info>$sLookupTableName</info> model to store the options for <comment>{$oProperty->getKey()}</comment>.");
                    $oLookupDataModel = DataModel::makeLookupTable($sLookupTableName, $oDataModel->getNamespaceName(), true);

                    $oLookupDataModel->save();
                    $oLookupDataModel->makeDefaultPrimaryKeyColumn();

                    $oLookupDataModelIdcolumn = $oLookupDataModel->makeDefaultPrimaryKeyColumn();
                    $oLookupDataModelIdcolumn->save();
                    $oLookupDataModelField = Property::toDataField($oLookupDataModel, $oProperty, [
                        'field_name' => 'item_key',
                        'field_type' => 'string',
                    ]);
                    $oLookupDataModelField->save();
                    $oLookupDataModelField = Property::toDataField($oLookupDataModel, $oProperty, [
                        'field_name' => 'item_label',
                        'field_type' => 'string',
                    ]);
                    $oLookupDataModelField->save();
                }
            }

            $oDataModel->save();

            $columnColor = function (DataField $oDataField): string {
                return "<comment>{$oDataField->getFkDataModel()->getName()}</comment>.<info>{$oDataField->getName()}</info>";
            };

            foreach ($oComponent->getComponentXml()->getProperties() as $oProperty) {
                $output->writeln("Adding field <info>{$oProperty->getKey()}</info> of type <info>{$oProperty->getType()}</info>");

                $sColumnName = $oProperty->getKey();

                if ($oProperty->getType() === 'enumeration') {
                    $sColumnName = 'fk_' . $oDataModel->getName() . '_' . $oProperty->getKey();
                }

                $oDataField = Property::toDataField($oDataModel, $oProperty, ['field_name' => $sColumnName]);
                $oDataField->save();

                if ($oProperty->getType() === 'enumeration') {
                    $sLookupTableName = $oDataModel->getName() . '_' . $oProperty->getKey();
                    echo "Lookup table Name $sLookupTableName" . PHP_EOL;
                    $oLookupTable = DataModelQuery::create()->findOneByName($sLookupTableName);
                    $oForeignDataField = DataFieldQuery::create()->filterByDataModelId($oLookupTable->getId())->filterByName('id')->findOne();

                    $sLookupClass = $oLookupTable->getNamespaceName() . '\\' . $oLookupTable->getPhpName();
                    $oDataField->setFormFieldLookups($sLookupClass . '.ItemLabel');
                    $oDataField->save();

                    $sFrom = $columnColor($oDataField);
                    $sTo = $columnColor($oForeignDataField);

                    $output->writeln("Create foreign key: {$sFrom} -> {$sTo}");

                    $oModelConstraintQuery = ModelConstraintQuery::create();
                    $oModelConstraintQuery->filterByLocalFieldId($oDataField->getId());
                    $oModelConstraintQuery->filterByForeignFieldId($oForeignDataField->getId());
                    $oModelConstraint = $oModelConstraintQuery->findOneOrCreate();
                    $oModelConstraint->setPhpName(Utils::camelCase($oLookupTable->getName()));
                    $oModelConstraint->setRefPhpName(Utils::camelCase($oDataField->getFkDataModel()->getName()));
                    $oModelConstraint->setOnDelete('restrict');
                    $oModelConstraint->setOnUpdate('restrict');
                    print_r($oModelConstraint->toArray());
                    $oModelConstraint->save();
                }
            }

            $oDataModel->save();
            $output->writeln("Stored data model <info>{$oDataModel->getName()}</info> with id <comment>{$oDataModel->getId()}</comment>");

            $output->writeln("Adding <comment>id</comment> column to <info>{$oDataModel->getName()}</info>");
            $oIdColumn = $oDataModel->makeDefaultPrimaryKeyColumn();
            $oIdColumn->save();

            $output->writeln("Adding <comment>ui_component_id</comment> column to <info>{$oDataModel->getName()}</info>");
            $oComponentIdColumn = $oDataModel->makeUiComponentColumn($output);

            $output->writeln("<info>{$oDataModel->getName()} component created</info>");
            $output->writeln('-');
            $oComponentIdColumn->save();
        }
    }
}
