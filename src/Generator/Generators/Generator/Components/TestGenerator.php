<?php
namespace Generator\Generators\Generator\Components;

use Generator\Generators\Generator\ConfigInterface;
use Generator\Generators\GeneratorInterface;
use Hurah\Types\Type\Primitive\PrimitiveArray;
use Hurah\Types\Type\Primitive\PrimitiveBool;
use Hurah\Types\Type\Primitive\PrimitiveFloat;
use Hurah\Types\Type\Primitive\PrimitiveInt;
use Hurah\Types\Type\Primitive\PrimitiveString;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpNamespace;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

final class TestGenerator implements GeneratorInterface
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
        $oTestNamespace = $this->oConfig->getTestClassName()->reduce(1);
        $oNamespace = new PhpNamespace("{$oTestNamespace->getShortName()}");

        $oTestClassName = $this->oConfig->getTestClassName();

        $oClass = new ClassType("{$oTestClassName->getShortName()}");
        $oClass->setExtends(TestCase::class);
        $this->addUseStatements($oNamespace, $this->oConfig);
        $this->addTestGenerateMethod($oClass, $this->oConfig);
        $this->addCreateGeneratorMethod($oClass, $this->oConfig);
        $oNamespace->add($oClass);
        return $oNamespace;
    }
    /**
     * @param PhpNamespace $oPhpNamespace
     * @param ConfigInterface $oConfig
     */
    private function addUseStatements(PhpNamespace $oPhpNamespace, ConfigInterface $oConfig): void {
        foreach ($oConfig->getProperties() as $property)
        {
            if ($property->getType()
                         ->isPrimitive())
            {
                continue;
            }
            $oPhpNamespace->addUse(TestCase::class);
            $oPhpNamespace->addUse($property->getType()
                                            ->toPhpNamespace());
            $oPhpNamespace->addUse($oConfig->getWorkerClassName());
            $oPhpNamespace->addUse(ArrayInput::class);
            $oPhpNamespace->addUse(ConsoleOutput::class);
        }
        $oPhpNamespace->addUse($oConfig->getConfigClassName());
    }
    private function addTestGenerateMethod(ClassType $oClass, ConfigInterface $oConfig): void {
        $oTestGenerateMethod = $oClass->addMethod('testGenerate');
        $oTestGenerateMethod->setReturnType(new Literal('void'));

        $sWorkerClass = $oConfig->getWorkerClassName()
                                ->getShortName();

        $oTestGenerateMethod->setBody(<<<EOT
\$o{$sWorkerClass} = \$this->{$this->makeCreateObjectMethodName($oConfig)}();
\$sGenerated = \$o{$sWorkerClass}->generate();
\$sNeedle = '';
\$this->assertStringContainsString(\$sNeedle, \$sGenerated);
EOT
        );
    }
    private function makeCreateObjectMethodName(ConfigInterface $oConfig): string {
        return 'create' . $oConfig->getWorkerClassName()
                                  ->getShortName();
    }

    private function addCreateGeneratorMethod(ClassType $oClass, ConfigInterface $oConfig) {
        $oCreateGeneratorMethod = $oClass->addMethod($this->makeCreateObjectMethodName($oConfig));
        $oCreateGeneratorMethod->setPrivate();
        $oCreateGeneratorMethod->setReturnType($oConfig->getWorkerClassName());
        $sConfigClassName = $oConfig->getConfigClassName()
                                    ->getShortName();
        $sConfigClassVarName = "\${$sConfigClassName}";
        $sWorkerClassName = $oConfig->getWorkerClassName()
                                    ->getShortName();

        $oCreateGeneratorMethod->setBody(<<<EOT
{$sConfigClassVarName} = {$sConfigClassName}::create(
{$this->makeCreateMethodParams($oConfig)}
);
return new {$sWorkerClassName}({$sConfigClassVarName}, new ArrayInput([]), new ConsoleOutput());
EOT
        );
    }

    private function makeCreateMethodParams(ConfigInterface $oConfig): string {
        $aProperties = [];
        foreach ($oConfig->getProperties() as $property)
        {
            if ($property->getType()
                         ->isPrimitive())
            {
                if ("{$property->getType()->toPhpNamespace()}" === PrimitiveBool::class)
                {
                    $aProperties[] = 'false';
                } else
                {
                    if ("{$property->getType()->toPhpNamespace()}" === PrimitiveArray::class)
                    {
                        $aProperties[] = '[]';
                    } else
                    {
                        if ("{$property->getType()->toPhpNamespace()}" === PrimitiveInt::class)
                        {
                            $aProperties[] = '1';
                        } else
                        {
                            if ("{$property->getType()->toPhpNamespace()}" === PrimitiveString::class)
                            {
                                $aProperties[] = '"some-generated-string"';
                            } else
                            {
                                if ("{$property->getType()->toPhpNamespace()}" === PrimitiveFloat::class)
                                {
                                    $aProperties[] = '1.1';
                                }
                            }
                        }
                    }
                }
            } else
            {
                $aProperties[] = 'new ' . $property->getType()
                                                   ->toPhpNamespace()
                                                   ->getShortName() . '("fill-me-in")';
            }
        }
        return join(', ' . PHP_EOL, $aProperties);
    }

}
