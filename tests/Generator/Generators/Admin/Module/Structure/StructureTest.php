<?php

namespace Test\Admin\Module\Structure;

use Generator\Generators\Admin\Module\Structure\Generator;
use Hurah\Types\Type\Path;
use Generator\Generators\Admin\Module\Structure\Config;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\ConsoleOutput;

class StructureTest extends TestCase {

    public function testGenerate() {

        $aSections = [
            'Invoices',
            'Bank',
            'Payments',
            'Messages'
        ];
        $oTempDir = Path::make(sys_get_temp_dir(), 'testing');
        $oConfigStructure = Config::create($oTempDir, $aSections);
        $oStructure = new Generator($oConfigStructure, new ConsoleOutput());
        $oStructure->generate();
        echo "Tempdir is {$oTempDir}";

    }
}
