<?php

namespace Test\Generator\Generators\Admin\Module\Menu;

use Generator\Generators\Admin\Module\Menu\ConfigMenuGenerator;
use Generator\Generators\Admin\Module\Menu\Item\ConfigMenuItemGenerator;
use Generator\Generators\Admin\Module\Menu\MenuGenerator;
use Hurah\Types\Exception\InvalidArgumentException;
use Hurah\Types\Type\Icon;
use Hurah\Types\Type\PlainText;
use Hurah\Types\Type\Url;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class MenuGeneratorTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function testGenerate()
    {
        $input = new ArrayInput([]);
        $output = new ConsoleOutput();
        $oItemConfig = ConfigMenuItemGenerator::create(new PlainText('Test'), new Icon('edit'), new Url('/bla'));

        $oConfigMenu = ConfigMenuGenerator::create(
            new PlainText('Finance'),
            new Icon('edit'),
            [
                $oItemConfig,
                $oItemConfig,
                $oItemConfig
            ]
        );

        $oMenu = new MenuGenerator($oConfigMenu, $input, $output);
        $sGenerated = $oMenu->generate();

        $this->assertStringContainsString('<span class="fa fa-edit"></span>', $sGenerated);
    }
}