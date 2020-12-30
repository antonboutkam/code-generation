<?php

namespace Generator\Helper\Code;

use Nette\PhpGenerator\ClassType;

class EmptyClass
{
    public static function create(
        string $sClassName,
        string $sExtends = null,
        array $aImplements = null,
        bool $bIsFinal = true):ClassType
    {
        $oClass = new ClassType($sClassName);

        if(is_iterable($aImplements))
        {
            foreach ($aImplements as $sImplements)
            {
                $oClass->addImplement($sImplements);
            }
        }

        if($sExtends)
        {
            $oClass->addExtend($sExtends);
        }
        $oClass->setFinal($bIsFinal);
        return $oClass;
    }

}
