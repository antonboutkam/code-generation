<?php
namespace Generator\Schema\ConverterAdapter;

use Model\System\DataModel\Field\DataField;
use SimpleXMLElement;
use Model\Module\ModuleQuery;
use Model\System\DataModel\Model\DataModel;
use Model\System\DataModel\Model\DataModelQuery;

interface IConverterAdapter
{
    public function convert(ModuleQuery $oModuleQuery, DataModelQuery $oDataModelQuery, string $sCustom, string $sXsdLocation = null):string;
}
