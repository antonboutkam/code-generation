<?php
namespace Generator\Helper;

use Cli\Tools\CommandUtils;
use Hurah\Types\Type\Path;
use Core\Utils;
use Helper\Schema\Table;
use Hurah\Types\Type\PhpNamespace;

class Crud
{
    const VERSION_BASE = 'Base';
    const VERSION_USER = null;
    const TYPE_INTERFACE = 'interface';
    const TYPE_CLASS = 'class';

    static function getFilePath(Table $oTable, string $sClassName, $sVersion = self::VERSION_USER, bool $bIsInterface = false): Path {
        $aRoot = [
            CommandUtils::getRoot(),
            'classes',
            'Crud',
            (string) $oTable->getCrudDir(),
            (string) $oTable->getPhpName(),
        ];
        $sPrefix= '';
        if($bIsInterface)
        {
            $sPrefix = 'I';
        }
        $sBaseTag = '';
        if($sVersion == self::VERSION_BASE)
        {
            $sBaseTag = 'Base';
        }
        return new Path(Utils::makePath($aRoot, $sVersion, "{$sPrefix}{$sBaseTag}{$sClassName}.php"));
    }

    static function makeClassName(string $sClassName, Table $oTable, $sVersion = self::VERSION_USER, $sType = self::TYPE_CLASS) : PhpNamespace {

        if($sVersion == self::VERSION_BASE)
        {
            $sClassName = 'Base' . $sClassName;
        }

        if($sType == self::TYPE_INTERFACE)
        {
            $sClassName = 'I' . $sClassName;
        }
        return new PhpNamespace(self::makeNamespace($oTable, $sVersion, $sClassName));
    }

    static function makeNamespace(Table $oTable, $sVersion = self::VERSION_USER, $sClassName = null)
    {
        if((string)$oTable->getCrudNamespace())
        {
            return Utils::makeNamespace((string)$oTable->getCrudNamespace(), (string)$oTable->getPhpName(), $sVersion, $sClassName);
        }
        return Utils::makeNamespace((string)$oTable->getPhpName(), $sVersion, $sClassName);
    }


}

