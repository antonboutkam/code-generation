<?php
namespace Generator\Admin\Module\Controller\DirectoryStructure;

use Generator\Admin\Module\Structure\StructureConfigInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Structure {
    private StructureConfigInterface $config;
    private OutputInterface $output;

    public function __construct(StructureConfigInterface $config, OutputInterface $oOutput) {
        $this->config = $config;
        $this->output = $oOutput;
    }

    public function generate() {
        $oRoot = $this->config->getInstallRoot();
        $oRoot->extend('Locales')->makeDir();

        foreach ($this->config->getModuleModels() as $model) {
            $oModelDir = $oRoot->extend($model);
            $oModelDir->extend('Locales');
            $oModelDir->extend('Base');
        }
    }
}
