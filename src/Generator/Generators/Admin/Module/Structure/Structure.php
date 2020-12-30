<?php
namespace Generator\Generators\Admin\Module\Structure;

use Generator\Generators\GeneratorInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Hurah\Types\Type\Path;

final class Structure implements GeneratorInterface{
    private StructureConfigInterface $config;
    private OutputInterface $output;

    public function __construct(StructureConfigInterface $config, OutputInterface $oOutput) {
        $this->config = $config;
        $this->output = $oOutput;
    }

    public function generate() {
        $oRoot = $this->config->getInstallRoot();
        $oBaseLocalesDir = $oRoot->extend('Locales')->makeDir();
        $this->output->writeln("Create directory <info>{$oBaseLocalesDir}</info>");

        foreach ($this->config->getModuleSections() as $model) {
            $this->output->writeln("Extend <info>{$oRoot}</info> with <info>{$model}</info>");

            $oModelDir = $oRoot->extend($model);
            $this->makeDir($oModelDir->extend('Locales'));
            $this->makeDir($oModelDir->extend('Base'));
        }
    }
    private function makeDir(Path $oPath)
    {
        if($oPath->isFile())
        {
            $this->output->writeln("Cannot create directory <comment>{$oPath}</comment>, file in the way");
        }
        elseif($oPath->isDir())
        {
            $this->output->writeln("Directory <comment>{$oPath}</comment> exists");
        }
        else
        {
            $this->output->writeln("Creating directory <info>{$oPath}</info>");

        }
    }
}
