<?php

namespace Generator\Generators\Generator\Components;

use Generator\Generators\Generator\ConfigInterface;
use Generator\Generators\GeneratorInterface;
use Hurah\Types\Type\Php\IsVoid;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpNamespace;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ConfigInterfaceGenerator extends AbstractConfigGenerator implements GeneratorInterface
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
        $this->addUseStatements($oNamespace, $this->oConfig);
        $this->addCreateMethod($oInterface, $this->oConfig);
        $this->addGetters($oInterface, $this->oConfig);

        $oNamespace->add($oInterface);

        return $oNamespace;
    }
    private function addCreateMethod(ClassType $oInterface, ConfigInterface $oConfig)
    {
        $oCreateMethod = $oInterface->addMethod('create');
        $oCreateMethod->setReturnType(new Literal('self'));
        $oCreateMethod->setStatic();

        foreach ($oConfig->getProperties() as $oConfigProperty) {
            $mDefaultValue = new IsVoid();
            if ($oConfigProperty->hasDefault()) {
                $mDefaultValue = $this->formatDefaultValue($oConfigProperty);
            }

            $oNetteProperty = $oCreateMethod->addParameter($oConfigProperty->getName(), $mDefaultValue);
            $oNetteProperty->setType("{$oConfigProperty->getType()}");
        }
    }
    private function addGetters(ClassType $oInterface, ConfigInterface $oConfig) {
        foreach ($oConfig->getProperties() as $property)
        {
            $sMethod = 'get' . ucfirst($property->getName());
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
