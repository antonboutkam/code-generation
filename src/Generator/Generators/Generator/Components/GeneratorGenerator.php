<?php
namespace Generator\Generators\Generator\Components;

use Generator\Generators\Generator\ConfigInterface;
use Generator\Generators\GeneratorInterface;
use Hurah\Types\Type\PlainText;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class GeneratorGenerator implements GeneratorInterface
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
        $sNamespaceName = $this->oConfig->getWorkerClassName()
                                        ->reduce(1);
        $sClassName = $this->oConfig->getWorkerClassName()
                                    ->getShortName();
        $oNamespace = new PhpNamespace($sNamespaceName);
        $oNamespace->addUse($this->oConfig->getConfigInterfaceName());
        $oNamespace->addUse(OutputInterface::class);
        $oNamespace->addUse(InputInterface::class);
        $oNamespace->addUse(GeneratorInterface::class);
        $oNamespace->addUse(PlainText::class);

        $oClass = new ClassType($sClassName);
        $oClass->addImplement(GeneratorInterface::class);

        $this->addProperties($oClass);
        $this->addConstructor($oClass, $this->oConfig);
        $this->addGenerate($oClass, $this->oConfig);

        // Add class to namespace
        $oNamespace->add($oClass);
        return $oNamespace;
    }
    private function addProperties(ClassType $oClass): void {
        // Add properties
        $oConfigProperty = $oClass->addProperty('config');
        $oConfigProperty->setType($this->oConfig->getConfigInterfaceName());
        $oConfigProperty->setPrivate();

        $oConfigProperty = $oClass->addProperty('input');
        $oConfigProperty->setType(InputInterface::class);
        $oConfigProperty->setPrivate();

        $oConfigProperty = $oClass->addProperty('output');
        $oConfigProperty->setType(OutputInterface::class);
        $oConfigProperty->setPrivate();
    }
    private function addConstructor(ClassType $oClass, ConfigInterface $oConfig): void {
        // Add __construct
        $oConstructMethod = $oClass->addMethod('__construct');

        $oConfigParam = $oConstructMethod->addParameter('config');
        $oConfigParam->setType($oConfig->getConfigInterfaceName());

        $oInputParam = $oConstructMethod->addParameter('input');
        $oInputParam->setType(InputInterface::class);

        $oOutputParam = $oConstructMethod->addParameter('output');
        $oOutputParam->setType(OutputInterface::class);

        $oConstructMethod->setBody(<<<EOT
\$this->config = \$config;
\$this->input = \$input;
\$this->output = \$output;
EOT
        );
    }
    private function addGenerate(ClassType $oClass, ConfigInterface $oConfig): void {
        // Add generate
        $oNameMethod = $oClass->addMethod('generate');
        $oNameMethod->setReturnType(PlainText::class);
        $oNameMethod->setBody($this->getGenerateBody($oConfig));
    }
    private function getGenerateBody(ConfigInterface $oConfig): PlainText {
        $oPlainText = new PlainText();

        $oPlainText->addLn('// Available methods in the config object');
        foreach ($oConfig->getProperties() as $property)
        {
            $sGetter = 'get' . ucfirst("{$property->getName()}()");
            $oPlainText->addLn('// $this->config->' . $sGetter);
        }
        $oPlainText->addLn('return new PlainText("");');

        return $oPlainText;
    }
}
