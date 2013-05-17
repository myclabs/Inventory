<?php
/**
 * @author matthieu.napoli
 * @package AF
 */

/**
 * @package Algo
 */
class ActionTest
{

    /**
     * Lance les autre classe de tests.
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('ActionSetUpTest');
        return $suite;
    }

}

/**
 * @package Algo
 */
class ActionSetUpTest extends PHPUnit_Framework_TestCase
{

    /**
     * @return AF_Model_Action
     */
    function testConstruct()
    {
        /** @var $o AF_Model_Action */
        $o = $this->getMockForAbstractClass('AF_Model_Action');
        $this->assertTrue($o instanceof AF_Model_Action);
        return $o;
    }

    /**
     * @depends testConstruct
     * @param AF_Model_Action $o
     * @return AF_Model_Action
     */
    function testLoad(AF_Model_Action $o)
    {
        $this->assertTrue($o instanceof AF_Model_Action);
        return $o;
    }

    /**
     * @depends testLoad
     * @param AF_Model_Action $o
     */
    function testDelete(AF_Model_Action $o)
    {
        $this->assertTrue($o instanceof AF_Model_Action);
    }

}

/**
 * @package Algo
 */
class ActionOtherTest extends PHPUnit_Framework_TestCase
{

    /**
     * Teste checkConfig
     */
    function testCheckConfig()
    {
        /** @var $o AF_Model_Action */
        $o = $this->getMockForAbstractClass('AF_Model_Action');
        $errors = $o->checkConfig();
        $this->assertCount(1, $errors);
        $this->assertTrue($errors[0]->getFatal());
        return $o;
    }

}
