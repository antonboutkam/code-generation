<?php

namespace Generator\Generators\Crud;

use Cli\Tools\CommandUtils;
use Helper\Schema\Table;
use Symfony\Component\Console\Output\OutputInterface;

final class DirectoryStructure
{

    public function __construct(OutputInterface $oOutput = null)
    {
        parent::__construct($oOutput);
    }

    public function create(Table $oTable)
    {

        $aDirs = [
            '/',
            '/Base',
            '/Field',
            '/Field/Base',
            '/Locales',
            '/Action',
        ];

        foreach ($aDirs as $sDir) {
            $sCrudDir = '';
            if ($oTable->getCrudDir()) {
                $sCrudDir = $oTable->getCrudDir() . '/';
            }

            $sDirPath = CommandUtils::getRoot() . '/classes/Crud/' . $sCrudDir . $oTable->getPhpName() . $sDir;

            $this->output("Checking if directory exists <info>$sDirPath</info>");
            if (!is_dir($sDirPath)) {
                $this->output("Creating directory <info>$sDirPath</info>");
                mkdir($sDirPath, 0777, true);
            } else {
                $this->output("Directory exists <info>$sDirPath</info>");
            }
        }
    }
}
