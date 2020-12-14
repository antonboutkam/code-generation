<?php

namespace Generator\Admin\Module\Config;

use Core\DataType\PlainText;
use Generator\Admin\Module\Menu\Item;
use Generator\Admin\Module\Menu\ItemConfig;
use Helper\Schema\Module;
use Hurah\Types\Type\Html\MenuItemCollection;
use Hurah\Types\Type\Icon;
use Hurah\Types\Type\Path;

class ConfigMenu implements MenuConfigInterface {

    private Icon $oIcon;
    private PlainText $oTitle;
    private array $aMenuItemConfigs = [];


    public function getIcon(): Icon {
        return $this->oIcon;
    }

    public function hasSubmenu(): bool {
        return count($this->aMenuItemConfigs) > 0;
    }

    public function getTitle(): PlainText {
        return $this->oTitle;
    }

    /**
     * @return ItemConfig[]
     */
    public function getMenu(): array {
        return $this->aMenuItemConfigs;
    }

    public function location(): Path {
        // TODO: Implement location() method.
    }


    public static function fromModule(Module $oModule):ConfigMenu
    {
        $oConfigMenu = new self();
        $oConfigMenu->oIcon = new Icon($oModule->getIcon());
        $oConfigMenu->oTitle = new PlainText($oModule->getTitle());

        foreach ($oModule->getTables() as $oTable)
        {
            $oConfigMenu->aMenuItemConfigs[] = ItemConfig::fromTable($oTable);
        }
        return $oConfigMenu;
    }

}
