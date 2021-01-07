<?php

namespace Generator\Schema\Importer;

use Hurah\Types\Type\Path;
use Hurah\Types\Type\PathCollection;
use Core\Utils;
use Exception;
use Helper\Schema;
use Helper\Schema\Database;
use Hi\Helpers\DirectoryStructure;
use Symfony\Component\Console\Output\OutputInterface;

final class SchemaToDb {
    private function reportFiles(PathCollection $oPathCollection, OutputInterface $output)
    {

        $output->writeln("<info>Files to import:</info>");
        foreach ($oPathCollection->getIterator() as $oSchemaPath) {
            if ($output) {
                $output->writeln("<comment>Result <info>{$oSchemaPath}</info></comment>");
            }
        }
    }

    private function recursiveSchemaFinder(PathCollection &$oPathCollection, Path $oSchemaPath, OutputInterface $output):void{
        $oSchema = Schema::fromFilename($oSchemaPath);
        $oPathCollection->add($oSchemaPath);
        $oExternalSchemaIterator = $oSchema->getDatabase()->getExternalSchemas();


        foreach ($oExternalSchemaIterator as $oExternalSchema) {

            $output->writeln("<comment>Found schema <info>{$oExternalSchema->getPath()}</info> in <info>$oSchemaPath</info></comment>");
            $output->writeln("<comment>Going inside <info>{$oExternalSchema->getPath()}</info>");
            $this->recursiveSchemaFinder($oPathCollection, $oExternalSchema->getPath(), $output);
        }
        $output->writeln("<comment>Finished reading <info>{$oSchemaPath}</info></comment>");
    }
    private function collectSchemas(IImporterConfig $oConfig, OutputInterface $output):PathCollection {
        $oSchemaPathCollection = new PathCollection();
        $oSchemaIterator = $oConfig->getSchemaFiles()->getIterator();
        foreach ($oSchemaIterator as $oSchemaPath) {
            $output->writeln("<comment>Starting recursive external schema seeker from  ---------------->  {$oSchemaPath}</comment>");
            $this->recursiveSchemaFinder($oSchemaPathCollection, $oSchemaPath, $output);
        }
        return $oSchemaPathCollection;
    }

    function run(IImporterConfig $oConfig, OutputInterface $output) {

        $output->writeln("<comment>Start schema import</comment>");
        // Move to the build directory (where the main schema is so relative paths are correct)
        $sPrevDir = getcwd();
        $oDirectoryStructure = new DirectoryStructure();
        $sBuildDir = Utils::makePath($oDirectoryStructure->getSystemDir(true), 'build', 'database', $oConfig->getSystemId());
        chdir($sBuildDir);
        $oSchemaPathCollection = $this->collectSchemas($oConfig, $output);

        $this->reportFiles($oSchemaPathCollection, $output);

        foreach ($oSchemaPathCollection as $oSchemaPath) {
            $output->writeln("<comment>Import schema structure  ---------------->  {$oSchemaPath}</comment>");
            $this->createModels(Schema::fromPath($oSchemaPath), $output);
        }

        foreach ($oSchemaPathCollection->getIterator() as $oSchemaPath) {
            $output->writeln("<comment>Import foreign keys  ---------------->  {$oSchemaPath}</comment>");
            $this->makeForeignKeyConstraints(Schema::fromPath($oSchemaPath), $output);
        }

        // Move back to the dir where we started.
        chdir($sPrevDir);
    }

    private function makeForeignKeyConstraints(Schema $oSchema, OutputInterface $output) {
        $oDatabase = $oSchema->getDatabase();
        $this->foreignKeysToDb($oDatabase, $output);
    }

    private function createModels(Schema $oSchema, OutputInterface $output) {
        $oDatabase = $oSchema->getDatabase();

        $this->modulesToDb($oDatabase, $output);
        $this->tablesToDb($oDatabase, $output);
    }

    /**
     * Make foreign key relationships
     * @param Database $oDatabase
     * @param OutputInterface $output
     */
    private function foreignKeysToDb(Database $oDatabase, OutputInterface $output): void {

        if ($oDatabase->getTables()->count()) {
            foreach ($oDatabase->getTables() as $oTable) {
                if ($oTable->getForeignKeys()->count()) {
                    foreach ($oTable->getForeignKeys() as $oForeignKey) {
                        $output->writeln('Creating foreign key: ' . $oTable->getName() . ' (' . $oForeignKey->getPhpName() . ').' . $oForeignKey->getLocalColumn() . ' -> ' . $oForeignKey->getForeignColumn());
                        $oForeignKey->toPropel($oTable);
                    }
                }
            }
        }
    }

    /**
     * Create all the tables, including the ones that do not belong to any model
     * @param Database $oDatabase
     */
    private function tablesToDb(Database $oDatabase, OutputInterface $output): void {
        if ($oDatabase->getTables()->count()) {
            foreach ($oDatabase->getTables() as $oTable) {
                try {
                    $output->writeln("Creating table <info>{$oTable->getName()}</info>");

                    $oDataModel = $oTable->toPropel(true, true);
                    $oModule = $oTable->getModule();

                    if ($oModule) {
                        $oPropelModule = $oModule->toPropel(true, true);
                        $oPropelModule->save();
                        $output->writeln("Adding table {$oTable->getName()} to module {$oTable->getModule()->getName()}, module has id {$oPropelModule->getId()}");

                        $oDataModel->setModuleId($oPropelModule->getId());
                        $oDataModel->save();
                    }
                } catch (Exception $e) {
                    echo "Could not create table" . PHP_EOL;
                    echo "---------------------------" . PHP_EOL;
                    echo json_encode($oTable) . PHP_EOL;
                    echo "---------------------------" . PHP_EOL;
                    echo $e->getMessage() . PHP_EOL;
                    echo $e->getFile() . PHP_EOL;
                    echo $e->getTraceAsString() . PHP_EOL . PHP_EOL;
                }
            }
        }
    }

    /**
     * First take care that all modules are available
     * @param Database $oDatabase
     * @param OutputInterface $output
     */
    private function modulesToDb(Database $oDatabase, OutputInterface $output): void {
        foreach ($oDatabase->getModules() as $oModule) {
            try {

                $output->writeln("Creating module:\\{$oDatabase->getCustom()}\\{$oModule->getName()}");
                $oModule->toPropel(true);
            } catch (Exception $e)
            {
                $output->writeln("<error>Could not create module: {$e->getMessage()}</error>");

            }
        }
    }
}
