<?php

namespace Generator\Generators\Crud;

use Cli\Tools\CommandUtils;
use Core\DataType\Path;
use Core\Utils;
use Helper\Schema\Table;
use Core\DataType\PhpNamespace;

class Crud
{
    public const VERSION_BASE = 'Base';
    public const VERSION_USER = null;
    public const TYPE_INTERFACE = 'interface';
    public const TYPE_CLASS = 'class';

    public static function getFilePath(
        Table $oTable,
        string $sClassName,
        $sVersion = self::VERSION_USER,
        bool $bIsInterface = false
    ): Path {
        $aRoot = [
            CommandUtils::getRoot(),
            'classes',
            'Crud',
            (string)$oTable->getCrudDir(),
            (string)$oTable->getPhpName(),
        ];
        $sPrefix = '';
        if ($bIsInterface) {
            $sPrefix = 'I';
        }
        $sBaseTag = '';
        if ($sVersion == self::VERSION_BASE) {
            $sBaseTag = 'Base';
        }
        return new Path(Utils::makePath($aRoot, $sVersion, "{$sPrefix}{$sBaseTag}{$sClassName}.php"));
    }

    public static function makeClassName(
        string $sClassName,
        Table $oTable,
        $sVersion = self::VERSION_USER,
        $sType = self::TYPE_CLASS
    ): PhpNamespace {

        if ($sVersion == self::VERSION_BASE) {
            $sClassName = 'Base' . $sClassName;
        }

        if ($sType == self::TYPE_INTERFACE) {
            $sClassName = 'I' . $sClassName;
        }
        return new PhpNamespace(self::makeNamespace($oTable, $sVersion, $sClassName));
    }

    public static function makeNamespace(Table $oTable, $sVersion = self::VERSION_USER, $sClassName = null)
    {
        return Utils::makeNamespace(
            (string)$oTable->getCrudNamespace(),
            (string)$oTable->getPhpName(),
            $sVersion,
            $sClassName
        );
    }
}
