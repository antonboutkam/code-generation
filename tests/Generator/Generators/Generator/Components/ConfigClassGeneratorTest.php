<?php

namespace Test\Generator\Generators\Generator\Components;

use Error;
use Exception;
use Generator\Generators\Generator\Components\ConfigClassGenerator;
use Generator\Generators\Generator\Config;
use Hurah\Types\Type\DnsName;
use Hurah\Types\Type\Php\Property;
use Hurah\Types\Type\Php\PropertyCollection;
use Hurah\Types\Type\PhpNamespace;
use Hurah\Types\Type\PlainText;
use Hurah\Types\Type\Primitive\PrimitiveBool;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class ConfigClassGeneratorTest extends TestCase
{

    public function testGenerate() {
        $oConfigClassGenerator = $this->createGenerator();

        $sClassConfig = $oConfigClassGenerator->generate();

        $sNeedle = "final class FakeGeneratorConfig implements FakeGeneratorConfigInterface";
        $this->assertStringContainsString($sNeedle, "{$sClassConfig}");

        $sNeedle =<<<EOT
public static function create(DnsName \$serverName = 'demo.novum.nu', bool \$autoLogin = false): self
EOT;

        $this->assertStringContainsString($sNeedle, "{$sClassConfig}");
    }

    private function createGenerator(): ConfigClassGenerator {
        try
        {
            $oBaseNamespace = PhpNamespace::make('Generator', 'Generators', 'Generator', 'Fake');
            $oBaseTestNamespace = PhpNamespace::make('Tests')
                                              ->extend($oBaseNamespace);
            $oConfig = Config::create(
                new PlainText("test:command"),
                new PlainText("This is a demo description"),
                new PlainText("This is a demo help text"),
                $oBaseNamespace->extend("FakeGenerator"),
                $oBaseNamespace->extend('FakeGeneratorConfig'),
                $oBaseNamespace->extend('FakeGeneratorCommand'),
                $oBaseNamespace->extend('FakeGeneratorConfigInterface'),
                $oBaseTestNamespace->extend('TestFake'),
                new PropertyCollection([
                    Property::create([
                        'name'    => 'serverName',
                        'type'    => DnsName::class,
                        'default' => 'demo.novum.nu',
                    ]),
                    Property::create([
                        'name'     => 'autoLogin',
                        'type'     => PrimitiveBool::class,
                        'nullable' => true,
                        'default'  => false,
                    ]),
                ])
            );

            $oInput = new ArrayInput([]);
            $oOutput = new ConsoleOutput();
            return new ConfigClassGenerator($oConfig, $oInput, $oOutput);
        } catch (Exception $e)
        {
            echo __METHOD__ . ':' . __LINE__ . PHP_EOL;
            $aBacktrace = debug_backtrace();
            foreach ($aBacktrace as $aLine)
            {
                echo $aLine['file'] . '::' . $aLine['line'] . PHP_EOL;
            }
            throw new Error("Could not instantiate configuration " . $e->getMessage());
        }
    }
}
