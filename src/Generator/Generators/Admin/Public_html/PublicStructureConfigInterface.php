<?php

namespace Generator\Generators\Admin\Public_html;

use Hurah\Types\Type\SystemId;

interface PublicStructureConfigInterface {

    public function getSystemId():SystemId;
}
