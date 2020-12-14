<?php
namespace Generator\Fragment;

use Core\DataType\PlainText;
use Hurah\Types\Type\Path;

abstract class Fragment
{
    private Path $location;
    private PlainText $generated;

    public function __construct(Path $location, PlainText $generated)
    {
        $this->location = $location;
        $this->generated = $generated;
    }
}
