<?php

namespace Generator\Admin\Module\Controller\Edit;

use AdminModules\GenericEditController;
use Crud\FormManager;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use Symfony\Component\Console\Output\OutputInterface;

class BaseGenerator {

    private GeneratorConfigInterface $config;
    private OutputInterface $output;

    public function __construct(GeneratorConfigInterface $config, OutputInterface $oOutput) {
        $this->config = $config;
        $this->output = $oOutput;
    }

    /**
     * @return string
     */
    public function generate(): string
    {
        $oNamespace = new PhpNamespace($this->config->getBaseNamespace());
        $oClass = new ClassType('EditController');

        $oNamespace->addUse(GenericEditController::class);
        $oClass->addExtend(GenericEditController::class);
        $oClass->setAbstract(true);

        $oClass->addComment("This class is automatically generated, do not modify manually.");
        $oClass->addComment("Modify " . $this->config->getPhpName() . " instead if you need to override or add functionality.");

        $oNamespace->addUse($this->config->getCrudNamespace());
        $oNamespace->addUse(FormManager::class);

        $oGetManagerMethod = $oClass->addMethod('getCrudManager');
        $oGetManagerMethod->setReturnType(FormManager::class);
        $oGetManagerMethod->setBody('return new ' . $this->config->getCrudNamespace()->getShortName() . '();');

        $oGetTitleMethod = $oClass->addMethod('getPageTitle');
        $oGetTitleMethod->setReturnType('string');
        $oGetTitleMethod->setBody('return "' . $this->config->getTitle() . '";');
        $oNamespace->add($oClass);
        return '<?php' . PHP_EOL . (string)$oNamespace;
    }
}
