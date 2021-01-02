<?php

namespace Generator\Generators\Admin\Module\Menu\Item;

use Hurah\Types\Type\Icon;
use Hurah\Types\Type\PlainText;
use Hurah\Types\Type\Url;

final class ConfigMenuItemGenerator implements ConfigMenuItemGeneratorInterface
{
    private PlainText $title;
    private Icon $icon;
    private Url $url;

    public static function create(PlainText $title, Icon $icon, Url $url): self
    {
        $oMenuItemGenerator = new self();
        $oMenuItemGenerator->title = $title;
        $oMenuItemGenerator->icon = $icon;
        $oMenuItemGenerator->url = $url;
        return $oMenuItemGenerator;
    }

    public function getTitle(): PlainText
    {
        return $this->title;
    }

    public function getIcon(): Icon
    {
        return $this->icon;
    }

    public function getUrl(): Url
    {
        return $this->url;
    }
}