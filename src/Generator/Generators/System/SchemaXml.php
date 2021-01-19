<?php
namespace Generator\Generators\System;

use Cli\Tools\VO\Contracts\ISchemaVo;
use Hurah\Types\Type\PhpNamespace;

class SchemaXml{

    private PhpNamespace $namespace;

    function __construct(ISchemaVo $oSchemaVo)
    {
        $this->namespace = $oSchemaVo->getNamespace();
    }

}
