<?php

namespace Generator\Vo\Util;

use Hurah\Types\Type\Composer\ServiceName;
use Hurah\Types\Type\Composer\Vendor;
use Hurah\Types\Type\IGenericDataType;
use Hurah\Types\Type\PhpNamespace;
use Core\Utils;

class NamespaceHelper
{
    public static function api(Vendor $vendor, ServiceName $service_name): PhpNamespace
    {
        return self::makeNamespace('Api', $vendor, $service_name);
    }

    public static function general(Vendor $vendor, ServiceName $service_name): PhpNamespace
    {
        return self::makeNamespace($vendor, $service_name);
    }

    private static function makeNamespace(...$parts): PhpNamespace
    {
        $aNamespaceParts = [];
        foreach ($parts as $part) {
            if ($part instanceof IGenericDataType || is_string($part)) {
                $sNamespacePart = strtolower(Utils::slugify($part, '_'));
                $sNamespacePart = ucfirst($sNamespacePart);
                $sNamespacePart = preg_replace('/^[0-9]+/', '', $sNamespacePart);
                $aNamespaceParts[] = ucfirst($sNamespacePart);
            }
        }
        return new PhpNamespace(join('', $aNamespaceParts));
    }
}
