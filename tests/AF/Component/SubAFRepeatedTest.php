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
class Form_SubAFRepeatedTest
{

    /**
     * Lance les autre classe de tests.
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Component_SubAFRepeatedSetUpTest');
        return $suite;
    }

}

/**
 * Form_SubAFSetUpTest.
 * @package Algo
 */
class Component_SubAFRepeatedSetUpTest extends PHPUnit_Framework_TestCase
{

    /**
     * @return AF_Model_Component_SubAF_Repeated
     */
    function testConstruct()
    {
        $o = new AF_Model_Component_SubAF_Repeated();
        $this->assertInstanceOf('AF_Model_Component_SubAF_Repeated', $o);

        // Valeurs par défaut
        $this->assertTrue($o->isVisible());
        $this->assertFalse($o->getWithFreeLabel());
        $this->assertEquals(AF_Model_Component_SubAF_Repeated::MININPUTNUMBER_0, $o->getMinInputNumber());
        $this->assertEquals(AF_Model_Component_SubAF::FOLDAWAY, $o->getFoldaway());

        return $o;
    }

    /**
     * @depends testConstruct
     * @param AF_Model_Component_SubAF_Repeated $o
     * @return AF_Model_Component_SubAF_Repeated
     */
    function testLoad(AF_Model_Component_SubAF_Repeated $o)
    {
        $this->assertInstanceOf('AF_Model_Component_SubAF_Repeated', $o);
        return $o;
    }


    /**
     * @depends testLoad
     * @param AF_Model_Component_SubAF_Repeated $o
     */
    function testDelete(AF_Model_Component_SubAF_Repeated $o)
    {
        $this->assertInstanceOf('AF_Model_Component_SubAF_Repeated', $o);
    }

}
