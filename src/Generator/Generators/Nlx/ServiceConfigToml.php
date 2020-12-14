<?php/*** @unfixed**/

namespace Generator\Nlx\ServiceConfigToml;

use Cli\Tools\CommandUtils;
use Core\InlineTemplate;
use Core\Utils;
use Helper\ApiXsd\Schema\Api;
use Helper\Schema\Database;
use Throwable;
use Twig_Error_Loader;
use Twig_Error_Syntax;

final class ServiceConfigToml
{
    /**
     * @param Api $oApi
     * @param Database $oDatabase
     * @throws Throwable
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Syntax
     */
    final public function create(Api $oApi, Database $oDatabase)
    {
        echo CommandUtils::getRoot() . PHP_EOL;
        $sTemplateFile = CommandUtils::getRoot() . "/public_html/_default_api/modules/Service/service-config.toml";
        $sTemplate = file_get_contents($sTemplateFile);

        echo "Use class: " . get_class($oApi) . PHP_EOL;
        echo "Read in " . $sTemplateFile . PHP_EOL;
        $aData = [
            "module_name" => Utils::slugify($oApi->getTitle()),
            "endpoint_url" => $oApi->getEndpoint_url(),
            "documentation_url" => $oApi->getDocumentation_url(),
            "authorization_model" => $oApi->getAuthorization_model(),
            "ca_cert_path" => $oApi->getCa_cert_path(),
            "contact_support" => $oApi->getSupport_contact(),
            "contact_tech" => $oApi->getTech_contact(),
        ];
        $sTemplate = InlineTemplate::parse($sTemplate, $aData);


        $sOldLocation = CommandUtils::getRoot() . '/public_html/' .  $oApi->getApi_dir() . '/service-config.toml';
        if (file_exists($sOldLocation)) {
            unlink($sOldLocation);
        }

        $sFileLocation = CommandUtils::getRoot() . '/public_html/' .  $oApi->getApi_dir() . '/nlx/service-config.toml';
        if (!is_dir(dirname($sFileLocation))) {
            mkdir(dirname($sFileLocation));
        }
        file_put_contents($sFileLocation, $sTemplate);

        echo "----------------" . PHP_EOL;
        echo "Write file: " . $sFileLocation . PHP_EOL;
        echo $sTemplate  . PHP_EOL;
        exit();
    }
}