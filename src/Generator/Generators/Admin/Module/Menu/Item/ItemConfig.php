<?php

namespace Generator\Admin\Module\Menu;

use Generator\Admin\Module\Config\ItemConfigInterface;
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
    public static function fromTable(Table $oTable):ItemConfig
    {
        $sCustom = (string)$oTable->getDatabase()->getCustom();
        $oModule = $oTable->getModule();

        if (empty($sCustom)) {
            $sUrl = strtolower('/' . $oModule->getModuleDir() . '/' . $oTable->getName() . '/overview');
        } else {
            $sUrl = '/custom/' . strtolower($sCustom . '/' . $oModule->getModuleDir() . '/' . $oTable->getName() . '/overview');
        }
        $oItemConfig = new ItemConfig();
        $oItemConfig->oUrl = new Url($sUrl);
        $oItemConfig->oIcon = new Icon('file-text-o');
        $oItemConfig->oTitle = new PlainText($oTable->getTitle());
        return $oItemConfig;
    }
}
