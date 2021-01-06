<?php

namespace Generator\Vo;

use Generator\Vo\Util\NamespaceHelper;
use Hurah\Types\Type\Composer\Dependency;
use Hurah\Types\Type\Composer\DependencyList;
use Hurah\Types\Type\Composer\Extra;
use Hurah\Types\Type\DnsName;
use Hurah\Types\Type\Path;
use Hurah\Types\Type\PhpNamespace;
use Hurah\Types\Type\PluginType;
use Hurah\Types\Type\SystemId;
use Core\Utils;
use Generator\Env\EnvHelper;
use Generator\ICompleteDomain;
use Hi\Helpers\DirectoryStructure;

class DomainBuildVo implements ICompleteDomain {
    use ComposerCommonTrait;

    public function getPackageName(): string {
        return "{$this->vendor}/domain-{$this->service_name}";
    }

    public function getAutoload(): array {
        return [];
    }

    public function getRequire(): DependencyList {
        return new DependencyList([
            new Dependency([
                "novum/innovation-app-core",
                "*",
            ]),
            new Dependency([
                "hurah/hurah-installer",
                "*",
            ]),
            new Dependency([
                "PHP",
                ">=7.4",
            ]),
        ]);
    }

    public function getInstallDir($sEnv = null): Path {
        $oDirectoryStructure = new DirectoryStructure();
        return new Path(Utils::makePath($oDirectoryStructure->getDomainDir(true), $this->getSystemId()));
    }

    public function getSystemId(): SystemId {
        return SystemId::make($this->vendor, $this->service_name);
    }

    public function getDbUser(): string {
        return EnvHelper::getDbUser((string)$this->getSystemId());
    }

    public function getDbPass(): string {
        return EnvHelper::getDbPass($this->getDbHost() . $this->getPackageName());
    }

    public function getDbHost(): string {
        return EnvHelper::getDbHost();
    }

    public function getDomainName(string $sEnv = null): DnsName {
        return new DnsName(str_replace('/https\:\/\//', '', $this->domain_name));
    }

    public function getAdminDomainName(): DnsName {
        return new DnsName('admin.' . str_replace('/https\:\/\//', '', $this->domain_name));
    }

    public function getNamespace(): PhpNamespace {
        return NamespaceHelper::general($this->vendor, $this->service_name);
    }

    public function getPackageType(): PluginType {
        return new PluginType(PluginType::DOMAIN);
    }

    public function getExtra(): Extra {
        return new Extra([
            "system_id" => (string)$this->getSystemId(),
        ]);
    }
}
