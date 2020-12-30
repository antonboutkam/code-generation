<?php

namespace Test\Generator\Generators\Admin\Module\Controller\Overview;

use Generator\Generators\Admin\Module\Controller\Overview\BaseGenerator;
use Hurah\Types\Type;

use Generator\Generators\Admin\Module\Controller\Overview\ConfigGenerator;
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
            Type\PhpNamespace::make('Model', 'Custom', 'Anton', 'Test', 'TestModuleQuery'),
            Type\PhpNamespace::make('Crud', 'Custom', 'Anton', 'Test', 'TestModule'),
            Type\PhpNamespace::make('Model', 'Custom', 'Anton', 'Test', 'TestModule'),
        );
        $oBaseGenerator = new BaseGenerator($oConfig, new ConsoleOutput());
        $sGenerated = $oBaseGenerator->generate();

        $this->assertTrue(strpos($sGenerated, '<?php') === 0);
        $this->assertTrue(strpos($sGenerated, 'extends GenericOverviewController') > 0);
        $this->assertTrue(strpos($sGenerated, 'class OverviewController') > 0);
        $this->assertTrue(substr_count($sGenerated, '{') === substr_count($sGenerated, '}'));
    }
}
