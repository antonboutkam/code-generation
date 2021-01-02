<?php
namespace Generator\Generators\Admin\Module\Menu\Item;

use Exception;
use Generator\Generators\Helper\Command\Question;
use Generator\Generators\Helper\Command\Util;
use Hurah\Types\Exception\InvalidArgumentException;
use Hurah\Types\Type\Icon;
use Hurah\Types\Type\PlainText;
use Hurah\Types\Type\TypeType;
use Hurah\Types\Type\Url;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class MenuItemGeneratorCommand extends BaseCommand
{
	public static bool $bAllArgumentsPassed = false;


	public function configure(): void
	{
		$this->setName('module:menu:item');
		$this->setDescription('Adds a new item to a module menu');

		$this->addArgument('title', InputArgument::REQUIRED, 'Title');
		$this->addArgument('icon', InputArgument::REQUIRED, 'Icon');
		$this->addArgument('url', InputArgument::REQUIRED, 'Url');
		$this->addOption('dry-run', 't', InputOption::VALUE_NONE | InputOption::VALUE_OPTIONAL, 'When set will not generate anything');
		$this->setHelp('Does not modify existing menu\'s, is called as part of a chain of commands');
	}


	public function initialize(InputInterface $input, OutputInterface $output): void
	{
		$bAllArgumentsPassed = true;
		if (!$input->getArgument('title'))
		{
		   $bAllArgumentsPassed = false;
		   $input->setArgument('title', '');
		}
		if (!$input->getArgument('icon'))
		{
		   $bAllArgumentsPassed = false;
		   $input->setArgument('icon', '');
		}
		if (!$input->getArgument('url'))
		{
		   $bAllArgumentsPassed = false;
		   $input->setArgument('url', '');
		}
		self::$bAllArgumentsPassed = $bAllArgumentsPassed;
	}


	public function interact(InputInterface $input, OutputInterface $output): void
	{
		if (self::$bAllArgumentsPassed)
		{
		   return;
		}

		Util::introText('Welcome to the Adds a new item to a module menu', $output);

		$oQuestionHelper = new Question($input, $output);

		$title = $oQuestionHelper->ask('Title', new TypeType(PlainText::class), 'title');
		$input->setArgument('title', $title);

		$icon = $oQuestionHelper->ask('Icon', new TypeType(Icon::class), 'icon');
		$input->setArgument('icon', $icon);

		$url = $oQuestionHelper->ask('Url', new TypeType(Url::class), 'url');
		$input->setArgument('url', $url);
	}


	/**
	 * @throws InvalidArgumentException
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int
	 */
	public function execute(InputInterface $input, OutputInterface $output): int
	{
		$oConfig = ConfigMenuItemGenerator::create(
		  new PlainText($input->getArgument('title')),
		  new Icon($input->getArgument('icon')),
		  new Url($input->getArgument('url'))
		);

		if ($input->getOption('dry-run'))
		{
		    $output->writeln('<error>This is a dry run</error>');
		}
		else
		{
		   try
		   {
		       $oMenuItemGenerator = new MenuItemGenerator($oConfig, $input, $output);
		       $oMenuItemGenerator->generate();
		   }
		   catch(Exception $e)
		   {
		       $output->writeln('<error>' . $e->getMessage() . '</error>');
		   }
		}
		return BaseCommand::SUCCESS;
	}
}