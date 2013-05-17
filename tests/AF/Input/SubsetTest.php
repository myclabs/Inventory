<?php
///**
// * @author hugo.charbonnier
// * @author thibaud.rolland
// * @package AF
// */
//
//require_once dirname(__FILE__).'/../config_test/config_test.php';
//
//
///**
// * subSetTest.
// * @author hugo.charbonnier
// * @author thibaud.rolland
// * @package AF
// */
//class Input_SubsetTest
//{
//     /**
//      * Creation of the test suite.
//      */
//     public static function suite()
//     {
//        $suite = new PHPUnit_Framework_TestSuite();
//        $suite->addTestSuite('Form_Input_SubsetSetUpTest');
////        $suite->addTestSuite('Form_Input_SubsetOtherTest');
//        return $suite;
//     }//end suite()
//
//
//     /**
//      * Generation of a test object.
//      * @return AF_Model_InputSet_Sub
//      */
//     public static function generateObject()
//     {
//        $o = new AF_Model_InputSet_Sub();
//        $o->setFreeLabel('test');
//        $o->setCompleteInput(false);
//        $o->save();
//        return $o;
//     }//end generateObject()
//
//
//     /**
//      * Deletion of an object created with generateObject.
//      * @param AF_Model_InputSet_Sub $o
//      */
//     public static function deleteObject($o)
//     {
//        $o->delete();
//     }//end deleteObject()
//}//end class Input_SubsetTest
//
//
///**
// * Test of the creation/modification/deletion of the entity
// * @package AF
// */
//class Form_Input_SubsetSetUpTest extends PHPUnit_Framework_TestCase
//{
//     /**
//      * Function called once, before all the tests
//      */
//     public static function setUpBeforeClass()
//     {
//         // Empty related tables
//         AF_Model_Input_DAO_Set::getInstance()->unitTestsClearTable();
//         AF_Model_Input_Set_DAO_Sub::getInstance()->unitTestsClearTable();
//     }//end setUpBeforeClass()
//
//
//     /**
//      * Constructor test
//      */
//     function testConstruct()
//     {
//         $o = new AF_Model_InputSet_Sub();
//         $this->assertTrue($o instanceof AF_Model_InputSet_Sub);
//         $this->assertFalse($o->getDynamic());
//         return $o;
//     }//end testConstruct()
//
//
//     /**
//      * Save test
//      * @param AF_Model_InputSet_Sub $o
//      * @depends testConstruct
//      */
//     function testSave(AF_Model_InputSet_Sub $o)
//     {
//         $o->setFreeLabel('testSubset');
//         $o->setCompleteInput(false);
//         $o->save();
//
//         $firstId    = $o->getKey();
//         $firstLabel = $o->getFreeLabel();
//         $this->assertNotNull($o->getKey(), "Object id is not defined");
//
//         //Test de l'update.
//         $o->SetFreeLabel('testSubsetUpdate');
//         $o->save();
//
//         $secondId    = $o->getKey();
//         $secondLabel = $o->getFreeLabel();
//
//         $this->assertEquals($firstId, $secondId);
//         $this->assertNotEquals($firstLabel, $secondLabel);
//         $this->assertEquals($secondLabel, 'testSubsetUpdate');
//         return $o;
//     }//end testSave()
//
//
//     /**
//      * Load test
//      * @depends testSave
//      * @param AF_Model_InputSet_Sub $o
//      */
//     function testLoad(AF_Model_InputSet_Sub $o)
//     {
//         $a = AF_Model_InputSet_Sub::getMapper()->load($o->getKey());
//         $this->assertEquals($a->getKey(), $o->getKey());
//     }//end testLoad()
//
//
//     /**
//      * Deletion test
//      * @param AF_Model_InputSet_Sub $o
//      * @depends testSave
//      * @expectedException Core_Exception_UndefinedAttribute
//      */
//     function testDelete(AF_Model_InputSet_Sub $o)
//     {
//        $o->delete();
//        $this->assertNull($o->getKey());
//
//        // test de l'exception générée dans le cas ou on tente de supprimer un élément
//        // non présent dans la base de données.
//        $o->delete();
//
//     }//end testDelete()
//
//
//     /**
//      * Function called once, after all the tests
//      */
//     public static function tearDownAfterClass()
//     {
//        // Check tables are empty
//        if (! AF_Model_Input_DAO_Set::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table Set n'est pas vide après les tests\n";
//        }
//        if (! AF_Model_Input_Set_DAO_Sub::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table Subset n'est pas vide après les tests\n";
//        }
//     }//end tearDownAfterClass()
//
//}//end class Form_Input_SubsetSetUpTest
