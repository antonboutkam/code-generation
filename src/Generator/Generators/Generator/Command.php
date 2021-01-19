<?php

namespace Generator\Generators\Generator;

use Generator\Generators\Generator\Helper\DerivedClassName;
use Generator\Generators\Helper\Command\Question;
use Generator\Generators\Helper\Command\Util;
use Hurah\Types\Exception\InvalidArgumentException;
use Hurah\Types\Exception\RuntimeException;
use Hurah\Types\Type\Path;
use Hurah\Types\Type\Php\IsVoid;
use Hurah\Types\Type\Php\Property;
use Hurah\Types\Type\Php\PropertyCollection;
use Hurah\Types\Type\Php\PropertyLabel;
use Hurah\Types\Type\Php\VarName;
use Hurah\Types\Type\PhpClassName;
use Hurah\Types\Type\PhpNamespace;
use Hurah\Types\Type\PlainText;
use Hurah\Types\Type\TypeType;
use Hurah\Types\Util\TypeTypeFactory;
use ReflectionException;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends BaseCommand
{
    private static bool $bAllArgumentsPassed = false;

    public function configure()
    {
        $this->setName('generators:generator');
        $this->addArgument('name', InputArgument::REQUIRED, 'Command name');
        $this->addArgument('description', InputArgument::REQUIRED, 'Command description');
        $this->addArgument('help', InputArgument::REQUIRED, 'Command help');

        $this->addArgument('psr', InputArgument::REQUIRED, 'Base php namespace');
        $this->addArgument('worker', InputArgument::REQUIRED, 'Base class name');
        $this->addArgument('properties', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Properties and their types');
        $this->addOption('dry-run', 't', InputOption::VALUE_NONE | InputOption::VALUE_OPTIONAL, 'When set will not generate anything');

        $this->setDescription('Generate code generator classes');
        $this->setHelp('Bootstrap code generation classes + tests');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws InvalidArgumentException
     */
    function initialize(InputInterface $input, OutputInterface $output)
    {

        $bAllArgumentsPassed = true;
        if (!$input->getArgument('name'))
        {
            $bAllArgumentsPassed = false;
            $input->setArgument('name', $this->makeCommandNameSuggestion());
        }
        if (!$input->getArgument('psr'))
        {
            $bAllArgumentsPassed = false;
            $input->setArgument('psr', $this->makeNamespaceSuggestion());
        }
        if (!$input->getArgument('worker'))
        {
            $bAllArgumentsPassed = false;
            $input->setArgument('worker', $this->makeWorkerSuggestion());
        }
        self::$bAllArgumentsPassed = $bAllArgumentsPassed;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws ReflectionException
     */
    function interact(InputInterface $input, OutputInterface $output)
    {

        if (self::$bAllArgumentsPassed)
        {
            return;
        }
        Util::introText('Welcome to the generator generator', $output);

        $oQuestionHelper = new Question($input, $output);

        $name = $oQuestionHelper->ask('Command name', new TypeType(PlainText::class), 'name');
        $description = $oQuestionHelper->ask('Command description', new TypeType(PlainText::class), 'description');
        $help = $oQuestionHelper->ask('Command help', new TypeType(PlainText::class), 'help');
        $psr = $oQuestionHelper->ask('Base php namespace', new TypeType(PhpNamespace::class), 'psr');
        $worker = $oQuestionHelper->ask('Base class name', new TypeType(PhpClassName::class), 'worker');

        $input->setArgument('name', $name);
        $input->setArgument('description', $description);
        $input->setArgument('help', $help);
        $input->setArgument('psr', $psr);
        $input->setArgument('worker', $worker);

        $output->writeln("<info>It is strongly recommended that you define all properties now!</info>");
        $sAddPropertiesQuestion = 'Add the class properties now?';
        $aTypes = TypeTypeFactory::getAll();

        // Is filled later on
        $oPropertyCollection = new PropertyCollection();
        $input->setArgument('properties', $oPropertyCollection);

        while ($sAnswer = $oQuestionHelper->confirm($sAddPropertiesQuestion, null, true))
        {
            $output->writeln("<comment>Please provide the name of the property as a php variable</comment>");
            $output->writeln("<comment>See: </comment>https://www.php-fig.org/psr/psr-12/#43-properties-and-constants");

            $sPropertyName = $oQuestionHelper->ask('Property name', new TypeType(PhpClassName::class));

            $oType = new TypeType(TypeType::class);
            $sPropertyType = $oQuestionHelper->choose(
                'Property type',
                $oType,
                null,
                null,
                $aTypes
            );

            echo "Got property type $sPropertyType" . PHP_EOL;

            $sPropertyLabel = $oQuestionHelper->ask(
                'Property label',
                new TypeType(PlainText::class),
                null,
                null
            );

            $bIsNullable = $oQuestionHelper->confirm(
                'Nullable',
                null,
                false
            );

            $output->writeln("<comment>Default value, needs to be literal. Add quotes when this value is a string</comment>");
            $oDefaultValue = $oQuestionHelper->ask('Default value', new TypeType(PlainText::class));

            $sAddPropertiesQuestion = "Add another property?";

            $oProperty = Property::create(
                new VarName($sPropertyName),
                new PropertyLabel($sPropertyLabel),
                new TypeType($sPropertyType),
                trim("{$oDefaultValue}") ? new PlainText($oDefaultValue) : new IsVoid(),
                $bIsNullable,
            );
            $oPropertyCollection->add($oProperty);
        }
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    function execute(InputInterface $input, OutputInterface $output)
    {
        $aProperties = new PropertyCollection($input->getArgument('properties'));
        $sWorkerClassName = $input->getArgument('worker');
        $oBaseNamespace = new PhpNamespace($input->getArgument("psr"));

        $oWorkerClassName = $oBaseNamespace->extend($sWorkerClassName);
        $oDerivedClassName = new DerivedClassName($sWorkerClassName, $oBaseNamespace);

        $oTestClassName = $oDerivedClassName->makeTestClassName();

        $oConfig = Config::create(
            new PlainText($input->getArgument('name')),
            new PlainText($input->getArgument('description')),
            new PlainText($input->getArgument('help')),
            $oWorkerClassName,
            $oDerivedClassName->makeConfigClassName(),
            $oDerivedClassName->makeCommandClassName(),
            $oDerivedClassName->makeConfigInterfaceName(),
            $oTestClassName,
            $aProperties
        );

        if ($input->getOption('dry-run'))
        {
            $output->writeln("<error>This is a dry run</error>");
        } else
        {
            $oGenerator = new Generator($oConfig, $input, $output);
            $oGenerator->generate();
        }

        return Command::SUCCESS;
    }


    /**
     * @return string
     * @throws InvalidArgumentException
     */
    private function makeCommandNameSuggestion(): string
    {
        $oPath = Path::make(getcwd());
        $oParent = $oPath->dirname(1);
        return strtolower("{$oParent->basename()}:{$oPath->basename()}");
    }

    /**
     * @return PhpNamespace
     * @throws InvalidArgumentException
     */
    private function makeNamespaceSuggestion(): PhpNamespace
    {
        $oPath = Path::make(getcwd());
        $c = 0;
        $oNamespace = new PhpNamespace();
        while (!($oPath->extend('composer.json'))->isFile())
        {
            if(++$c > 7)
            {
                break;
            }
            $sDefault = trim($oPath->basename(), '\\');
            $oNamespace->prepend($sDefault);
            $oPath = $oPath->dirname();
        }
        return $oNamespace->shift();
    }

    /**
     * @return PhpClassName
     * @throws InvalidArgumentException
     */
    private function makeWorkerSuggestion(): PhpClassName
    {
        $oPath = Path::make(getcwd());
        return new PhpClassName("{$oPath->basename()}");
    }
}
