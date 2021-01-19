<?php

namespace Generator\Schema\ConverterAdapter;

use Core\Json\JsonUtils;
use Core\Utils;
use Model\Logging\Except_log;
use Model\Module\ModuleQuery;
use Model\System\DataModel\Model\DataModel;
use Model\System\DataModel\Model\DataModelQuery;
use Model\System\DataModel\Model\Map\DataModelTableMap;
use Model\System\DataModel\Model\ModelConstraintQuery;
use Propel\Runtime\Map\TableMap;

class Json implements IConverterAdapter
{
    function convert(ModuleQuery $oModuleQuery, DataModelQuery $oDataModelQuery, string $sCustom, string $sXsdLocation = null): string
    {
        // 1. Lijst met tabellen.
        // 2. Lijst met foreign keys tussen tabellen.

        $aOut = [
            'tables' => $this->getTables($oDataModelQuery),
            'relations' => $this->getRelations($oDataModelQuery)
        ];


        return JsonUtils::encode($aOut);
    }
    private function getTables(DataModelQuery $oDataModelQuery):array
    {
        $aTables = [];
        foreach ($oDataModelQuery->find() as $oDataModel)
        {
            $aTables[] = $this->getTable($oDataModel);
        }
        return $aTables;
    }
    public function getTable(DataModel $oDataModel): array  {

        $aModels = DataModelQuery::create()->find();
        $aOptions = Utils::makeSelectOptions($aModels, 'getName', $oDataModel->getId());

        $iModuleId = null;
        if($oDataModel->getModule())
        {
            $iModuleId = $oDataModel->getModuleId();
        }
        return [
            'name' => $oDataModel->getName(),
            'title' => $oDataModel->getTitle(),
            'fields' => $this->getFields($oDataModel),
            'id' => $oDataModel->getId(),
            'module_id' => $oDataModel->getModule()->getId(),
            'api_exposed' => $oDataModel->getApiExposed(),
            'api_description' => $oDataModel->getApiDescription(),
        ];
    }
    private function getFields(DataModel $oDataModel) {
        $aFields = [];
        try
        {
            foreach ($oDataModel->getDataFields() as $oField)
            {
                $oModelConstraintQuery = ModelConstraintQuery::create();
                $aRelations = $oModelConstraintQuery->findByLocalFieldId($oField->getId());

                $aFields[] = [
                    'id' => $oField->getId(),
                    'name' => $oField->getName(),
                    'label' => $oField->getLabel(),
                    'icon' => $oField->getIcon()->getId(),
                    'icon_name' => $oField->getIcon()->getName(),
                    'form_type' => $oField->getFormFieldType()->getName(),
                    'form_field_type_id' =>  $oField->getFormFieldType()->getId(),
                    'data_type_id' => $oField->getDataType()->getId(),
                    'type' => $oField->getDataType()->getName(),
                    'required' => $oField->getRequired(),
                    'primary' => $oField->getPrimaryKey(),
                    'is_foreign_key' => !$aRelations->isEmpty(),
                    'auto_increment' => $oField->getAutoIncrement(),

                ];
            }
        } catch (\Exception $e)
        {
            Except_log::register($e, false);
            return [];
        }
        return $aFields;
    }
    private function getRelations(DataModelQuery $oDataModelQuery): array
    {
        $aRelations = [];

        try
        {
            foreach ($oDataModelQuery->find() as $oDataModel)
            {
                foreach ($oDataModel->getDataFields() as $oField)
                {

                    $oModelConstraintQuery = ModelConstraintQuery::create();
                    $aConstaints = $oModelConstraintQuery->findByLocalFieldId($oField->getId());

                    if (!$aConstaints->isEmpty())
                    {
                        foreach ($aConstaints as $oConstraint)
                        {
                            $oFromField = $oConstraint->getFkLocalField();
                            $oToField = $oConstraint->getFkForeignField();
                            $oToModel = $oToField->getFkDataModel();

                            $aRelations[] = [
                                'from_model' => $oDataModel->getName(), 'from_field' => $oFromField->getName(),

                                'to_model' => $oToModel->getName(), 'to_field' => $oToField->getName(),

                                'on_delete' => $oConstraint->getOnDelete(), 'on_update' => $oConstraint->getOnUpdate(),];
                        }
                    }
                }
            }
        } catch (\Exception $e)
        {
            Except_log::register($e, false);
            return [];
        }
        return $aRelations;
    }

}
