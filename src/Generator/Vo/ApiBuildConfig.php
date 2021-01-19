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
use Generator\ICompleteApi;
use Hi\Helpers\DirectoryStructure;

class ApiBuildConfig implements ICompleteApi
{
    use ComposerCommonTrait;

    public function getNamespace(): PhpNamespace
    {
        return NamespaceHelper::general($this->vendor, $this->service_name);
    }

    public function getDomainName(string $sEnvironment): DnsName
    {
        [$sTld] = explode('.', $this->domain_name);
        if ($sEnvironment === 'dev') {
            return new DnsName("$sTld.innovatieapp.nl");
        } else {
            if ($sEnvironment === 'test') {
                $sDomainName = str_replace("$sTld.", "$sTld.test.", $this->domain_name);
                return new DnsName("$sDomainName");
            }
        }
        return new DnsName($this->domain_name);
    }

    public function getAutoload(): array
    {
        $sNamespace = (string)$this->getApiNamespace();
        return [
            "psr-4" => [
                "$sNamespace\\" => "modules",
            ],
        ];
    }

    public function getRequire(): DependencyList
    {
        $sPackageType = (string)$this->getPackageType();
        $sDomainPackageName = "{$this->vendor}/{$sPackageType}-{$this->service_name}";

        return new DependencyList([
            new Dependency([
                "novum/innovation-app-core",
                "*",
            ]),
            new Dependency([
                $sDomainPackageName,
                "dev-master",
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

    public function getSystemId(): SystemId
    {
        return new SystemId([
            $this->vendor,
            $this->service_name,
        ]);
    }

    public function getPackageName(): string
    {
        $sPackageType = (string)$this->getPackageType();
        return "{$this->vendor}/$sPackageType-{$this->service_name}";
    }

    public function getInstallDir($sEnv = null): Path
    {
        $oDirectoryStructure = new DirectoryStructure();
        $sDomainName = $this->getApiDomainName($sEnv);
        return new Path(Utils::makePath($oDirectoryStructure->getSystemRoot(), 'public', $sDomainName));
    }

    public function getApiDomainName(string $sEnvironment): DnsName
    {
        return $this->getDomainName($sEnvironment)->createSubdomain('api');
    }

    public function getApiNamespace(): PhpNamespace
    {
        return NamespaceHelper::api($this->vendor, $this->service_name);
    }

    public function getPackageType(): PluginType
    {
        return new PluginType(PluginType::API);
    }

    public function getExtra(): Extra
    {
        return new Extra([
            "install_dir" => (string)$this->getApiDomainName('live'),
        ]);
    }
}
