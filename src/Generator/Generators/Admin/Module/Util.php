<?php

namespace Generator\Admin\Module;

use Hurah\Types\Type\Url;
use Hurah\Types\Type\Path;
use Hurah\Types\Type\PhpNamespace;

class Util {

    public static function getNamespaceName(string $sCustom, string $sModule):PhpNamespace {
        $aNamespace = ['AdminModules'];
        if(!empty($sCustom))
        {
            $aNamespace[] = 'Custom';
            $aNamespace[] = $sCustom;
        }
        $aNamespace[] = $sModule;
        return PhpNamespace::make($aNamespace);
    }

    public static function url(string $sCustom, string $sModuleDir, string $sModelName):Url {
        $oUrl = new Url();

        if ($sCustom) {
            $oUrl->addPath('custom', $sCustom);
        }
        $oUrl->addPath($sModuleDir, $sModelName);
        return $oUrl;
    }

    public static function location(string $sCustom, string $sModule):Path {

        $aPath = ['admin_modules'];

        if(!empty($sCustom))
        {
            $aPath[] = 'Custom';
            $aPath[] =  $sCustom;
        }
        $aPath[] = $sModule;

        return Path::make(...$aPath);
    }
}
