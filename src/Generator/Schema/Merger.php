<?php

namespace Generator\Schema;

use Core\Utils;
use GuzzleHttp\Client;
use Hi\Helpers\DirectoryStructure;
use Symfony\Component\Console\Output\OutputInterface;

class Merger {
    const XSD_ORIGIN = 'https://novumgit.gitlab.io/innovation-app-schema-xsd/v1/schema.xsd';
    private ?string $sRootFile;
    private ?OutputInterface $output;
    private string $sDatabaseBuildRootDir = '.';
    private string $sGeneratedSchemaDir;

    protected static function log(string $sMessage): void {
        echo $sMessage . PHP_EOL;
    }

    private function output(string $sString) {
        if ($this->output) {
            $this->output->writeln($sString);
        }
    }

    /**
     * Merger constructor.
     * @param string $sFileName
     * @param string|null $sAbsolutePath if passed, should point to the directory that contains the main schema.xml file
     * @param OutputInterface|null $output
     */
    public function __construct(string $sFileName, string $sAbsolutePath = null, OutputInterface $output = null) {
        if ($output) {
            $this->output = $output;
        }

        self::log(__METHOD__);
        $this->sRootFile = $sFileName;
        if ($sAbsolutePath) {
            $this->sRootFile = $sAbsolutePath . DIRECTORY_SEPARATOR . $sFileName;
            $this->sDatabaseBuildRootDir = $sAbsolutePath;
        }
        $this->sGeneratedSchemaDir = $this->sDatabaseBuildRootDir . DIRECTORY_SEPARATOR . 'generated-schema';
    }

    public function addImports(array $aFiles = null) {
        $sSchemaFileLocation = Utils::makePath($this->sGeneratedSchemaDir, 'schema.xml');
        $sSourceContent = file_get_contents($sSchemaFileLocation);
        $sNewFile = $sSourceContent;
        foreach ($aFiles as $sFile) {
            $this->output->writeln("Adding external-schema <info>$sFile</info>");
            $sReplacement = '<external-schema filename="' . $sFile . '"/>' . PHP_EOL . '</database>';
            $sReplace = '</database>';
            $sNewFile = str_replace($sReplace, $sReplacement, $sNewFile);
        }
        $this->output->writeln("Writing new schema <info>$sSchemaFileLocation</info>");
        file_put_contents($sSchemaFileLocation, $sNewFile);
    }

    private function getLocalXsdLocation(): string {
        $oDirectoryStructure = new DirectoryStructure();

        return $oDirectoryStructure->getSystemRoot() . DIRECTORY_SEPARATOR . $oDirectoryStructure->getSystemDir() . DIRECTORY_SEPARATOR . $oDirectoryStructure->getSchemaXsdDir() . DIRECTORY_SEPARATOR . 'schema.xsd';
    }

    private function copyFile(string $sSourceFile): void {
        $sDestFile = $this->sGeneratedSchemaDir . DIRECTORY_SEPARATOR . basename($sSourceFile);
        if (!is_dir($this->sGeneratedSchemaDir)) {
            $this->output('<comment>mkdir</comment> <info>' . $this->sGeneratedSchemaDir . '</info>');
            mkdir($this->sGeneratedSchemaDir);
        }
        $this->output('<comment>copy</comment> <info>' . $sSourceFile . '</info> -> <info>' . $sDestFile . '</info>');
        copy($sSourceFile, $sDestFile);
    }

    private function fetchXsd(): void {

        $client = new Client();
        $result = $client->request('GET', self::XSD_ORIGIN);
        if ($result->getStatusCode() === 200) {
            file_put_contents($this->getLocalXsdLocation(), $result->getBody());
        }
    }

    private function stripNewlines(string &$sSchema): void {
        self::log(__METHOD__);
        $sSchema = preg_replace('/^\s+\r?\n$/', '', $sSchema);
    }

    public function merge(): void {
        $this->fetchXsd();
        // Copy XSD to generated-schema dir.
        $this->copyFile($this->getLocalXsdLocation());

        // Merge all includes into new combined XML file starting from root / current file.
        $this->convertHandler($this->sRootFile);
    }

    private function makePropelCompatible(string &$sSchemaXml) {
        $this->stripModules($sSchemaXml);
        $this->stripNewlines($sSchemaXml);

        // Propel needs it's own
        $this->changeXsd($sSchemaXml);
    }

    public function changeXsd(string &$sSchema): void {
        self::log(__METHOD__);

        $sSchema = preg_replace('/"[.\/a-zA-Z0-9-]+\.xsd"/', '"./schema.xsd"', $sSchema);
    }

    private function convertHandler(string $sSourceFile, $bIsRootFile = true): void {
        self::log(__METHOD__);
        $sSchemaXml = $this->load($sSourceFile);

        // Find external file paths
        $aExternalFileNames = $this->getExternalSchemaFiles($sSchemaXml);

        // Remove custom elements and attributes from schema, set XSD to the propel version.
        $this->output->writeln("Making <info>$sSourceFile</info> propel compatible");
        $this->makePropelCompatible($sSchemaXml);

        if (!empty($aExternalFileNames) && is_array($aExternalFileNames)) {
            $this->adjustExternalSchemaPaths($sSchemaXml, $aExternalFileNames);

            foreach ($aExternalFileNames as $sExternalFile) {
                $this->convertHandler($sExternalFile, false);
            }
        }

        $this->store($sSourceFile, $sSchemaXml, $bIsRootFile);
    }

    private function load(string $sSchema): string {
        self::log(__METHOD__);
        return file_get_contents($sSchema);
    }

    private function makeImportDir(string $sDestDir): void {
        if (!is_dir($sDestDir)) {
            mkdir($sDestDir);
        }
    }

    private function store(string $sFileName, string $sSchema, bool $bIsRootFile): void {
        self::log(__METHOD__);
        $sFileName = basename($sFileName);
        if ($bIsRootFile) {
            $sDestFile = $this->sGeneratedSchemaDir . DIRECTORY_SEPARATOR . $sFileName;
        } else {
            $sDestDir = $this->sGeneratedSchemaDir . DIRECTORY_SEPARATOR . 'import';
            $this->makeImportDir($sDestDir);
            $sDestFile = $sDestDir . DIRECTORY_SEPARATOR . $sFileName;
        }

        $this->output->writeln('<comment>writing file </comment> <info>' . $sDestFile . '</info> <comment>cwd is</comment> <info>' . getcwd() . '</info>');

        file_put_contents($sDestFile, $sSchema);
    }

    private function stripModules(string &$sSchema): void {
        self::log(__METHOD__);
        $sSchema = str_replace('<modules>', '', $sSchema);
        $sSchema = str_replace('</modules>', '', $sSchema);
        $sSchema = preg_replace('/<module.+\/>/', '', $sSchema);
    }

    private function getExternalSchemasFull(string $sSchema): array {
        self::log(__METHOD__);
        $aMatches = [];
        preg_match_all('/\<external\-schema.+filename="(.+)".+/', $sSchema, $aMatches);
        return $aMatches;
    }

    public function adjustExternalSchemaPaths(string &$sSchemaXml, array $aExternalSchemaPaths): void {
        self::log(__METHOD__);
        foreach ($aExternalSchemaPaths as $sOriginalPath) {
            $sLocalPath = basename($sOriginalPath);
            $sSchemaXml = str_replace($sOriginalPath, './generated-schema/import/' . $sLocalPath, $sSchemaXml);
        }
    }

    public function getExternalSchemaFiles(string $sSchema): ?array {
        self::log(__METHOD__);
        $aSchemas = $this->getExternalSchemasFull($sSchema);
        return $aSchemas[1] ?? null;
    }

}
