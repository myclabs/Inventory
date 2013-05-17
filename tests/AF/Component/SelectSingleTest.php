<?php
/**
 * @author yoann.croizer
 * @package AF
 */

// Les 2 lignes du dessous sont à décomenter pour les tests en local et à commenter avant commit
//  require_once dirname(__FILE__).'/OptionTest.php';
//  require_once dirname(__FILE__).'/../AFTest.php';

/**
 * @package AF
 */
class Form_SelectSingleTest
{
     /**
      * Creation of the test suite.
      */
     public static function suite()
     {
        $suite = new PHPUnit_Framework_TestSuite();
//        $suite->addTestSuite('Form_SelectSingleSetUpTest');
//        $suite->addTestSuite('Form_SelectSingleLogiqueMetierTest');
        return $suite;
     }

     /**
      * Generation of a test object.
      * @param String $refBranch Ref de la branche de l'af
      * @param String $refClassifVersion Ref de la version de classif de la branche
      * @return AF_Model_Component_Select_Single
      */
     public static function generateObject($refBranch = 'refBranch', $refClassifVersion = 'Ref_generate_classif_version')
     {
//        $select = new AF_Model_Component_Select();
        $af = AFTest::generateObject($refBranch, $refClassifVersion);
        $o = new AF_Model_Component_Select_Single();
        $o->setRef('save');
        $o->setLabel('label');
        $o->setHelp('help');
        $o->setAf($af);

        $o->save();
        return $o;
     }//end generateObject()

     /**
      * Deletion of an object created with generateObject0
      * @param AF_Model_Component_Select_Single $o
      */
     public static function deleteObject($o)
     {
        $af = AF_Model_AF::load($o->getIdAf());
        AFTest::deleteObject($af);
        $o->delete();
     }
}//end class Form_SelectSingleTest

/**
 * Test of the creation/modification/deletion of the entity.
 * @package AF
 */
class Form_SelectSingleSetUpTest extends PHPUnit_Framework_TestCase
{
     /**
      * Function called once, before all the tests.
      */
     public static function setUpBeforeClass()
     {
        AF_Model_DAO_Version::getInstance()->unitTestsClearTable();
        AF_Model_DAO_Branch::getInstance()->unitTestsClearTable();
        AF_Model_DAO_AF::getInstance()->unitTestsClearTable();
        Classif_Model_DAO_Version::getInstance()->unitTestsClearTable();
        AF_Model_Form_DAO_Component::getInstance()->unitTestsClearTable();
        AF_Model_Form_DAO_Group::getInstance()->unitTestsClearTable();
        AF_Model_Form_DAO_Element::getInstance()->unitTestsClearTable();
        AF_Model_Form_Element_Select_DAO_Single::getInstance()->unitTestsClearTable();
        AF_Model_Form_Element_Select_DAO_Option::getInstance()->unitTestsClearTable();
        AF_Model_Form_DAO_Action::getInstance()->unitTestsClearTable();
        AF_Model_Form_DAO_ActionComponent::getInstance()->unitTestsClearTable();
     }

     /**
      * Constructor test.
      */
     function testConstruct()
     {
         $o = new AF_Model_Component_Select_Single();
         $this->assertTrue($o instanceof AF_Model_Component_Select_Single);
         $this->assertFalse($o->isEnabled());
         $this->assertTrue($o->getRequired());
         $this->assertFalse($o->isVisible());
         return $o;
     }

     /**
      * Save test.
      * @param AF_Model_Component_Select_Single $o
      * @depends testConstruct
      */
     function testSave(AF_Model_Component_Select_Single $o)
     {
         $af = AFTest::generateObject();

         $option1 = Form_OptionTest::generateObject();
         $option2 = Form_OptionTest::generateObject();
         $option3 = Form_OptionTest::generateObject();
         $o->addOptions(array($option1, $option2, $option3));

         // Pour tester qu'il n'enregistre pas les options dont l'état est deleted.
         // Le test est effectué dans le load.
         $o->removeOption($option3);
         $option3->delete();

         $o->setRef('save');
         $o->setLabel('label');
         $o->setHelp('help');
         $o->setAf($af);
         $o->save();

         $firstId  = $o->getKey();
         $firstRef = $o->getRef();

         $this->assertNotNull($o->getKey(), "Object id is not defined");
         $this->assertEquals("save", $firstRef);

         //update test
         $o->setRef("update");
         $o->save();

         $secondId  = $o->getKey();
         $secondRef = $o->getRef();
         $this->assertTrue($firstId === $secondId && $firstRef !== $secondRef);
         $this->assertTrue($o->getRef() == 'update');

         return $o;
     }//end testSave()


     /**
      * Load test.
      * @depends testSave
      * @param AF_Model_Component_Select_Single $o
      * @expectedException Core_Exception_NotFound
      */
     function testLoad(AF_Model_Component_Select_Single $o)
     {
         $a = AF_Model_Component_Select_Single::load($o->getKey());
         $this->assertEquals($o->getOptions(), $a->getOptions());
         $this->assertEquals(2, count($a->getOptions()));
         $this->assertEquals($a->getKey(), $o->getKey());

         $b = AF_Model_Component_Select_Single::load(0);

     }

    /**
     * @depends testSave
     * @param AF_Model_Component_Select_Single $o
     */
    function testLoadByRef(AF_Model_Component_Select_Single $o)
    {
        $a = AF_Model_Component_Select_Single::loadByRef($o->getRef(), $o->getIdAf());
        $this->assertEquals($o->getRef(), $a->getRef());
        $this->assertEquals($o->getKey(), $a->getKey());
        $this->assertTrue($a instanceof AF_Model_Component_Select_Single);
    }

    /**
     * @depends testSave
     * @param AF_Model_Component_Select_Single $o
     * @expectedException Core_Exception_NotFound
     */
    function testLoadByRefNotFound(AF_Model_Component_Select_Single $o)
    {
        // Test exception générée lorsque le ref n'existe pas dans la bd.
        $a = AF_Model_Component_Select_Single::loadByRef('la', $o->getIdAf());
    }

//    /**
//     * @depends testSave
//     * @param AF_Model_Component_Select_Single $o
//     */
//    function testLoadByRefNotUnique(AF_Model_Component_Select_Single $o)
//    {
//         // Test erreur générée lorsque plusieurs composants ont le même ref.
//        $af = AF_Model_AF::load($o->getIdAf());
//        $b  = new AF_Model_Component_Select_Single();
//        $b->setRef('update');
//        $b->setAf($af);
//        $b->save();
//        try {
//            $a = AF_Model_Component_Select_Single::loadByRef('update', $o->getIdAf());
//            $this->assertFails('Pas d\'exception levée');
//        } catch (Core_Exception_NotFound $e) {
//            // Test ok
//        }
//        $b->delete();
//    }

     /**
      * Deletion test.
      * @param AF_Model_Component_Select_Single $o
      * @depends testSave
      * @expectedException Core_Exception_InvalidArgument
      */
     function testDelete(AF_Model_Component_Select_Single $o)
     {
        $af             = AF_Model_AF::load($o->getIdAf());
        $version        = AF_Model_Version::load($af->getIdVersion());
        $branch         = $version->getBranch();

        $o->delete();
        $this->assertEquals(null, $o->getKEy());

        $af->delete();
        AF_Model_BranchTest::deleteObject($branch);

        // Test de l'exception.
        $o->delete();
     }

     /**
      * Function called once, after all the tests.
      */
     public static function tearDownAfterClass()
     {

        if (! AF_Model_DAO_Version::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Version n'est pas vide après les tests\n";
        }
        if (! AF_Model_DAO_Branch::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Branche n'est pas vide après les tests\n";
        }
        if (! AF_Model_DAO_AF::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table AF n'est pas vide après les tests\n";
        }

        if (! Classif_Model_DAO_Version::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Classif Version n'est pas vide après les tests\n";
        }

        // Check tables are empty
        if (! AF_Model_Form_DAO_Component::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Component n'est pas vide après les tests de la classe SelectSingle\n";
        }
        if (! AF_Model_Form_DAO_Group::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Group n'est pas vide après les tests\n";
        }
        if (! AF_Model_Form_DAO_Element::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Element n'est pas vide après les tests de la classe SelectSingle\n";
        }
        if (! AF_Model_Form_Element_Select_DAO_Single::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table SelectSingle n'est pas vide après les tests de la classe SelectSingle\n";
        }
        if (! AF_Model_Form_Element_Select_DAO_Option::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Option n'est pas vide après les tests de la classe SelectSingle\n";
        }
        if (! AF_Model_Form_DAO_Action::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Action n'est pas vide après les tests de la classe SelectSingle\n";
        }
        if (! AF_Model_Form_DAO_ActionComponent::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table ActionComponent n'est pas vide après les tests de la classe SelectSingle\n";
        }
     }//end tearDownAfterClass()

}//end class Form_SelectSingleSetUpTest


/**
 * Form_SelectSingleLogiqueMetierTest
 * @package AF
 */
class Form_SelectSingleLogiqueMetierTest extends PHPUnit_Framework_TestCase
{

    protected $_selectSingle;
    protected $_option1;
    protected $_option2;
    protected $_option3;

   /**
    * Function called once, before all the tests.
    */
    public static function setUpBeforeClass()
    {
        // Empty related tables
         AF_Model_Form_DAO_Component::getInstance()->unitTestsClearTable();
         AF_Model_Form_DAO_Element::getInstance()->unitTestsClearTable();
         AF_Model_Form_Element_Select_DAO_Single::getInstance()->unitTestsClearTable();
         AF_Model_Form_Element_Select_DAO_Option::getInstance()->unitTestsClearTable();
         AF_Model_Form_DAO_Action::getInstance()->unitTestsClearTable();
         AF_Model_Form_DAO_ActionComponent::getInstance()->unitTestsClearTable();
    }

    /**
     * Function called before each tests.
     */
    protected function setUp()
    {
        $this->_selectSingle = Form_SelectSingleTest::generateObject();
        $this->_option1 = Form_OptionTest::generateObject();
        $this->_option2 = Form_OptionTest::generateObject();
        $this->_option3 = Form_OptionTest::generateObject();
        $this->_option1->setSelect($this->_selectSingle);
        $this->_option2->setSelect($this->_selectSingle);
        $this->_option3->setSelect($this->_selectSingle);
        $this->_option1->save();
        $this->_option2->save();
        $this->_option3->save();
    }

    /**
     * Enter description here ...
     */
    function testAddHasRemoveGetOption()
    {
        //Test addOptions() avec tableau en paramètre.
        $this->_selectSingle->addOptions(array($this->_option1, $this->_option2));
        //Test addOptions() avec options simple en paramètre.
        $this->_selectSingle->addOption($this->_option3);

        // Test addOptions() avec paramètre incorrect dans un tableau.
        try {
            $this->_selectSingle->addOptions('la');
        } catch (Core_Exception_InvalidArgument $e) {
            $this->assertEquals($e->getMessage(), 'Param must be an array of AF_Model_Component_Select_Option');
        }
        // Test addOptions() avec paramètre incorrect (pas un tableau et pas un AF_Model_Form_Option).
        try {
            $this->_selectSingle->addOptions(array(1));
        } catch (Core_Exception_InvalidArgument $e) {
            $this->assertEquals($e->getMessage(), 'Param must be an array of AF_Model_Component_Select_Option');
        }

        // Test getOptions (et vérification du bon fonctionnement de addOption().
        $options = $this->_selectSingle->getOptions();
        $this->assertEquals(3, count($options));
        $this->assertContains($this->_option1, $options);
        $this->assertContains($this->_option2, $options);
        $this->assertContains($this->_option3, $options);

        // Test hasOption
        $this->assertTrue($this->_selectSingle->hasOption($this->_option1));
        $this->assertTrue($this->_selectSingle->hasOption($this->_option2));
        $this->assertTrue($this->_selectSingle->hasOption($this->_option3));

        //Test removeOption
        $this->_selectSingle->removeOption($this->_option1);

        $options = $this->_selectSingle->getOptions();
        $this->assertEquals(2, count($options));
        $this->assertFalse($this->_selectSingle->hasOption($this->_option1));
        $this->_selectSingle->removeOption($this->_option2);
        $this->_selectSingle->removeOption($this->_option3);
    }//end testAddHasRemoveOutputElement()

    /**
     * Enter description here ...
     */
    function testGenerate()
    {
        $af = AFTest::generateObject();
        $test = $this->_selectSingle->generate($af);
        $this->assertTrue($this->_selectSingle->generate($af) instanceof UI_Form_Element_Select);
        AFTest::deleteObject($af);
    }

    /**
     * Function called after each tests.
     */
    protected function tearDown()
    {
        Form_SelectSingleTest::deleteObject($this->_selectSingle);
        Form_OptionTest::deleteObject($this->_option1);
        Form_OptionTest::deleteObject($this->_option2);
        Form_OptionTest::deleteObject($this->_option3);
    }

     /**
      * Function called once, after all the tests
      */
     public static function tearDownAfterClass()
     {
        // Check tables are empty
        if (! AF_Model_Form_DAO_Component::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Component n'est pas vide après les tests de la classe selectSingle\n";
        }
        if (! AF_Model_Form_DAO_Element::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Element n'est pas vide après les tests de la classe selectSingle\n";
        }
        if (! AF_Model_Form_Element_Select_DAO_Single::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table selectSingle n'est pas vide après les tests de la classe selectSingle\n";
        }
        if (! AF_Model_Form_Element_Select_DAO_Option::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Option n'est pas vide après les tests de la classe selectSingle\n";
        }
     }
}//end class Form_SelectSingleLogiqueMetierTest
