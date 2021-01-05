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
use Generator\Schema\Builder\ISchemaBuilderConfig;

interface ICompleteSite extends ISiteStructureConfig, ISiteJsonConfig, ICompleteComponent
{

}