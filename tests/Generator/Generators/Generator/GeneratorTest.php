<?php

namespace Test\Generator\Generators\Generator;

use Generator\Generators\Generator\Config;
use Generator\Generators\Generator\Generator;
use Hurah\Types\Type\DnsName;
use Hurah\Types\Type\Php\Property;
use Hurah\Types\Type\Php\PropertyCollection;
use Hurah\Types\Type\PhpNamespace;
use Hurah\Types\Type\PlainText;
use Hurah\Types\Type\Primitive\PrimitiveBool;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class GeneratorTest extends TestCase
{

    public function testGenerate() {
        $oConfig = $this->getConfigObject();
        $oGenerator = new Generator($oConfig, new ArrayInput([]), new ConsoleOutput());
        $oGenerator->generate();
    }

    /**
     * @return Config
     * @throws \Hurah\Types\Exception\InvalidArgumentException
     * @throws \Hurah\Types\Exception\RuntimeException
     * @throws \ReflectionException
     */
    private function getConfigObject(): Config {
        return Config::create(
            new PlainText('test:command'),
            new PlainText('Test command description'),
            new PlainText('Test command help'),
            PhpNamespace::make('Generator', 'Generators', 'This', 'Is', 'Some', 'SomeWorker'),
            PhpNamespace::make('Generator', 'Generators', 'This', 'Is', 'Some', 'SomeConfig'),
            PhpNamespace::make('Generator', 'Generators', 'This', 'Is', 'Some', 'SomeCommand'),
            PhpNamespace::make('Generator', 'Generators', 'This', 'Is', 'Some', 'SomeInterface'),
            PhpNamespace::make('Test', 'Generator', 'Generators', 'This', 'Is', 'Some', 'Test'),
            (new PropertyCollection(
                [
                    Property::create([
                        'name' => 'serverName',
                        'type' => DnsName::class,
                    ]),
                    Property::create([
                        'name' => 'createHostsLocal',
                        'type' => PrimitiveBool::class,
                    ]),
                ])
            )
        );
    }

}
