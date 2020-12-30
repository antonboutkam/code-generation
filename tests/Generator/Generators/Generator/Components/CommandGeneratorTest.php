<?php

namespace Test\Generator\Generators\Generator\Components;

use Error;
use Exception;
use Generator\Generators\Generator\Components\CommandGenerator;
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

class CommandGeneratorTest extends TestCase
{

    public function testGenerate() {

        $oCommandGenerator = $this->createGenerator();

        $aNeedles = [
            'namespace Generator\\Generators\\Generator\\Fake;',
            'final class Command extends \\Cli\\Composer\\BaseCommand'
        ];
        $sGenerted = $oCommandGenerator->generate();
        foreach ($aNeedles as $sNeedle)
        {
            $this->assertStringContainsString($sNeedle, $sGenerted, 'Expected output to contain ' . $sNeedle);
        }
    }

    /**
     * @return CommandGenerator
     */
    private function createGenerator(): CommandGenerator {
        try
        {
            $oBaseNamespace = PhpNamespace::make('Generator', 'Generators', 'Generator', 'Fake');
            $oBaseTestNamespace = PhpNamespace::make('Tests')
                                              ->extend($oBaseNamespace);
            $oConfig = Config::create(
                new PlainText("test:command"),
                new PlainText("This is a demo description"),
                new PlainText("This is a demo help text"),
                $oBaseNamespace->extend('Generator'),
                $oBaseNamespace->extend('Config'),
                $oBaseNamespace->extend('Command'),
                $oBaseNamespace->extend('Interface'),
                $oBaseTestNamespace->extend('TestFake'),
                new PropertyCollection([
                    Property::create([
                        'name' => 'serverName',
                        'type' => DnsName::class,
                        'default' => 'demo.novum.nu'
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
            return new CommandGenerator($oConfig, $oInput, $oOutput);
        } catch (Exception $e)
        {
            throw new Error("Could not instantiate configuration " . $e->getMessage());
        }
    }
}
