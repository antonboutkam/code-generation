<?php

namespace Generator\Generators\Generator;

use Hurah\Types\Type\Php\PropertyCollection;
use Hurah\Types\Type\PhpNamespace as Pns;
use Hurah\Types\Type\PlainText;

class Config implements ConfigInterface {

    private PlainText $oCommandName;
    private PlainText $oCommandDescription;
    private PlainText $oCommandHelp;

    private Pns $oWorkerClassName;
    private Pns $oConfigClassName;
    private Pns $oCommandClassName;
    private Pns $oConfigInterfaceName;
    private Pns $oTestClassName;
    private PropertyCollection $oPropertyCollection;

    /**
     * @param PlainText $oCommandName
     * @param PlainText $oCommandDescription
     * @param PlainText $oCommandHelp
     * @param Pns $oWorkerClassName
     * @param Pns $oConfigClassName
     * @param Pns $oCommandClassName;
     * @param Pns $oConfigInterfaceName
     * @param Pns $oTestClassName
     * @param PropertyCollection $oPropertyCollection

     * @return static
     */
    public static function create(
        PlainText $oCommandName,
        PlainText $oCommandDescription,
        PlainText $oCommandHelp,
        Pns $oWorkerClassName,
        Pns $oConfigClassName,
        Pns $oCommandClassName,
        Pns $oConfigInterfaceName,
        Pns $oTestClassName,
        PropertyCollection $oPropertyCollection):self
    {

        $Config = new self();
        $Config->oCommandName = $oCommandName;
        $Config->oCommandDescription = $oCommandDescription;
        $Config->oCommandHelp = $oCommandHelp;
        $Config->oCommandName = $oCommandName;
        $Config->oWorkerClassName = $oWorkerClassName;
        $Config->oConfigClassName = $oConfigClassName;
        $Config->oCommandClassName = $oCommandClassName;
        $Config->oConfigInterfaceName = $oConfigInterfaceName;
        $Config->oTestClassName = $oTestClassName;
        $Config->oPropertyCollection = $oPropertyCollection;
        return $Config;
    }

    public function getCommandName(): PlainText {
        return $this->oCommandName;
    }

    public function getCommandDescription(): PlainText {
        return $this->oCommandDescription;
    }

    public function getCommandHelp(): PlainText {
        return $this->oCommandHelp;
    }

    public function getWorkerClassName(): Pns {
        return $this->oWorkerClassName;
    }

    public function getConfigClassName(): Pns {
        return $this->oConfigClassName;
    }

    public function getConfigInterfaceName(): Pns {
        return $this->oConfigInterfaceName;
    }

    public function getCommandClassName(): Pns {
        return $this->oCommandClassName;
    }

    public function getTestClassName(): Pns {
        return $this->oTestClassName;
    }

    public function getProperties(): PropertyCollection {
        return $this->oPropertyCollection;
    }

}
