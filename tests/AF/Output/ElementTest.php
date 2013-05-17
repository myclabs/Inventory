<?php
/**
 * @author hugo.charbonnier
 * @author thibaud.rolland
 * @package AF
 */

// Commenter la ligne ci dessous avant de comiter
// require_once dirname(__FILE__).'/../AFTest.php';

/**
 * Class de testunitaire de Output_Element
 * @package AF
 */
class Output_ElementTest
{
     /**
      * Creation of the test suite
      */
     public static function suite()
     {
        $suite = new PHPUnit_Framework_TestSuite();
//        $suite->addTestSuite('Output_ElementSetUpTest');
        return $suite;
     }

     /**
      * Generation of a test object
      * @param bool $save True (default value) if we need to save the object
      * @return AF_Model_Output_Element
      */
     public static function generateObject($save=true)
     {
        $date = new Core_Date();
        $classifVersion = new Classif_Model_Version();
        $classifVersion->setRef(Core_Tools::generateString());
        $classifVersion->setLabel('Label_generate_classif_version');
        $classifVersion->setCreationDate($date->now()->get('YYYY-MM-dd HH:mm:ss'));
        $classifVersion->save();

        $classifIndicator = new Classif_Model_Indicator();
        $classifIndicator->setLabel('Label_generate_classif_indicator');
        $classifIndicator->setRef(Core_Tools::generateString());
        $classifIndicator->setUnit(new Unit_Model_APIUnit("ref"));
        $classifIndicator->setVersion($classifVersion);
        $classifIndicator->setUnitRatio(new Unit_Model_APIUnit('t'));
        $classifIndicator->save();

        $af = AFTest::generateObject();

        $afPrimarySet = new AF_Model_InputSet_Primary();
        $afPrimarySet->setAf($af);
        $afPrimarySet->save();

        $o = new AF_Model_Output_Element();
        $o->value = new Calc_Value();
        $o->value->digitalValue = 3;
        $o->value->relativeUncertainty = 0;
        $o->setContextIndicator($classifIndicator);
        $o->setClassifContext($classifVersion);
        $o->setInputSet($afPrimarySet);
        if ($save) {
            $o->save();
        }

        return $o;
     }

     /**
      * Supprime les objets qui ont été utilisé lors de la génération d'un
      * OutputElement
      * Rappel : On ne supprime pas un outputElement directement mais on le supprime
      * à partir de son InputSet. Ceci est automatiquement fait lors de la suppression
      * de l'inputSet via la méthode deleteFromPrimarySet()
      * @param Af_Model_Output_Element $o
      */
     public static function deleteAssociatedObject($o)
     {
         $o->getContextIndicator()->getVersion()->delete();
         $af = $o->getInputSet()->getAf();
         AFTest::deleteObject($af);
     }

}

/**
 * Test of the creation/modification/deletion of the entity
 * @package AF
 */
class Output_ElementSetUpTest extends PHPUnit_Framework_TestCase
{
     /**
      * Function called once, before all the tests
      */
     public static function setUpBeforeClass()
     {
         // Empty related tables
         AF_Model_DAO_AF::getInstance()->unitTestsClearTable();
         AF_Model_DAO_Branch::getInstance()->unitTestsClearTable();
         AF_Model_DAO_Version::getInstance()->unitTestsClearTable();

         AF_Model_Form_DAO_Component::getInstance()->unitTestsClearTable();
         AF_Model_Form_DAO_Group::getInstance()->unitTestsClearTable();

         AF_Model_Output_DAO_Element::getInstance()->unitTestsClearTable();

         Tree_Model_DAO_Component::getInstance()->unitTestsClearTable();

         Classif_Model_DAO_Indicator::getInstance()->unitTestsClearTable();
         Classif_Model_DAO_Version::getInstance()->unitTestsClearTable();
         Classif_Model_DAO_Context::getInstance()->unitTestsClearTable();
     }

     /**
      * Constructor test
      */
     function testConstruct()
     {
        $o = Output_ElementTest::generateObject(false);

        $this->assertEquals(3, $o->value->digitalValue);

        return $o;
     }

     /**
      * Save test
      * @param AF_Model_Output_Element $o
      * @depends testConstruct
      */
     function testSave(AF_Model_Output_Element $o)
     {
        // une sauvegarde est faite
        $o->save();
        $this->assertEquals(3, $o->value->digitalValue);
        return $o;
     }

     /**
      * Load test
      * @depends testSave
      * @param AF_Model_Output_Element $o
      */
     function testLoad(AF_Model_Output_Element $o)
     {
         $a = AF_Model_Output_Element::load($o->getKey());
         $this->assertEquals($a->getKey(), $o->getKey());
         $this->assertEquals($o->value, $a->value);
     }

     /**
      * Deletion test
      * @param AF_Model_Output_Element $o
      * @expectedException Core_Exception
      * @depends testSave
      */
     function testDelete(AF_Model_Output_Element $o)
     {
        Output_ElementTest::deleteAssociatedObject($o);
        $o->delete();
     }

     /**
      * Function called once, after all the tests
      */
     public static function tearDownAfterClass()
     {
        // Check tables are empty
        if (! AF_Model_Output_DAO_Element::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Element n'est pas vide après les tests\n";
        }
        if (! AF_Model_DAO_Version::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table AF_Version n'est pas vide après les tests\n";
        }
        if (! AF_Model_DAO_AF::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table AF n'est pas vide après les tests\n";
        }
        if (! AF_Model_DAO_Branch::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table AF_Branch n'est pas vide après les tests\n";
        }
        if (! AF_Model_Form_DAO_Component::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table AF_Component n'est pas vide après les tests\n";
        }
        if (!  AF_Model_Form_DAO_Group::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table AF_Group n'est pas vide après les tests\n";
        }
        if (! Tree_Model_DAO_Component::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Tree_Component n'est pas vide après les tests\n";
        }
        if (! Classif_Model_DAO_Indicator::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Classif_Indicator n'est pas vide après les tests\n";
        }
        if (!  Classif_Model_DAO_Context::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Classif_Context n'est pas vide après les tests\n";
        }
        if (! Classif_Model_DAO_Version::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Classif_Version n'est pas vide après les tests\n";
        }
     }
}
