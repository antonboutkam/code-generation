<?php

namespace Generator\Generators\Generator;

use Hurah\Types\Type\Php\PropertyCollection;
use Hurah\Types\Type\PhpNamespace;
use Hurah\Types\Type\PlainText;

interface ConfigInterface
{
    public function getCommandName(): PlainText;

    public function getCommandDescription(): PlainText;

    public function getCommandHelp(): PlainText;

    public function getWorkerClassName(): PhpNamespace;

    public function getCommandClassName(): PhpNamespace;

    public function getConfigClassName(): PhpNamespace;

    public function getConfigInterfaceName(): PhpNamespace;

    public function getTestClassName(): PhpNamespace;

    public function getProperties(): PropertyCollection;

}
