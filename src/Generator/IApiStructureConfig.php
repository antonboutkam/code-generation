<?php
namespace Generator;

use Hurah\Types\Type\DnsName;
use Hurah\Types\Type\Path;
use Hurah\Types\Type\PhpNamespace;
use Hurah\Types\Type\SystemId;

interface IApiStructureConfig extends IComposerConfig, ICompleteComponent
{
    public function getApiDomainName(string $sEnvironment):DnsName;
    public function getSystemId():SystemId;
    public function getNamespace():PhpNamespace;
    public function getApiNamespace():PhpNamespace;
    public function getInstallDir(string $sEnv):Path;
}
