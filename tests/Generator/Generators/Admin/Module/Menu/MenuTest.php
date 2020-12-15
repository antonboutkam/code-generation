<?php

namespace Test\Generator\Generators\Admin\Module\Menu;

use Generator\Admin\Module\Menu\Item\ItemConfig;
use Hurah\Types\Exception\InvalidArgumentException;
use Hurah\Types\Type\Icon;
use Hurah\Types\Type\PlainText;
use Hurah\Types\Type\Url;
use PHPUnit\Framework\TestCase;
use Generator\Admin\Module\Menu\ConfigMenu;
use Generator\Admin\Module\Menu\Menu;
use Symfony\Component\Console\Output\ConsoleOutput;

class MenuTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function testGenerate()
    {
        $output = new ConsoleOutput();
        $oItemConfig = ItemConfig::create(new PlainText('Test'), new Url('/bla'), new Icon('edit'));

        $oConfigMenu = ConfigMenu::create(
            new PlainText('Finance'),
            new Icon('edit'),
            [
                $oItemConfig,
                $oItemConfig,
                $oItemConfig
            ]
        );

        $oMenu = new Menu($oConfigMenu, $output);
        $sGenerated = $oMenu->generate();
        echo $sGenerated;
    }
}
