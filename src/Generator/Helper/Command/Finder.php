<?php

namespace Generator\Helper\Command;

use Exception;
use Hurah\Types\Exception\NullPointerException;
use Hurah\Types\Type\Path;
use Hurah\Types\Type\PhpNamespace;
use ReflectionClass;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder as ComponentFinder;

class Finder {

    private InputInterface $input;
    private OutputInterface $output;

    function __construct(InputInterface $input, OutputInterface $output) {
        $this->input = $input;
        $this->output = $output;
    }
    public function getByName($sName):Command
    {
        $oCommandCollection = $this->find();
        foreach($oCommandCollection as $oCommand)
        {
            if($oCommand->getName() == $sName)
            {
                return $oCommand;
            }
        }
        throw new NullPointerException("Command {$sName} not found");
    }

    public function find(): Collection {

        $oCommandCollection = new Collection();

        $oPossibleFiles = $this->findFiles();

        if ($oPossibleFiles->hasResults()) {
            foreach ($oPossibleFiles as $oFile) {
                $oClassName = $this->fileNameToPsr4($oFile);

                if ($this->isCliCommand($oClassName)) {

                    $oCommand = $oClassName->getConstructed();
                    $oCommandCollection->add($oCommand);
                }
            }
        }
        return $oCommandCollection;
    }

    private function isCliCommand(PhpNamespace $oClassName) {
        try {
            $oReflector = new ReflectionClass("{$oClassName}");

            if ($oReflector->isSubclassOf(Command::class)) {
                return true;
            }
        } catch (Exception $e) {
            $this->output->writeln("<error>An error occurred: {$e->getMessage()}</error>");
        }
        return false;
    }

    private function fileNameToPsr4(SplFileInfo $oFile): PhpNamespace {
        $fileNameWithExtension = $oFile->getRelativePathname();
        $sNamespaceSemiFinished = 'Generator\\Generators\\' . str_replace('/', '\\', $fileNameWithExtension);
        $sClassName = str_replace('.php', '', $sNamespaceSemiFinished);
        return new PhpNamespace($sClassName);
    }

    private function findFiles(): ComponentFinder {
        $oCommandDir = Path::make(__DIR__)->dirname(4)->extend('src', 'Generator', 'Generators');

        $oFinder = new ComponentFinder();
        $oFinder->files();
        $oFinder->in("{$oCommandDir}");
        $oFinder->name('*.php');
        $oFinder->notName('*Interface*');
        return $oFinder;
    }
}
