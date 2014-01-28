<?php

namespace Tests\Core;

use Core\Test\TestCase;

class Utf8Test extends TestCase
{
    /**
     * Vérification simple
     */
    public function testStrlen()
    {
        $this->assertEquals('UTF-8', mb_internal_encoding());
        $this->assertEquals(3, mb_strlen('ééé', 'UTF-8'));
        $this->assertEquals(3, mb_strlen('éée'));
        $this->assertEquals(5, strlen('éée'));
        $this->assertEquals(22, mb_strlen('Informations générales'));
        $this->assertEquals(24, strlen('Informations générales'));
    }

    /**
     * Cas particulier pour les ressources des ACL
     */
    public function testSerialize()
    {
        $this->assertEquals(22, mb_strlen('Informations générales'));
        $this->assertEquals('s:24:"Informations générales";', serialize('Informations générales'));
        $this->assertEquals('Informations générales', unserialize(serialize('Informations générales')));
        $this->assertEquals(22, mb_strlen(unserialize(serialize('Informations générales'))));
    }
}
