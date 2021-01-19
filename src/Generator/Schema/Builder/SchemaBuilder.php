<?php
namespace Generator\Schema\Builder;

use Generator\Generators\System\Helper\Skeleton;
use Generator\Generators\System\Helper\Target;
use Hi\Helpers\DirectoryStructure;

final class SchemaBuilder {

    private ISchemaBuilderConfig $oBuilderConfig;

    public function save()
    {
        $schemaXml = $this->__toString();
        $oDestination = Target::getDomainFilePath($this->oBuilderConfig->getSystemId(), 'schema.xml');

        echo "Writing " . $oDestination . PHP_EOL;
        file_put_contents($oDestination, $schemaXml);
    }
    public function __toString()
    {
        $oDirectoryStructure = new DirectoryStructure();

        return Skeleton::parseTemplate(null, 'schema.xml', ['system' => $this->oBuilderConfig]);
    }
    function __construct(ISchemaBuilderConfig $oBuilderConfig)
    {
        $this->oBuilderConfig = $oBuilderConfig;
    }
}
