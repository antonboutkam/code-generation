<?php

namespace Generator\Generators\Admin\Module\Menu;

use Generator\Generators\Admin\Module\Menu\Item\ItemConfigInterface;
use Generator\Generators\Admin\Module\Menu\Item\Item;
use Generator\Generators\GeneratorInterface;
use Hurah\Types\Exception\InvalidArgumentException;
use Hurah\Types\Type\Html\IElementizable;
use Symfony\Component\Console\Output\OutputInterface;
use Hurah\Types\Type\Html\Element;
use Hurah\Types\Type\Html\Link;
use Hurah\Types\Type\Path;
use Hurah\Types\Type\PlainText;
use Hurah\Types\Type\Url;

final class Menu implements IElementizable, GeneratorInterface {
    private MenuConfigInterface $config;
    private OutputInterface $output;

    public function __construct(MenuConfigInterface $config, OutputInterface $oOutput) {
        $this->config = $config;
        $this->output = $oOutput;
    }

    protected function location(): Path {
        return $this->config->location();
    }


    /**
     * @return ItemConfigInterface[]
     */
    private function getMenu(): array {
        return $this->config->getMenu();
    }

    private function hasSubmenu(): bool {
        return count($this->getMenu()) > 0;
    }

    private function getTitle(): PlainText {
        return $this->config->getTitle();
    }

    private function getTranslatingTitle(): PlainText {
        return new PlainText('{{ ' . $this->getTitle() . '|translate }}');
    }

    /**
     * @return Element
     * @throws InvalidArgumentException
     */
    public function toElement():Element
    {
        $oElement = new Element('li');
        if (!$this->hasSubmenu()) {
            return $oElement;
        }

        $oElement
            ->addChild(
                Link::create(new Url('#'))
                    ->addClass('accordion-toggle {{ menu_state }}')
                    ->setId('module_{{ module_name }}')
                    ->addChild($this->config->getIcon())
                        ->addChild(Element::create('span')
                            ->addClass('sidebar-title')
                            ->addHtml($this->getTranslatingTitle())
                        )
                    ->addChild(
                        Element::create('span')
                        ->addClass('caret'))
                    );

        foreach ($this->getMenu() as $oItemConfig) {
            $oItem = new Item($oItemConfig, $this->output);
            $oElement->addChild($oItem->generate());
        }
        return $oElement;
    }
    /**
     * @return string
     * @throws InvalidArgumentException
     */
    public function generate(): string {

        return "{$this->toElement()}";
    }
}
