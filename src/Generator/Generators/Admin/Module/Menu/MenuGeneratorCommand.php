<?php
namespace Generator\Generators\Admin\Module\Menu;

use Exception;
use Generator\Generators\Helper\Command\Question;
use Generator\Generators\Helper\Command\Util;
use Hurah\Types\Exception\InvalidArgumentException;
use Hurah\Types\Type\Icon;
use Hurah\Types\Type\PlainText;
use Hurah\Types\Type\Primitive\PrimitiveArray;
use Hurah\Types\Type\TypeType;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class MenuGeneratorCommand extends BaseCommand
{
	public static bool $bAllArgumentsPassed = false;


	public function configure(): void
	{
		$this->setName('module:menu');
		$this->setDescription('Adds a new menu item');

		$this->addArgument('title', InputArgument::REQUIRED, 'Title');
		$this->addArgument('Icon', InputArgument::REQUIRED, 'Icon');
		$this->addArgument('menuItems', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Menu items');
		$this->addOption('dry-run', 't', InputOption::VALUE_NONE | InputOption::VALUE_OPTIONAL, 'When set will not generate anything');
		$this->setHelp('@todo');
	}


	public function initialize(InputInterface $input, OutputInterface $output): void
	{
		$bAllArgumentsPassed = true;
		if (!$input->getArgument('title'))
		{
		   $bAllArgumentsPassed = false;
		   $input->setArgument('title', '');
		}
		if (!$input->getArgument('Icon'))
		{
		   $bAllArgumentsPassed = false;
		   $input->setArgument('Icon', '');
		}
		if (!$input->getArgument('menuItems'))
		{
		   $bAllArgumentsPassed = false;
		   $input->setArgument('menuItems', '');
		}
		self::$bAllArgumentsPassed = $bAllArgumentsPassed;
	}


	public function interact(InputInterface $input, OutputInterface $output): void
	{
		if (self::$bAllArgumentsPassed)
		{
		   return;
		}

		Util::introText('Welcome to the Adds a new menu item', $output);

		$oQuestionHelper = new Question($input, $output);

		$title = $oQuestionHelper->ask('Title', new TypeType(PlainText::class), 'title');
		$input->setArgument('title', $title);

		$Icon = $oQuestionHelper->ask('Icon', new TypeType(Icon::class), 'Icon');
		$input->setArgument('Icon', $Icon);

		$menuItems = $oQuestionHelper->ask('Menu items', new TypeType(PrimitiveArray::class), 'menuItems');
		$input->setArgument('menuItems', $menuItems);
	}


	/**
	 * @throws InvalidArgumentException
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int
	 */
	public function execute(InputInterface $input, OutputInterface $output): int
	{
		$oConfig = ConfigMenuGenerator::create(
		  new PlainText($input->getArgument('title')),
		  new Icon($input->getArgument('Icon')),
		$input->getArgument('menuItems')
		);

		if ($input->getOption('dry-run'))
		{
		    $output->writeln('<error>This is a dry run</error>');
		}
		else
		{
		   try
		   {
		       $oMenuGenerator = new MenuGenerator($oConfig, $input, $output);
		       $oMenuGenerator->generate();
		   }
		   catch(Exception $e)
		   {
		       $output->writeln('<error>' . $e->getMessage() . '</error>');
		   }
		}
		return BaseCommand::SUCCESS;
	}
}