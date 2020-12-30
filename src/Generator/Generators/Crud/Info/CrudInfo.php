<?php

namespace Generator\Generators\Crud\Info;

use Cli\Tools\CommandUtils;
use Helper\Schema\Table;
use Symfony\Component\Console\Output\OutputInterface;

final class CrudInfo
{
    public function __construct(OutputInterface $oOutput = null)
    {
        parent::__construct($oOutput);
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
