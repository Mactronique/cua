<?php
/**
 * This file is part of Composer Update Analyser package.
 *
 * @author Jean-Baptiste Nahan <814683+macintoshplus@users.noreply.github.com>
 * @copyright 2016-2019 - Jean-Baptiste Nahan
 * @license MIT
 */
namespace Mactronique\CUA\Tests\Units\Persistence;

use atoum;

class YamlFile extends atoum
{
    public function testInit()
    {
        $this->newTestedInstance(['path'=>'here', 'path_security'=>'here_security']);
        $this->assert('type')->object($this->testedInstance)->isInstanceOf('Mactronique\CUA\Persistence\Persistence');
    }
}
