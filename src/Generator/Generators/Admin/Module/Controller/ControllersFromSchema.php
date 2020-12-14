<?php/*** @unfixed**/

namespace Generator\Admin\Module\Controller\ControllersFromSchema;

use DOMDocument;
use Exception\LogicException;
use Helper\Schema\Database;
use Hi\Helpers\DirectoryStructure;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\Console\Output\OutputInterface;

final class ControllersFromSchema extends BaseGenerator
{

    private string $sSchemaLocation;
    private ?string $sSecondSchemaLocation;

    public function __construct(string $sSchemaLocation, string $sSecondSchemaLocation = null, OutputInterface $oOutput = null)
    {
        $this->sSchemaLocation = $sSchemaLocation;
        $this->sSecondSchemaLocation = $sSecondSchemaLocation;
        parent::__construct($oOutput);
    }

    /**
     * @throws PropelException
     */
    public function run()
    {

        $oDom = new DOMDocument();
        $sCustomerSchemaContents = file_get_contents($this->sSchemaLocation);

        $aXmlSchemas = [];
        $aXmlSchemas[] = $this->sSchemaLocation;

        if (isset($this->sSecondSchemaLocation)) {
            $aXmlSchemas[] = $this->sSecondSchemaLocation;
        }

        $oDirectoryStructure = new DirectoryStructure();

        if (strpos($sCustomerSchemaContents, '<external-schema filename="../../schema/core-schema-extra.xml"')) {
            $aXmlSchemas[] = $oDirectoryStructure->getSystemDir(true) . '/build/schema/core-schema-extra.xml';
        }
        $aModulesForMenus = [];
        foreach ($aXmlSchemas as $sSchemaFile) {
            echo "Loading schema " . $sSchemaFile . PHP_EOL;
            $sXml = file_get_contents($sSchemaFile);
            $oDom->loadXML($sXml);

            $oDatabase = simplexml_load_string($sXml, Database::class, LIBXML_NOCDATA);

            if (!$oDatabase instanceof Database) {
                throw new LogicException("Could not parse schema.xml");
            }

            foreach ($oDatabase->getTables() as $oTable) {
                if ($oTable->getSkipControllers()) {
                    echo "Skipping controller \"" . $oTable->getName() . "\" skipController=true  " . $sSchemaFile . PHP_EOL;
                    continue;
                }
                if ($oTable->getModule() == null) {
                    echo "Skipping controller \"" . $oTable->getName() . "\" no module configured, see " . $sSchemaFile . PHP_EOL;
                    continue;
                }
                echo "Custom: " . $oDatabase->getCustom() . PHP_EOL;
                echo "Create crud overview for module: " . $oTable->getModuleName() . '"' . PHP_EOL;
                echo "Slugged: " . $oTable->getModule()->getModuleDir() . PHP_EOL;

                (new DirectoryStructure())->create($oTable);
                (new ControllerOverviewGenerator())->create($oTable);
                (new ControllerEditorGenerator())->create($oTable);

                (new Config())->create($oTable);

                echo PHP_EOL;
            }
            $aModules = $oDatabase->getModules();

            foreach ($aModules as $oModule) {
                $aModulesForMenus[$oModule->getName()]['module'] = $oModule;
                foreach ($oModule->getTables() as $oTable) {
                    if (!isset($aModulesForMenus[$oModule->getName()]['tables'])) {
                        $aModulesForMenus[$oModule->getName()]['tables'] = [];
                    }
                    $aModulesForMenus[$oModule->getName()]['tables'][] = $oTable;
                }
            }
            foreach ($aModulesForMenus as $sModuleName => $aProps) {
                if (isset($aProps['tables'])) {
                    (new Menu())->create($oDatabase, $aProps['module'], $aProps['tables']);
                }
            }
            $aModulesForMenus = [];
        }
    }
}