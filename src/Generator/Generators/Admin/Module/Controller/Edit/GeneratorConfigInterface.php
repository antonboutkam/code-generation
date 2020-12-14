<?php

namespace Generator\Admin\Module\Controller\Edit;

use Hurah\Types\Type\PlainText;
use Hurah\Types\Type\PhpNamespace;

interface GeneratorConfigInterface {

    public function getTitle():string;
    public function getPhpName():string;

    public function getBaseNamespace():PhpNamespace;
    public function getNamespace():PhpNamespace;
    public function getCrudNamespace():PhpNamespace;
    public function getQueryClass():PhpNamespace;
    public function getModelClass():PhpNamespace;
    public function getModuleName():PlainText;
    public function getModelNamespace():PhpNamespace;
}
