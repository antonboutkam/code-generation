<?php

namespace Generator\Vo;

use Hurah\Types\Type\Composer\AuthorList;
use Hurah\Types\Type\Composer\License;
use Hurah\Types\Type\Composer\ServiceName;
use Hurah\Types\Type\Composer\Stability;
use Hurah\Types\Type\Composer\Vendor;
use Hurah\Types\Type\DnsName;
use Hurah\Types\Type\KeywordList;
use Hurah\Types\Type\Url;

trait ComposerCommonTrait
{
    protected Vendor $vendor;
    protected ServiceName $service_name;
    protected DnsName $domain_name;
    protected Url $homepage;
    protected string $description;
    protected License $license;
    protected Stability $stability;
    protected AuthorList $authors;
    protected KeywordList $keywords;
    protected bool $prefer_stable;

    /**
     * DomainBuildVo constructor.
     * @param string $vendor example: novum
     * @param string $service_name example: svb
     * @param string $domain_name example: svb.demo.novum.nu
     * @param string $homepage example: https://docs.demo.novum.nu
     * @param string $description example: "Fake endpoint of the Sociale verzekeringsbank"
     * @param string $license example: MIT
     * @param string $stability example: dev
     * @param array $author example: [email : "anton@novum.nu, name" : "Anton Boutkam"]
     * @param array $keywords example: api, novum, innovatie-lab
     * @param bool $prefer_stable
     */
    function __construct(string $vendor, string $service_name, string $domain_name, string $homepage, string $description, string $license, string $stability, array $authors, array $keywords, bool $prefer_stable = true)
    {
        $this->vendor = new Vendor($vendor);
        $this->service_name = new ServiceName($service_name);
        $this->domain_name = new DnsName($domain_name);
        $this->homepage = new Url($homepage);
        $this->description = $description;
        $this->license = new License($license);
        $this->stability = new Stability($stability);
        $this->authors = AuthorList::fromArray($authors);
        $this->keywords = KeywordList::fromArray($keywords);
        $this->prefer_stable = $prefer_stable;
    }

    function getLicense(): License
    {
        return new License($this->license);
    }

    function getPreferStable(): bool
    {
        return $this->prefer_stable;
    }

    function getDescription(): string
    {
        return $this->description;
    }

    function getHomepage(): Url
    {
        return $this->homepage;
    }

    function getMinimumStability(): Stability
    {
        return $this->stability;
    }

    function getAuthors(): AuthorList
    {
        return $this->authors;
    }

    function getKeywords(): KeywordList
    {
        return $this->keywords;
    }
}
