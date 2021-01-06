<?php

namespace Generator\Vo;

use Hurah\Types\Type\DnsName;
use Core\Utils;
use Generator\IApiStructureConfig;
use Generator\IBaseBuildVo;
use Generator\IConfigBuildVo;
use Generator\IPropelConfigBuildVo;
use Generator\ISiteStructureConfig;
use InvalidArgumentException;

class SystemBuildConfig implements IConfigBuildVo, ISiteStructureConfig, IApiStructureConfig, IPropelConfigBuildVo, IBaseBuildVo {
    private $title;
    private $api_title;
    private $support_name;
    private $technical_name;
    private $technical_email;
    private $support_email;
    private bool $has_nlx;
    private $production_server;
    private $test_server;
    private $ssh_keys_installed;
    private $system_url;
    private $config_folder;
    private $build_folder;
    private $namespace;
    private $has_admin;
    private $has_api;
    private bool $create_db_local;
    private bool $create_hosts_local;
    private bool $create_db_test;
    private bool $create_db_production;
    private bool $create_vhost_local;
    private bool $create_vhost_test;
    private bool $create_vhost_production;
    private $production_mysql_root_user;
    private $production_mysql_root_pass;
    private $test_mysql_root_user;
    private $test_mysql_root_pass;
    private $linux_user_test;
    private $linux_user_production;
    private $service_name;
    private $api_description;
    private $db_server;

    use ComposerCommonTrait;

    public function getSiteDomainName($sEnv = 'live'): string {
        $sDomain = preg_replace('/(?:https?:\/\/)?(?:www\.)?(.*)\/?$/i', '$1', $this->getSystemUrl());
        if ($sEnv == 'test') {
            $aParts = explode('.', $sDomain);
            $top = array_shift($aParts);
            array_unshift($aParts, 'test');
            array_unshift($aParts, $top);
            $sDomain = join('.', $aParts);
        } else {
            if ($sEnv == 'dev') {
                $aParts = explode('.', $sDomain);

                if ($aParts[0] == 'api' && count($aParts) == 4) {
                    array_pop($aParts);
                }
                array_pop($aParts);
                $aParts[] = 'innovatieapp.nl';
                $sDomain = join('.', $aParts);
            }
        }
        return $sDomain;
    }

    public function setSystemUrl(string $sSystemUrl): void {
        $this->system_url = $sSystemUrl;
    }

    public function getPassword(string $sEnv): string {
        return $this->generatePass($sEnv);
    }

    public function getApiDomainName(string $sEnvironment = 'live'): DnsName {
        return new DnsName('api.' . $this->getSiteDomainName($sEnvironment));
    }

    public function getLinuxUser($sEnv): string {
        if ($sEnv == 'test') {
            return $this->linux_user_test;
        }
        return $this->linux_user_production;
    }

    public function getLinuxUserTest(): string {
        return $this->linux_user_test;
    }

    public function getLinuxUserProduction(): string {
        return $this->linux_user_production;
    }

    public function getUniqueNlxPortPrefix(): string {
        $sPortsFile = $_ENV['SYSTEM_ROOT'] . '/data/nlx-ports';

        if (!file_exists($sPortsFile)) {
            touch($sPortsFile);
            $iCount = 500;
        } else {
            $iCount = (int)file_get_contents($sPortsFile);
        }
        $iCount++;
        file_put_contents($sPortsFile, $iCount);

        return $iCount;
    }

    public function __construct(array $aArguments) {
        $this->title = $aArguments['title'] ?? null;
        $this->api_title = $aArguments['api_title'] ?? null;

        $this->technical_name = $aArguments['technical_name'] ?? null;
        $this->technical_email = $aArguments['technical_email'] ?? null;

        $this->support_name = $aArguments['support_name'] ?? null;
        $this->support_email = $aArguments['support_email'] ?? null;

        $this->production_server = $aArguments['production_server'] ?? null;
        $this->test_server = $aArguments['test_server'] ?? null;

        $this->linux_user_test = $aArguments['linux_user_test'] ?? null;
        $this->linux_user_production = $aArguments['linux_user_production'] ?? null;

        $this->ssh_keys_installed = $aArguments['ssh_keys_installed'] ?? null;

        $this->production_mysql_root_user = $aArguments['production_mysql_root_user'] ?? null;
        $this->production_mysql_root_pass = $aArguments['production_mysql_root_pass'] ?? null;
        $this->test_mysql_root_user = $aArguments['test_mysql_root_user'] ?? null;
        $this->test_mysql_root_pass = $aArguments['test_mysql_root_pass'] ?? null;

        $this->system_url = $aArguments['system_url'] ?? null;
        $this->config_folder = $aArguments['config_folder'] ?? null;
        $this->build_folder = $aArguments['build_folder'] ?? null;
        $this->namespace = $aArguments['namespace'] ?? null;
        $this->has_admin = $aArguments['has_admin'] ?? null;
        $this->has_api = $aArguments['has_api'] ?? null;

        $this->service_name = $aArguments['service_name'] ?? null;
        $this->api_description = $aArguments['api_description'] ?? null;

        $this->db_server = $aArguments['db_server'] ?? 'localhost';
        $this->has_nlx = isset($aArguments['has_nlx']) && $aArguments['has_nlx'] === 'yes';
        $this->create_db_local = isset($aArguments['create_db_local']) && $aArguments['create_db_local'] === 'yes';
        $this->create_hosts_local = isset($aArguments['create_hosts_local']) && $aArguments['create_hosts_local'] === 'yes';
        $this->create_db_test = isset($aArguments['create_db_test']) && $aArguments['create_db_test'] === 'yes';
        $this->create_db_production = isset($aArguments['create_db_production']) && $aArguments['create_db_production'] === 'yes';
        $this->create_vhost_local = isset($aArguments['create_vhost_local']) && $aArguments['create_vhost_local'] === 'yes';
        $this->create_vhost_production = isset($aArguments['create_vhost_production']) && $aArguments['create_vhost_production'] === 'yes';
        $this->create_vhost_test = isset($aArguments['create_vhost_test']) && $aArguments['create_vhost_test'] === 'yes';
    }

    public function getApiDescription(): string {
        return $this->api_description;
    }

    public function getServiceName(): string {
        return $this->service_name;
    }

    /**
     * @return mixed
     */
    public function getTitle(): string {
        return $this->title;
    }

    /**
     * @return mixed
     */
    public function getApiTitle(): string {
        return $this->api_title;
    }

    /**
     * @return mixed
     */
    public function getTechnicalName(): string {
        return $this->technical_name;
    }

    /**
     * @return mixed
     */
    public function getSupportName(): string {
        return $this->support_name;
    }

    /**
     * @return mixed
     */
    public function getTechnicalEmail(): string {
        return $this->technical_email;
    }

    /**
     * @return mixed
     */
    public function getSupportEmail(): string {
        return $this->support_email;
    }

    /**
     * @return bool
     */
    public function isHasNlx(): bool {
        return $this->has_nlx;
    }

    /**
     * @return mixed
     */
    public function getSshKeysInstalled() {
        return $this->ssh_keys_installed;
    }

    /**
     * @return mixed
     */
    public function getProductionMysqlRootUser() {
        return $this->production_mysql_root_user;
    }

    /**
     * @return mixed
     */
    public function getProductionMysqlRootPass() {
        return $this->production_mysql_root_pass;
    }

    /**
     * @return mixed
     */
    public function getTestMysqlRootUser() {
        return $this->test_mysql_root_user;
    }

    /**
     * @return mixed
     */
    public function getTestMysqlRootPass() {
        return $this->test_mysql_root_pass;
    }

    public function getDbName($sEnv): string {
        return strtolower($sEnv . '_' . Utils::slugify($this->getConfigFolder(), '_'));
    }

    public function getDbUser($sEnv): string {
        return strtolower($sEnv . '_' . Utils::slugify($this->getConfigFolder(), '_'));
    }

    public function generatePass($sEnv): string {
        return substr('A!!@!2' . sha1($sEnv . 'A231feaews' . serialize($this)), 0, 20);
    }

    /**
     * @param string $sEnv [test|dev]
     * @return string
     */
    public function getServer(string $sEnv = 'live'): string {
        if ($sEnv == 'live') {
            return $this->getProductionServer();
        } else {
            if ($sEnv == 'test') {
                return $this->getTestServer();
            }
        }

        throw new InvalidArgumentException("Invalid environment, expected one of test or dev");
    }

    /**
     * @return mixed
     */
    public function getProductionServer(): string {
        return $this->production_server;
    }

    /**
     * @return mixed
     */
    public function getTestServer(): string {
        return $this->test_server;
    }

    /**
     * @return mixed
     */
    public function getSystemUrl(): string {
        return $this->system_url;
    }

    /**
     * @return mixed
     */
    public function getSystemId(): string {
        return $this->config_folder;
    }

    /**
     * @return mixed
     */
    public function getBuildFolder(): string {
        return $this->build_folder;
    }

    /**
     * @return mixed
     */
    public function getNamespace(): string {
        return $this->namespace;
    }

    /**
     * @return mixed
     */
    public function getHasAdmin(): bool {
        return $this->has_admin;
    }

    /**
     * @return mixed
     */
    public function getHasApi(): bool {
        return $this->has_api;
    }

    /**
     * @return mixed
     */
    public function getCreateDbLocal(): bool {
        return $this->create_db_local;
    }

    /**
     * @return mixed
     */
    public function getCreateHostsLocal(): bool {
        return $this->create_hosts_local;
    }

    /**
     * @return mixed
     */
    public function getCreateDbTest(): bool {
        return $this->create_db_test;
    }

    /**
     * @return mixed
     */
    public function getCreateDbProduction(): bool {
        return $this->create_db_production;
    }

    /**
     * @return mixed
     */
    public function getCreateVhostLocal(): bool {
        return $this->create_vhost_local;
    }

    /**
     * @return mixed
     */
    public function getCreateVhostTest(): bool {
        return $this->create_vhost_test;
    }

    /**
     * @return mixed
     */
    public function getCreateVhostProduction(): bool {
        return $this->create_vhost_production;
    }

    public function getDbServer(string $sEnv): string {
        return $this->db_server;
    }
}
