<?php

namespace Generator\Generators\Generator\Components;

use Generator\Generators\Generator\ConfigInterface;
use Generator\Generators\GeneratorInterface;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpNamespace;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ConfigInterfaceGenerator implements GeneratorInterface
{

    private ConfigInterface $oConfig;
    private OutputInterface $output;
    private InputInterface $input;

    function __construct(ConfigInterface $oConfig, InputInterface $input, OutputInterface $output) {

        $this->oConfig = $oConfig;
        $this->input = $input;
        $this->output = $output;
    }
    function generate(): string {

        $sNamespace = $this->oConfig->getConfigInterfaceName()->reduce(1);
        $oNamespace = new PhpNamespace($sNamespace);
        $oInterface = new ClassType($this->oConfig->getConfigInterfaceName()->getShortName());
        $oInterface->setInterface();
        $oNamespace->addUse(OutputInterface::class);
        $this->addUseStatements($oNamespace, $this->oConfig);
        $this->addGetters($oInterface, $this->oConfig);

        $oCreateMethod = $oInterface->addMethod('create');
        $oCreateMethod->setReturnType(new Literal('self'));
        $oNamespace->add($oInterface);

        return $oNamespace;
    }
    private function addGetters(ClassType $oInterface, ConfigInterface $oConfig) {
        foreach ($oConfig->getProperties() as $property)
        {
            $sMethod = 'get' . ucfirst($property->getName());
            echo "Add public method {$sMethod}" . PHP_EOL;
            $classProperty = $oInterface->addMethod($sMethod);
            $classProperty->setPublic();
            $classProperty->setReturnType($property->getType());
        }
    }
    private function addUseStatements(PhpNamespace $oNamespace, ConfigInterface $oConfig) {
        foreach ($oConfig->getProperties() as $property)
        {
            if($property->getType()->isPrimitive())
            {
                continue;
            }
            $oNamespace->addUse("{$property->getType()}");
        }
    }

}
