<?php
namespace Generator\Generators\Nodered;

use Cli\Tools\CommandUtils;
use Core\InlineTemplate;
use Core\Utils;

class GenerateDatasources
{
    /**
     * @param array $aDataSources
     * @param string $sDirection
     * @throws \Throwable
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Syntax
     */
    final public function create(array $aDataSources, string $sDirection)
    {

        $aDataSources = $this->makeBaseArgs($aDataSources, $sDirection);

        foreach($aDataSources as $aDataSource)
        {
            $aDataSource = $this->makeBaseArgs($aDataSource, $sDirection);

            if(!is_dir($aDataSource['install_path']))
            {
                mkdir($aDataSource['install_path']);
            }

            $this->makeJs($aDataSource);
            $this->makeHtml($aDataSource);
        }
        $this->makePackageJson($aDataSources, $sDirection);
/*
        if(!is_dir($bInstallPath))
        {
            $this->installPackage($sMethod, $aDataSource);
        }
        // $this->installPackage($sMethod, $aDataSource);
*/
    }
    function restartSupervisor()
    {
        $sCommand = 'sudo supervisorctl restart hurah_red';
        echo "Restart supervisor" . PHP_EOL;
        echo $sCommand . PHP_EOL;
        echo shell_exec($sCommand);
    }

    private function makePackageJson(array $aDataSources)
    {
        $sTemplateFile = CommandUtils::getRoot() . "/classes/Cli/Tools/Template/nodered/package.json";
        $sTemplate = file_get_contents($sTemplateFile);

        foreach($aDataSources as $aDataSource)
        $sTemplate = InlineTemplate::parse($sTemplate, $aDataSource);

//        "{{ code_lc }}": "{{ code_lc }}.js"

        $sFile = $aDataSource['install_path'] . '/package.json';
        file_put_contents($sFile, $sTemplate);
    }
    private function makeHtml(array $aDataSource)
    {
        $sTemplateFile = CommandUtils::getRoot() . "/classes/Cli/Tools/Template/nodered/settings.html";
        $sTemplate = file_get_contents($sTemplateFile);

        $sTemplate = InlineTemplate::parse($sTemplate, $aDataSource);

        $sFile = $aDataSource['install_path'] . '/' . $aDataSource['base_file_name'] . '.html';
        file_put_contents($sFile, $sTemplate);
    }

    private function makeBaseArgs(array $aDataSource, $sDirection): array
    {
        $sFuncName = Utils::camelCase($aDataSource['titel']) . $sDirection;
        $aDataSource['function_name'] = $sFuncName;
        $aDataSource['direction'] = $sDirection;
        $aDataSource['install_path'] = $this->getPluginRoot('1overheid-' . $sDirection);
        $aDataSource['action_label'] = strtolower($aDataSource['titel']);
        $aDataSource['base_file_name'] = Utils::snake_case($aDataSource['titel']);

        return $aDataSource;
    }

    /**
     * @param array $aDataSource
     * @throws \Throwable
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Syntax
     */
    private function makeJs(array $aDataSource)
    {
        $sTemplateFile = CommandUtils::getRoot() . "/classes/Cli/Tools/Template/nodered/jsnode.js";
        $sTemplate = file_get_contents($sTemplateFile);

        $sTemplate = InlineTemplate::parse($sTemplate, $aDataSource);


        $sFile = $aDataSource['install_path'] . '/' . $aDataSource['base_file_name'] . '.js';
        file_put_contents($sFile, $sTemplate);
    }

    private function getNodeRoot()
    {
        return CommandUtils::getRoot() . '/nodered/';
    }
    private function getPluginRoot(string $sNodeName)
    {
        return $this->getNodeRoot() . '/' . $this->getPluginPath($sNodeName);
    }
    private function getPluginPath(string $sNodeName)
    {
        return 'custom-nodes/' . $sNodeName;
    }

}
