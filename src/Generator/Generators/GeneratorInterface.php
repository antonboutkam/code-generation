<?php
namespace Generator;

use Generator\Fragment\FragmentInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface GeneratorInterface {

    public function __construct(BaseGeneratorConfig $input, OutputInterface $oOutput);
    public function getGenerated():FragmentInterface;

}
