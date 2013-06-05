<?php
/**
 * @author matthieu.napoli
 * @author hugo.charbonnier
 * @author thibaud.rolland
 * @package AF
 */

/**
 * @package Algo
 */
class Form_SubAFRepeatedTest extends PHPUnit_Framework_TestCase
{

    function testConstruct()
    {
        $o = new AF_Model_Component_SubAF_Repeated();
        $this->assertInstanceOf('AF_Model_Component_SubAF_Repeated', $o);

        // Valeurs par défaut
        $this->assertTrue($o->isVisible());
        $this->assertFalse($o->getWithFreeLabel());
        $this->assertEquals(AF_Model_Component_SubAF_Repeated::MININPUTNUMBER_0, $o->getMinInputNumber());
        $this->assertEquals(AF_Model_Component_SubAF::FOLDAWAY, $o->getFoldaway());
    }

}
