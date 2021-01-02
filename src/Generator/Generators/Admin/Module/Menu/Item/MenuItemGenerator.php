<?php

namespace Generator\Generators\Admin\Module\Menu\Item;

use Generator\Generators\GeneratorInterface;
use Hurah\Types\Type\Html\MenuItem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MenuItemGenerator implements GeneratorInterface
{
    private ConfigMenuItemGeneratorInterface $config;
    private InputInterface $input;
    private OutputInterface $output;


    public function __construct(ConfigMenuItemGeneratorInterface $config, InputInterface $input, OutputInterface $output)
    {
        $this->config = $config;
        $this->input = $input;
        $this->output = $output;
    }

    public function generate(): MenuItem
    {
        // Tells Twig that this string is translatable.
        $sTitle = "{{ {$this->config->getTitle()}|translate }}";
        return MenuItem::create($this->config->getUrl(), $this->config->getIcon(), $sTitle);
    }
}