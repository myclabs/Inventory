<?php
/**
 * @author hugo.charbonnier
 * @author thibaud.rolland
 * @package AF
 */

// Les deux lignes du dessous sont à décomenter pour les tests en local et aàcommenter avant commit
//  require_once dirname(__FILE__).'/CheckboxTest.php';
//  require_once dirname(__FILE__).'/../AFTest.php';

/**
 * @package AF
 */
class Form_GroupTest
{
    /**
     * Lance les autre classe de tests.
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
//        $suite->addTestSuite('Form_GroupSetUpTest');
//        $suite->addTestSuite('Form_GroupLogiqueMetierTest');
        return $suite;
    }//end suite()


    /**
     * Permet de générer un objet de base sur lequel on pourra travailler.
     */
    public static function generateObject()
    {
        $o = new AF_Model_Component_Group();
        $o->setRef('group1');
        $o->setLabel('Premier groupe');
        $o->setHelp('Aide groupe');
        $o->setVisible(true);
        $o->setFoldaway(2);
        $o->save();
        return $o;
    }


    /**
     * Permet de générer un component de tree.
     * @return Tree_Model_Component $o
     */
    public static function generateTreeElement()
    {
    }//end generateTreeElement()


    /**
     * Supprime un objet utilisé dans les tests.
     * @param AF_Model_Component_Group $o
     */
    public static function deleteObject(AF_Model_Component_Group $o)
    {
        $o->delete();
    }//end deleteObject()

}//end class Form_GroupTest

/**
 * Form_GroupSetUpTest
 * @package AF
 */
class Form_GroupSetUpTest extends PHPUnit_Framework_TestCase
{

    /**
     * Méthode appelée avant l'appel à la classe de test.
     */
    public static function setUpBeforeClass()
    {
        AF_Model_DAO_Version::getInstance()->unitTestsClearTable();
        AF_Model_DAO_Branch::getInstance()->unitTestsClearTable();
        AF_Model_DAO_AF::getInstance()->unitTestsClearTable();

        Classif_Model_DAO_Version::getInstance()->unitTestsClearTable();

        AF_Model_Form_DAO_Component::getInstance()->unitTestsClearTable();
        AF_Model_Form_DAO_Group::getInstance()->unitTestsClearTable();
    }


    /**
     * Méthode appelée avant chaque méthode de test.
     */
    function setUp()
    {
    }

    /**
     * Enter description here ...
     */
    function testConstruct()
    {
        $o = new AF_Model_Component_Group();

        $this->assertEquals($o->getFoldaway(), 1);
        $this->assertFalse($o->isVisible());
        $this->assertTrue($o instanceof AF_Model_Component_Group);
    }


    /**
     * Test de la sauvegarde en bdd.
     * @return AF_Model_Component_Group $o
     */
    function testSave()
    {
//        $treeComponent = Form_GroupTest::generateTreeElement();

        $af = AFTest::generateObject();

        // Test de l'insertion
        $o = new AF_Model_Component_Group();
        $o->setRef('save');
        $o->setLabel('label');
        $o->setHelp('help');
//        $o->setTreeComponent($treeComponent);
        $o->setVisible(true);
        $o->setFoldaway(1);
        $o->setAf($af);
        $o->save();

        $firstId  = $o->getKey();
        $firstRef = $o->getRef();
        $this->assertTrue($firstId > 0);

        // Test de l'update
        $o->setRef('update');
        $o->save();

        $secondId  = $o->getKey();
        $secondRef = $o->getRef();

        $this->assertTrue($firstId === $secondId && $firstRef !== $secondRef);
        $this->assertTrue($secondRef === 'update');

        return $o;
    }//end testSave()

    /**
     * @depends testSave
     * @param AF_Model_Component_Group $o
     * @expectedException Core_Exception_NotFound
     */
    function testLoad(AF_Model_Component_Group $o)
    {
        $a = AF_Model_Component_Group::load($o->getKey());
        $this->assertTrue($a instanceof AF_Model_Component_Group);
        $this->assertEquals($o->getKey(), $a->getKey());

        // Test erreur de chargement
        $b = AF_Model_Component_Group::load(0);
    }

    /**
     * @depends testSave
     * @param AF_Model_Component_Group $o
     */
    function testLoadByRef(AF_Model_Component_Group $o)
    {
        // Test fonctionnement ok.
         $a = AF_Model_Component_Group::loadByRef('update', $o->getIdAf());
         $this->assertEquals('update', $a->getRef());
         $this->assertEquals($o->getKey(), $a->getKey());
         $this->assertTrue($a instanceof AF_Model_Component_Group);
    }


    /**
     * @depends testSave
     * @param AF_Model_Component_Group $o
     * @expectedException Core_Exception_NotFound
     */
    function testLoadByRefNotFound(AF_Model_Component_Group $o)
    {
        $a = AF_Model_Component_Group::loadByRef('la', $o->getIdAf());
    }

    /**
     * @depends testSave
     * @param AF_Model_Component_Group $o
     */
    function testLoadByRefNotUnique(AF_Model_Component_Group $o)
    {
        // Test erreur générée lorsque plusieurs composants ont le même ref.
        $af = AF_Model_AF::load($o->getIdAf());
        $b = new AF_Model_Component_Group();
        $b->setRef('update');
        $b->setAf($af);
        $b->save();
        try {
            $a = AF_Model_Component_Group::loadByRef('update', $o->getIdAf());
            $this->assertFails('Pas d\'exception levée');
        } catch (Core_Exception_NotFound $e) {
            // Test ok
        }
        $b->delete();
    }

    /**
     * @depends testSave
     * @param AF_Model_Component_Group $o
     * @expectedException Core_Exception_Systeme
     */
    function testDelete(AF_Model_Component_Group $o)
    {
        $af             = AF_Model_AF::load($o->getIdAf());
        $version        = AF_Model_Version::load($af->getIdVersion());
        $branch         = $version->getBranch();

        $o->delete();
        $this->assertEquals(null, $o->getKey());

        $af->delete();
        AF_Model_BranchTest::deleteObject($branch);

        // Test de l'exception.
         $o->delete();
    }

    /**
     * Méthode appelée à la fin de chaque test.
     */
    protected function tearDown()
    {

    }

    /**
     * Méthode appelée à la fin de la classe de test.
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
            echo "\nLa table Component n'est pas vide après les tests de la classe Group\n";
        }
        if (! AF_Model_Form_DAO_Group::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Group n'est pas vide après les tests de la classe Group\n";
        }
    }
}//end class Form_GroupSetUpTest


/**
 * Form_GroupLogiqueMetierTest
 * @package AF
 */
class Form_GroupLogiqueMetierTest extends PHPUnit_Framework_TestCase
{
    protected $_group;
    protected $_checkbox;
    protected $_tree;
    protected $_tree2;

   /**
    * Function called once, before all the tests.
    */
    public static function setUpBeforeClass()
    {
        AF_Model_Form_DAO_Component::getInstance()->unitTestsClearTable();
        AF_Model_Form_DAO_Group::getInstance()->unitTestsClearTable();
        AF_Model_Form_DAO_Element::getInstance()->unitTestsClearTable();
        AF_Model_Form_Element_DAO_Checkbox::getInstance()->unitTestsClearTable();
        Tree_Model_DAO_Component::getInstance()->unitTestsClearTable();
    }//end setUpBeforeClass()


    /**
     * Function called before each tests.
     */
    protected function setUp()
    {
        $this->_group = Form_GroupTest::generateObject();

        $this->_tree = new Tree_Model_Composite();
        $this->_tree->setEntity($this->_group);
        $this->_tree->save();

        $this->_group->setTreeComponent($this->_tree);
        $this->_group->save();

        $this->_checkbox = Component_CheckboxTest::generateObject();

        $this->_tree2 = new Tree_Model_Composite();
        $this->_tree2->setEntity($this->_checkbox);
        $this->_tree2->setParent($this->_tree);
        $this->_tree2->save();

        $this->_checkbox->setTreeComponent($this->_tree2);
        $this->_checkbox->save();
    }

    /**
     * Enter description here ...
     */
    function testGenerate()
    {
        $af = new AF_Model_AF();
        $af->setRef("test");

        $result = $this->_group->generate($af, null, null, AF_ViewConfiguration::MODE_WRITE);

        $this->assertTrue($result instanceof UI_Form_Element_Group);
        $this->assertTrue($result->foldaway);
        $this->assertTrue($result->folded);
        $this->assertTrue($result->getElement()->hidden);
        $this->assertEquals(1, count($result->getElement()->children));
    }

    /**
     * Function called after each tests.
     */
    protected function tearDown()
    {
        Form_GroupTest::deleteObject($this->_group);
    }

     /**
      * Function called once, after all the tests
      */
     public static function tearDownAfterClass()
     {
        // Check tables are empty
        if (! AF_Model_Form_DAO_Component::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Component n'est pas vide après les tests de la classe Group\n";
        }
        if (! AF_Model_Form_DAO_Group::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Group n'est pas vide après les tests de la classe Group\n";
        }
        if (! AF_Model_Form_DAO_Element::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Element n'est pas vide après les tests de la classe Group\n";
        }
        if (! AF_Model_Form_Element_DAO_Checkbox::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Checkbox n'est pas vide après les tests de la classe Group\n";
        }
        if (! Tree_Model_DAO_Component::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Tree Component n'est pas vide après les tests de la classe Group\n";
        }

     }

}//end class Form_GroupLogiqueMetierTest
