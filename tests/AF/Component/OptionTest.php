<?php
/**
 * @author hugo.charbonnier
 * @author thibaud.rolland
 * @package AF
 */

/**
 * @package Algo
 */
class Form_OptionTest
{
     /**
      * Creation of the test suite
      */
     public static function suite()
     {
        $suite = new PHPUnit_Framework_TestSuite();
//        $suite->addTestSuite('Form_OptionSetUpTest');
//        $suite->addTestSuite('Form_OptionOtherTest');
        return $suite;
     }

     /**
      * Generation of a test object
      * @return AF_Model_Component_Select_Option
      */
     public static function generateObject()
     {
        $o = new AF_Model_Component_Select_Option();
        $o->setRef("test");
        $o->setLabel("labelTest");
        $o->save();
        return $o;
     }

     /**
      * Deletion of an object created with generateObject
      * @param AF_Model_Component_Select_Option $o
      */
     public static function deleteObject($o)
     {
        $o->delete();
     }
}

/**
 * Test of the creation/modification/deletion of the entity
 * @package AF
 */
class Form_OptionSetUpTest extends PHPUnit_Framework_TestCase
{
     /**
      * Function called once, before all the tests
      */
     public static function setUpBeforeClass()
     {
         // Empty related tables
         AF_Model_Form_Element_Select_DAO_Option::getInstance()->unitTestsClearTable();
     }

     /**
      * Constructor test
      */
     function testConstruct()
     {
        $o = new AF_Model_Component_Select_Option();
        $this->assertTrue($o instanceof AF_Model_Component_Select_Option);
        return $o;
     }

     /**
      * Save test
      * @param AF_Model_Component_Select_Option $o
      * @depends testConstruct
      */
     function testSave(AF_Model_Component_Select_Option $o)
     {
        // Test Save.
        $o->setRef('save');
        $o->setLabel('labelTest');

        $o->save();

        $firstId  = $o->getKey();
        $firstRef = $o->getRef();

        $this->assertNotNull($o->getKey(), "Object id is not defined");
        $this->assertEquals('save', $o->getRef());

        // Test update.
        $o->setRef('update');
        $o->save();

        $secondId  = $o->getKey();
        $secondRef = $o->getRef();

        $this->assertTrue($firstId === $secondId);
        $this->assertTrue(($firstRef !== $secondRef) &&($secondRef === $secondRef));

        // Test erreur générée si paramètre de setLabel n'est pas un string
        $a = new AF_Model_Component_Select_Option();
        try {
            $a->setLabel(1);
        } catch (Core_Exception_InvalidArgument $e) {
            $this->assertEquals($e->getMessage(), 'String attendue');
        }
        return $o;
     }

     /**
      * Load test
      * @depends testSave
      * @param AF_Model_Component_Select_Option $o
      */
     function testLoad(AF_Model_Component_Select_Option $o)
     {
         $a = AF_Model_Component_Select_Option::getMapper()->load($o->getKey());
         $this->assertEquals($a->getKey(), $o->getKey());
     }

     /**
      * Deletion test
      * @param AF_Model_Component_Select_Option $o
      * @depends testSave
      */
     function testDelete(AF_Model_Component_Select_Option $o)
     {
        $o->delete();
        $this->assertNull($o->getKey());
     }

     /**
      * Function called once, after all the tests
      */
     public static function tearDownAfterClass()
     {
        // Check tables are empty
        if (! AF_Model_Form_Element_Select_DAO_Option::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Option n'est pas vide après les tests\n";
        }
     }
}
