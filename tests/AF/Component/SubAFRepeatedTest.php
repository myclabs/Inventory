<?php

namespace Tests\AF\Component;

use AF\Domain\Component\SubAF;
use AF\Domain\Component\SubAF\RepeatedSubAF;
use Core\Test\TestCase;

class SubAFRepeatedTest extends TestCase
{
    public function testConstruct()
    {
        $o = new RepeatedSubAF();

        // Valeurs par défaut
        $this->assertTrue($o->isVisible());
        $this->assertFalse($o->getWithFreeLabel());
        $this->assertEquals(RepeatedSubAF::MININPUTNUMBER_0, $o->getMinInputNumber());
        $this->assertEquals(SubAF::FOLDAWAY, $o->getFoldaway());
    }
}
