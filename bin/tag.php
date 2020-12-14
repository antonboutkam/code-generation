<?php

use Hurah\Types\Type\File as FileAlias;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Finder\Finder;
use Utils\Text;

require '../vendor/autoload.php';

$oFinder = new Finder();

// find all files in the current directory
$sBasePath = "../src/Generator/Generators";
$oFinder->files()->in("../src/Generator/Generators")->name('*.php');

$output = new ConsoleOutput();

if ($oFinder->hasResults()) {
    foreach ($oFinder as $oSplFile) {
        $oFile = FileAlias::fromSplFileInfo($oSplFile);
        $sSearch = '<?php';
        $sTag = '@unfixed';
        $sReplace = Text::concat($sSearch, '/**', '* ' . $sTag, '**/');

        if ($oFile->contents()->contains($sTag)) {
            $output->writeln("SKipping {$oFile}");
        }
        else
        {
            $output->writeln("Replacing in {$oFile}");
            $oFile->replaceInContent($sSearch, $sReplace);
        }
    }
}
