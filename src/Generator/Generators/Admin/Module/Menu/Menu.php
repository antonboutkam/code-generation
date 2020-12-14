<?php

namespace Generator\Admin\Module\Menu;

use Generator\Admin\Module\Menu\Item\ItemConfigInterface;
use Generator\Admin\Module\Menu\Item\Item;
use Hurah\Types\Exception\InvalidArgumentException;
use Symfony\Component\Console\Output\OutputInterface;
use Hurah\Types\Type\Html\Element;
use Hurah\Types\Type\Html\Link;
use Hurah\Types\Type\Icon;
use Hurah\Types\Type\Path;
use Hurah\Types\Type\PlainText;
use Hurah\Types\Type\Url;

final class Menu {
    private MenuConfigInterface $config;
    private OutputInterface $output;

    public function __construct(MenuConfigInterface $config, OutputInterface $oOutput) {
        $this->config = $config;
        $this->output = $oOutput;
    }

    protected function location(): Path {
        return $this->config->location();
    }

    private function getIcon(): Icon {
        return $this->config->getIcon();
    }

    /**
     * @return ItemConfigInterface[]
     */
    private function getMenu(): array {
        return $this->config->getMenu();
    }

    private function hasSubmenu(): bool {
        return $this->hasSubmenu();
    }

    private function getTitle(): PlainText {
        return $this->getTitle();
    }

    private function getTranslatingTitle(): PlainText {
        return new PlainText('{{ ' . $this->getTitle() . '|translate }}');
    }

    /**
     * @return string
     * @throws InvalidArgumentException
     */
    public function generate(): string {
        if (!$this->hasSubmenu()) {
            return '';
        }

        $oElement = (new Element('li'))->addChild(Link::create(new Url('#'))->addClass('accordion-toggle {{ menu_state }}')->setId('module_{{ module_name }}')->addChild($this->getIcon())->addChild(Element::create('span')->addClass('sidebar-title')->addHtml($this->getTranslatingTitle()))->addChild(Element::create('span')->addClass('caret')));

        foreach ($this->getMenu() as $oItemConfig) {
            $oItem = new Item($oItemConfig, $this->output);
            $oElement->addChild($oItem->generate());
        }
        return $oElement;
    }
}
