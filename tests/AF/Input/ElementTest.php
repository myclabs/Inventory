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
//  require_once dirname(__FILE__).'/PrimarySetTest.php';
//
///**
// * @author hugo.charbonnier
// * @author thibaud.rolland
// * @package AF
// */
//class Input_ElementTest
//{
//     /**
//      * Creation of the test suite
//      */
//     public static function suite()
//     {
//        $suite = new PHPUnit_Framework_TestSuite();
//        $suite->addTestSuite('Form_Input_ElementSetUpTest');
//        $suite->addTestSuite('Form_Input_ElementLogiqueMetierTest');
//        return $suite;
//     }//end suite()
//
//     /**
//      * Generation of a test object
//      * @return AF_Model_Input
//      */
//     public static function generateObject()
//     {
//        $o = new AF_Model_Input();
//        $o->save();
//        return $o;
//     }
//
//     /**
//      * Deletion of an object created with generateObject
//      * @param AF_Model_Input $o
//      */
//     public static function deleteObject($o)
//     {
//        $o->delete();
//     }//end deleteObject()
//
//}//end class Input_ElementTest
//
//
///**
// * Test of the creation/modification/deletion of the entity
// * @package AF
// */
//class Form_Input_ElementSetUpTest extends PHPUnit_Framework_TestCase
//{
//     /**
//      * Function called once, before all the tests
//      */
//     public static function setUpBeforeClass()
//     {
//         // Empty related tables
//         AF_Model_Input_DAO_Set::getInstance()->unitTestsClearTable();
//         AF_Model_Input_Set_DAO_Primary::getInstance()->unitTestsClearTable();
//         AF_Model_Input_Set_DAO_Sub::getInstance()->unitTestsClearTable();
//         AF_Model_Input_DAO_Element::getInstance()->unitTestsClearTable();
//
//         AF_Model_Form_DAO_Component::getInstance()->unitTestsClearTable();
//         AF_Model_Form_DAO_Element::getInstance()->unitTestsClearTable();
//
//         AF_Model_Form_DAO_Numeric::getInstance()->unitTestsClearTable();
//         AF_Model_Form_DAO_NumericInput::getInstance()->unitTestsClearTable();
//     }//end setUpBeforeClass()
//
//     /**
//      * Constructor test
//      */
//     function testConstruct()
//     {
//         $o = new AF_Model_Input();
//         $this->assertTrue($o instanceof AF_Model_Input);
//         return $o;
//     }//end testConstruct()
//
//     /**
//      * Save test
//      * @param AF_Model_Input $o
//      * @depends testConstruct
//      */
//     function testSave(AF_Model_Input $o)
//     {
//        $af        = AFTest::generateObject();
//        $set       = Input_PrimarySetTest::generateObject($af);
//        $component = Form_NumericInputTest::generateObject();
//        $o->setSet($set);
//        $o->setComponent($component);
//        $o->value = 5;
//        $o->save();
//
//        $component2 = $o->getComponent();
//
//        $this->assertNotNull($o->getComponent(), "Object id is not defined");
//        $this->assertNotNull($o->getSet(), "Object id is not defined");
//        $this->assertEquals(5, $o->value);
//        return $o;
//     }//end testSave()
//
//
//     /**
//      * Load test
//      * @depends testSave
//      * @param AF_Model_Input $o
//      */
//     function testLoad(AF_Model_Input $o)
//     {
//         $a = AF_Model_Input::load(array($o->getSet()->getKey()), $o->getComponent()->id);
//
//         $this->assertEquals($a->getComponent(), $o->getComponent());
//         $this->assertEquals($a->getSet(), $o->getSet());
//         $this->assertTrue($a->getComponent() instanceof AF_Model_Component);
//         $this->assertTrue($a->getSet() instanceof AF_Model_InputSet);
//     }//end testLoad()
//
//
//     /**
//      * Deletion test
//      * @param AF_Model_Input $o
//      * @depends testSave
//      */
//     function testDelete(AF_Model_Input $o)
//     {
//         $af = $o->getSet()->getAf();
//         AFTest::deleteObject($af);
//         $component = $o->getComponent();
//         $o->delete();
//         Form_NumericInputTest::deleteObject($component);
//         try {
//             $o->getComponent();
//             $o->getSet();
//         } catch (Core_Exception_InvalidArgument $e) {
//             $this->assertEquals($e->getMessage(), 'nullAttribute');
//         }
//     }//end testDelete()
//
//     /**
//      * Function called once, after all the tests
//      */
//     public static function tearDownAfterClass()
//     {
//        // Check tables are empty
//         if (! AF_Model_Input_DAO_Set::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table Set n'est pas vide après les tests de la classe Input_Element\n";
//         }
//         if (! AF_Model_Input_Set_DAO_Primary::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table PrimarySet n'est pas vide après les tests de la classe Input_Element\n";
//         }
//         if (! AF_Model_Input_Set_DAO_Sub::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table SubSet n'est pas vide après les tests de la classe Input_Element\n";
//         }
//         if (! AF_Model_Input_DAO_Element::getInstance()->unitTestsIsTableEmpty()) {
//            echo "\nLa table InputElement n'est pas vide après les tests de la classe Input_Element\n";
//         }
//
//         if (! AF_Model_Form_DAO_Component::getInstance()->unitTestsIsTableEmpty()) {
//             echo "\nLa table Component n'est pas vide après les tests de la classe Input_Element\n";
//         }
//         if (! AF_Model_Form_DAO_Element::getInstance()->unitTestsIsTableEmpty()) {
//             echo "\nLa table Element n'est pas vide après les tests de la classe Input_Element\n";
//         }
//         if (! AF_Model_Form_DAO_Numeric::getInstance()->unitTestsIsTableEmpty()) {
//             echo "\nLa table Numeric n'est pas vide après les tests de la classe Input_Element\n";
//         }
//         if (! AF_Model_Form_DAO_NumericInput::getInstance()->unitTestsIsTableEmpty()) {
//             echo "\nLa table NumericInput n'est pas vide après les tests de la classe Input_Element\n";
//         }
//     }//end tearDownAfterClass()
//
//}//end class Form_Input_Element_SetUpTest
//
//
///**
// * Form_Input_ElementLogiqueMetierTest
// * @package AF
// */
//class Form_Input_ElementLogiqueMetierTest extends PHPUnit_Framework_TestCase
//{
//    /**
//     * AF
//     * @var AF_Model_AF
//     */
//    protected $_af         = null;
//
//    /**
//     * PrimarySet
//     * @var AF_Model_InputSet_Primary
//     */
//    protected $_primarySet = null;
//
//
//    /**
//     * Composant de AF
//     * @var  AF_Model_Form_NumericInput
//     */
//    protected $_component = null;
//
//    /**
//     * Input Element de AF
//     * @var AF_Model_Input
//     */
//    protected $_inputElement = null;
//
//    /**
//     * Revision associated to the changes
//     * @var Log_Model_Revision
//     */
//    protected $_revision;
//
//    /**
//     * Function called once, before all the tests.
//     */
//    public static function setUpBeforeClass()
//    {
//        // Empty related tables
//        AF_Model_Input_DAO_Set::getInstance()->unitTestsClearTable();
//        AF_Model_Input_Set_DAO_Primary::getInstance()->unitTestsClearTable();
//        AF_Model_Input_Set_DAO_Sub::getInstance()->unitTestsClearTable();
//        AF_Model_Input_DAO_Element::getInstance()->unitTestsClearTable();
//
//        AF_Model_Form_DAO_Component::getInstance()->unitTestsClearTable();
//        AF_Model_Form_DAO_Element::getInstance()->unitTestsClearTable();
//        AF_Model_Form_DAO_Numeric::getInstance()->unitTestsClearTable();
//        AF_Model_Form_DAO_NumericInput::getInstance()->unitTestsClearTable();
//
//        Log_Model_DAO_Change::getInstance()->unitTestsClearTable();
//        Log_Model_DAO_ChangeAttribute::getInstance()->unitTestsClearTable();
//        Log_Model_DAO_ChangeParentObject::getInstance()->unitTestsClearTable();
//        Log_Model_DAO_ChangeParentObjectCache::getInstance()->unitTestsClearTable();
//        Log_Model_DAO_ChangeDependentObject::getInstance()->unitTestsClearTable();
//        Log_Model_DAO_Revision::getInstance()->unitTestsClearTable();
//        Log_Model_DAO_RevisionArgument::getInstance()->unitTestsClearTable();
//    }//end setUpBeforeClass()
//
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
//        $this->_component  = Form_NumericInputTest::generateObject();
//
//        $this->_inputElement = new AF_Model_Input();
//        $this->_inputElement->setSet($this->_primarySet);
//        $this->_inputElement->setComponent($this->_component);
//        $this->_inputElement->save();
//    }//end setUp()
//
//
//    /**
//     * Test des fonctions set/getSet et set/getComponent.
//     */
//    function testSetterGetter()
//    {
//        // Test fonctionnement ok.
//
//        $this->assertEquals($this->_primarySet->getKey(), $this->_inputElement->getSet()->getKey());
//        $this->assertEquals($this->_component->id, $this->_inputElement->getComponent()->id);
//
//        // Test des exceptions.
//
//        $elementErreur = new AF_Model_Input();
//
//        // Test exception levée par setSet si l'id du set est null.
//        $set = new AF_Model_InputSet_Primary();
//        try {
//            $elementErreur->setSet($this->_primarySet);
//        } catch (Core_Exception_InvalidArgument $e) {
//            $this->assertEquals($e->getMessage(), 'nullParameterId');
//        }
//
//        // Test exception levée par setComponent si l'id du set est null.
//        $component = new AF_Model_Component_Group();
//        try {
//            $elementErreur->setComponent($component);
//        } catch (Core_Exception_InvalidArgument $e) {
//            $this->assertEquals($e->getMessage(), 'nullParameterId');
//        }
//
//        // Test exception levée par getComponent si l'attribut idComponent n'est pas correctement renseigné.
//        try {
//            $elementErreur->getComponent();
//        } catch (Core_Exception_InvalidArgument $e) {
//            $this->assertEquals($e->getMessage(), 'nullAttribute');
//        }
//
//        // Test exception levée par getSet si l'attribut _idSet n'est pas correctement renseigné.
//        try {
//            $elementErreur->getSet();
//        } catch (Core_Exception_InvalidArgument $e) {
//            $this->assertEquals($e->getMessage(), 'nullAttribute');
//        }
//    }//end testSetterGetter()
//
//
//    /**
//     * Test de le méthode isValid().
//     */
//    function testIsValid()
//    {
//        // Test du controle de l'onglet saisie renvoi un message champ requis
//        $result = $this->_inputElement->isValid();
//        $this->assertEquals($result['value'], __('UI', 'formValidation', 'emptyRequiredField'));
//
//        // Cas isValid() et value = integer
//        // @todo ? pourquoi on test pour un integer ?
//        $this->_inputElement->value = 5;
//        $this->_inputElement->save();
//        $result = $this->_inputElement->isValid();
//        $this->assertEquals($result['value'], null);
//
//        // Cas isValid() et value = calc_value
//        $value                       = new Calc_Value();
//        $value->digitalValue         = 5;
//        $this->_inputElement->value  = $value;
//        $this->_inputElement->save();
//
//        // Test du controle de l'onglet saisie renvoi un message null donc valide
//        $result = $this->_inputElement->isValid();
//        $this->assertEquals($result['value'], null);
//    }//end testIsValid()
//
//    /**
//     * Function called after each tests.
//     */
//    protected function tearDown()
//    {
//        AFTest::deleteObject($this->_af);
//        Form_NumericInputTest::deleteObject($this->_component);
//        $this->_revision->delete();
//        Zend_Registry::getInstance()->set('lastRevision', null);
//    }
//
//    /**
//     * Function called once, after all the tests.
//     */
//    public static function tearDownAfterClass()
//    {
//       // Check tables are empty
//       if (! AF_Model_Input_DAO_Set::getInstance()->unitTestsIsTableEmpty()) {
//          echo "\nLa table Set n'est pas vide après les tests de la classe Input_Element\n";
//       }
//       if (! AF_Model_Input_Set_DAO_Primary::getInstance()->unitTestsIsTableEmpty()) {
//          echo "\nLa table PrimarySet n'est pas vide après les tests de la classe Input_Element\n";
//       }
//       if (! AF_Model_Input_Set_DAO_Sub::getInstance()->unitTestsIsTableEmpty()) {
//          echo "\nLa table SubSet n'est pas vide après les tests de la classe Input_Element\n";
//       }
//       if (! AF_Model_Input_DAO_Element::getInstance()->unitTestsIsTableEmpty()) {
//          echo "\nLa table InputElement n'est pas vide après les tests de la classe Input_Element\n";
//       }
//       if (! AF_Model_Form_DAO_Component::getInstance()->unitTestsIsTableEmpty()) {
//           echo "\nLa table Component n'est pas vide après les tests de la classe Input_Element\n";
//       }
//       if (! AF_Model_Form_DAO_Element::getInstance()->unitTestsIsTableEmpty()) {
//           echo "\nLa table Element n'est pas vide après les tests de la classe Input_Element\n";
//       }
//       if (! AF_Model_Form_DAO_Numeric::getInstance()->unitTestsIsTableEmpty()) {
//           echo "\nLa table Numeric n'est pas vide après les tests de la classe Input_Element\n";
//       }
//       if (! AF_Model_Form_DAO_NumericInput::getInstance()->unitTestsIsTableEmpty()) {
//           echo "\nLa table NumericInput n'est pas vide après les tests de la classe Input_Element\n";
//       }
//    }//end tearDownAfterClass()
//
//}//end class Form_Input_ElementLogiqueMetierTest
