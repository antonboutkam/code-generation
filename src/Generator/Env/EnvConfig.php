<?php
namespace Generator\Env;

use Hurah\Types\Type\SystemId;
use Generator\IEnvConfig;

class EnvConfig implements IEnvConfig {

    private SystemId $oSystemId;

    function __construct(SystemId $oSystemId)
    {
        $this->oSystemId = $oSystemId;
    }

    function getSystemId(): SystemId {
        return $this->oSystemId;
    }

    function getDbUser(): string {
        return EnvHelper::getDbUser((string) $this->getSystemId());
    }

    function getDbHost(): string {
        return EnvHelper::getDbHost();
    }

    function getDbPass(): string {
        return EnvHelper::getDbPass('aaa' . rand(0, 2323223));
    }
}
