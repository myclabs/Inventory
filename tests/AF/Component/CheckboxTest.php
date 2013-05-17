<?php
/**
 * @author hugo.charbonnier
 * @author thibaud.rolland
 * @package AF
 */

// La ligne du dessous est à décomenter pour les tests en local et à commenter avant commit
//  require_once dirname(__FILE__).'/../AFTest.php';

/**
 * @package AF
 */
class Component_CheckboxTest
{
    /**
     * Lance les autre classe de tests.
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
//        $suite->addTestSuite('Component_CheckboxSetUpTest');
//        $suite->addTestSuite('Component_CheckboxLogiqueMetierTest');
        return $suite;
    }

    /**
     * Permet de générer un objet de base sur lequel on pourra travailler
     * @return AF_Model_Form_Field_Checkbox
     */
    public static function generateObject()
    {
        $o = new AF_Model_Component_Checkbox();
        $o->setRef('checkbox1');
        $o->setLabel('Première checkbox');
        $o->setHelp('Aide checkbox');
        $o->setDefaultValue(true);
        $o->save();
        return $o;
    }

    /**
     * Supprime un objet utilisé dans les tests
     * @param AF_Model_Form_Field_Checkbox $o
     */
    public static function deleteObject(AF_Model_Component_Checkbox $o)
    {
        $o->delete();
    }

}

/**
 * Component_CheckboxSetUpTest
 * @package AF
 */
class Component_CheckboxSetUpTest extends PHPUnit_Framework_TestCase
{

    /**
     * Méthode appelée avant l'appel à la classe de test
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
        AF_Model_Form_Element_DAO_Checkbox::getInstance()->unitTestsClearTable();
    }


    /**
     * Enter description here ...
     */
    function testConstruct()
    {
        $o = new AF_Model_Component_Checkbox();

        $this->assertTrue($o instanceof AF_Model_Component_Checkbox);
        $this->assertFalse($o->getDefaultValue());
        $this->assertFalse($o->isEnabled());
        $this->assertFalse($o->isVisible());
    }

    /**
     * Test de la sauvegarde en bdd.
     * @return AF_Model_Form_Field_Checkbox $o
     */
    function testSave()
    {
        $af = AFTest::generateObject();

        // Test de l'insertion
        $o = new AF_Model_Component_Checkbox();
        $o->setRef('save');
        $o->setLabel('label');
        $o->setHelp('help');
        $o->setDefaultValue(true);
        $o->setAf($af);
        $o->save();

        $firstId    = $o->getKey();
        $firstRef   = $o->getRef();
        $firstValue = $o->getDefaultValue();

        $this->assertTrue($firstId > 0);

        // Test de l'update
        $o->setRef('update');
        $o->setDefaultValue(false);
        $o->save();

        $secondId    = $o->getKey();
        $secondRef   = $o->getRef();
        $secondValue = $o->getDefaultValue();

        $this->assertTrue($firstId === $secondId && $firstRef !== $secondRef && $firstValue !== $secondValue);
        $this->assertTrue($secondRef === 'update' && $secondValue === false);

        return $o;
    }//end testSave()

    /**
     * @depends testSave
     * @param AF_Model_Form_Field_Checkbox $o
     * @expectedException Core_Exception_NotFound
     */
    function testLoad(AF_Model_Component_Checkbox $o)
    {
        $a = AF_Model_Component_Checkbox::load($o->getKey());
        $this->assertTrue($a instanceof AF_Model_Component_Checkbox);
        $this->assertEquals($o->getKey(), $a->getKey());

        // Test erreur de chargement
        $b = AF_Model_Component_Group::load(0);
    }

    /**
     * @depends testSave
     * @param AF_Model_Form_Field_Checkbox $o
     */
    function testLoadByRef(AF_Model_Component_Checkbox $o)
    {
        $a = AF_Model_Component::loadByRef('update', $o->getIdAf());
        $this->assertTrue($a instanceof AF_Model_Component_Checkbox);
        $this->assertEquals('update', $a->getRef());
        $this->assertEquals($o->getKey(), $a->getKey());
    }

    /**
     * @depends testSave
     * @param AF_Model_Form_Field_Checkbox $o
     * @expectedException Core_Exception_NotFound
     */
    function testLoadByRefNotFound(AF_Model_Component_Checkbox $o)
    {
        $a = AF_Model_Component::loadByRef('la', $o->getIdAf());
    }

    /**
     * @depends testSave
     * @param AF_Model_Form_Field_Checkbox $o
     */
    function testLoadByRefNotUnique(AF_Model_Component_Checkbox $o)
    {
        // Test erreur générée lorsque plusieurs composants ont le même ref.
        $af = AF_Model_AF::load($o->getIdAf());
        $b = new AF_Model_Component_Checkbox();
        $b->setRef('update');
        $b->setAf($af);

        $b->save();
        try {
            $a = AF_Model_Component::loadByRef('update', $b->getIdAf());
            $this->assertFails('Pas d\'exception levée');
        } catch (Core_Exception_NotFound $e) {
            // Test ok
        }
        $b->delete();
    }

    /**
     * @depends testSave
     * @param AF_Model_Form_Field_Checkbox $o
     * @expectedException Core_Exception_InvalidArgument
     */
    function testDelete(AF_Model_Component_Checkbox $o)
    {
        $af      = AF_Model_AF::load($o->getIdAf());
        $version = AF_Model_Version::load($af->getIdVersion());
        $branch  = $version->getBranch();

        $o->delete();
        $this->assertEquals(null, $o->getKey());

        $af->delete();
        AF_Model_BranchTest::deleteObject($branch);

        // Test de l'exception.
        $o->delete();
    }

    /**
     * Méthode appelée à la fin de la classe de test
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
            echo "\nLa table Component n'est pas vide après les tests\n";
        }
        if (! AF_Model_Form_DAO_Group::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Group n'est pas vide après les tests\n";
        }
        if (! AF_Model_Form_DAO_Element::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Element n'est pas vide après les tests\n";
        }
        if (! AF_Model_Form_Element_DAO_Checkbox::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Component n'est pas vide après les tests\n";
        }
    }
}//end class Component_CheckboxSetUpTest

/**
 * Component_CheckboxLogiqueMetierTest
 * @package AF
 */
class Component_CheckboxLogiqueMetierTest extends PHPUnit_Framework_TestCase
{

    protected $_checkbox;

   /**
    * Function called once, before all the tests.
    */
    public static function setUpBeforeClass()
    {
        AF_Model_Form_DAO_Component::getInstance()->unitTestsClearTable();
        AF_Model_Form_DAO_Element::getInstance()->unitTestsClearTable();
        AF_Model_Form_Element_DAO_Checkbox::getInstance()->unitTestsClearTable();
    }

    /**
     * Function called before each tests.
     */
    protected function setUp()
    {
        $this->_checkbox = Component_CheckboxTest::generateObject();
    }

    /**
     * Enter description here ...
     */
    function testGenerate()
    {
        $af = new AF_Model_AF();
        $af->setRef("test");

        $result = $this->_checkbox->generate($af, null, null, AF_ViewConfiguration::MODE_WRITE);
        $this->assertTrue($result instanceof UI_Form_Element_Checkbox);
        $this->assertTrue($result->getValue());
        $this->assertFalse($result->getElement()->hidden);
        $this->assertFalse($result->getElement()->disabled);
    }

    /**
     * Function called after each tests.
     */
    protected function tearDown()
    {
        Component_CheckboxTest::deleteObject($this->_checkbox);
    }

     /**
      * Function called once, after all the tests
      */
     public static function tearDownAfterClass()
     {
        // Check tables are empty
        if (! AF_Model_Form_Element_DAO_Checkbox::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Component n'est pas vide après les tests\n";
        }
        if (! AF_Model_Form_DAO_Element::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Element n'est pas vide après les tests\n";
        }
        if (! AF_Model_Form_DAO_Component::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Component n'est pas vide après les tests\n";
        }
     }
}//end class Component_CheckboxLogiqueMetierTest
