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
//  require_once dirname(__FILE__).'/../Output/ElementTest.php';
//  require_once dirname(__FILE__).'/../Form/NumericInputTest.php';
//  require_once dirname(__FILE__).'/../AFTest.php';
//
///**
// * primarySetTest.
// * @author hugo.charbonnier
// * @author thibaud.rolland
// * @package AF
// */
//class Input_PrimarySetTest
//{
//     /**
//      * Creation of the test suite
//      */
//     public static function suite()
//     {
//        $suite = new PHPUnit_Framework_TestSuite();
//        $suite->addTestSuite('Form_Input_PrimarySetSetUpTest');
//        $suite->addTestSuite('Form_Input_PrimarySetOtherTest');
//        return $suite;
//     }//end suite()
//
//
//     /**
//      * Generation of a test object
//      * @param AF_Model_AF
//      * @return AF_Model_InputSet_Primary
//      */
//     public static function generateObject($af)
//     {
//        $o = new AF_Model_InputSet_Primary();
//        $o->setAf($af);
//        $o->setFreeLabel('test');
//        $o->comment = "comment";
//        $o->save();
//        return $o;
//     }//end generateObject()
//
//
//     /**
//      * Deletion of an object created with generateObject
//      * @param AF_Model_InputSet_Primary $o
//      */
//     public static function deleteObject($o)
//     {
//        $o->delete();
//     }//end deleteObject()
//
//}//end class Input_PrimarySetTest
//
//
///**
// * Test of the creation/modification/deletion of the entity.
// * @package AF
// */
//class Form_Input_PrimarySetSetUpTest extends PHPUnit_Framework_TestCase
//{
//     /**
//      * Function called once, before all the tests
//      */
//     public static function setUpBeforeClass()
//     {
//         // Empty related tables
//         AF_Model_Input_DAO_Set::getInstance()->unitTestsClearTable();
//         AF_Model_Input_Set_DAO_Primary::getInstance()->unitTestsClearTable();
//
//         AF_Model_Input_DAO_Element::getInstance()->unitTestsClearTable();
//         AF_Model_Input_DAO_ValueTab::getInstance()->unitTestsClearTable();
//
//         AF_Model_Form_DAO_Group::getInstance()->unitTestsClearTable();
//
//         AF_Model_Output_DAO_Element::getInstance()->unitTestsClearTable();
//
//         AF_Model_Form_DAO_Component::getInstance()->unitTestsClearTable();
//         AF_Model_Form_DAO_Element::getInstance()->unitTestsClearTable();
//         AF_Model_Form_DAO_Numeric::getInstance()->unitTestsClearTable();
//         AF_Model_Form_DAO_NumericInput::getInstance()->unitTestsClearTable();
//     }//end setUpBeforeClass()
//
//
//     /**
//      * Constructor test
//      */
//     function testConstruct()
//     {
//         $o = new AF_Model_InputSet_Primary();
//         $this->assertTrue($o instanceof AF_Model_InputSet_Primary);
//         $this->assertFalse($o->getDynamic());
//         return $o;
//     }//end testConstruct()
//
//
//     /**
//      * Save test
//      * @param AF_Model_InputSet_Primary $o
//      * @depends testConstruct
//      */
//     function testSave(AF_Model_InputSet_Primary $o)
//     {
//        $af           = AFTest::generateObject();
//        $o->setFreeLabel('testPrimary');
//        $o->comment   = "comment";
//        $o->setAf($af);
//        $o->save();
//
//        $firstId    = $o->getKey();
//        $firstLabel = $o->getFreeLabel();
//        $this->assertNotNull($o->getKey(), "Object id is not defined");
//
//        //Test de l'update.
//        $o->setFreeLabel('testPrimaryUpdate');
//        $o->save();
//        $secondId = $o->getKey();
//        $secondLabel = $o->getFreeLabel();
//
//        $this->assertEquals($firstId, $secondId);
//        $this->assertNotEquals($firstLabel, $secondLabel);
//        $this->assertEquals($secondLabel, 'testPrimaryUpdate');
//        return $o;
//     }//end testSave()
//
//
//     /**
//      * Load test
//      * @depends testSave
//      * @param AF_Model_InputSet_Primary $o
//      */
//     function testLoad(AF_Model_InputSet_Primary $o)
//     {
//         $a = AF_Model_InputSet_Primary::load($o->getKey());
//         $this->assertEquals($a->getKey(), $o->getKey());
//         $this->assertEquals($a->getFreeLabel(), $o->getFreeLabel());
//         $this->assertTrue($a instanceof AF_Model_InputSet_Primary);
//     }//end testLoad()
//
//
//     /**
//      * Deletion test
//      * @param AF_Model_InputSet_Primary $o
//      * @depends testSave
//      */
//     function testDelete(AF_Model_InputSet_Primary $o)
//     {
//        // Test de la suppression des éléments associés à un set
//        // lors de la suppression de ce set.
//        $component1 = Form_NumericInputTest::generateObject();
//        $component2 = Form_NumericInputTest::generateObject();
//
//        $element1 = new AF_Model_Input();
//        $element1->setComponent($component1);
//        $element1->setSet($o);
//        $element1->value = array(1=>1,
//                                 2=>2);
//        $element1->save();
//
//        $element2 = new AF_Model_Input();
//        $element2->setComponent($component2);
//        $element2->setSet($o);
//        $element2->value = array(1=>1,
//                                 2=>2);
//        $element2->save();
//
//        // Pour les tests uniquement
//        $af = AFtest::deleteObject($o->getAf());
//
//        $o->delete();
//        $this->assertNull($o->getKey());
//        Form_NumericInputTest::deleteObject($component1);
//        Form_NumericInputTest::deleteObject($component2);
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
//        if (! AF_Model_Input_Set_DAO_Primary::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table PrimarySet n'est pas vide après les tests\n";
//        }
//
//        if (! AF_Model_Output_DAO_Element::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table OutputElement n'est pas vide après les tests\n";
//        }
//
//        if (! AF_Model_Input_DAO_Element::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table inputElement n'est pas vide après les tests\n";
//        }
//        if (! AF_Model_Input_DAO_ValueTab::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table valueTab n'est pas vide après les tests\n";
//        }
//        if (! AF_Model_Form_DAO_Component::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table Component n'est pas vide après les tests\n";
//        }
//        if (! AF_Model_Form_DAO_Element::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table Element n'est pas vide après les tests\n";
//        }
//        if (! AF_Model_Form_DAO_Numeric::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table Numeric n'est pas vide après les tests\n";
//        }
//        if (! AF_Model_Form_DAO_NumericInput::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table NumericInput n'est pas vide après les tests\n";
//        }
//     }//end tearDownAfterClass()
//
//}//end class Form_Input_PrimarySetSetUpTest
//
///**
// * Form_Input_PrimarySetOtherTest
// * @package AF
// */
//class Form_Input_PrimarySetOtherTest extends PHPUnit_Framework_TestCase
//{
//    /**
//     * AF
//     * @var AF_Model_AF
//     */
//    protected $_af;
//
//    /**
//     * PrimarySet de l'AF
//     * @var AF_Model_InputSet_Primary
//     */
//    protected $_primarySet;
//
//    /**
//     * Un Element de l'AF
//     * @var AF_Model_Input
//     */
//    protected $_element1;
//
//    /**
//     * Un Element de l'AF
//     * @var AF_Model_Input
//     */
//    protected $_element2;
//
//    /**
//     * Composant de AF
//     * @var  AF_Model_Form_NumericInput
//     */
//    protected $_component1;
//
//    /**
//     * Composant de AF
//     * @var  AF_Model_Form_NumericInput
//     */
//    protected $_component2;
//
//    /**
//     * Revision associated to the changes
//     * @var Log_Model_Revision
//     */
//    protected $_revision;
//
//
//   /**
//    * Function called once, before all the tests.
//    */
//    public static function setUpBeforeClass()
//    {
//         Zend_Registry::set('desactiverMultiton', false);
//         // Empty related tables
//         AF_Model_Input_DAO_Set::getInstance()->unitTestsClearTable();
//         AF_Model_Input_Set_DAO_Primary::getInstance()->unitTestsClearTable();
//
//         AF_Model_DAO_AF::getInstance()->unitTestsClearTable();
//
//         AF_Model_Input_DAO_Element::getInstance()->unitTestsClearTable();
//         AF_Model_Input_DAO_ValueTab::getInstance()->unitTestsClearTable();
//
//         AF_Model_Output_DAO_Element::getInstance()->unitTestsClearTable();
//
//         AF_Model_Form_DAO_Component::getInstance()->unitTestsClearTable();
//         AF_Model_Form_DAO_Element::getInstance()->unitTestsClearTable();
//         AF_Model_Form_DAO_Numeric::getInstance()->unitTestsClearTable();
//         AF_Model_Form_DAO_NumericInput::getInstance()->unitTestsClearTable();
//
//         Classif_Model_DAO_Version::getInstance()->unitTestsClearTable();
//         Classif_Model_DAO_Indicator::getInstance()->unitTestsClearTable();
//
//         Log_Model_DAO_Change::getInstance()->unitTestsClearTable();
//         Log_Model_DAO_ChangeAttribute::getInstance()->unitTestsClearTable();
//         Log_Model_DAO_ChangeParentObject::getInstance()->unitTestsClearTable();
//         Log_Model_DAO_ChangeParentObjectCache::getInstance()->unitTestsClearTable();
//         Log_Model_DAO_ChangeDependentObject::getInstance()->unitTestsClearTable();
//         Log_Model_DAO_Revision::getInstance()->unitTestsClearTable();
//         Log_Model_DAO_RevisionArgument::getInstance()->unitTestsClearTable();
//    }//end setUpBeforeClass()
//
//    /**
//     * Function called before each tests.
//     */
//    protected function setUp()
//    {
//        // Pour forcer les logs à logguer les changements d'état des objets
//        $this->_revision = new Log_Model_Revision();
//        $this->_revision->action = 'action';
//        $this->_revision->command = 'command';
//        $this->_revision->addArgument('arg');
//        $this->_revision->setUser(User_Model_User::login('user', 'user'));
//        $this->_revision->save();
//        Zend_Registry::getInstance()->set('lastRevision', $this->_revision);
//
//        $this->_af         = AFTest::generateObject();
//        $this->_primarySet = Input_PrimarySetTest::generateObject($this->_af);
//
//        $this->_component1 = Form_NumericInputTest::generateObject();
//        $this->_component2 = Form_NumericInputTest::generateObject();
//
//        $this->_element1 = new AF_Model_Input();
//        $this->_element1->setComponent($this->_component1);
//        $this->_element1->setSet($this->_primarySet);
//        $this->_element1->value = array(1=>1, 2=>2);
//        $this->_element1->save();
//
//        $this->_element2 = new AF_Model_Input();
//        $this->_element2->setComponent($this->_component2);
//        $this->_element2->setSet($this->_primarySet);
//        $this->_element2->value = array(1=>1, 2=>2);
//        $this->_element2->save();
//
//
//
//        // Création des objets utiles pour Créer des AF_Output_Element
//        $date           = new Core_Date();
//        $this->_classifVersion = new Classif_Model_Version();
//        $this->_classifVersion->setRef(Core_Tools::generateString());
//        $this->_classifVersion->setLabel('Label_generate_classif_version');
//        $this->_classifVersion->setCreationDate($date->now()->get('YYYY-MM-dd HH:mm:ss'));
//        $this->_classifVersion->save();
//
//
//        $this->_afDead = AFTest::generateObject();
//
//        $this->_classifIndicator = new Classif_Model_Indicator();
//        $this->_classifIndicator->setLabel('Label_generate_classif_indicator');
//        $this->_classifIndicator->setRef(Core_Tools::generateString());
//        $this->_classifIndicator->setUnit(new Unit_Model_APIUnit("ref"));
//        $this->_classifIndicator->setVersion($this->_classifVersion);
//        $this->_classifIndicator->setUnitRatio(new Unit_Model_APIUnit('t'));
//        $this->_classifIndicator->save();
//
//
//        $this->_afDeadPrimarySet = new AF_Model_InputSet_Primary();
//        $this->_afDeadPrimarySet->setAf($this->_afDead);
//        $this->_afDeadPrimarySet->save();
//
//        // Output Element 1
//        $this->_outputElement1 = new AF_Model_Output_Element();
//        $this->_outputElement1->value = new Calc_Value();
//        $this->_outputElement1->value->digitalValue = 3;
//        $this->_outputElement1->value->relativeUncertainty = 0;
//        $this->_outputElement1->setIdClassifIndicator($this->_classifIndicator);
//        $this->_outputElement1->setClassifContext($this->_classifVersion);
//        $this->_outputElement1->setInputSet($this->_afDeadPrimarySet);
//        $this->_outputElement1->save();
//
//        // Output Element 2
//        $this->_outputElement2 = new AF_Model_Output_Element();
//        $this->_outputElement2->value = new Calc_Value();
//        $this->_outputElement2->value->digitalValue = 3;
//        $this->_outputElement2->value->relativeUncertainty = 0;
//        $this->_outputElement2->setIdClassifIndicator($this->_classifIndicator);
//        $this->_outputElement2->setClassifContext($this->_classifVersion);
//        $this->_outputElement2->setInputSet($this->_afDeadPrimarySet);
//        $this->_outputElement2->save();
//    }//end setUp()
//
//
//    /**
//     * Enter description here ...
//     */
//    function testAddHasRemoveGetOutputElement()
//    {
//         //Test de AddOutputElement()
//         // On supprime le primary set initialement associé à notre output pour
//         // qu'après son remplacement, il ne reste pas en bd.
//         $this->_outputElement1->setInputSet($this->_primarySet);
//         $this->_outputElement1->save();
//
//         // On supprime le primary set initialement associé à notre output pour
//         // qu'après son remplacement, il ne reste pas en bd.
//         $this->_outputElement2->setInputSet($this->_primarySet);
//         $this->_outputElement2->save();
//
//         $this->_primarySet->addOutputElement($this->_outputElement1);
//         $this->_primarySet->addOutputElement($this->_outputElement2);
//
//         // Test getOutputElements()
//         $this->assertEquals(2, count($this->_primarySet->getOutputElements()));
//         $this->assertContains($this->_outputElement1, $this->_primarySet->getOutputElements());
//         $this->assertContains($this->_outputElement2, $this->_primarySet->getOutputElements());
//
//         // Vérification de l'update d'une outputElement déjà ajouté au tableau.
//         $this->_primarySet->addOutputElement($this->_outputElement2);
//         $this->assertEquals(2, count($this->_primarySet->getOutputElements()));
//
//
//         // Test de l'erreur générée si l'output passé en paramètre de addOutputElement
//         // n'est pas enregistré en base.
//         $outputElement3 = new AF_Model_Output_Element();
//         try {
//             $this->_primarySet->addOutputElement($outputElement3);
//         } catch (Core_Exception_InvalidArgument $e) {
//             $this->assertEquals($e->getMessage(), 'Invalid Output : id is null');
//         }
//
//
//         // Test de la méthode hasOutputElement()
//         $this->assertTrue($this->_primarySet->hasOutputElement($this->_outputElement2));
//         $this->assertFalse($this->_primarySet->hasOutputElement($outputElement3));
//
//
//         // Test de la méthode removeElement()
//         $this->_primarySet->removeOutputElement($this->_outputElement1);
//         $this->assertFalse($this->_primarySet->hasOutputElement($this->_outputElement1));
//
//    }//end testAddHasRemoveOutputElement()
//
//    /**
//     * Test de la fonction getElements
//     */
//    function testGetElements()
//    {
//        $result = $this->_primarySet->getElements()->toArray();
//        $this->assertEquals(2, count($result));
//        $this->assertContains($this->_element1, $result);
//        $this->assertContains($this->_element2, $result);
//    }
//
//
//    /**
//     * Enter description here ...
//     * @expectedException Core_Exception_InvalidArgument
//     */
//    function testGetElementByComponentId()
//    {
//        $result = $this->_primarySet->getInputForComponent($this->_component1);
//
//        $this->assertEquals($result, $this->_element1);
//
//        //Test erreur générée si le composant passé en paramètre n'a pas été préalablement enregistré.
//        $component = new AF_Model_Form_NumericInput();
//        $result = $this->_primarySet->getInputForComponent($component);
//    }
//
//	/**
//     * test de l'attribus Finished
//     */
//    function testGetSetFinished()
//    {
//        $tempComplete = $this->_primarySet->isInputComplete();
//
//        $this->_primarySet->setCompleteInput(true);
//
//        $this->_primarySet->setFinished(true);
//        $this->assertTrue($this->_primarySet->getFinished());
//
//        $this->_primarySet->setFinished(false);
//        $this->assertFalse($this->_primarySet->getFinished());
//
//        $this->_primarySet->setCompleteInput($tempComplete);
//    }
//
//
//    /**
//     * Function called after each tests.
//     */
//    protected function tearDown()
//    {
//        AFtest::deleteObject($this->_af);
//        AFtest::deleteObject($this->_afDead);
//        Form_NumericInputTest::deleteObject($this->_component1);
//        Form_NumericInputTest::deleteObject($this->_component2);
//        $this->_classifIndicator->delete();
//        $this->_classifVersion->delete();
//        $this->_element1->delete();
//        $this->_element2->delete();
//        $this->_revision->delete();
//        Zend_Registry::getInstance()->set('lastRevision', null);
//    }//end tearDown()
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
//        if (! AF_Model_Input_Set_DAO_Primary::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table PrimarySet n'est pas vide après les tests\n";
//        }
//
//        if (! AF_Model_Output_DAO_Element::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table OutputElement n'est pas vide après les tests\n";
//        }
//
//        if (! AF_Model_Input_DAO_Element::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table inputElement n'est pas vide après les tests\n";
//        }
//        if (! AF_Model_Input_DAO_ValueTab::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table valueTab n'est pas vide après les tests\n";
//        }
//        if (! AF_Model_Form_DAO_Component::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table Component n'est pas vide après les tests\n";
//        }
//        if (! AF_Model_Form_DAO_Element::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table Element n'est pas vide après les tests\n";
//        }
//        if (! AF_Model_Form_DAO_Numeric::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table Numeric n'est pas vide après les tests\n";
//        }
//        if (! AF_Model_Form_DAO_NumericInput::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table NumericInput n'est pas vide après les tests\n";
//        }
//        if (! Classif_Model_DAO_Version::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table Classif_Verion n'est pas vide après les tests\n";
//        }
//        if (! Classif_Model_DAO_Indicator::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table Classif_Indicator n'est pas vide après les tests\n";
//        }
//
//         Zend_Registry::set('desactiverMultiton', true);
//     }//end tearDownAfterClass()
//}
