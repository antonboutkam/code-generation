<?php

namespace Generator\Generators\Crud\CollectionType;

use Cli\CodeGen\Helpers\Crud;
use Crud\IField;
use Helper\Schema\Table;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use Symfony\Component\Console\Output\OutputInterface;
use Generator\Generators\BaseGenerator;

final class CrudFieldCollectionTypeGenerator
{
    public const INTERFACE_NAME = 'CollectionField';

    public function __construct(OutputInterface $oOutput = null)
    {
        parent::__construct($oOutput);
    }

    public function create(Table $oTable)
    {
        $this->makeUserFieldCollectionType($oTable);
        $this->makeBaseFieldCollectionType($oTable);
    }

    public static function getPublicInterfaceName(Table $oTable): \Core\DataType\PhpNamespace
    {
        return Crud::makeClassName(self::INTERFACE_NAME, $oTable, Crud::VERSION_USER, Crud::TYPE_INTERFACE);
    }

    private function makeUserFieldCollectionType(Table $oTable)
    {
        $sInterfaceName = self::getPublicInterfaceName($oTable);
        $oBaseInterfaceName = Crud::makeClassName(self::INTERFACE_NAME, $oTable, Crud::VERSION_BASE, Crud::TYPE_INTERFACE);
        $oFilePath = Crud::getFilePath($oTable, self::INTERFACE_NAME, Crud::VERSION_USER, true);
        /*
                if ($oFilePath->exists())
                {
                    $this->output('Skipped creating the placeholder version of <info>' . $sInterfaceName . '</info> it already exists');
                    return null;
                }
        */
        $sGeneratedCrudNamespace = Crud::makeNamespace($oTable, Crud::VERSION_USER);

        $oNamespace = new PhpNamespace($sGeneratedCrudNamespace);
        $oInterface = new ClassType($sInterfaceName->getShortName());
        $oNamespace->add($oInterface);
        $oInterface->setInterface();

        $oInterface->setExtends((string)$oBaseInterfaceName);
        $oInterface->setComment("Skeleton interface used for grouping fields that are belong to " . $oTable->getPhpName() . ".");
        $oInterface->addComment(str_repeat(PHP_EOL, 6));
        $oInterface->addComment("You may/can add additional methods to this interface to meet your application requirements.");
        $oInterface->addComment("This interface will only be generated once / when it does not exist already.");

        $oNamespace->add($oInterface);

        $this->output("Writing 8 <error>{$oFilePath}</error>");
        $oFilePath->write((string)$oNamespace);
    }

    private function makeBaseFieldCollectionType(Table $oTable)
    {

        $oUserInterfaceName = Crud::makeClassName(self::INTERFACE_NAME, $oTable, Crud::VERSION_USER, Crud::TYPE_INTERFACE);
        $oBaseInterfaceName = Crud::makeClassName(self::INTERFACE_NAME, $oTable, Crud::VERSION_BASE, Crud::TYPE_INTERFACE);
        $oFilePath = Crud::getFilePath($oTable, self::INTERFACE_NAME, Crud::VERSION_BASE, true);

        $oNamespace = new PhpNamespace((string)Crud::makeNamespace($oTable, Crud::VERSION_BASE));
        $oNamespace->addUse(IField::class);

        $oBaseInterface = new ClassType($oBaseInterfaceName->getShortName());
        $oNamespace->add($oBaseInterface);
        $oBaseInterface->setInterface();

        $oBaseInterface->addComment("This interface is automatically generated, do not modify manually.");
        $oBaseInterface->addComment("Modify {$oUserInterfaceName} instead if you need to override or add functionality.");
        $oBaseInterface->setExtends([IField::class]);

        $this->output("Writing 9 <error>{$oFilePath}</error>");
        $oFilePath->write((string)$oNamespace);
    }
}
