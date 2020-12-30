<?php
namespace Generator\Generators\Admin\Module\Menu\Item;

use Hurah\Types\Type\Url;
use Hurah\Types\Type\Icon;
use Hurah\Types\Type\PlainText;

interface ItemConfigInterface {

    public function getIcon(): Icon;
    public function getTitle(): PlainText;
    public function getUrl(): Url;
}
