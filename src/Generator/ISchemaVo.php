<?php
namespace Cli\Tools\VO\Contracts;

use Hurah\Types\Type\PhpNamespace;

interface ISchemaVo {

    public function getNamespace():PhpNamespace;
}
