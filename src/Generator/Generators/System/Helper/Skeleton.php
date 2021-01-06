<?php
namespace Generator\Generators\System\Helper;

use Hurah\Types\Type\Path;
use Core\InlineTemplate;
use Core\Utils;
use Hi\Helpers\DirectoryStructure;
use DirectoryIterator;

final class Skeleton
{
    static function copyParseStructure(Path $sSourceDirectory, Path $oDestinationDirectory, $aData) : void
    {
        $oSourceFileDirectoryIterator = new DirectoryIterator($sSourceDirectory);

        foreach($oSourceFileDirectoryIterator as $oSourceItem)
        {
            if($oSourceItem->isDot())
            {
                continue;
            }

            $sDestinationFilename = Utils::makePath($oDestinationDirectory, $oSourceItem->getBasename());
            if($oSourceItem->isDir())
            {
                echo "Creating directory $sDestinationFilename " . PHP_EOL;
                Utils::makeDir($sDestinationFilename);
                self::copyParseStructure(new Path($oSourceItem->getPathname()), new Path($sDestinationFilename), $aData);
                chmod($sDestinationFilename, 0777);
            }
            else if($oSourceItem->isFile())
            {
                echo "Creating file $sDestinationFilename " . PHP_EOL;
                $sTemplateHtml = file_get_contents($oSourceItem->getPathname());

                // Skip parsing binary files
                if(ctype_print($sTemplateHtml))
                {
                    $sTemplateHtml =  InlineTemplate::parse($sTemplateHtml, $aData);
                }
                Utils::makeDir($oDestinationDirectory);
                Utils::filePutContents($sDestinationFilename, $sTemplateHtml, 0777);

            }
        }
    }


    static function parseTemplate(?string $sFolder, string $sFile, $aVars):string
    {
        $oDirectoryStructure = new DirectoryStructure();
        if($sFolder)
        {
            $sSkeletonFile = Utils::makePath($oDirectoryStructure->getSystemDir(true), 'build', '_skel', $sFolder, "$sFile.twig");
        }
        else
        {
            $sSkeletonFile = Utils::makePath($oDirectoryStructure->getSystemDir(true), 'build', '_skel', "$sFile.twig");
        }

        $sTemplateHtml = file_get_contents($sSkeletonFile);
        return InlineTemplate::parse($sTemplateHtml, $aVars);
    }
}
