<?php

namespace Generator\Generators\Admin\Module\Config;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends BaseCommand
{
    protected function configure() {
        $this->setName('generator:admin:module:config');
        $this->setDescription("Generate module config file");
        $this->addArgument('custom', InputArgument::OPTIONAL, 'Custom name / system id', null);
        $this->addArgument('module', InputArgument::OPTIONAL, 'Module name', null);
    }
    protected function initialize(InputInterface $input, OutputInterface $output) {

    }
    protected function interact(InputInterface $input, OutputInterface $output) {

    }
    protected function execute(InputInterface $input, OutputInterface $output) {
        $oConfig = ConfigConfig::create($input->getArgument('custom'), $input->getArgument('module'));
        $oGenerator = new Config($oConfig, $input, $output);
        $oGenerator->generate();
    }
}
