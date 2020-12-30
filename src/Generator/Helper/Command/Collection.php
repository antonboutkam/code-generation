<?php
namespace Generator\Helper\Command;

use Iterator;
use Symfony\Component\Console\Command\Command;

class Collection implements Iterator {
    private int $position;
    private array $array = [];

    public function __construct() {
        $this->position = 0;
    }

    public function rewind() {
        $this->position = 0;
    }
    public function add(Command $oCommand) {
        $this->array[] = $oCommand;
    }
    public function current():Command {
        return $this->array[$this->position];
    }

    public function key() {
        return $this->position;
    }

    public function next() {
        ++$this->position;
    }

    public function valid() {
        return isset($this->array[$this->position]);
    }
}
