<?php

namespace Generator\Generators\Admin\Module\Structure;

use Hurah\Types\Type\Path;
use Generator\Generators\Admin\Module\Util;
use Helper\Schema\Module;

class ConfigStructure implements StructureConfigInterface {

    private array $aModuleSections = [];
    private Path $oInstallRoot;

    /**
     * @return Path
     */
    public function getInstallRoot(): Path {
        return $this->oInstallRoot;
    }

    /**
     * @return string[]
     */
    public function getModuleSections(): array {
        return $this->aModuleSections;
    }

    /**
     * @param Path $oRootPath
     * @param array $aSections
     * @return static
     */
    public static function create(Path $oRootPath, array $aSections):self{
        $oConfigStructure = new Config();
        $oConfigStructure->aModuleSections = $aSections;
        $oConfigStructure->oInstallRoot = $oRootPath;
        return $oConfigStructure;
    }
    public static function fromModule(Module $oModule)
    {
        $oInstallRoot = Util::location($oModule->getDatabase()->getCustom(), $oModule->getName());

        $aModuleSections = [];
        foreach($oModule->getTables() as $oTable)
        {
            $aModuleSections[] = ucfirst($oTable->getName());
        }
        return self::create($oInstallRoot, $aModuleSections);

    }
}
