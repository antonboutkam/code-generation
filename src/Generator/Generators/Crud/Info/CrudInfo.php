<?php

namespace Generator\Generators\Crud\Info;

use Cli\Tools\CommandUtils;
use Helper\Schema\Table;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

final class CrudInfo
{
    private OutputInterface $output;

    private function output(string $sMessage)
    {
        $this->output->writeln($sMessage);
    }

    public function __construct(OutputInterface $oOutput = null)
    {
        if($oOutput)
        {
            $this->output = $oOutput;
        }
        else
        {
            $this->output = new ConsoleOutput();
        }

    }

    public function create(Table $oTable)
    {
        $sClassName = 'Crud' . $oTable->getPhpName() . 'Manager';

        $sDir = '';
        if ($oTable->getCrudDir()) {
            $sDir = $oTable->getCrudDir() . '/';
        }

        $sCrudRootDirectory = CommandUtils::getRoot() . '/classes/Crud/' . $sDir . $oTable->getPhpName();
        $sModelSpace = $oTable->getModelNamespace();

        $this->output("Crud: <info>$sClassName</info>");
        $this->output("Namespace: <info>{$oTable->getCrudNamespace()}</info>");
        $this->output("Crud root: <info>{$sCrudRootDirectory}</info>");

        $this->output("Model: <info>{$sModelSpace}</info>");
        $this->output("QueryClass: <info>{$oTable->getQueryClass() }</info>");
        $this->output("ModelClass: <info>{$oTable->getModelClass() }</info>");
    }
}
