<?php

namespace Generator\Generators\Generator\Components;

use Generator\Generators\Generator\ConfigInterface;
use Generator\Generators\GeneratorInterface;
use Hurah\Types\Type\Php\IsVoid;
use Hurah\Types\Type\PlainText;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpNamespace;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ConfigClassGenerator extends AbstractConfigGenerator implements GeneratorInterface
{

    private ConfigInterface $oConfig;
    private OutputInterface $output;
    private InputInterface $input;

    function __construct(ConfigInterface $oConfig, InputInterface $input, OutputInterface $output)
    {

        $this->oConfig = $oConfig;
        $this->input = $input;
        $this->output = $output;
    }

    function generate(): string
    {

        // Create namespace
        $oNamespace = new PhpNamespace($this->oConfig->getConfigClassName()->reduce(1));
        $this->addUseStatements($oNamespace);

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

    private function addUseStatements(PhpNamespace $oNamespace): void
    {
        $oNamespace->addUse(OutputInterface::class);
        $oNamespace->addUse(InputInterface::class);
        $oNamespace->addUse($this->oConfig->getConfigInterfaceName());
        $oNamespace->addUse($this->oConfig->getWorkerClassName());

        foreach ($this->oConfig->getProperties() as $property) {
            if (!$property->getType()->isPrimitive()) {
                $oNamespace->addUse("{$property->getType()->toPhpNamespace()}");
            }
        }
    }

    private function addProperties(ClassType $oClass)
    {
        foreach ($this->oConfig->getProperties() as $property) {

            $mDefaultValue = $this->formatDefaultValue($property);
            if ($property->hasDefault()) {
                $oProperty = $oClass->addProperty($property->getName(), $mDefaultValue);
            } else {
                $oProperty = $oClass->addProperty($property->getName());
            }

            echo "{$property->getName()} from type {$property->getType()}" . PHP_EOL;
            echo "{$property->getName()} adding property {$property->getType()}" . PHP_EOL;

            $oProperty->setType("{$property->getType()}");
            $oProperty->setPrivate();
        }
    }

    private function addGetters(ClassType $oClass)
    {
        foreach ($this->oConfig->getProperties() as $property) {
            $oMethod = $oClass->addMethod('get' . ucfirst($property->getName()));
            $oMethod->setReturnType($property->getType());
            $oMethod->setBody('return $this->' . $property->getName() . ';');
        }
    }

    private function addCreateMethod(ClassType $oClass)
    {
        $oCreateMethod = $oClass->addMethod('create');
        $oCreateMethod->setStatic();
        foreach ($this->oConfig->getProperties() as $oConfigProperty) {
            $mDefaultValue = new IsVoid();
            if ($oConfigProperty->hasDefault()) {
                $mDefaultValue = $this->formatDefaultValue($oConfigProperty);
            }

            $oNetteProperty = $oCreateMethod->addParameter($oConfigProperty->getName(), $mDefaultValue);
            $oNetteProperty->setType($oConfigProperty->getType());
        }

        $oCreateMethod->setReturnType(new Literal('self'));
        $oCreateMethod->setBody($this->getCreateMethodBody());
    }


    private function getCreateMethodBody(): PlainText
    {
        $oBody = new PlainText();

        $sWorkerClassVarName = "\$o{$this->oConfig->getWorkerClassName()->getShortName()}";


        $oBody->addLn("$sWorkerClassVarName = new self();");
        foreach ($this->oConfig->getProperties() as $oConfigProperty) {
            $sGlobalVarName = "{$sWorkerClassVarName}->{$oConfigProperty->getName()}";
            $sArgumentVarName = "\${$oConfigProperty->getName()}";
            $oBody->addLn("$sGlobalVarName = $sArgumentVarName;");
        }

        $oBody->addLn("return $sWorkerClassVarName;");
        return $oBody;
    }
}
