<?php
namespace Generator\Composer;

use Cli\CodeGen\System\Helper\Target;
use Core\Utils;
use Generator\IComposerConfig;
use Hurah\Types\Type\Json;
use Hurah\Types\Type\Path;


final class Builder {
    private IComposerConfig $oComposerConfig;

    function __construct(IComposerConfig $oComposerConfig) {
        $this->oComposerConfig = $oComposerConfig;
    }

    public function toComposerJson():Json {
        return new Json($this->toComposerArray());
    }

    private function toComposerArray():array {

        return [
            "name"              => $this->oComposerConfig->getPackageName(),
            "description"       => (string) $this->oComposerConfig->getDescription(),
            "type"              => 'novum-' . (string) $this->oComposerConfig->getPackageType(),
            "require"           => $this->oComposerConfig->getRequire()->toArray(),
            "license"           => (string) $this->oComposerConfig->getLicense(),
            "prefer-stable"     => (bool) $this->oComposerConfig->getPreferStable(),
            "homepage"          => (string) $this->oComposerConfig->getHomepage(),
            "minimum-stability" => (string) $this->oComposerConfig->getMinimumStability(),
            "keywords"          => $this->oComposerConfig->getKeywords()->toArray(),
            "authors"           => $this->oComposerConfig->getAuthors()->toArray(),
            "extra"             => $this->oComposerConfig->getExtra()->toArray(),
            "autoload"          => $this->oComposerConfig->getAutoload()
        ];
    }

    public function save() {
        $oJson = $this->toComposerJson();
        $oDestination = Utils::makePath($this->oComposerConfig->getInstallDir('live'), 'composer.json');
        echo "Writing " . $oDestination . PHP_EOL;
        file_put_contents($oDestination, $oJson->getValue());
    }
    public function __toString() {
        return $this->toComposerJson()->__toString();
    }
}

