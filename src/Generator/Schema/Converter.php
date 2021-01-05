<?php
namespace Generator\Schema;

use Generator\Schema\ConverterAdapter\Custom;
use Generator\Schema\ConverterAdapter\Json;
use Generator\Schema\ConverterAdapter\Propel;
use Generator\Schema\ConverterAdapter\IConverterAdapter;
use InvalidArgumentException;
use Model\System\DataModel\Model\DataModelQuery;
use Model\Module\ModuleQuery;

class Converter
{
    private ModuleQuery $oModuleQuery;
    private DataModelQuery $oDataModelQuery;
    private string $sCustom;
    private ?string $sXsdLocation = null;

    function __construct(ModuleQuery $oModuleQuery, DataModelQuery $oDataModelQuery, string $sCustom) {
        $this->oModuleQuery = $oModuleQuery;
        $this->oDataModelQuery = $oDataModelQuery;
        $this->sCustom = $sCustom;
    }

    function changeXsd(string $sLocation) {
        $this->sXsdLocation = $sLocation;
    }
    /**
     * @return string
     */
    function asXml():string {
        $oAdapter = self::getAdapter('custom');
        return $oAdapter->convert($this->oModuleQuery, $this->oDataModelQuery, $this->sCustom, $this->sXsdLocation);
    }
    /**
     * @return string
     */
    function asPropelXml():string {
        $oAdapter = self::getAdapter('propel');
        return $oAdapter->convert($this->oModuleQuery, $this->oDataModelQuery, $this->sCustom, $this->sXsdLocation);
    }
    /**
     * @return string
     */
    function asJson():string {
        $oAdapter = self::getAdapter('json');
        return $oAdapter->convert($this->oModuleQuery, $this->oDataModelQuery, $this->sCustom);
    }

    private static function getAdapter(string $sAdapterReference):IConverterAdapter {
        $aAdapters = [
            'propel' => Propel::class,
            'custom' => Custom::class,
            'json' => Json::class,
        ];
        if(!isset($aAdapters[$sAdapterReference]))
        {
            throw new InvalidArgumentException("Unsupported schema converer adapter $sAdapterReference.");
        }
        return new $aAdapters[$sAdapterReference];
    }
}
