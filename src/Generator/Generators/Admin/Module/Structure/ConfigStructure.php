<?php

namespace Generator\Admin\Module\Structure;

use Hurah\Types\Type\Path;
use Generator\Admin\Module\Util;
use Helper\Schema\Module;

class ConfigStructure implements StructureConfigInterface {

    private array $aModels = [];
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
    public function getModuleModels(): array {
        return $this->aModels;
    }

    public function fromModule(Module $oModule)
    {
        $this->oInstallRoot = Util::location($oModule->getDatabase()->getCustom(), $oModule->getName());

        foreach($oModule->getTables() as $oTable)
        {
            $this->aModels[] = ucfirst($oTable->getName());
        }

    }
}
