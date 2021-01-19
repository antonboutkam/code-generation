<?php
namespace Generator\Generators\Admin\Module\Menu;

use Generator\Generators\Admin\Module\Menu\Item\ConfigMenuItemGeneratorInterface;
use Hurah\Types\Type\Icon;
use Hurah\Types\Type\PlainText;

final class ConfigMenuGenerator implements ConfigMenuGeneratorInterface
{
	private PlainText $title;
	private Icon $Icon;
	private array $menuItems;


	public function getTitle(): PlainText
	{
		return $this->title;
	}

    /**
     * @return ConfigMenuItemGeneratorInterface[]
     */
    public function getMenu(): array {
        return $this->menuItems;
    }

	public function getIcon(): Icon
	{
		return $this->Icon;
	}


	public function getMenuItems(): array
	{
		return $this->menuItems;
	}


	public static function create(PlainText $title, Icon $Icon, array $menuItems): self
	{
		$oMenuGenerator = new self();
		$oMenuGenerator->title = $title;
		$oMenuGenerator->Icon = $Icon;
		$oMenuGenerator->menuItems = $menuItems;
		return $oMenuGenerator;
	}
}