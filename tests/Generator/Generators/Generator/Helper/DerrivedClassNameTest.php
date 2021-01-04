<?php

namespace Test\Generator\Generators\Generator\Helper;

use Generator\Generators\Generator\Helper\DerivedClassName;
use Hurah\Types\Type\PhpNamespace;
use PHPUnit\Framework\TestCase;

class DerrivedClassNameTest extends TestCase
{
    const BASE_NAMESPACE = 'Generator\\Generators\\Admin\\Module\\Menu';

    public function testMakeConfigClassName()
    {
        $oClassNameTest = $this->createDerivedClassNameGenerator();
        $sExpected = 'Generator\\Generators\\Admin\\Module\\Menu\\ConfigGenerator';

        $this->assertEquals($sExpected, "{$oClassNameTest->makeConfigClassName()}",
            "{$oClassNameTest->makeConfigClassName()}");
    }

    private static function createDerivedClassNameGenerator(): DerivedClassName
    {
        $oFakeNamespace = new PhpNamespace(self::BASE_NAMESPACE);
        return new DerivedClassName('Generator', $oFakeNamespace);
    }

    public function testMakeConfigInterfaceName()
    {
        $oClassNameTest = $this->createDerivedClassNameGenerator();
        $sExpected = 'Generator\\Generators\\Admin\\Module\\Menu\\ConfigGeneratorInterface';

        $this->assertEquals($sExpected, "{$oClassNameTest->makeConfigInterfaceName()}",
            "{$oClassNameTest->makeConfigInterfaceName()}");
    }

    public function testMakeCommandClassName()
    {
        $oClassNameTest = $this->createDerivedClassNameGenerator();

        $this->assertEquals(
            'Generator\\Generators\\Admin\\Module\\Menu\\GeneratorCommand',
            "{$oClassNameTest->makeCommandClassName()}",
            "{$oClassNameTest->makeCommandClassName()}");
    }

    public function testMakeTestClassName()
    {
        $sExpected = 'Test\\Generator\\Generators\\Admin\\Module\\Menu\\GeneratorTest';
        $oClassNameTest = $this->createDerivedClassNameGenerator();
        $this->assertEquals(
            $sExpected,
            "{$oClassNameTest->makeTestClassName()}",
            "{$oClassNameTest->makeTestClassName()}");
    }

}
