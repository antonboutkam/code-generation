<?php

namespace Generator;

use Hurah\Types\Type\DnsName;
use Hurah\Types\Type\Path;
use Hurah\Types\Type\PhpNamespace;
use Hurah\Types\Type\SystemId;

interface IEnvConfig extends IBuildFolderOwner
{
    function getSystemId(): SystemId;
    function getDbUser():string;
    function getDbHost():string;
    function getDbPass():string;
}
