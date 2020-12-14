<?php

namespace Generator\Admin\Module\Menu;

use Generator\Admin\Module\Config\ItemConfigInterface;
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

    private function getIcon(): Icon {
        return $this->config->getIcon();
    }

    private function getUrl(): Url {
        return $this->config->getUrl();
    }

    private function getTitle(): PlainText {
        return $this->getTitle();
    }

    /**
     * @return string
     * @throws \Hurah\Types\Exception\InvalidArgumentException
     */
    public function generate(): MenuItem {
        // Tells Twig that this string is translatable.
        $sTitle = "{{ {$this->getTitle()}|translate }}";
        return MenuItem::create($this->getUrl(), $this->getIcon(), $sTitle);
    }

}
