<?php

namespace Generator\Generators\Helper;

use Hurah\Types\Type\Path;
use Core\Utils;
use Hi\Helpers\DirectoryStructure;

final class Target
{
    /**
     * Small wrapper function that returns the absolute path to a file that belongs to a domain. If the parent directory
     * does not exist it attempts to create it.
     *
     * @param string $sSystemId
     * @param string $sFileName
     * @return Path
     */
    public static function getDomainFilePath(string $sSystemId, string $sFileName): Path
    {
        $oDirectoryStructure = new DirectoryStructure();
        $sDestinationDirectory = Utils::makePath($oDirectoryStructure->getDomainDir(true), $sSystemId);
        Utils::makeDir($sDestinationDirectory);
        return new Path(Utils::makePath($sDestinationDirectory, $sFileName));
    }

    public static function makeDirectoryStructure(array $aDirectories): void
    {
        foreach ($aDirectories as $sDirectory) {
            if (!is_dir($sDirectory)) {
                echo "Creating directory " . $sDirectory . PHP_EOL;
                mkdir($sDirectory, 0777, true);
            }
        }
    }
}
