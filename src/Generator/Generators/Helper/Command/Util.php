<?php

namespace Generator\Generators\Helper\Command;

use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Util {

    static function introText(string $sString, OutputInterface $output) {
        $oFormatterHelper = new FormatterHelper();

        $output->writeln([
            '',
            $oFormatterHelper->formatBlock($sString, 'bg=blue;fg=white', true),
            '',
        ]);
    }

    public static function ask(string $sVarname, string $sLabel, InputInterface $input, OutputInterface $output) {
    }

}
