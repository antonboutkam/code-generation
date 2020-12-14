<?php

namespace Test\Generator\Generators\Admin\Module\Config;

use Generator\Admin\Module\Config\Config;
use Generator\Admin\Module\Config\ConfigConfig;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\ConsoleOutput;


class ConfigTest extends TestCase {

    public function testGenerate() {

        $oConfig = new Config(ConfigConfig::create('anton', 'Finance'), new ConsoleOutput());
        $this->assertTrue(strpos($oConfig->generate(), 'Translate::fromCode("Finance");') > 0);
        $this->assertTrue(strpos($oConfig->generate(), '<?php') === 0);
    }
}
