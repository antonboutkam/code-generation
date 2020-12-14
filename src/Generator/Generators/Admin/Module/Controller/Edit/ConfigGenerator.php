<?php

namespace Generator\Admin\Module\Controller\Edit;

use Generator\Admin\Module\Util;
use Helper\Schema\Table;
use Hurah\Types\Type\PhpNamespace;
use Hurah\Types\Type\PlainText;

class ConfigGenerator implements GeneratorConfigInterface {

    private PhpNamespace $oPhpNamespace;
    private PhpNamespace $oBaseNamespace;
    private PhpNamespace $oCrudNamespace;
    private PhpNamespace $oQueryClass;
    private PhpNamespace $oModelClass;
    private PhpNamespace $oModelNamespace;
    private PlainText $sTitle;
    private PlainText $sPhpName;
    private PlainText $oModuleName;

    public static function create(
        PlainText $sTitle,
        PlainText $sPhpName,
        PlainText $oModuleName,
        PhpNamespace $oPhpNamespace,
        PhpNamespace $oBaseNamespace,
        PhpNamespace $oCrudNamespace,
        PhpNamespace $oQueryClass,
        PhpNamespace $oModelClass,
        PhpNamespace $oModelNamespace
    ):self{

        $oConfigGenerator = new self();
        $oConfigGenerator->sTitle = $sTitle;
        $oConfigGenerator->sPhpName = $sPhpName;
        $oConfigGenerator->oModuleName = $oModuleName;

        $oConfigGenerator->oPhpNamespace = $oPhpNamespace;
        $oConfigGenerator->oBaseNamespace = $oBaseNamespace;
        $oConfigGenerator->oCrudNamespace = $oCrudNamespace;
        $oConfigGenerator->oQueryClass = $oQueryClass;
        $oConfigGenerator->oModelClass = $oModelClass;
        $oConfigGenerator->oModelNamespace = $oModelNamespace;
        return $oConfigGenerator;

    }
    public function getTitle(): string {
        return $this->sTitle;
    }
    public function getPhpName(): string {
        return $this->sPhpName;
    }
    public function getBaseNamespace(): PhpNamespace {
        return $this->oBaseNamespace;
    }
    public function getNamespace(): PhpNamespace {
        return $this->oPhpNamespace;
    }
    public function getCrudNamespace(): PhpNamespace {
        return $this->oCrudNamespace;
    }
    public function getQueryClass(): PhpNamespace {
        return $this->oQueryClass;
    }
    public function getModelClass($bFqn = false): PhpNamespace {
        return $this->oModelClass;
    }
    public function getModuleName(): PlainText {
        return $this->oModuleName;
    }
    public function getModelNamespace(): PhpNamespace {
        return $this->oModelNamespace;
    }
    public static function fromTable(Table $oTable) : ConfigGenerator{
        $sCustom = (string)$oTable->getDatabase()->getCustom();
        $sModule = (string)$oTable->getModule()->getModuleDir();
        $sModelDir = ucfirst($oTable->getName());

        $oConfigGenerator = new ConfigGenerator();
        $oConfigGenerator->sTitle = new PlainText($oTable->getTitle());
        $oConfigGenerator->sPhpName = new PlainText($oTable->getPhpName());
        $oConfigGenerator->oPhpNamespace = Util::getNamespaceName($sCustom, $sModule)->extend($sModelDir);
        $oConfigGenerator->oBaseNamespace = $oConfigGenerator->oPhpNamespace->extend('Base');
        $oConfigGenerator->oCrudNamespace = new PhpNamespace($oTable->getCrudNamespace());
        $oConfigGenerator->oQueryClass = new PhpNamespace($oTable->getQueryClass());
        $oConfigGenerator->oModelClass = new PhpNamespace($oTable->getModelClass(true));
        $oConfigGenerator->oModelNamespace = new PhpNamespace($oTable->getModelNamespace());
        $oConfigGenerator->oModuleName = new PlainText($sModule);
        return $oConfigGenerator;
    }
}
