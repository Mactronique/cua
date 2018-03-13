<?php
/**
 * This file is part of Composer Update Analyser package.
 *
 * @author Jean-Baptiste Nahan <jean-baptiste.nahan@inextenso.fr>
 * @copyright 2016 - Jean-Baptiste Nahan
 * @license MIT
 */
namespace InExtenso\CUA\Tests\Units\Persistence;

use atoum;

class YamlFile extends atoum
{
    public function testInit()
    {
        $this->newTestedInstance(['path'=>'here']);
        $this->assert('type')->object($this->testedInstance)->isInstanceOf('InExtenso\CUA\Persistence\Persistence');
    }
}
