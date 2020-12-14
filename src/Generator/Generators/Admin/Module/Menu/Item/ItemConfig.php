<?php

namespace Generator\Admin\Module\Menu\Item;

use Generator\Admin\Module\Util;
use Helper\Schema\Table;
use Hurah\Types\Type\Url;
use Hurah\Types\Type\PlainText;
use Hurah\Types\Type\Icon;

final class ItemConfig implements ItemConfigInterface {

    private Icon $oIcon;
    private PlainText $oTitle;
    private Url $oUrl;

    public function getIcon(): Icon {
        return $this->oIcon;
    }

    public function getTitle(): PlainText {
        return $this->oTitle;
    }

    public function getUrl(): Url {
        return $this->oUrl;
    }
    public static function create(PlainText $oTitle, Url $oUrl, Icon $oIcon):ItemConfig
    {
        $oItemConfig = new ItemConfig();
        $oItemConfig->oUrl = $oUrl;
        $oItemConfig->oIcon = $oIcon;
        $oItemConfig->oTitle = $oTitle;
        return $oItemConfig;
    }

    public static function fromTable(Table $oTable):ItemConfig
    {
        $sCustom = (string)$oTable->getDatabase()->getCustom();
        $oModule = $oTable->getModule();
        Util::location()

        if (empty($sCustom)) {
            $sUrl = strtolower('/' . $oModule->getModuleDir() . '/' . $oTable->getName() . '/overview');
        } else {
            $sUrl = '/custom/' . strtolower($sCustom . '/' . $oModule->getModuleDir() . '/' . $oTable->getName() . '/overview');
        }

        return ItemConfig::create(new PlainText($oTable->getTitle()), new Url($sUrl), new Icon('file-text-o'));
    }
}
