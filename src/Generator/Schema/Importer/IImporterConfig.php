<?php
namespace Generator\Schema\Importer;

use Hurah\Types\Type\PathCollection;
use Hurah\Types\Type\SystemId;

interface IImporterConfig
{
    function getSystemId():SystemId;
    function getSchemaFiles():PathCollection;
}
