<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @author  thibaud.rolland
 * @package AF
 */

/**
 * @package Algo
 */
class Component_SubAFNotRepeatedTest
{

    /**
     * Lance les autre classe de tests.
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Component_SubAFNotRepeatedSetUpTest');
        return $suite;
    }

}

/**
 * @package Algo
 */
class Component_SubAFNotRepeatedSetUpTest extends PHPUnit_Framework_TestCase
{

    /**
     * @return AF_Model_Component_SubAF_NotRepeated
     */
    function testConstruct()
    {
        $o = new AF_Model_Component_SubAF_NotRepeated();
        $this->assertInstanceOf('AF_Model_Component_SubAF_NotRepeated', $o);

        // Visible par défaut
        $this->assertTrue($o->isVisible());
        $this->assertEquals(AF_Model_Component_SubAF::FOLDAWAY, $o->getFoldaway());

        return $o;
    }

    /**
     * @depends testConstruct
     * @param AF_Model_Component_SubAF_NotRepeated $o
     * @return AF_Model_Component_SubAF_NotRepeated
     */
    function testLoad(AF_Model_Component_SubAF_NotRepeated $o)
    {
        $this->assertInstanceOf('AF_Model_Component_SubAF_NotRepeated', $o);
        return $o;
    }

    /**
     * @depends testLoad
     * @param AF_Model_Component_SubAF_NotRepeated $o
     */
    function testDelete(AF_Model_Component_SubAF_NotRepeated $o)
    {
        $this->assertInstanceOf('AF_Model_Component_SubAF_NotRepeated', $o);
    }

}
