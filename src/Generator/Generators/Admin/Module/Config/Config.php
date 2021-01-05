<?php

namespace Generator\Generators\Admin\Module\Config;

use AdminModules\ModuleConfig;
use Hurah\Types\Type\PlainText;
use Core\Translate;
use Generator\Generators\Fragment\Php;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Config {

    private ConfigConfigInterface $config;
    private OutputInterface $output;
    private InputInterface $input;

    public function __construct(ConfigConfigInterface $config, InputInterface $oInput, OutputInterface $oOutput) {
        $this->config = $config;
        $this->input = $oInput;
        $this->output = $oOutput;
    }

    public function getGenerated(): Php {
        return new Php($this->config->location(), new PlainText($this->generate()));
    }

    public function generate(): string {
        $sClassName = 'Config';
        $oNamespace = new PhpNamespace((string)$this->config->getNamespaceName());
        $oNamespace->addUse(ModuleConfig::class);
        $oNamespace->addUse(Translate::class);

        $oClass = new ClassType($sClassName);
        $oClass->setExtends(ModuleConfig::class);
        $oClass->setFinal();
        $oClass->addMethod('isEnabelable')->setBody('return true;')->setReturnType('bool');
        $oClass->addMethod('getModuleTitle')->setBody('return Translate::fromCode("' . $this->getModule() . '");')->setReturnType('string');

        $oNamespace->add($oClass);
        return '<?php' . PHP_EOL . (string)$oNamespace;
    }

    private function getModule(): string {
        return $this->config->getModule();
    }

    private function isCustom(): bool {
        return trim($this->config->getCustom()) !== '';
    }

    private function getCustom(): string {
        return $this->config->getCustom();
    }
}
