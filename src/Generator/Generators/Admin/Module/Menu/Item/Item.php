<?php

namespace Generator\Admin\Module\Menu\Item;

use Symfony\Component\Console\Output\OutputInterface;
use Hurah\Types\Type\Html\MenuItem;
use Hurah\Types\Type\Icon;
use Hurah\Types\Type\PlainText;
use Hurah\Types\Type\Url;

final class Item {
    public function __construct(ItemConfigInterface $config, OutputInterface $oOutput) {
        $this->config = $config;
        $this->output = $oOutput;
    }

    /**
     * @return string
     * @throws \Hurah\Types\Exception\InvalidArgumentException
     */
    public function generate(): MenuItem {
        // Tells Twig that this string is translatable.
        $sTitle = "{{ {$this->config->getTitle()}|translate }}";
        return MenuItem::create($this->config->getUrl(), $this->config->getIcon(), $sTitle);
    }

}
