<?php

namespace Generator\Admin\Module\Menu;

use Helper\Schema\Module;
use Hurah\Types\Type\Icon;
use Hurah\Types\Type\Path;
use Hurah\Types\Type\PlainText;
use Generator\Admin\Module\Menu\Item\ItemConfig;

final class ConfigMenu implements MenuConfigInterface {

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

    public static function create(PlainText $oTitle, Icon $oIcon, array $aMenuItemConfigs):self
    {
        $oConfigMenu = new self();
        $oConfigMenu->oIcon = $oIcon;
        $oConfigMenu->oTitle = $oTitle;
        $oConfigMenu->aMenuItemConfigs = $aMenuItemConfigs;
        foreach ($oModule->getTables() as $oTable)
        {
            $oConfigMenu->aMenuItemConfigs[] = ItemConfig::fromTable($oTable);
        }
        return $oConfigMenu;

    }
    public static function fromModule(Module $oModule):self
    {
        $aMenuItemConfigs = [];
        foreach ($oModule->getTables() as $oTable)
        {
            $aMenuItemConfigs[] = ItemConfig::fromTable($oTable);
        }
        return self::create(
            new Icon($oModule->getIcon()),
            new PlainText($oModule->getTitle()),
            $aMenuItemConfigs
        );
    }

}
