<?php

namespace Generator\Schema\ConverterAdapter;

use Generator\Composer\Utils;
use Model\Logging\Except_log;
use Model\Module\Module;
use Model\Module\ModuleQuery;
use Model\System\DataModel\Field\DataField;
use Model\System\DataModel\Model\DataModel;
use Model\System\DataModel\Model\DataModelQuery;
use Model\System\DataModel\Model\UniqueFieldGroup;
use Model\System\DataModel\Model\UniqueFieldGroupFieldQuery;
use Model\System\DataModel\Model\UniqueFieldGroupQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Exception\PropelException;
use SimpleXMLElement;

abstract class AbstractAdapter implements IConverterAdapter
{
    // ng
    public function convert(ModuleQuery $oModuleQuery, DataModelQuery $oDataModelQuery, string $sCustom, string $sXsdLocation = null): string
    {
        $aOut = ['<?xml version="1.0" encoding="UTF-8"?>'];
        $aOut[] = $this->getContainer($oModuleQuery, $oDataModelQuery, $sCustom, $sXsdLocation);
        return join(PHP_EOL, $aOut);
    }

    abstract function getContainerAttributes(string $sCustom, string $sXsdLocation): array;

    // ng
    public function getContainer(ModuleQuery $oModuleQuery, DataModelQuery $oDataModelQuery, string $sCustom, string $sXsdLocation = null): string
    {
        $aAttributes = $this->getContainerAttributes($sCustom, $sXsdLocation);
        $aContent = [];
        $aContent[] = $this->getModules($oModuleQuery);
        $aContent[] = $this->getTables($oDataModelQuery, $sCustom);
        return $this->element('database', $aAttributes, join(PHP_EOL, $aContent));
    }

    /**
     * @param DataModel $oDataModel
     * @return SimpleXMLElement[]
     */
    // ng
    public function getColumns(DataModel $oDataModel): string {
        $aOut = [];
        try
        {
            if (!$oDataModel->hasIdField())
            {
                // Create an id field if none is available, the system needs it.
                // $oDataField = $oDataModel->makeDefaultPrimaryKeyColumn();
                // $oDataField->save();
            }
            $aFieldsSorted = [];
            foreach ($oDataModel->getDataFields() as $oDataField)
            {
                if ($oDataField->getName() == 'id')
                {
                    array_unshift($aFieldsSorted, $oDataField);
                } else
                {
                    $aFieldsSorted[] = $oDataField;
                }
            }
            if ($aFieldsSorted)
            {
                foreach ($aFieldsSorted as $oDataField)
                {
                    $aOut[] = "\t" . $this->getColumn($oDataField);
                }
            }
        } catch (PropelException $e)
        {
            Except_log::register($e, true);
        }
        return join(PHP_EOL, $aOut);
    }

    // ng
    abstract function getColumnAttributes(DataField $oDataField): array;

    // ng
    public function getColumn(DataField $oDataField): string
    {
        $aAttributes = $this->getColumnAttributes($oDataField);
        return $this->element('column', $aAttributes);
    }

    // ng
    public function getModules(ModuleQuery $oModuleQuery): string
    {
        $aOut = [];
        foreach ($oModuleQuery->find() as $oModule)
        {
            $aOut[] = "\t" . $this->getModule($oModule);
        }
        return $this->element('modules', null, join(PHP_EOL, $aOut));
    }

    abstract function getModuleAttributes(Module $oModule): array;

    // ng
    private function attributes(array $aAttributes = null): string
    {
        $aAttributesSet = [];
        if ($aAttributes)
        {
            foreach ($aAttributes as $sAttrKey => $sAttrValue)
            {
                $aAttributesSet[] = $sAttrKey . '="' . $sAttrValue . '"';
            }
        }
        $sAttributes = join(' ', $aAttributesSet);
        return $sAttributes;
    }

    // ng
    protected function element(string $sKey, array $aAttributes = null, string $sValue = null): string
    {
        $sAttributes = $this->attributes($aAttributes);

        $aOut = [];
        if ($sValue)
        {
            $aOut[] = "<" . $sKey . " $sAttributes>" . PHP_EOL;
            $aOut[] = $sValue . PHP_EOL;
            $aOut[] = "</" . $sKey . ">" . PHP_EOL;
            return join('', $aOut);
        }
        return "<" . $sKey . " $sAttributes />";
    }

    // ng
    public function getModule(Module $oModule): string
    {
        $aAttributes = $this->getModuleAttributes($oModule);
        return $this->element('module', $aAttributes);
    }
    public function getTimeStampable(): string
    {
        return $this->element('behavior', ['name' => 'timestampable']);
    }

    abstract function getTableAttributes(DataModel $oDataModel, string $sCustom = null): array;

    public function getTable(DataModel $oDataModel, string $sCustom = null): string
    {
        $aAttributes = $this->getTableAttributes($oDataModel, $sCustom);
        $sColumns = $this->getColumns($oDataModel);
        $sForeignKeys = $this->getForeignKeys($oDataModel);
        $sUniqueKeys = $this->getUniqueColumns($oDataModel);

        return $this->element('table', $aAttributes, $sColumns .  PHP_EOL . $sUniqueKeys . PHP_EOL . $sForeignKeys . PHP_EOL . "\t\t" . $this->getTimeStampable());
    }

    function getUniqueColumns(DataModel $oDataModel): string
    {
        try
        {
            $aOut = [];
            $aUniqueGroups = $oDataModel->getUniqueFieldGroups();

            foreach ($aUniqueGroups as $oUniqueGroup)
            {
                if($oUniqueGroup instanceof UniqueFieldGroup)
                {
                    $aOut[] = '<unique name="' . $oUniqueGroup->getName() . '">';
                    $aCols= [];
                    $oUniqueFieldGroupQuery = UniqueFieldGroupFieldQuery::create();
                    $oUniqueFieldGroupQuery->filterByUniqueFieldGroupId($oUniqueGroup->getId());
                    $oUniqueFieldGroupQuery->orderBySorting(Criteria::DESC);
                    $aFields = $oUniqueFieldGroupQuery->find();
                    foreach($aFields as $oField)
                    {
                        $aCols[] = '    <unique-column name="' . $oField->getDataField()->getName() . '" sort="' . $oField->getSorting() . '"  />';
                    }
                    $aOut[] = join(PHP_EOL, array_reverse($aCols));
                    $aOut[] = '</unique>';
                }
            }
        } catch (\Exception $e)
        {
            echo 'ERROR' . $e->getMessage() . PHP_EOL;
        }
        return join(PHP_EOL, $aOut);
    }

    function getForeignKeys(DataModel $oDataModel): string
    {
        try
        {
            $aOut = [];

            $aFields = $oDataModel->getDataFields();
            foreach ($aFields as $oField)
            {
                $aConstraints = $oField->getModelConstraintsRelatedByLocalFieldId();

                if (!$aConstraints->isEmpty())
                {
                    foreach ($aConstraints as $oConstraint)
                    {

                        $oForeignField = $oConstraint->getFkForeignField();
                        $oForeignTable = $oForeignField->getFkDataModel();





                        $aAttributes = [
                            'foreignTable' => $oForeignTable->getName()
                        ];

                        if($oConstraint->getPhpName())
                        {
                            $aAttributes['phpName'] = $oConstraint->getPhpName();
                        }
                        if($oConstraint->getRefPhpName())
                        {
                            $aAttributes['phpName'] = $oConstraint->getPhpName();
                        }


                        if($oConstraint->getOnUpdate())
                        {
                            $aAttributes['onUpdate'] = strtoupper($oConstraint->getOnUpdate());
                        }

                        if($oConstraint->getOnDelete())
                        {
                            $aAttributes['onDelete'] = strtoupper($oConstraint->getOnDelete());
                        }

                        $sAttributes = $this->attributes($aAttributes);
                        $aOut[] = '     <foreign-key ' . $sAttributes . '>';
                        $aOut[] = '         <reference local="' . $oField->getName() . '" foreign="id"/>';
                        $aOut[] = '     </foreign-key>';
                    }
                }
            }
            /*
            if($oDataModel->isUiComponent())
            {
                $aOut[] = '<foreign-key foreignTable="ui_component" phpName="FkUiComponent" onDelete="restrict">';
                $aOut[] = '    <reference local="ui_component_id" foreign="id"/>';
                $aOut[] = '</foreign-key>';
            }
            */
        } catch (\Exception $e)
        {
            echo 'ERROR' . $e->getMessage() . PHP_EOL;
        }
        return join(PHP_EOL, $aOut);
    }

    public function getTables(DataModelQuery $oDataModelQuery, string $sCustom): string
    {
        $aTables = [];
        foreach ($oDataModelQuery->find() as $oDataModel)
        {
            $aTables[] = $this->getTable($oDataModel, $sCustom);
        }
        return join(PHP_EOL, $aTables);
    }

}


