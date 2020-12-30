<?php

namespace Generator\Generators\Build;

use Cli\Tools\VO\Contracts\ISchemaVo;
use Core\DataType\PhpNamespace;

class SchemaXml
{

    private PhpNamespace $namespace;

    public function __construct(ISchemaVo $oSchemaVo)
    {
        $this->namespace = $oSchemaVo->getNamespace();
    }
}
