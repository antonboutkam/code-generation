<?php
namespace Generator\Generators;

use Hurah\Types\Type\Path;
use Core\DataType\PlainText;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseGenerator
{
    protected BaseGeneratorConfig $config;
    protected OutputInterface $output;

    abstract protected function generate():string;
    abstract protected function location():Path;
}
