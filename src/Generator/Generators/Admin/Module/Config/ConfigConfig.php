<?php

namespace Generator\Generators\Admin\Module\Config;


use Generator\Generators\Admin\Module\Util;
use Generator\Generators\GeneratorInterface;
use Helper\Schema\Table;
use Hurah\Types\Type\Path;
use Hurah\Types\Type\PhpNamespace as PhpNamespaceType;

class ConfigConfig implements ConfigConfigInterface, GeneratorInterface {


    private string $sCustom;
    private string $sModule;

    public static function create(string $sCustom, string $sModuleName) {
        $ConfigConfig = new ConfigConfig();
        $ConfigConfig->sCustom = $sCustom;
        $ConfigConfig->sModule = $sModuleName;
        return $ConfigConfig;
    }
    public static function fromTable(Table $oTable) {
        $ConfigConfig = new ConfigConfig();
        $ConfigConfig->sCustom = (string) $oTable->getDatabase()->getCustom();
        $ConfigConfig->sModule = (string) $oTable->getModuleName();
    }
    public function location(): Path {
        return Util::location($this->getCustom(), $this->getModule())->extend('Config.php');
    }
    public function getNamespaceName(): PhpNamespaceType {
        return Util::getNamespaceName($this->getCustom(), $this->getModule());
    }
    public function getCustom(): string {
        return $this->sCustom;
    }

    public function getModule(): string {
        return $this->sModule;
    }

    public function getName(): string {
        return 'generator:generate';
    }
}
