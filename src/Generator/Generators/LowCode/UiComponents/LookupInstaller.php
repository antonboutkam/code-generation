<?php/*** @unfixed**/

namespace Generator\LowCode\UiComponents\LookupInstaller;

use Cli\Tools\CommandUtils;
use Core\Reflector;
use Core\Utils;
use Hi\Helpers\DirectoryStructure;
use LowCode\ComponentFactory;
use Model\System\DataModel\Model\DataModel;
use Model\System\DataModel\Model\DataModelQuery;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LookupInstaller
{

    public static function install(array $aAnswers, OutputInterface $output)
    {
        $oDirectoryStructure = new DirectoryStructure();
        require_once Utils::makePath($oDirectoryStructure->getVendorDir(), 'autoload.php');
        require_once Utils::makePath($oDirectoryStructure->getConfigRoot(), $aAnswers['config_dir'], 'propel', 'config.php');
        require_once Utils::makePath($oDirectoryStructure->getConfigRoot(), $aAnswers['config_dir'], 'config.php');

        $aComponents = ComponentFactory::getAll();
        foreach ($aComponents as $oComponent) {
            foreach ($oComponent->getComponentXml()->getProperties() as $oProperty) {
                if ($oProperty->getType() === 'enumeration') {
                    $sLookupTableName = $oComponent->getModelName() . '_' . $oProperty->getKey();
                    $oDataModel = DataModelQuery::create()->findOneByName($sLookupTableName);

                    if (!$oDataModel instanceof DataModel) {
                        $output->writeln("<error>Could not find component model {$oComponent->getModelName()}, skipping</error>");
                        continue;
                    }

                    $aEnumerationValues = $oProperty->getEnumerationValues();

                    $oPropelModel = $oDataModel->getPropelQueryObject();
                    $oModel = $oPropelModel->findOneByItemKey('0');

                    if ($oModel) {
                        $oModel->delete();
                    }
                    foreach ($aEnumerationValues as $aValueSet) {
                        $oPropelModel = $oDataModel->getPropelQueryObject();

                        $output->writeln("Adding <info>{$oComponent->getComponentXml()->getId()}</info> -> <info>{$oProperty->getKey()}</info> -> <info>{$aValueSet['key']}</info>");

                        $oPropelModelInDb = $oPropelModel->findOneByItemKey($aValueSet['key']);

                        if (!$oPropelModelInDb instanceof ActiveRecordInterface) {
                            $oPropelModelInDb = $oDataModel->getPropelModel();
                            $oPropelModelInDb->setItemKey($aValueSet['key']);
                        }

                        $oPropelModelInDb->setItemLabel($aValueSet['value']);
                        $oPropelModelInDb->save();
                    }
                }
            }
        }
    }
}