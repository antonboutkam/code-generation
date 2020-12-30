<?php
namespace Generator\Generators\Admin\Module\Structure;

use Hurah\Types\Type\Path;


interface StructureConfigInterface {

    public function getInstallRoot():Path;
    public function getModuleSections():array;


}
