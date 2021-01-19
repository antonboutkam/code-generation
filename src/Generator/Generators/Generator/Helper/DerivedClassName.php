<?php
namespace Generator\Generators\Generator\Helper;

use Hurah\Types\Type\PhpNamespace;

class DerivedClassName
{
    private string $sWorkerClassName;
    private PhpNamespace $oBaseNamespace;
    
    public function __construct(string $sWorkerClassName, PhpNamespace $oBaseNamespace)
    {
        $this->sWorkerClassName = $sWorkerClassName;
        $this->oBaseNamespace = $oBaseNamespace;
    }

    public function makeConfigClassName():PhpNamespace {
        $sConfigClassName = "Config{$this->sWorkerClassName}";
        return $this->oBaseNamespace->extend($sConfigClassName);
    }

    public function makeConfigInterfaceName():PhpNamespace {
        $sConfigClassName = "Config{$this->sWorkerClassName}Interface";
        return $this->oBaseNamespace->extend($sConfigClassName);
    }

    public function makeCommandClassName():PhpNamespace {
        $sConfigClassName = "{$this->sWorkerClassName}Command";
        return $this->oBaseNamespace->extend($sConfigClassName);
    }

    public function makeTestClassName():PhpNamespace {

        // namespace Generator\Generators\Generator\Command;
        // namespace Test\Generator\Generators\Generator\CommandTest;
        return PhpNamespace::make('Test', $this->oBaseNamespace, $this->sWorkerClassName . 'Test');
    }
}