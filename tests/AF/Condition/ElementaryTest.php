<?php
///**
// * @author hugo.charbonnier
// * @author thibaud.rolland
// * @package AF
// */
//
//require_once dirname(__FILE__).'/../config_test/config_test.php';
//
//// Les 2 lignes du dessous sont à décomenter pour les tests en local et à commenter avant commit
//  require_once dirname(__FILE__).'/../Form/MultiTest.php';
//  require_once dirname(__FILE__).'/../AFTest.php';
//
//class Condition_ElementaryTest
//{
//     /**
//      * Creation of the test suite
//      */
//     public static function suite()
//     {
//        $suite = new PHPUnit_Framework_TestSuite();
//        $suite->addTestSuite('Condition_ElementarySetUpTest');
//        $suite->addTestSuite('Condition_ElementaryOtherTest');
//        return $suite;
//     }
//
//    /**
//     * Generation of a test object
//     * @return AF_Model_Condition_Elementary
//     */
//    public static function generateObject()
//    {
//        static $id = 1;
//
//        $af = AFTest::generateObject();
//
//        $o = new AF_Model_Condition_Elementary();
//        $o->ref = "maCondition".$id;
//        $id++;
//        $o->relation = AF_Model_Condition_Elementary::RELATION_EQUAL;
//        $o->value = 5;
//        $o->setAF($af);
//        $o->save();
//
//        return $o;
//    }
//
//     /**
//      * Deletion of an object created with generateObject
//      * @param AF_Model_Condition_Elementary $o
//      */
//     public static function deleteObject(AF_Model_Condition_Elementary $o)
//     {
//         AFTest::deleteObject($o->getAF());
//     }
//}
//
///**
// * Test of the creation/modification/deletion of the entity
// */
//class Condition_ElementarySetUpTest extends PHPUnit_Framework_TestCase
//{
//     /**
//      * Function called once, before all the tests
//      */
//     public static function setUpBeforeClass()
//     {
//         // Empty related tables
//         AF_Model_Condition_DAO_Condition::getInstance()->unitTestsClearTable();
//         AF_Model_Condition_DAO_Elementary::getInstance()->unitTestsClearTable();
//
//         AF_Model_DAO_AF::getInstance()->unitTestsClearTable();
//         AF_Model_DAO_Branch::getInstance()->unitTestsClearTable();
//         AF_Model_DAO_Version::getInstance()->unitTestsClearTable();
//
//         Classif_Model_DAO_Context::getInstance()->unitTestsClearTable();
//         Classif_Model_DAO_Version::getInstance()->unitTestsClearTable();
//
//         AF_Model_Form_DAO_Component::getInstance()->unitTestsClearTable();
//         AF_Model_Form_DAO_Element::getInstance()->unitTestsClearTable();
//         AF_Model_Form_DAO_Group::getInstance()->unitTestsClearTable();
//         AF_Model_Form_DAO_MultiStatic::getInstance()->unitTestsClearTable();
//     }
//
//     /**
//      * Constructor test
//      */
//     function testConstruct()
//     {
//        $o = new AF_Model_Condition_Elementary();
//        $this->assertTrue($o instanceof AF_Model_Condition_Elementary);
//        return $o;
//     }
//
//     /**
//      * Save test
//      * @param AF_Model_Condition_Elementary $o
//      * @depends testConstruct
//      */
//     function testSave(AF_Model_Condition_Elementary $o)
//     {
//        $element = Form_MultiTest::generateObject();
//
//        $o->ref = "maCondition";
//        $o->setField($element);
//        $o->relation = AF_Model_Condition_Elementary::RELATION_EQUAL;
//        $o->value = 5;
//
//        //Test de la sauvegarde d'une expression sans AF
//        try {
//            $o->save();
//            $this->assertTrue(false);
//        } catch (Core_Exception_Database $e) {
//            // @todo tester le message d'erreur
//            $this->assertTrue(true);
//        }
//
//        // Test de la sauvegarde d'un objet correct
//        $af = AFTest::generateObject('ref_de_la_branche', 'ref_de_la_version');
//        $o->setAF($af);
//        $o->save();
//
//        $this->assertNotNull($o->id, "Object id is not defined");
//
//        $firstId = $o->id;
//        $firstRef = $o->ref;
//
//        //update test
//        $o->ref = "uneCondition";
//        $o->save();
//
//        $this->assertTrue($firstId == $o->id && $firstRef != $o->ref);
//        $this->assertTrue($o->ref == 'uneCondition');
//        return $o;
//     }
//
//     /**
//      * Load test
//      * @depends testSave
//      * @param AF_Model_Condition_Elementary $o
//      */
//     function testLoad(AF_Model_Condition_Elementary $o)
//     {
//         $a = AF_Model_Condition_Elementary::load($o->id);
//         $this->assertEquals($a->id, $o->id);
//         $this->assertEquals($a->getField(), $o->getField());
//     }
//
//     /**
//      * Deletion test
//      * @param AF_Model_Condition_Elementary $o
//      * @depends testSave
//      */
//     function testDelete(AF_Model_Condition_Elementary $o)
//     {
//        $multi = $o->getField();
//        $af    = $o->getAF();
//
//        $o->delete();
//        $this->assertNull($o->id);
//
//        Form_MultiTest::deleteObject($multi);
//        AFTest::deleteObject($af);
//     }
//
//     /**
//      * Function called once, after all the tests
//      */
//     public static function tearDownAfterClass()
//     {
//        // Check tables are empty
//        if (! AF_Model_Condition_DAO_Condition::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table Condition n'est pas vide après les tests de la classe Elementary\n";
//        }
//        if (! AF_Model_Condition_DAO_Elementary::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table Elementary n'est pas vide après les tests de la classe Elementary\n";
//        }
//
//        if (! AF_Model_DAO_AF::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table AF n'est pas vide après les tests de la classe Elementary\n";
//        }
//        if (! AF_Model_DAO_Branch::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table AF_Branch n'est pas vide après les tests de la classe Elementary\n";
//        }
//        if (! AF_Model_DAO_Version::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table AF_Version n'est pas vide après les tests de la classe Elementary\n";
//        }
//        if (! Classif_Model_DAO_Context::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table Classif_Context n'est pas vide après les tests de la classe Elementary\n";
//        }
//        if (! Classif_Model_DAO_Version::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table Classif_Version n'est pas vide après les tests de la classe Elementary\n";
//        }
//
//        if (! AF_Model_Form_DAO_Component::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table AF_Component n'est pas vide après les tests de la classe Elementary\n";
//        }
//        if (! AF_Model_Form_DAO_Element::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table AF_Element n'est pas vide après les tests de la classe Elementary\n";
//        }
//        if (! AF_Model_Form_DAO_Group::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table AF_Group n'est pas vide après les tests de la classe Elementary\n";
//        }
//        if (! AF_Model_Form_DAO_MultiStatic::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table AF_Element_Multi n'est pas vide après les tests de la classe Elementary\n";
//        }
//     }
//}
//
//class Condition_ElementaryOtherTest extends PHPUnit_Framework_TestCase
//{
//    protected $_elementary;
//     /**
//      * Function called once, before all the tests
//      */
//     public static function setUpBeforeClass()
//     {
//         // Empty related tables
//
//         AF_Model_Condition_DAO_Condition::getInstance()->unitTestsClearTable();
//         AF_Model_Condition_DAO_Elementary::getInstance()->unitTestsClearTable();
//
//         AF_Model_DAO_AF::getInstance()->unitTestsClearTable();
//         AF_Model_DAO_Branch::getInstance()->unitTestsClearTable();
//         AF_Model_DAO_Version::getInstance()->unitTestsClearTable();
//
//         Classif_Model_DAO_Context::getInstance()->unitTestsClearTable();
//         Classif_Model_DAO_Version::getInstance()->unitTestsClearTable();
//
//         AF_Model_Form_DAO_Component::getInstance()->unitTestsClearTable();
//         AF_Model_Form_DAO_Element::getInstance()->unitTestsClearTable();
//         AF_Model_Form_DAO_Group::getInstance()->unitTestsClearTable();
//         AF_Model_Form_DAO_MultiStatic::getInstance()->unitTestsClearTable();
//     }
//
//    /**
//     * Function called before each test
//     */
//     protected function setUp()
//     {
//        // Create a test object
//        $this->_elementary = Condition_ElementaryTest::generateObject();
//     }
//
//     /**
//      * Test de setField et getField
//      */
//     function testSetGetElement()
//     {
//        $element = Form_MultiTest::generateObject('refBranch2', 'ref_classif_version' );
//
//        $this->_elementary->setField($element);
//        $this->assertEquals($element, $this->_elementary->getField());
//
//        Form_MultiTest::deleteObject($element);
//     }
//
//     /**
//      * Test de testLoadByRefAndIdAF
//      */
////     function testLoadByRefAndIdAF()
////     {
//         // @todo développer la méthode Condition_ElementaryTest::generateObject()
//         // faite dans le setUp, car elle ne fait rien actuellement
////        $elementary = AF_Model_Condition_Elementary::loadByRefAndAF($this->_elementary->ref,
////                                                                      2);
////        $this->assertEquals($this->_elementary, $elementary);
////     }
//
//    /**
//     * Function called after each test
//     */
//     protected function tearDown()
//     {
//       Condition_ElementaryTest::deleteObject($this->_elementary);
//     }
//
//     /**
//      * Function called once, after all the tests
//      */
//     public static function tearDownAfterClass()
//     {
//        // Check tables are empty
//        if (! AF_Model_Condition_DAO_Condition::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table Condition n'est pas vide après les tests de la classe Elementary\n";
//        }
//        if (! AF_Model_Condition_DAO_Elementary::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table Elementary n'est pas vide après les tests de la classe Elementary\n";
//        }
//
//        if (! AF_Model_DAO_AF::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table AF n'est pas vide après les tests de la classe Elementary\n";
//        }
//        if (! AF_Model_DAO_Branch::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table AF_Branch n'est pas vide après les tests de la classe Elementary\n";
//        }
//        if (! AF_Model_DAO_Version::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table AF_Version n'est pas vide après les tests de la classe Elementary\n";
//        }
//        if (! Classif_Model_DAO_Context::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table Classif_Context n'est pas vide après les tests de la classe Elementary\n";
//        }
//        if (! Classif_Model_DAO_Version::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table Classif_Version n'est pas vide après les tests de la classe Elementary\n";
//        }
//
//        if (! AF_Model_Form_DAO_Component::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table AF_Component n'est pas vide après les tests de la classe Elementary\n";
//        }
//        if (! AF_Model_Form_DAO_Element::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table AF_Element n'est pas vide après les tests de la classe Elementary\n";
//        }
//        if (! AF_Model_Form_DAO_Group::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table AF_Group n'est pas vide après les tests de la classe Elementary\n";
//        }
//        if (! AF_Model_Form_DAO_MultiStatic::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table AF_Element_Multi n'est pas vide après les tests de la classe Elementary\n";
//        }
//     }
//}
