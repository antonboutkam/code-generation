<?php
namespace Generator\Domain;

use Generator\Generators\System\Helper\Skeleton;
use Hurah\Types\Type\Path;
use Core\Utils;
use Generator\IDomainStructureConfig;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class DirectoryStructure
{
    private OutputInterface $output;

    private function output(string $sMessage)
    {
        $this->output->writeln($sMessage);
    }


    private IDomainStructureConfig $oDomainStructureConfig;

    function __construct(IDomainStructureConfig $oDomainStructureConfig)
    {
        $this->oDomainStructureConfig = $oDomainStructureConfig;
        $this->output = new ConsoleOutput();

    }

    function create()
    {

        $oDirectoryStructure = new \Hi\Helpers\DirectoryStructure();


        $sDestinationDirectory =  $this->oDomainStructureConfig->getInstallDir('live');
        $sSourceDirectory = Utils::makePath($oDirectoryStructure->getSystemDir(true), 'build', '_skel', 'domain_structure', "tree");

        $aData = [
            'system' => $this->oDomainStructureConfig
        ];

        Skeleton::copyParseStructure(new Path($sSourceDirectory), new Path($sDestinationDirectory), $aData);
    }

}