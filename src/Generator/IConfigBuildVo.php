<?php

namespace Generator;

use Hurah\Types\Type\DnsName;
use Hurah\Types\Type\PhpNamespace;
use Hurah\Types\Type\SystemId;

interface IConfigBuildVo extends IBuildFolderOwner
{
    function getSystemId(): SystemId;
    function getNamespace(): PhpNamespace;
    function getDomainName(string $sEnv = null): DnsName;
    function getAdminDomainName(): DnsName;
}
