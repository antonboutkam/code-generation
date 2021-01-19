<?php
namespace Generator;

interface IPropelConfigBuildVo extends IBuildFolderOwner
{
    function getDbServer(string $sEnv):string;
    function getPassword(string $sEnv):string;
    function getDbName(string $sEnv):string;
    function getDbUser(string $sEnv):string;
    function getBuildFolder():string;
}
