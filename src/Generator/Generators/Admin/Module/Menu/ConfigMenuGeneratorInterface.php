<?php
namespace Generator\Generators\Admin\Module\Menu;

use Hurah\Types\Type\Icon;
use Hurah\Types\Type\PlainText;

interface ConfigMenuGeneratorInterface
{
	static function create(PlainText $title, Icon $Icon, array $menuItems): self;


	public function getTitle(): PlainText;


	public function getIcon(): Icon;


	public function getMenuItems(): array;
}