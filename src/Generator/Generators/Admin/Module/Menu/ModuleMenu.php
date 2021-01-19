<?php
namespace Generator\Generators\Admin\Module\Menu;

use Helper\Schema\Module;
use Helper\Schema\Table;
use Helper\Schema\Database;

final class ModuleMenu
{
    private function getMenuDir(Database $oDatabase, Module $oModule):string
    {
        if((string) $oDatabase->getCustom() == '')
        {
            return \Cli\Tools\CommandUtils::getRoot() .
                '/admin_modules/' .
                $oModule->getModuleDir();
        }

        return \Cli\Tools\CommandUtils::getRoot() .
            '/admin_modules/Custom/' .
            $oDatabase->getCustom() . '/' .
            $oModule->getModuleDir();

    }
    function create(Database $oDatabase, Module $oModule, array $aTables = [])
    {
        $sMenuDir = $this->getMenuDir($oDatabase, $oModule);
        $sMenuPath = $sMenuDir . '/menu.twig';

        if($oModule->getTables()->count() == 0)
        {
            echo "Not creating menu structure, no datamodels that need it" . PHP_EOL;
            return;
        }
        $aTemplate = [];
        $aTemplate[] = '<li>';
        $aTemplate[] = '    <a class="accordion-toggle {{ menu_state }}" id="module_{{ module_name }}" href="#">';
        $aTemplate[] = '        <span class="fa fa-' . $oModule->getIcon() . '"></span>';
        $aTemplate[] = '        <span class="sidebar-title">{{ \'' . $oModule->getTitle() . '\'|translate }}</span>';
        $aTemplate[] = '        <span class="caret"></span>';
        $aTemplate[] = '    </a>';
        $aTemplate[] = '    <ul class="nav sub-nav">';

        foreach ($aTables as $oTable)
        {
            if($oTable instanceof Table)
            {

                if((string)$oDatabase->getCustom() === '')
                {
                    $sUrl = strtolower('/' . $oModule->getModuleDir() .
                        '/' . $oTable->getName() . '/overview');
                }
                else
                {
                    $sUrl = '/custom/' . strtolower($oDatabase->getCustom() .
                            '/' . $oModule->getModuleDir() .
                            '/' . $oTable->getName() . '/overview');
                }


                $aTemplate[] = '        <li>';
                $aTemplate[] = '            <a href="' . $sUrl  . '">';
                $aTemplate[] = '                <span class="fa fa-file-text-o"></span>';
                // $aTemplate[] = '       ' . print_r($oTable->getTitle(), true);
                $aTemplate[] = '                    {{ \'' . $oTable->getTitle() . '\'|translate }}';
                $aTemplate[] = '            </a>';
                $aTemplate[] = '        </li>';
            }
        }
        $aTemplate[] = '    </ul>';

        $aTemplate[] = '</li>';

        if(!is_dir($sMenuDir))
        {
            echo "Create directory $sMenuDir" . PHP_EOL;
            mkdir($sMenuDir, 0777, true);
        }

        file_put_contents($sMenuPath, join(PHP_EOL, $aTemplate));
        echo "Writing $sMenuPath " . PHP_EOL;
    }
}