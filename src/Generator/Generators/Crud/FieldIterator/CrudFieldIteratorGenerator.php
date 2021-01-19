<?php

namespace Generator\Generators\Crud\FieldIterator;

use Crud\BaseCrudFieldIterator;
use Crud\ICrudFieldIterator;
use Generator\Generators\Crud\CollectionType\CrudFieldCollectionTypeGenerator;
use Generator\Generators\Helper\Crud;
use Helper\Schema\Table;
use Hurah\Types\Type\PhpNamespace as PhpNamespaceType;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpNamespace;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

final class CrudFieldIteratorGenerator
{
    public const GENERATED_CLASS_NAME = 'FieldIterator';

    private OutputInterface $output;

    public function __construct(OutputInterface $oOutput = null)
    {
        if ($oOutput) {
            $this->output = $oOutput;
        } else {
            $this->output = new ConsoleOutput();
        }

    }

    public function create(Table $oTable)
    {
        $this->makeIteratorPlaceholder($oTable);
        $this->makeBaseIterator($oTable);
    }

    private function makeIteratorPlaceholder(Table $oTable)
    {
        $oInterfaceName = self::makeInterfaceName($oTable);

        $this->output("<info>Creating field iterator user class</info> <info>{$oInterfaceName}</info>");
        $oFilePath = Crud::getFilePath($oTable, self::GENERATED_CLASS_NAME);
        /*
                if ($oFilePath->exists())
                {
                    $this->output('Skipped creating the placeholder version of <info>' . $oInterfaceName . '</info> it already exists');
                    return null;
                }
        */
        $oNamespace = new PhpNamespace(Crud::makeNamespace($oTable));
        $oNamespace->addUse((string)self::makeBaseInterfaceName($oTable));

        $oInterface = new ClassType($oInterfaceName->getShortName());

        $oInterface->setExtends((string)self::makeBaseInterfaceName($oTable));
        $oInterface->setFinal(true);
        $oInterface->setComment("Skeleton crud field iterator for representing a collection of " . $oTable->getPhpName() . " crud fields.");
        $oInterface->addComment(str_repeat(PHP_EOL, 6));
        $oInterface->addComment("You may/can add additional methods to this class to meet your application requirements.");
        $oInterface->addComment("This class will only be generated once / when it does not exist already.");

        $oNamespace->add($oInterface);
        $this->output("<comment>Saving field iterator user class</comment> <info>{$oFilePath}</info>");
        $oFilePath->write('<?php' . PHP_EOL . (string)$oNamespace);
    }

    public static function makeInterfaceName(Table $oTable): PhpNamespaceType
    {
        return Crud::makeClassName(self::GENERATED_CLASS_NAME, $oTable, Crud::VERSION_USER);
    }

    private function output(string $sMessage)
    {
        $this->output->writeln($sMessage);
    }

    private static function makeBaseInterfaceName(Table $oTable): PhpNamespaceType
    {
        return Crud::makeClassName(self::GENERATED_CLASS_NAME, $oTable, Crud::VERSION_BASE);
    }

    private function makeBaseIterator(Table $oTable)
    {
        $oUserInterfaceName = self::makeInterfaceName($oTable);
        $oBaseInterfaceName = self::makeBaseInterfaceName($oTable);

        $this->output("<comment> Creating</comment> <info>{$oBaseInterfaceName->getShortName()}</info>");

        /**
         * Namespace
         */
        $oNamespace = new PhpNamespace((string)Crud::makeNamespace($oTable, Crud::VERSION_BASE));

        /**
         * Use
         */
        $sCollectionFieldAlias = $oTable->getPhpName() . 'Field';

        $oNamespace->addUse(CrudFieldCollectionTypeGenerator::getPublicInterfaceName($oTable), $sCollectionFieldAlias);
        $oNamespace->addUse(ICrudFieldIterator::class);
        $oNamespace->addUse(BaseCrudFieldIterator::class);

        $oGeneratedManager = new ClassType($oBaseInterfaceName->getShortName());
        $oGeneratedManager->setExtends(BaseCrudFieldIterator::class);

        $aInterfaces = [ICrudFieldIterator::class];

        $oGeneratedManager->setImplements($aInterfaces);

        $oGeneratedManager->setAbstract();
        $oGeneratedManager->addComment("This class is automatically generated, do not modify manually.");
        $oGeneratedManager->addComment("Modify {$oUserInterfaceName} instead if you need to override or add functionality.");

        /**
         * Class properties
         */

        $oFieldCollectionClassProperty = $oGeneratedManager->addProperty('aFields', new Literal('[]'));
        $oFieldCollectionClassProperty->setType(new Literal('array'));
        $oFieldCollectionClassProperty->setPrivate();
        $oFieldCollectionClassProperty->addComment('@param ' . $sCollectionFieldAlias . '[] $aFields');

        /**
         * Class methods
         */

        // __construct
        $oGetQueryObjectMethod = $oGeneratedManager->addMethod('__construct');
        $oGetQueryObjectMethod->addComment('@param ' . $sCollectionFieldAlias . '[] $aFields');
        $oGetQueryObjectMethod->setBody(join(PHP_EOL, [
            'foreach($aFields as $oField) { ',
            '   if($oField instanceof ' . $sCollectionFieldAlias . ' ) {',
            '       $this->aFields[] = $oField;',
            '   }',
            '}',
        ]));
        $oGetQueryObject = $oGetQueryObjectMethod->addParameter('aFields');
        $oGetQueryObject->setType('array');

        // key
        $oKeyMethod = $oGeneratedManager->addMethod('key');
        $oKeyMethod->setReturnType(new Literal('int'));
        $oKeyMethod->setBody('return key($this->aFields);');

        // next
        $oNextMethod = $oGeneratedManager->addMethod('next');
        $oNextMethod->setReturnType(new Literal('void'));
        $oNextMethod->setBody('next($this->aFields);');

        // valid
        $oValidMethod = $oGeneratedManager->addMethod('valid');
        $oValidMethod->setReturnType(new Literal('bool'));
        $oValidMethod->setBody(join(PHP_EOL, [
            '$key = key($this->aFields);',
            'return ($key !== null && $key !== false);',
        ]));

        // rewind
        $oRewindMethod = $oGeneratedManager->addMethod('rewind');
        $oRewindMethod->setReturnType(new Literal('void'));
        $oRewindMethod->setBody('reset($this->aFields);');

        // add
        $oGetQueryObjectMethod = $oGeneratedManager->addMethod('add');

        $aBody = [
            '$this->aFields[] = $oField;',
        ];

        $oGetQueryObjectMethod->setBody(join(PHP_EOL, $aBody));
        $oGetQueryObjectMethod->setReturnType(new Literal('void'));
        $oGetQueryObject = $oGetQueryObjectMethod->addParameter('oField');
        $oGetQueryObject->setType(CrudFieldCollectionTypeGenerator::getPublicInterfaceName($oTable));

        // getCurrent
        $oGetQueryObjectMethod = $oGeneratedManager->addMethod('current');
        $oGetQueryObjectMethod->setVisibility('public');
        $oGetQueryObjectMethod->setBody('return current($this->aFields);');
        $oGetQueryObjectMethod->setReturnType(CrudFieldCollectionTypeGenerator::getPublicInterfaceName($oTable));

        // Save / file path
        $oFilePath = Crud::getFilePath($oTable, self::GENERATED_CLASS_NAME, Crud::VERSION_BASE);
        $this->output("<comment>Saving class:</comment> <info>{$oFilePath}</info>");

        $oNamespace->add($oGeneratedManager);
        $oFilePath->write('<?php' . PHP_EOL . (string)$oNamespace);
    }
}
