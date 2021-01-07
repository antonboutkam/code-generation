<?php

namespace Test\Generator\Generators\Generator;

use Generator\Generators\Generator\Config;
use Hurah\Types\Exception\InvalidArgumentException;
use Hurah\Types\Type\Php\PropertyCollection;
use Hurah\Types\Type\PhpNamespace;
use Hurah\Types\Type\PlainText;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class ConfigTest extends TestCase {

    /**
     * @return Config
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    private function createConfig(): Config {
        $oBaseNamespace = PhpNamespace::make('Generator', 'Generators', 'Generator', 'Fake');
        $oBaseTestNamespace = PhpNamespace::make('Tests')->extend($oBaseNamespace);
        return Config::create(
            new PlainText("test:command"),
            new PlainText("test:command"),
            new PlainText("test:command"),
            $oBaseNamespace->extend('FakeGenerator'),
            $oBaseNamespace->extend('FakeGeneratorConfig'),
            $oBaseNamespace->extend('FakeCommand'),
            $oBaseNamespace->extend('FakeGeneratorConfigInterface'),
            $oBaseTestNamespace->extend('TestFake'),
            new PropertyCollection()
        );
    }

    public function testGetConfigInterfaceName() {

        $oConfig = $this->createConfig();
        $sExpected = 'Generator\\Generators\\Generator\\Fake\\FakeGeneratorConfigInterface';
        $this->assertEquals($sExpected, "{$oConfig->getConfigInterfaceName()}", "{$oConfig->getConfigInterfaceName()}");
    }

    public function testGetWorkerClassName() {

        $oConfig = $this->createConfig();
        $sExpected = 'Generator\\Generators\\Generator\\Fake\\FakeGenerator';
        $this->assertEquals($sExpected, $oConfig->getWorkerClassName(), $oConfig->getWorkerClassName());
    }

    public function testGetProperties() {
        $oConfig = $this->createConfig();
        $this->assertInstanceOf(
            PropertyCollection::class,
            $oConfig->getProperties(),
            'Expected a Property collection');
    }

    public function testGetConfigClassName() {
        $oConfig = $this->createConfig();
        $sExpected = 'Generator\Generators\Generator\Fake\FakeGeneratorConfig';
        $this->assertEquals($sExpected, $oConfig->getConfigClassName(), $oConfig->getConfigClassName());
    }

    public function testGetCommandName() {
        $oConfig = $this->createConfig();
        $sResult = 'test:command';
        $this->assertEquals($sResult, "{$oConfig->getCommandName()}", 'Command name incorrect');
    }

    public function testGetTestClassName() {
        $oConfig = $this->createConfig();
        $sResult = 'Tests\\Generator\\Generators\\Generator\\Fake\\TestFake';
        $this->assertEquals($sResult, "{$oConfig->getTestClassName()}", "{$oConfig->getTestClassName()}");
    }

    public function testCreate() {
        $oConfig = $this->createConfig();
        $this->assertInstanceOf(Config::class, $oConfig);
    }
}
