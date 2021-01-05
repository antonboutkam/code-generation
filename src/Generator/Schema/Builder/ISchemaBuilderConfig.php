<?php
namespace Generator\Schema\Builder;

use Hurah\Types\Type\PhpNamespace;
use Hurah\Types\Type\SystemId;

interface ISchemaBuilderConfig
{
    function getNamespace():PhpNamespace;
    function getSystemId():SystemId;
}
