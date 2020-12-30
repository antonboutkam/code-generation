<?php

namespace Test\Admin\Module\Menu\Item;

use Generator\Generators\Admin\Module\Menu\Item\Item;
use Generator\Generators\Admin\Module\Menu\Item\ItemConfig;
use Hurah\Types\Type\Icon;
use Hurah\Types\Type\PlainText;
use Hurah\Types\Type\Url;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\ConsoleOutput;

class ItemTest extends TestCase {

    public function testGenerate() {

        $oItemConfig = ItemConfig::create(new PlainText('Testing'), new Url('/xx'), new Icon('edit'));
        $oItem = new Item($oItemConfig, new ConsoleOutput());
        $this->assertStringContainsString('xx', "{$oItem->generate()}");
    }
}
