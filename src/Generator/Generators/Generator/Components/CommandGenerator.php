<?php

namespace Generator\Generators\Generator\Components;

use Exception;
use Generator\Generators\Generator\ConfigInterface;
use Generator\Generators\GeneratorInterface;
use Generator\Generators\Helper\Command\Question;
use Generator\Generators\Helper\Command\Util;
use Generator\Helper\Code\EmptyClass;
use Hurah\Types\Exception\InvalidArgumentException;
use Hurah\Types\Type\PlainText;
use Hurah\Types\Type\Primitive;
use Hurah\Types\Type\TypeType;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpNamespace;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class CommandGenerator implements GeneratorInterface
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

        // Create namespace
        $oNamespace = new PhpNamespace($this->oConfig->getCommandClassName()
                                                     ->reduce(1));
        $oNamespace->addUse(Command::class, 'BaseCommand');
        $oNamespace->addUse($this->oConfig->getConfigClassName());
        $oNamespace->addUse(InputInterface::class);
        $oNamespace->addUse(Util::class);
        $oNamespace->addUse(Exception::class);
        $oNamespace->addUse(TypeType::class);
        $oNamespace->addUse(OutputInterface::class);
        $oNamespace->addUse(InvalidArgumentException::class);
        $oNamespace->addUse(Question::class);
        $oNamespace->addUse(InputArgument::class);
        $oNamespace->addUse(InputInterface::class);
        $oNamespace->addUse(InputOption::class);
        $oNamespace->addUse(OutputInterface::class);

        foreach ($this->oConfig->getProperties() as $oProperty)
        {
            $oNamespace->addUse($oProperty->getType()->toPhpNamespace());
        }

        // Create class
        $oClass = EmptyClass::create($this->oConfig->getCommandClassName()
                                                   ->getShortName(), Command::class);
        $oAllArgumentsPassedProperty = $oClass->addProperty('bAllArgumentsPassed', new Literal('false'));
        $oAllArgumentsPassedProperty->setType(new Literal('bool'));
        $oAllArgumentsPassedProperty->setStatic();

        $oConfigureMethod = $this->addMethod($oClass, 'configure', new Literal('void'), false);
        $oInitializeMethod = $this->addMethod($oClass, 'initialize', new Literal('void'));
        $oInteractMethod = $this->addMethod($oClass, 'interact', new Literal('void'));
        $oExecuteMethod = $this->addMethod($oClass, 'execute', new Literal('int'));

        $oConfigureMethod->setBody($this->getConfigureBody($this->oConfig));
        $oInitializeMethod->setBody($this->getInitializeBody($this->oConfig));
        $oInteractMethod->setBody($this->getInteractBody($this->oConfig));

        $oExecuteMethod->addComment('@throws InvalidArgumentException');
        $oExecuteMethod->addComment('@param InputInterface $input');
        $oExecuteMethod->addComment('@param OutputInterface $output');
        $oExecuteMethod->addComment('@return int');


        $oExecuteMethod->setBody($this->getExecuteBody($this->oConfig));

        $oNamespace->add($oClass);
        return $oNamespace;
    }
    private function addMethod(
        ClassType $oClass,
        string $sMethodMame,
        Literal $oReturnType = null,
        bool $bAddIo = true
    ): Method {
        $oMethod = $oClass->addMethod($sMethodMame);
        if ($oReturnType)
        {
            $oMethod->setReturnType($oReturnType);
        }
        if ($bAddIo)
        {
            $this->addIoParams($oMethod);
        }

        return $oMethod;
    }
    private function addIoParams(Method $oMethod): Method {
        $oInput = $oMethod->addParameter('input');
        $oInput->setType(InputInterface::class);

        $oInput = $oMethod->addParameter('output');
        $oInput->setType(OutputInterface::class);

        return $oMethod;
    }
    private function getConfigureBody(ConfigInterface $oConfig): PlainText {
        $oResult = new PlainText();
        $oResult->addLn('$this->setName(\'' . $oConfig->getCommandName() . '\');');
        $oResult->addLn('$this->setDescription(\'' . $oConfig->getCommandDescription() . '\');');
        $oResult->addLn("");
        foreach ($oConfig->getProperties() as $oProperty)
        {
            $aMode = [];

            if ($oProperty->isNullable())
            {
                $aMode[] = 'InputArgument::OPTIONAL';
            } else
            {
                $aMode[] = 'InputArgument::REQUIRED';
            }
            if ("{$oProperty->getType()}" === 'array')
            {
                $aMode[] = 'InputArgument::IS_ARRAY';
            }
            $sMode = join(' | ', $aMode);

            $sArgument = '\'' . $oProperty->getName() . '\', ' . $sMode . ', \'' . $oProperty->getLabel() . '\'';
            $oResult->addLn('$this->addArgument(' . $sArgument . ');');
        }
        $sMode = 'InputOption::VALUE_NONE | InputOption::VALUE_OPTIONAL';
        $sLabel = '\'When set will not generate anything\'';
        $oResult->addLn('$this->addOption(\'dry-run\', \'t\', ' . $sMode . ', ' . $sLabel . ');');

        $oResult->addLn('$this->setHelp(\'' . $oConfig->getCommandHelp() . '\');');

        return $oResult;
    }
    private function getInitializeBody(ConfigInterface $oConfig): PlainText {
        $oResult = new PlainText();
        $oResult->addLn('$bAllArgumentsPassed = true;');
        foreach ($oConfig->getProperties() as $oProperty)
        {
            $sName = $oProperty->getName();
            $sDefault = $oProperty->getDefault();

            $oResult->addLn('if (!$input->getArgument(\'' . $sName . '\'))');
            $oResult->addLn('{');
            $oResult->addLn('   $bAllArgumentsPassed = false;');
            $oResult->addLn('   $input->setArgument(\'' . $sName . '\', \'' . $sDefault . '\');');
            $oResult->addLn('}');
        }
        $oResult->addLn('self::$bAllArgumentsPassed = $bAllArgumentsPassed;');
        return $oResult;
    }


    /**
     * @param ConfigInterface $oConfig
     * @return PlainText
     */
    private function getInteractBody(ConfigInterface $oConfig): PlainText {
        $oResult = new PlainText();
        $oResult->addLn('if (self::$bAllArgumentsPassed)');
        $oResult->addLn('{');
        $oResult->addLn('   return;');
        $oResult->addLn('}');
        $oResult->addLn("");
        $oResult->addLn('Util::introText(\'Welcome to the ' . $oConfig->getCommandDescription() . '\', $output);');
        $oResult->addLn("");
        $oResult->addLn('$oQuestionHelper = new Question($input, $output);');
        $oResult->addLn("");
        foreach ($oConfig->getProperties() as $property)
        {
            $sType = 'new TypeType(' . $property->getType()->toPhpNamespace()->getShortName() . '::class)';
            $sLabel = "'{$property->getLabel()}'";
            $sName = "'{$property->getName()}'";
            $sVarName = "\${$property->getName()}";
            $oResult->addLn($sVarName . ' = $oQuestionHelper->ask(' . $sLabel . ', ' . $sType . ', ' . $sName . ');');
        }
        $oResult->addLn("");
        foreach ($oConfig->getProperties() as $property)
        {
            $sName = "'{$property->getName()}'";
            $sVarName = "\${$property->getName()}";
            $oResult->addLn('$input->setArgument(' . $sName . ', ' . $sVarName . ');');
        }

        return $oResult;
    }
    private function getExecuteBody(ConfigInterface $oConfig): PlainText {
        $oResult = new PlainText();

        $sConfigClassName = $this->oConfig->getConfigClassName()
                                          ->getShortName();

        $oResult->addLn('$oConfig = ' . $sConfigClassName . '::create(');
        $aSignatureArguments = [];
        foreach ($oConfig->getProperties() as $property)
        {
            if ($property->getType()->isPrimitive())
            {
                $aSignatureArguments[] = '$input->getArgument(\'' . $property->getName() . '\')';
            }
            else
            {
                $sPropertyType = $property->getType()
                                          ->toPhpNamespace()
                                          ->getShortName();
                $sPropertyName = "'" . $property->getName() . "'";
                $aSignatureArguments[] = '  new ' . $sPropertyType . '($input->getArgument(' . $sPropertyName . '))';
            }
        }

        $sWorkerClassName = $oConfig->getWorkerClassName()->getShortName();
        $oResult->addLn(join(',' . PHP_EOL, $aSignatureArguments));
        $oResult->addLn(');');
        $oResult->addLn('');

        $oResult->addLn('if ($input->getOption(\'dry-run\'))');
        $oResult->addLn('{');
        $oResult->addLn('    $output->writeln(\'<error>This is a dry run</error>\');');
        $oResult->addLn('}');
        $oResult->addLn('else');
        $oResult->addLn('{');
        $oResult->addLn('   try');
        $oResult->addLn('   {');
        $oResult->addLn("       \$o{$sWorkerClassName} = new $sWorkerClassName(\$oConfig, \$input, \$output);");
        $oResult->addLn("       \$o{$sWorkerClassName}->generate();");
        $oResult->addLn('   }');
        $oResult->addLn('   catch(Exception $e)');
        $oResult->addLn('   {');
        $oResult->addLn('       $output->writeln(\'<error>\' . $e->getMessage() . \'</error>\');');
        $oResult->addLn('   }');
        $oResult->addLn('}');
        $oResult->addLn('return BaseCommand::SUCCESS;');
        return $oResult;
    }
}
