<?php
namespace Generator;

use Hurah\Types\Type\DnsName;
use Hurah\Types\Type\PhpNamespace;
use Hurah\Types\Type\SystemId;

interface ISiteJsonConfig extends ISiteStructureConfig {

    public function getSystemId():SystemId;
    public function getDomainName(string $sEnvironment):DnsName;
    public function getNamespace():PhpNamespace;
}
