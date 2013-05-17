<?php
/**
 * @author cyril.perraud
 * @package AF
 */

// Commenter la ligne ci dessous avant de comiter
// require_once dirname(__FILE__).'/../AFTest.php';

/**
 * Class de testunitaire de Output_Total
 * @package AF
 */
class Output_TotalTest
{
     /**
      * Creation of the test suite
      */
     public static function suite()
     {
        $suite = new PHPUnit_Framework_TestSuite();
//        $suite->addTestSuite('Output_TotalSetUpTest');
        return $suite;
     }

     /**
      * Generation of a test object
      * @param bool $save True (default value) if we need to save the object
      * @return AF_Model_Output_Total
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

        $o = new AF_Model_Output_Total();
        $o->setValue(new Calc_Value());
        $o->getValue()->digitalValue = 3;
        $o->getValue()->relativeUncertainty = 0;
        $o->setClassifIndicator($classifIndicator);

        $af = AFTest::generateObject();

        $afPrimarySet = new AF_Model_InputSet_Primary();
        $afPrimarySet->setAf($af);
        $afPrimarySet->save();

        $o->setPrimaryInputSet($afPrimarySet);

        if ($save) {
            $o->save();
        }

        return $o;
     }

     /**
      * Supprime les objets qui ont été utilisé lors de la génération d'un
      * OutputTotal
      * @param Af_Model_Output_Total $o
      */
     public static function deleteAssociatedObject($o)
     {
         $af = $o->getPrimaryInputSet()->getAf();
         AFTest::deleteObject($af);
         $o->getClassifIndicator()->getVersion()->delete();
     }
}

/**
 * Test of the creation/modification/deletion of the entity
 * @package AF
 */
class Output_TotalSetUpTest extends PHPUnit_Framework_TestCase
{
     /**
      * Function called once, before all the tests
      */
     public static function setUpBeforeClass()
     {
         // Empty related tables
         AF_Model_Output_DAO_Total::getInstance()->unitTestsClearTable();

         AF_Model_Input_DAO_Set::getInstance()->unitTestsClearTable();
         AF_Model_Input_Set_DAO_Primary::getInstance()->unitTestsClearTable();

         AF_Model_DAO_AF::getInstance()->unitTestsClearTable();
         AF_Model_DAO_Branch::getInstance()->unitTestsClearTable();
         AF_Model_DAO_Version::getInstance()->unitTestsClearTable();

         AF_Model_Form_DAO_Component::getInstance()->unitTestsClearTable();
         AF_Model_Form_DAO_Group::getInstance()->unitTestsClearTable();

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
        // On génère un objet sans le sauvegarder ( argument à false )
        $o = Output_TotalTest::generateObject(false);
        $this->assertEquals(3, $o->getValue()->digitalValue);
        return $o;
     }

     /**
      * Save test
      * @param AF_Model_Output_Total $o
      * @depends testConstruct
      */
     function testSave(AF_Model_Output_Total $o)
     {
        $o->save();
        $this->assertEquals(3, $o->getValue()->digitalValue);
        return $o;
     }

     /**
      * Load test
      * @depends testSave
      * @param AF_Model_Output_Total $o
      */
     function testLoad(AF_Model_Output_Total $o)
     {
         $a = AF_Model_Output_Total::load($o->getKey());
         $this->assertEquals($a->getKey(), $o->getKey());
         $this->assertEquals($o->getValue(), $a->getValue());
     }

     /**
      * Deletion test
      * @param AF_Model_Output_Total $o
      * @depends testSave
      */
     function testDelete(AF_Model_Output_Total $o)
     {
        $o->delete();
        Output_TotalTest::deleteAssociatedObject($o);
     }

     /**s
      * Function called once, after all the tests
      */
     public static function tearDownAfterClass()
     {
        // Check tables are empty
        if (! AF_Model_Output_DAO_Total::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Total n'est pas vide après les tests\n";
        }
        if (! AF_Model_Input_DAO_Set::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table AF_Set n'est pas vide après les tests\n";
        }
        if (! AF_Model_Input_Set_DAO_Primary::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table AF_Set_PrimarySet n'est pas vide après les tests\n";
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
