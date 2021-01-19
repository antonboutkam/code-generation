<?php
namespace Test\Generator\Generators\Admin\Module\Menu\Item;

use Generator\Generators\Admin\Module\Menu\Item\ConfigMenuItemGenerator;
use Generator\Generators\Admin\Module\Menu\Item\MenuItemGenerator;
use Hurah\Types\Type\Icon;
use Hurah\Types\Type\PlainText;
use Hurah\Types\Type\Url;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class MenuItemGeneratorTest extends TestCase
{
	public function testGenerate(): void
	{
		$oMenuItemGenerator = $this->createMenuItemGenerator();
		$sGenerated = $oMenuItemGenerator->generate();
		$sNeedle = '';
		$this->assertStringContainsString($sNeedle, $sGenerated);
	}


	private function createMenuItemGenerator(): MenuItemGenerator
	{
		$ConfigMenuItemGenerator = ConfigMenuItemGenerator::create(
		new PlainText("fill-me-in"),
		new Icon("fill-me-in"),
		new Url("fill-me-in")
		);
		return new MenuItemGenerator($ConfigMenuItemGenerator, new ArrayInput([]), new ConsoleOutput());
	}
}