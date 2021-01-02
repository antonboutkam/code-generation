<?php


namespace Generator\Generators\Generator\Components;


use Hurah\Types\Type\Php\IsVoid;
use Hurah\Types\Type\Php\Property;

abstract class AbstractConfigGenerator
{

    /**
     * @param Property $oConfigProperty
     * @return bool|IsVoid
     */
    protected function formatDefaultValue(Property $oConfigProperty)
    {
        $sType = "{$oConfigProperty->getType()}";
        $bIsBool = $sType === 'bool';
        $mDefaultValue = "{$oConfigProperty->getDefault()}";
        if ($bIsBool) {
            $mDefaultValue = !empty($oConfigProperty->getDefault());
        }
        return $mDefaultValue;
    }
}