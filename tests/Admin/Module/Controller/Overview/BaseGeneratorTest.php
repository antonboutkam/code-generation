<?php

namespace Test\Admin\Module\Controller\Overview\ControllerOverviewGenerator;

use Generator\Admin\Module\Controller\Overview\BaseGenerator;
use Hurah\Types\Type;

use Generator\Admin\Module\Controller\Overview\ConfigGenerator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\ConsoleOutput;

class BaseGeneratorTest extends TestCase {

    public function testGenerate() {

        $oConfig = ConfigGenerator::create(
            new Type\PlainText('Test module'),
            new Type\PlainText('TestModule'),
            new Type\PlainText('TestScripts'),
            Type\PhpNamespace::make('AdminModules', 'Custom', 'Anton', 'Test'),
            Type\PhpNamespace::make('AdminModules', 'Custom', 'Anton', 'Test', 'Base'),
            Type\PhpNamespace::make('Crud', 'Custom', 'Anton', 'Test'),
            Type\PhpNamespace::make('Crud', 'Custom', 'Anton', 'Test', 'TestModuleQuery'),
            Type\PhpNamespace::make('Crud', 'Custom', 'Anton', 'Test', 'TestModule'),
            Type\PhpNamespace::make('Crud', 'Custom', 'Anton', 'Test', 'TestModule'),
        );
        $oBaseGenerator = new BaseGenerator($oConfig, new ConsoleOutput());
        echo $oBaseGenerator->generate();
    }
}
