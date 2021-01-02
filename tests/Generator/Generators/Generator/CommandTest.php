<?php

namespace Test\Generator\Generators\Generator;

use Generator\Generators\Generator\Command;
use Generator\Helper\Command\Finder as CommandFinder;
use Hurah\Types\Exception\InvalidArgumentException;
use Hurah\Types\Exception\NullPointerException;
use Hurah\Types\Type\DnsName;
use Hurah\Types\Type\Php\Property;
use Hurah\Types\Type\Php\PropertyCollection;
use Hurah\Types\Type\PhpNamespace;
use Hurah\Types\Type\Primitive\PrimitiveArray;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Tester\CommandTester;

class CommandTest extends TestCase
{

    public function testInitialize()
    {

    }

    public function testInteract()
    {

    }

    public function testConfigure()
    {

    }

    /**
     * @throws InvalidArgumentException
     * @throws NullPointerException
     * @throws ReflectionException
     */
    public function testExecute()
    {

        $input = new ArrayInput([]);
        $output = new ConsoleOutput();

        $oCommandFinder = new CommandFinder($input, $output);
        $oCommand = $oCommandFinder->getByName('generators:generator');
        $commandTester = new CommandTester($oCommand);
        $commandTester->execute([
            // pass arguments to the helper
            '--dry-run',
            'name'          => 'test:command',
            'description'   => 'This is not an actual test but unit test output',
            'help'          => 'Fill in later',
            'psr'           => PhpNamespace::make('Generator', 'Generators', 'Generator', 'Fake'),
            'worker'        => 'FakeCommand',
            'properties'    => (new PropertyCollection([
                Property::create([
                    'name' => 'serverName',
                    'type' => DnsName::class,
                ]),
                Property::create([
                    'name' => 'createHostsLocal',
                    'type' => 'bool',
                ]),
                Property::create([
                    'name' => 'createHostsLocal',
                    'type' => PrimitiveArray::class,
                ]),
            ]))
        ]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        echo $output;
        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
    }
}
