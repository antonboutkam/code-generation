<?php
namespace Generator\Composer;

use Hurah\Types\Type\Composer\IComposerComponent;
use Hurah\Types\Type\Composer\IComposerRootElement;
use Hurah\Types\Type\Path;
use Core\Json\JsonUtils;
use Exception\LogicException;

/**
 * @todo this class is untested, it works in my specific case but certainly does work in all cases.
 * Class Utils
 * @package Generator\Composer
 */
class Utils
{
    private array $aComposerFile;
    private Path $sComposerFileName;

    function __construct(Path $oComposerFile)
    {
        $this->sComposerFileName = $oComposerFile;
        $sComposerFileContents = file_get_contents($oComposerFile);
        $this->aComposerFile = JsonUtils::decode($sComposerFileContents, true) ?? [];
    }

    function add(IComposerComponent $oComposerComponent)
    {
        if($oComposerComponent instanceof IComposerRootElement)
        {
            $aMergeData = $oComposerComponent->toArray();
            if(!isset($this->aComposerFile[$oComposerComponent->getKey()]) && $aMergeData)
            {
                $this->aComposerFile[$oComposerComponent->getKey()] = array_values($aMergeData);
                return true;
            }

            $aMergeSource = $this->aComposerFile[$oComposerComponent->getKey()];

            if(is_string($aMergeSource))
            {
                $this->aComposerFile[$oComposerComponent->getKey()] = $aMergeData;
                return true;
            }

            if(is_iterable($aMergeData))
            {
                $this->aComposerFile[$oComposerComponent->getKey()] = array_values(array_unique(array_merge($this->aComposerFile[$oComposerComponent->getKey()], $aMergeData), SORT_REGULAR));
                return true;
            }
            throw new LogicException("Could not merge in JSON data");
        }
    }

    function render():string
    {
        return json_encode($this->aComposerFile, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
    function save()
    {
        \Core\Utils::filePutContents($this->sComposerFileName, $this->render());
    }
}
