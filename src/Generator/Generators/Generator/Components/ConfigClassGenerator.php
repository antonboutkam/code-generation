<?php

namespace Generator\Generators\Generator\Components;

use Generator\Generators\Generator\ConfigInterface;
use Generator\Generators\GeneratorInterface;
use Hurah\Types\Type\Php\IsVoid;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpNamespace;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;

final class ConfigClassGenerator implements GeneratorInterface {

    private ConfigInterface $oConfig;
    private OutputInterface $output;
    private InputInterface $input;

    function __construct(ConfigInterface $oConfig, InputInterface $input, OutputInterface $output){

        $this->oConfig = $oConfig;
        $this->input = $input;
        $this->output = $output;
    }

    function generate() : string {

        // Create namespace
        $oNamespace = new PhpNamespace($this->oConfig->getConfigClassName()->reduce(1));
        $this->addUseStatements($oNamespace, $this->oConfig);

        // Create class
        $oClass = new ClassType($this->oConfig->getConfigClassName()->getShortName());
        $oClass->addImplement($this->oConfig->getConfigInterfaceName());
        $oClass->setFinal(true);

        $this->addProperties($oClass);
        $this->addGetters($oClass);
        $this->addCreateMethod($oClass);

        $oNamespace->add($oClass);
        return "{$oNamespace}";
    }
    private function addProperties(ClassType $oClass)
    {
        foreach($this->oConfig->getProperties() as $property)
        {
            $oProperty = $oClass->addProperty($property->getName(), "{$property->getDefault()}");
            $oProperty->setType("{$property->getType()}");
            $oProperty->setPrivate();
        }
    }
    private function addUseStatements(PhpNamespace $oNamespace, ConfigInterface $oConfig):void
    {
        $oNamespace->addUse(OutputInterface::class);
        $oNamespace->addUse(InputInterface::class);
        $oNamespace->addUse($this->oConfig->getConfigInterfaceName());
        foreach ($this->oConfig->getProperties() as $property)
        {
            if(!$property->getType()->isPrimitive())
            {
                $oNamespace->addUse($property->getType());
            }
        }
    }
    private function addGetters(ClassType $oClass)
    {
        foreach($this->oConfig->getProperties() as $property)
        {
            $oMethod = $oClass->addMethod('get' . ucfirst($property->getName()));
            $oMethod->setReturnType($property->getType());
        }
    }
    private function addCreateMethod(ClassType $oClass) {
        // Add "create" method
        $oCreateMethod = $oClass->addMethod('create');
        $oCreateMethod->setStatic();
        foreach ($this->oConfig->getProperties() as $oConfigProperty)
        {
            if($oConfigProperty->getDefault() instanceof IsVoid)
            {
                $oNetteProperty = $oCreateMethod->addParameter($oConfigProperty->getName());
            }
            else
            {
                $sType = "{$oConfigProperty->getType()}";
                $bIsBool = $sType === 'bool';
                $mDefault = $oConfigProperty->getDefault();
                if($bIsBool){
                    $mDefault = !empty($oConfigProperty->getDefault());
                }
                $oNetteProperty = $oCreateMethod->addParameter($oConfigProperty->getName(), $mDefault);
            }
            $oNetteProperty->setType($oConfigProperty->getType());
        }

        $oCreateMethod->setReturnType(new Literal('self'));
        $oCreateMethod->setBody('// @todo implement');
    }
}
