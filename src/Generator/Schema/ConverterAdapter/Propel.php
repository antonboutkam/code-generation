<?php

namespace Generator\Schema\ConverterAdapter;

use Exception\LogicException;
use Model\Module\Module;
use Model\Module\ModuleQuery;
use Model\System\DataModel\Field\DataField;
use Model\System\DataModel\Model\DataModel;
use Model\System\DataModel\Model\DataModelQuery;

class Propel extends AbstractAdapter implements IConverterAdapter {

    const defaultXsdLocation = 'https://novumgit.gitlab.io/innovation-app-schema-xsd/v1/schema.xsd';

    function getContainer(ModuleQuery $oModuleQuery, DataModelQuery $oDataModelQuery, string $sCustom, string $sXsdLocation = null): string {
        if(!$sXsdLocation)
        {
            $sXsdLocation = self::defaultXsdLocation;
        }
        $aAttributes = $this->getContainerAttributes($sCustom, $sXsdLocation);
        $aContent = [];
        $aContent[] = $this->getTables($oDataModelQuery, $sCustom);
        return $this->element('database', $aAttributes, join(PHP_EOL, $aContent));
    }

    function getContainerAttributes(string $sCustom, string $sXsdLocation): array {
        return [
            'name'                          => 'hurah',
            'defaultIdMethod'               => 'native',
            'xmlns:xsi'                     => 'http://www.w3.org/2001/XMLSchema-instance',
            'xsi:noNamespaceSchemaLocation' => $sXsdLocation,
        ];
    }


    /**
     * A little helper method to find unresolvable dependency crap. Has no purpose besides debugging.
     * @param string[] $aDataModels
     * @return array
     */
    function findUnmetDependency(array $aDataModels, DataModel $oUnmetModel, string $sAppend = '--'):void
    {
        if(strlen($sAppend) > 20)
        {
            exit('to much recursion');
        }
        $sTableAvailable = in_array($oUnmetModel->getName(), $aDataModels) ? 'yes' : 'no';
        $aSubDependencies = $oUnmetModel->getDependencies();
        $sHasOwnDependencies = count($aSubDependencies) ? 'yes' : 'no';

        echo $sAppend . " Following: " . $oUnmetModel->getName() . ", table available: " .  $sTableAvailable . ", has own dependencies " . $sHasOwnDependencies . " dependecy". PHP_EOL;

        foreach($aSubDependencies as $oDependecy)
        {
            $sAppend = $sAppend . '--';

            if(!in_array($oDependecy->getName(), $aDataModels))
            {
                // echo $sAppend . " FOUND! Missing {$oDependecy->getName()}" . PHP_EOL;
                throw new \LogicException("Missing dependency {$oDependecy->getName()}");
            }

            foreach($oDependecy->getDependencies() as $oDependency)
            {
                echo $sAppend . " depends on " . $oDependency->getName() . PHP_EOL;
            }
            foreach($oDependecy->getDependencies() as $oDependency)
            {
                $this->findUnmetDependency($aDataModels, $oDependency, $sAppend);
            }

        }
    }


    /**
     * @param array $aDataModels
     * @return array
     */
    private function sortDependencies(array $aDataModels) {
        $res = array();
        $doneList = array();

        // while not all items are resolved:
        while(count($aDataModels) > count($res)) {
            $doneSomething = false;

            foreach($aDataModels as $itemIndex => $oDataModel) {
                if(isset($doneList[$oDataModel->getName()])) {
                    // item already in resultset
                    continue;
                }
                $resolved = true;
                $aDependencies = $oDataModel->getDependencies();
                if(isset($aDependencies)) {
                    // echo "Dependencies for {$oDataModel->getName()} " . PHP_EOL;

                    foreach($aDependencies as $oDataModelDependency)
                    {
                        if($oDataModel->getName() === $oDataModelDependency->getName())
                        {
                            $doneList[$oDataModelDependency->getName()] = $oDataModelDependency->getName();
                        }

                        if(!isset($doneList[$oDataModelDependency->getName()])) {
                            // echo "Unmet dependency {$oDataModelDependency->getName()} " . count($doneList) . ' / ' . count($aDataModels). PHP_EOL;
                            $aNames = [];
                            foreach($aDataModels as $tmpDataModel)
                            {
                                $aNames[] = $tmpDataModel->getName();
                            }
                            $this->findUnmetDependency($aNames, $oDataModelDependency);
                            // there is a dependency that is not met:
                            $resolved = false;
                            break;
                        }
                    }
                }
                if($resolved) {
                    //all dependencies are met:
                    $doneList[$oDataModel->getName()] = $oDataModel->getName();
                    $res[] = $oDataModel;
                    $doneSomething = true;
                }
            }
            if(!$doneSomething) {

                throw new LogicException('Unresolvable foreign key dependency in: ' . ($oDataModel ? $oDataModel->getName() : null));

            }
        }
        return $res;
    }

    public function getTables(DataModelQuery $oDataModelQuery, string $sCustom): string
    {
        foreach ($oDataModelQuery->find() as $oDataModel) {
            $aAllModelsUnsorted[] = $oDataModel;
        }

        $aResolvedDependencies = $this->sortDependencies($aAllModelsUnsorted);

        $aTables = [];
        foreach ($aResolvedDependencies as $oDataModel)
        {
            $aTables[] = $this->getTable($oDataModel, $sCustom);
        }
        return join(PHP_EOL, $aTables);
    }

    function getModuleAttributes(Module $oModule): array {
        return [];
    }


    function getColumnAttributes(DataField $oDataField): array {
        $aColumnAttributes = [
            'name'    => $oDataField->getName()
        ];

        if($oDataField->getPhpName())
        {
            $aColumnAttributes['phpName'] = $oDataField->getPhpName();
        }

        if($oDataField->getLabel())
        {
            $aColumnAttributes['title'] = $oDataField->getLabel();
        }


        $sType = $oDataField->getDataType()->getName();
        if ($sType !== 'VARCHAR') {
            $aColumnAttributes['type'] = $sType;
        }

        if ($oDataField->getRequired()) {
            $aColumnAttributes['required'] = 'true';
        }
        if ($oDataField->getDefaultValue()) {
            $aColumnAttributes['default'] = $oDataField->getDefaultValue();
        }



        if($oDataField->getSize())
        {
            $aColumnAttributes['size'] = $oDataField->getSize();
        }

        if($oDataField->getIsPrimaryKey())
        {
            $aColumnAttributes['primaryKey'] = 'true';
        }

        if ($oDataField->getAutoIncrement())
        {
            $aColumnAttributes['autoIncrement'] = 'true';
        }

        return $aColumnAttributes;
    }
    function getTableAttributes(DataModel $oDataModel, string $sCustom = null): array {

        $aTableAttributes = [
            'name'      => $oDataModel->getName(),
            'namespace' => $oDataModel->getNamespaceName(),
        ];

        if($oDataModel->getPhpName())
        {
            $aTableAttributes['phpName'] = $oDataModel->getPhpName();
        }
        if($oDataModel->getTitle())
        {
            $aTableAttributes['title'] = $oDataModel->getTitle();
        }
        return $aTableAttributes;
    }
}
