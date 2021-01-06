<?php

namespace Generator\Vo;

use Hurah\Types\Type\Path;
use Hurah\Types\Type\PathCollection;
use Hurah\Types\Type\SystemId;
use Generator\Schema\Importer\IImporterConfig;

class SchemaToDbVo implements IImporterConfig
{

    private SystemId $oSystemId;

    public function __construct(SystemId $oSystemId)
    {
        $this->oSystemId = $oSystemId;
    }

    public function getSystemId(): SystemId
    {
        return $this->oSystemId;
    }

    public function getSchemaFiles(): PathCollection
    {
        return new PathCollection([
            new Path('./schema.xml'),
        ]);
    }
}
