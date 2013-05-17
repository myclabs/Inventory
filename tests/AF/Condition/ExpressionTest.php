<?php
/**
// * @author hugo.charbonnier
// * @author thibaud.rolland
// * @package AF
// */
//
//require_once dirname(__FILE__).'/../config_test/config_test.php';
//
//// Les 2 lignes du dessous sont à décomenter pour les tests en local et à commenter avant commit
//  require_once dirname(__FILE__).'/../Condition/ElementaryTest.php';
//  require_once dirname(__FILE__).'/../AFTest.php';
//
//class Condition_ExpressionTest
//{
//     /**
//      * Creation of the test suite
//      */
//     public static function suite()
//     {
//        $suite = new PHPUnit_Framework_TestSuite();
//        $suite->addTestSuite('Condition_ExpressionSetUpTest');
//        $suite->addTestSuite('Condition_ExpressionOtherTest');
//        return $suite;
//     }
//
//     /**
//      * Generation of a test object
//      * @return AF_Model_Condition_Expression
//      */
//     public static function generateObject()
//     {
//        $af = AFTest::generateObject();
//
//        $o = new AF_Model_Condition_Expression();
//        $o->ref = "maCondition";
//        $o->expression = "maCondition1 & maCondition2";
//        $o->setAF($af);
//        $o->save();
//        return $o;
//     }
//
//     /**
//      * Deletion of an object created with generateObject
//      * @param AF_Model_Condition_Expression
//      */
//     public static function deleteObject(AF_Model_Condition_Expression $o)
//     {
//        $o->getAF()->delete();
//        $o->delete();
//     }
//}
//
///**
// * Test of the creation/modification/deletion of the entity
// */
//class Condition_ExpressionSetUpTest extends PHPUnit_Framework_TestCase
//{
//     /**
//      * Function called once, before all the tests
//      */
//     public static function setUpBeforeClass()
//     {
//         // Empty related tables
//         AF_Model_Condition_DAO_Condition::getInstance()->unitTestsClearTable();
//         AF_Model_Condition_DAO_Expression::getInstance()->unitTestsClearTable();
//
//         AF_Model_DAO_AF::getInstance()->unitTestsClearTable();
//         AF_Model_DAO_Branch::getInstance()->unitTestsClearTable();
//         AF_Model_DAO_Version::getInstance()->unitTestsClearTable();
//
//         Classif_Model_DAO_Context::getInstance()->unitTestsClearTable();
//         Classif_Model_DAO_Version::getInstance()->unitTestsClearTable();
//
//         AF_Model_Form_DAO_Component::getInstance()->unitTestsClearTable();
//         AF_Model_Form_DAO_Group::getInstance()->unitTestsClearTable();
//     }
//
//     /**
//      * Constructor test
//      */
//     function testConstruct()
//     {
//        $o = new AF_Model_Condition_Expression();
//        $this->assertTrue($o instanceof AF_Model_Condition_Expression);
//        return $o;
//     }
//
//     /**
//      * Save test
//      * @param AF_Model_Condition_Expression $o
//      * @depends testConstruct
//      */
//     function testSave(AF_Model_Condition_Expression $o)
//     {
//        $o->ref = "uneCondition";
//        $o->expression = "maCondition1 & maCondition2";
//        //Test de la sauvegarde d'une expression sans AF
//        try {
//            $o->save();
//        } catch (Core_Exception_Database $e) {
//            $this->assertEquals("Impossible d'ajouter une condition sans AF", $e->getMessage());
//        }
//
//        // Test de la sauvegarde d'un objet correct
//        $af = AFTest::generateObject();
//        $o->setAF($af);
//        $o->save();
//        $this->assertNotNull($o->id, "Object id is not defined");
//        $this->assertEquals("uneCondition", $o->ref);
//
//        $firstId = $o->id;
//        $firstRef = $o->ref;
//
//        //update test
//        $o->ref = 'maCondition';
//        $o->save();
//
//        $this->assertTrue($firstId == $o->id && $firstRef != $o->ref);
//        $this->assertTrue($o->ref == 'maCondition');
//
//        return $o;
//     }
//
//     /**
//      * Load test
//      * @depends testSave
//      * @param AF_Model_Condition_Expression
//      */
//     function testLoad(AF_Model_Condition_Expression $o)
//     {
//         $a = AF_Model_Condition_Expression::load($o->id);
//         $this->assertEquals($a->id, $o->id);
//         $this->assertEquals($a->ref, $o->ref);
//     }
//
//     /**
//      * Deletion test
//      * @param AF_Model_Condition_Expression $o
//      * @depends testSave
//      */
//     function testDelete(AF_Model_Condition_Expression $o)
//     {
//        $af = $o->getAF();
//
//        $o->delete();
//        $this->assertNull($o->id);
//
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
//        if (! AF_Model_Condition_DAO_Expression::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table Expression n'est pas vide après les tests de la classe Expression\n";
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
//        if (! AF_Model_Form_DAO_Group::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table AF_Group n'est pas vide après les tests de la classe Elementary\n";
//        }
//     }
//}
//
///**
// * Tests of Condition_Expression class
// * @package AF
// * @subpackage Test
// */
//class Condition_ExpressionOtherTest extends PHPUnit_Framework_TestCase
//{
//    protected $expression;
//
//     /**
//      * Function called once, before all the tests
//      */
//     public static function setUpBeforeClass()
//     {
//         // Empty related tables
//         // Empty related tables
//         AF_Model_Condition_DAO_Condition::getInstance()->unitTestsClearTable();
//         AF_Model_Condition_DAO_Expression::getInstance()->unitTestsClearTable();
//
//         AF_Model_DAO_AF::getInstance()->unitTestsClearTable();
//         AF_Model_DAO_Branch::getInstance()->unitTestsClearTable();
//         AF_Model_DAO_Version::getInstance()->unitTestsClearTable();
//
//         Classif_Model_DAO_Context::getInstance()->unitTestsClearTable();
//         Classif_Model_DAO_Version::getInstance()->unitTestsClearTable();
//
//         AF_Model_Form_DAO_Component::getInstance()->unitTestsClearTable();
//         AF_Model_Form_DAO_Group::getInstance()->unitTestsClearTable();
//
//
////         AF_Model_Condition_DAO_Elementary::getInstance()->unitTestsClearTable();
////
////         TEC_Model_DAO_Leaf::getInstance()->unitTestsClearTable();
////         TEC_Model_DAO_Composite::getInstance()->unitTestsClearTable();
////         TEC_Model_DAO_Expression::getInstance()->unitTestsClearTable();
//     }
//
//    /**
//     * Function called before each test
//     */
//     protected function setUp()
//     {
////         try {
////            // Create a test object
////            $this->expression = Condition_ExpressionTest::generateObject();
////         } catch (Exception $e) {
////            $this->fail($e);
////         }
//     }
//
//    /**
//     * Test of getTECTree
//     */
//     function testGetTECTree()
//     {
////        $tree = $this->expression->getTECTree();
////        $this->assertEquals(TEC_Model_Component::LOGIQUE_AND, $tree->operator);
////        $childs = $tree->getChild();
////        $this->assertEquals('maCondition1', $childs[0]->name);
////        $this->assertEquals('maCondition2', $childs[1]->name);
////
////        //si l'expression est mauvaise
////        $this->expression->expression = "condition |";
////        try {
////            $tree = $this->expression->getTECTree();
////        } catch (Core_Exception_InvalidArgument $e) {
////            $this->assertEquals($e->getMessage(), 'Invalid Expression');
////        }
////        $tree->delete();
//     }
//
//    /**
//     * Test of getElementary
//     * @depends testGetTECTree
//     */
//     function testGetElementary()
//     {
////        $cond1 = Condition_ElementaryTest::generateObject();
////        $cond2 = Condition_ElementaryTest::generateObject();
////        $this->expression->expression = $cond1->ref.' & '.$cond2->ref;
////        $tree = $this->expression->getTECTree();
////        $elementary = $this->expression->getElementary($tree);
////        $this->assertEquals($cond1->ref, $elementary[1]->ref);
////        $this->assertEquals($cond2->ref, $elementary[0]->ref);
////        Condition_ElementaryTest::deleteObject($cond1);
////        Condition_ElementaryTest::deleteObject($cond2);
////        $tree->delete();
//     }
//
//    /**
//     * Function called after each test
//     */
//     protected function tearDown()
//     {
////         try {
////             // Delete the test object
////             Condition_ExpressionTest::deleteObject($this->expression);
////         } catch (Exception $e) {
////            $this->fail($e);
////         }
//     }
//
//    /**
//     * Function called once, after all the tests
//     */
//     public static function tearDownAfterClass()
//     {
//        // Check tables are empty
//        if (! AF_Model_Condition_DAO_Condition::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table Condition n'est pas vide après les tests de la classe Expression\n";
//        }
//        if (! AF_Model_Condition_DAO_Expression::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table Expression n'est pas vide après les tests de la classe Expression\n";
//        }
//        if (! AF_Model_Condition_DAO_Elementary::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table Elementary n'est pas vide après les tests de la classe Expression\n";
//        }
//        if (! AF_Model_DAO_AF::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table Af n'est pas vide après les tests de la classe Expression\n";
//        }
//        if (! AF_Model_Form_DAO_Component::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table Component n'est pas vide après les tests de la classe Expression\n";
//        }
//        if (! AF_Model_Form_DAO_Group::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table Group n'est pas vide après les tests de la classe Elementary\n";
//        }
//
//
//        if (! TEC_Model_DAO_Composite::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table de TEC: Node n'est pas vide après les tests de la classe Expression\n";
//        }
//        if (! TEC_Model_DAO_Leaf::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table de TEC: Leaf n'est pas vide après les tests de la classe Expression\n";
//        }
//        if (! TEC_Model_DAO_Leaf::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table de TEC: Tec n'est pas vide après les tests de la classe Expression\n";
//        }
//     }
//}
