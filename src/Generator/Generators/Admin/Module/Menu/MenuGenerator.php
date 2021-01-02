<?php
namespace Generator\Generators\Admin\Module\Menu;

use Generator\Generators\Admin\Module\Menu\Item\ConfigMenuItemGeneratorInterface;
use Generator\Generators\Admin\Module\Menu\Item\MenuItemGenerator;
use Generator\Generators\GeneratorInterface;
use Hurah\Types\Exception\InvalidArgumentException;
use Hurah\Types\Type\Html\Element;
use Hurah\Types\Type\Html\IElementizable;
use Hurah\Types\Type\Html\Link;
use Hurah\Types\Type\PlainText;
use Hurah\Types\Type\Url;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MenuGenerator implements GeneratorInterface, IElementizable
{
	private ConfigMenuGeneratorInterface $config;
	private InputInterface $input;
	private OutputInterface $output;


	public function __construct(ConfigMenuGeneratorInterface $config, InputInterface $input, OutputInterface $output)
	{
		$this->config = $config;
		$this->input = $input;
		$this->output = $output;
	}
    /**
     * @return ConfigMenuItemGeneratorInterface[]
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
            $oItem = new MenuItemGenerator($oItemConfig, $this->input, $this->output);
            $oElement->addChild($oItem->generate());
        }
        return $oElement;
    }
    /**
     * @return string
     * @throws InvalidArgumentException
     */
    public function generate(): PlainText {

        return new PlainText("{$this->toElement()}");
    }

}