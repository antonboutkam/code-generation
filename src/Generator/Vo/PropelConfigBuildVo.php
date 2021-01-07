<?php

namespace Generator\Vo;

use Hurah\Types\Type\SystemId;
use Generator\Generators\IPropelConfigBuildVo;

class PropelConfigBuildVo implements IPropelConfigBuildVo
{

    private string $db_user;
    private string $db_pass;
    private string $db_name;
    private string $db_server;
    private SystemId $system_id;
    private string $build_folder;

    function __construct(string $sSystemId, string $sDbUser, string $sDbPass, string $sDbName, string $sDbServer, string $sBuildFolder)
    {
        $this->system_id = new SystemId($sSystemId);
        $this->db_user = $sDbUser;
        $this->db_pass = $sDbPass;
        $this->db_name = $sDbName;
        $this->db_server = $sDbServer;
        $this->build_folder = $sBuildFolder;
    }

    public function getSystemId(): SystemId
    {
        return $this->system_id;
    }

    public function getDbServer(string $sEnv): string
    {
        return $this->db_server;
    }

    public function getPassword(string $sEnv): string
    {
        return $this->db_pass;
    }

    public function getDbName(string $sEnv): string
    {
        return $this->db_name;
    }

    public function getDbUser(string $sEnv): string
    {
        return $this->db_user;
    }

    public function getBuildFolder(): string
    {
        return $this->build_folder;
    }
}
