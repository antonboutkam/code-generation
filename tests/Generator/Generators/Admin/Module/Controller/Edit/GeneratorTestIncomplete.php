<?php

namespace Test\Generator\Generators\Admin\Module\Controller\Edit;

use Generator\Generators\Admin\Module\Controller\Overview\Generator;
use Hurah\Types\Type;

// use Generator\Generators\Admin\Module\Controller\Overview\ConfigGenerator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\ConsoleOutput;

class GeneratorTestIncomplete extends TestCase {

    /*
    public function testGenerate() {

        $oConfig = ConfigGenerator::create(
            new Type\PlainText('Test module'),
            new Type\PlainText('TestModule'),
            new Type\PlainText('TestScripts'),
            Type\PhpNamespace::make('AdminModules', 'Custom', 'Anton', 'Test'),
            Type\PhpNamespace::make('AdminModules', 'Custom', 'Anton', 'Test', 'Base'),
            Type\PhpNamespace::make('Crud', 'Custom', 'Anton', 'Test'),
            Type\PhpNamespace::make('Model', 'Custom', 'Anton', 'Test', 'TestModuleQuery'),
            Type\PhpNamespace::make('Crud', 'Custom', 'Anton', 'Test', 'TestModule'),
            Type\PhpNamespace::make('Model', 'Custom', 'Anton', 'Test', 'TestModule'),
        );
        $oBaseGenerator = new Generator($oConfig, new ConsoleOutput());
        $sGenerated = $oBaseGenerator->generate();
        $this->assertTrue(strpos($sGenerated, '<?php') === 0);
        $this->assertTrue(strpos($sGenerated, 'extends Base\EditController') > 0);
        $this->assertTrue(strpos($sGenerated, 'class EditController') > 0);
        $this->assertTrue(substr_count($sGenerated, '{') === substr_count($sGenerated, '}'));
    }
    */
}
