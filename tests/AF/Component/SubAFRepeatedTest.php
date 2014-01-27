<?php

namespace Tests\AF\Component;

use AF_Model_Component_SubAF;
use AF_Model_Component_SubAF_Repeated;
use Core\Test\TestCase;

class SubAFRepeatedTest extends TestCase
{
    public function testConstruct()
    {
        $o = new AF_Model_Component_SubAF_Repeated();

        // Valeurs par défaut
        $this->assertTrue($o->isVisible());
        $this->assertFalse($o->getWithFreeLabel());
        $this->assertEquals(AF_Model_Component_SubAF_Repeated::MININPUTNUMBER_0, $o->getMinInputNumber());
        $this->assertEquals(AF_Model_Component_SubAF::FOLDAWAY, $o->getFoldaway());
    }
}
