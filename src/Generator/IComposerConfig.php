<?php
namespace Generator;

use Hurah\Types\Type\Composer\AuthorList;
use Hurah\Types\Type\Composer\DependencyList;
use Hurah\Types\Type\Composer\Extra;
use Hurah\Types\Type\Composer\Stability;
use Hurah\Types\Type\Composer\License;
use Hurah\Types\Type\KeywordList;
use Hurah\Types\Type\Path;
use Hurah\Types\Type\PluginType;
use Hurah\Types\Type\SystemId;
use Hurah\Types\Type\Url;

interface IComposerConfig
{
    function getSystemId():SystemId;
    function getPackageName():string;
    function getAutoload():array;
    function getPackageType():PluginType;
    function getRequire():?DependencyList;
    function getLicense():License;
    function getPreferStable():bool;
    function getDescription():string;
    function getHomepage():Url;
    function getMinimumStability():Stability;
    function getKeywords():KeywordList;
    function getAuthors():AuthorList;
    function getExtra():?Extra;
    public function getInstallDir(string $sEnv):Path;
}
