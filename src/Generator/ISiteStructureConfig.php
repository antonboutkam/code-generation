<?php
namespace Generator;

use Hurah\Types\Type\DnsName;
use Hurah\Types\Type\Path;
use Hurah\Types\Type\PhpNamespace;
use Hurah\Types\Type\SystemId;

interface ISiteStructureConfig extends IBaseBuildVo, IComposerConfig
{
    public function getDomainName(string $sEnv):DnsName;
    public function getSystemId():SystemId;
    public function getNamespace():PhpNamespace;
    public function getInstallDir(string $sEnv):Path;
}
