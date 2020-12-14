<?php /*** @unfixed* */

namespace Generator\Admin\Module;

use Generator\Admin\Module\Config\ConfigConfigInterface;
use Generator\Admin\Module\Config\ItemConfigInterface;use Generator\BaseGeneratorConfig;
use Generator\InputInterface;
use Helper\Schema\Table;

final class ConfigModule extends BaseGeneratorConfig implements InputInterface, ItemConfigInterface, ConfigConfigInterface {

    protected string $sCustom;
    protected string $sModule;


    public function getCustom(): string {
        return $this->sCustom;
    }

    public function getModule(): string {
        return $this->sModule;
    }

    private function setCustom(string $sCustom): void {
        $this->sCustom = $sCustom;
    }

    private function setModule(string $sModule): void {
        $this->sModule = $sModule;
    }

    public static function fromTable(Table $oTable) {
        $oInput = new ConfigModule();
        $oInput->setCustom((string) $oTable->getDatabase()->getCustom());
        $oInput->setModule((string) $oTable->getModuleName());
    }
}
