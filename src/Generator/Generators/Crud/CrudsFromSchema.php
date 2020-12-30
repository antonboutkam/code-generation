<?php

namespace Generator\Generators\Crud;

use DOMDocument;
use Exception\LogicException;
use Helper\ApiXsd\Schema\Api;
use Helper\Schema\Database;
use Hi\Helpers\DirectoryStructure;
use Symfony\Component\Console\Output\OutputInterface;

class CrudsFromSchema
{
    private string $schemaLocation;

    public function __construct(string $sSchemaLocation, OutputInterface $oOutput = null)
    {
        $this->schemaLocation = $sSchemaLocation;
        parent::__construct($oOutput);
    }

    public function run()
    {
        $this->output("Generating cruds from schema.xml <info>$this->schemaLocation</info>");
        $sCustomerSchemaContents = file_get_contents($this->schemaLocation);

        $oDom = new DOMDocument();
        $aXmlSchemas = [];

        $aXmlSchemas[] = $this->schemaLocation;

        $oDirectoryStructure = new DirectoryStructure();

        if (strpos($sCustomerSchemaContents, '<external-schema filename="../../schema/core-schema-extra.xml"')) {
            $sExtraFile = $oDirectoryStructure->getSystemDir(true) . '/build/schema/core-schema-extra.xml';
            $this->output("Encountered additional schema file <info>$sExtraFile</info>");
            $aXmlSchemas[] = $sExtraFile;
        }

        foreach ($aXmlSchemas as $sSchemaFile) {
            $this->output("Loading schema <info>$sSchemaFile</info>");
            $sXmlString = file_get_contents($sSchemaFile);

            $this->output("Validating XSD <info>$sSchemaFile</info>");
            $oDom->loadXML($sXmlString);

            $this->output("Parsing XML <info>$sSchemaFile</info>");
            $oDatabase = simplexml_load_string($sXmlString, Database::class, LIBXML_NOCDATA);

            if (!$oDatabase instanceof Database) {
                $this->output("<error>Could not parse schema </error> <info>$sSchemaFile</info>");
                throw new LogicException("Could not parse schema.xml");
            }

            $oApi = null;

            $sApiXmlLocation = dirname($this->schemaLocation) . '/api.xml';
            $this->output("Checking for api.xml: <info>$sApiXmlLocation</info>");

            if (file_exists($sApiXmlLocation) && is_file($sApiXmlLocation)) {
                $this->output("Found api.xml, obtaining additional info <info>$sApiXmlLocation</info>");
                $sXml = file_get_contents($sApiXmlLocation);

                if (trim($sXml) !== '') {
                    $oDom->loadXML($sXml);
                    $oApi = simplexml_load_file($sApiXmlLocation, Api::class, LIBXML_NOCDATA);
                }
            }

            foreach ($oDatabase->getTables() as $oTable) {
                $this->output("Analyzing <info>{$oTable->getName()}</info>");
                if ($oTable->getSkipCruds()) {
                    $this->output("Skipping <info>{$oTable->getName()}</info> because skipCruds = <info>true</info>");
                    continue;
                }

                $this->output("Creating cruds for <info>{$oTable->getName()}</info>");
                $oCrudInfo = new CrudInfo($this->getOutputInterface());
                $oCrudInfo->create($oTable);

                $this->output("Making directory structure <info>{$oTable->getName()}</info>");
                $oCrudDirectoryStructure = new DirectoryStructure($this->getOutputInterface());
                $oCrudDirectoryStructure->create($oTable);

                $this->output("Making crud manager classes <info>{$oTable->getName()}</info>");
                $oCrudGenerator = new CrudManagerGenerator($this->getOutputInterface());
                $oCrudGenerator->create($oTable, $oApi);

                $this->output("Making crud field iterator interfaces <info>{$oTable->getName()}</info>");
                $oCrudGenerator = new CrudFieldIteratorGenerator($this->getOutputInterface());
                $oCrudGenerator->create($oTable);

                $this->output("Making crud field collection classes <info>{$oTable->getName()}</info>");
                $oCrudGenerator = new CrudFieldCollectionTypeGenerator($this->getOutputInterface());
                $oCrudGenerator->create($oTable);

                $this->output("Creating crud field objects <info>{$oTable->getName()}</info>");
                $oCrudGenerator = new CrudFieldGenerator($this->getOutputInterface());
                $oCrudGenerator->create($oTable, $oApi);
            }
        }
    }
}
