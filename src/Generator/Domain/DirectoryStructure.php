<?php
namespace Generator\Domain;

use Cli\CodeGen\System\Helper\Skeleton;
use Hurah\Types\Type\Path;
use Core\Utils;
use Generator\IDomainStructureConfig;

class DirectoryStructure
{

    private IDomainStructureConfig $oDomainStructureConfig;

    function __construct(IDomainStructureConfig $oDomainStructureConfig)
    {
        $this->oDomainStructureConfig = $oDomainStructureConfig;
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