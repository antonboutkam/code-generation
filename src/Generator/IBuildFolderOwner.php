<?php
namespace Generator;


use Hurah\Types\Type\SystemId;

interface IBuildFolderOwner extends IBaseBuildVo
{
    function getSystemId():SystemId;
}
