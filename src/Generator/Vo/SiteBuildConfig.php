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
use Generator\ICompleteSite;
use Hi\Helpers\DirectoryStructure;

class SiteBuildConfig implements ICompleteSite {
    use ComposerCommonTrait;

    public function getNamespace(): PhpNamespace {
        return NamespaceHelper::general($this->vendor, $this->service_name);
    }

    public function getInstallDir(string $sEnv): Path {
        $oDirectoryStructure = new DirectoryStructure();
        $sDomainName = $this->getDomainName($sEnv);
        return new Path(Utils::makePath($oDirectoryStructure->getSystemRoot(), 'public', $sDomainName));
    }

    public function getAutoload(): array {
        $sNamespace = (string)$this->getNamespace();
        return [
            "psr-4" => [
                "$sNamespace\\" => "modules",
            ],
        ];
    }

    public function getDomainName(string $sEnv): DnsName {
        return new DnsName($this->domain_name);
    }

    public function getPackageName(): string {
        return "{$this->vendor}/site-{$this->service_name}";
    }

    public function getSystemId(): SystemId {
        return new SystemId([
            $this->vendor,
            $this->service_name,
        ]);
    }

    public function getPackageType(): PluginType {
        return new PluginType(PluginType::SITE);
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
        ]);
    }

    public function getExtra(): Extra {
        return new Extra([
            "install_dir" => (string)$this->getDomainName('live'),
        ]);
    }
}
