<?php

namespace Test\Generator\Generators\Admin\Module\Config;

use Generator\Generators\Admin\Module\Config\Config;
use Generator\Generators\Admin\Module\Config\ConfigConfig;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;


class ConfigTest extends TestCase {

    public function testGenerate() {

        $oInput = new ArrayInput([]);

        $oConfig = new Config(ConfigConfig::create('anton', 'Finance'), $oInput, new ConsoleOutput());
        $this->assertTrue(strpos($oConfig->generate(), 'Translate::fromCode("Finance");') > 0);
        $this->assertTrue(strpos($oConfig->generate(), '<?php') === 0);

        print_r(parse_url('/bla/die/da?x=y'));
    }
}
