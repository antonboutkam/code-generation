<?php

namespace Generator;


/**
 * Interface ICompleteComponent
 * This interface is used to mark / group other interfaces that make up a complete component.
 * @package Generator
 */
interface ICompleteComponent
{
    function getPackageName():string;

}