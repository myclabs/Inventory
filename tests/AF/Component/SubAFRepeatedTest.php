<?php

namespace Tests\AF\Component;

use AF\Domain\Component\SubAF;
use AF\Domain\Component\SubAF\RepeatedSubAF;
use Core\Test\TestCase;

/**
 * @covers \AF\Domain\Component\SubAF\RepeatedSubAF
 */
class SubAFRepeatedTest extends TestCase
{
    public function testDefaultValues()
    {
        $o = new RepeatedSubAF();

        // Valeurs par défaut
        $this->assertTrue($o->isVisible());
        $this->assertEquals(RepeatedSubAF::MININPUTNUMBER_0, $o->getMinInputNumber());
        $this->assertEquals(SubAF::FOLDAWAY, $o->getFoldaway());
    }
}
