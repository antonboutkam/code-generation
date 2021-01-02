<?php
namespace Generator\Generators\Admin\Module\Menu\Item;

use Hurah\Types\Type\Icon;
use Hurah\Types\Type\PlainText;
use Hurah\Types\Type\Url;

interface ConfigMenuItemGeneratorInterface
{
	static function create(PlainText $title, Icon $icon, Url $url): self;


	public function getTitle(): PlainText;


	public function getIcon(): Icon;


	public function getUrl(): Url;
}