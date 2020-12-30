<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Generator\Helper\Command\Finder as CommandFinder;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

$input = new ArrayInput([]);
$output = new ConsoleOutput();

$oCommandFinder = new CommandFinder($input, $output);
$oCommandCollection = $oCommandFinder->find();

$oApplication = new Application('Code generators', '1.0.0');

foreach($oCommandCollection as $oCommand)
{
    $oApplication->add($oCommand);
}

$oApplication->run();
