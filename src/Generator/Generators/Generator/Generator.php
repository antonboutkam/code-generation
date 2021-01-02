<?php
namespace Generator\Generators\Generator;

use Generator\Generators\Generator\Components\CommandGenerator;
use Generator\Generators\Generator\Components\ConfigClassGenerator;
use Generator\Generators\Generator\Components\ConfigInterfaceGenerator;
use Generator\Generators\Generator\Components\GeneratorGenerator;
use Generator\Generators\Generator\Components\TestGenerator;
use Generator\Generators\GeneratorInterface;
use Hurah\Types\Exception\RuntimeException;
use Hurah\Types\Type\Path;
use Hurah\Types\Type\PhpNamespace;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Generator implements GeneratorInterface{
    private ConfigInterface $config;
    private OutputInterface $output;
    private InputInterface $input;

    public function __construct(ConfigInterface $config, InputInterface $oInput, OutputInterface $oOutput) {
        $this->config = $config;
        $this->output = $oOutput;
        $this->input = $oInput;
    }

    public function generate() {
        $oConfigClassGenerator = new ConfigClassGenerator($this->config, $this->input, $this->output);
        $oInterfaceGenerator = new ConfigInterfaceGenerator($this->config, $this->input, $this->output);
        $oGeneratorGenerator = new GeneratorGenerator($this->config, $this->input, $this->output);
        $oCommandGenerator = new CommandGenerator($this->config, $this->input, $this->output);
        $oTestGenerator = new TestGenerator($this->config, $this->input, $this->output);

        $aResults = [
            [
                'name' => $this->config->getWorkerClassName(),
                'contents' => $oGeneratorGenerator->generate()
            ],
            [
                'name' => $this->config->getConfigInterfaceName(),
                'contents' => $oInterfaceGenerator->generate()
            ],
            [
                'name' => $this->config->getConfigClassName(),
                'contents' => $oConfigClassGenerator->generate()
            ],
            [
                'name' => $this->config->getCommandClassName(),
                'contents' => $oCommandGenerator->generate()
            ],
            [
                'name' => $this->config->getTestClassName(),
                'contents' => $oTestGenerator->generate(),
                'is_test' => true
            ]
        ];

        $sCurrentDirectory = __DIR__;
        $oProjectRoot = Path::make($sCurrentDirectory);
        while (!($oProjectRoot->extend('composer.json'))->exists())
        {
            $oProjectRoot = $oProjectRoot->dirname();
        }

        foreach ($aResults as $aResult)
        {
            $sDir = isset($aResult['is_test']) ? 'tests' : 'src';

            $oPath = $oProjectRoot->extend($sDir);
            $oNamespaceName = PhpNamespace::make("{$aResult['name']}");
            $sDestinationFileName = "{$oNamespaceName->getShortName()}.php";
            $aNamespaceToPathParts = explode('\\', "{$oNamespaceName->reduce(1)}");

            if(isset($aResult['is_test']) && $aResult['is_test'])
            {
                unset($aNamespaceToPathParts[0]);
            }
            $oDestination = $oPath->extend($aNamespaceToPathParts);
            if($oDestination->isFile())
            {
                throw new RuntimeException("Cannot create directory {$oDestination}, file is in the way");
            }
            $this->output->writeln("Creating directory {$oDestination}");
            $oDestination->makeDir();

            $oDestinationFile = $oDestination->extend($sDestinationFileName);

            $this->output->writeln("Writing {$oDestinationFile}");
            $oDestinationFile->write(trim('<?php' . PHP_EOL . $aResult['contents']));
        }
    }
}
