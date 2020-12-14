<?php

namespace Generator\Admin\Module;

use Hurah\Types\Type\Path;
use Hurah\Types\Type\PhpNamespace as PhpNamespaceType;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ModuleGenerator  {

    public function __construct(ConfigModule $config, OutputInterface $oOutput) {
        $this->config = $config;
        $this->output = $oOutput;
    }

    abstract protected function generate():string;




}
