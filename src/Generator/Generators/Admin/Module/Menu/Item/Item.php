<?php

namespace Generator\Generators\Admin\Module\Menu\Item;

use Hurah\Types\Exception\InvalidArgumentException;
use Hurah\Types\Type\Html\MenuItem;
use Symfony\Component\Console\Output\OutputInterface;

final class Item
{
    public function __construct(ItemConfigInterface $config, OutputInterface $oOutput) {
        $this->config = $config;
        $this->output = $oOutput;
    }

    /**
     * @return string
     * @throws InvalidArgumentException
     */
    public function generate(): MenuItem {
        // Tells Twig that this string is translatable.
        $sTitle = "{{ {$this->config->getTitle()}|translate }}";
        return MenuItem::create($this->config->getUrl(), $this->config->getIcon(), $sTitle);
    }

}
