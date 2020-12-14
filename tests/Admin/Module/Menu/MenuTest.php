<?php

namespace Test\Admin\Module\Menu;

use Generator\Admin\Module\Menu\Item\Item;
use Generator\Admin\Module\Menu\Item\ItemConfig;
use Hurah\Types\Type\Icon;
use Hurah\Types\Type\PlainText;
use PHPUnit\Framework\TestCase;
use Generator\Admin\Module\Menu\ConfigMenu;
use Generator\Admin\Module\Menu\Menu;

class MenuTest extends TestCase
{

    public function testGenerate()
    {
        $oConfigMenu = ConfigMenu::create(
            new PlainText('Finance'),
            new Icon('edit'),
            [
                new Item(ItemConfig::)
            ]
        )
    }
}
