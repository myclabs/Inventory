<?php
/**
 * @author yoann.croizer
 * @author hugo.charbonnier
 * @package Algo
 */

/**
 * @package Algo
 * @subpackage Elementary
 */
class Condition_Elementary_BooleanTest
{
    /**
     * Lance les autres classe de tests.
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
//        $suite->addTestSuite('Condition_Elementary_BooleanSetUpTest');
//        $suite->addTestSuite('Condition_Elementary_BooleanLogiqueMetierTest');
        return $suite;
    }//end suite()

    /**
     * Permet de générer un objet de base sur lequel on pourra travailler
     */
    public static function generateObject()
    {
        $o = new Algo_Model_Condition_Elementary_Boolean();
        $o->ref = 'ConditionCheckbox';
        $o->inputRef = 'algoCheckBox';
        $o->relation = Algo_Model_Condition_Elementary::RELATION_NOTEQUAL;
        $o->value = false;
        $o->save();
        return $o;
    }//end generateObject()

    /**
     * Supprime un objet utilisé dans les tests
     * @param Algo_Model_Condition_Elementary_Boolean $o
     */
    public static function deleteObject(Algo_Model_Condition_Elementary_Boolean $o)
    {
        $o->delete();
    }


}//end class Condition_Elementary_BooleanTest

/**
 * Condition_Elementary_BooleanSetUpTest
 * @package Algo
 */
class Condition_Elementary_BooleanSetUpTest extends PHPUnit_Framework_TestCase
{
    /**
     * Méthode appelée avant l'appel à la classe de test
     */
    public static function setUpBeforeClass()
    {
        Algo_Model_DAO_Algo::getInstance()->unitTestsClearTable();
        Algo_Model_Condition_DAO_Elementary::getInstance()->unitTestsClearTable();
        Algo_Model_Condition_Elementary_DAO_Checkbox::getInstance()->unitTestsClearTable();

    }// end setUpBeforeClass()

    /**
     * Méthode appelée avant chaque méthode de test.
     */
    function setUp()
    {

    }// end setUp()


    /**
     * Test du constructeur.
     * @return Algo_Model_Condition_Elementary_Boolean $o
     */
    function testConstruct()
    {
        $o = new Algo_Model_Condition_Elementary_Boolean();
        $this->assertTrue($o instanceof Algo_Model_Condition_Elementary_Boolean);
        $this->assertEquals('Algo_Model_Condition_Elementary_Boolean', $o->type);
        return $o;
    }// end testConstruct


    /**
     * Test la sauvegarde de l'objet.
     * @depends testConstruct
     * @param Algo_Model_Condition_Elementary_Boolean $o
     * @return Algo_Model_Condition_Elementary_Boolean $o
     */
    function testSave(Algo_Model_Condition_Elementary_Boolean $o)
    {
        $o->ref = 'testCheckbox';
        $o->inputRef = 'vapeur';
        $o->relation = Algo_Model_Condition_Elementary::RELATION_EQUAL;
        $o->value = true;
        $id = $o->save();

        $this->assertEquals($o->type, 'Algo_Model_Condition_Elementary_Boolean');
        $this->assertNotNull($o->id, 'object is not defined');

        // Test du cas ou 'input ref' n'est pas correctement renseigné.
        $a = new Algo_Model_Condition_Elementary_Boolean();
        $a->ref = 'testCheckbox2';
        $a->relation = Algo_Model_Condition_Elementary::RELATION_EQUAL;
        $a->type = 'Algo_Model_Condition_Elementary_Boolean';
        $a->value = true;
        try {
            $a->save();
        } catch (Core_Exception_InvalidArgument $e) {
            $this->assertEquals(null, $a->id);
        }

        // Test du cas ou 'relation' n'est pas correctement renseigné.
        $a->relation = null;
        $a->inputRef = "test";
        try {
            $a->save();
        } catch (Core_Exception_InvalidArgument $e) {
            $this->assertEquals(null, $a->id);
        }

        // Test du cas ou 'value' n'est pas correctement renseigné.
        $a->relation = Algo_Model_Condition_Elementary::RELATION_EQUAL;
        $a->value = null;
        try {
            $a->save();
        } catch (Core_Exception_InvalidArgument $e) {
            $this->assertEquals(null, $a->id);
        }
        return $o;
    }// end testSave()


    /**
     * Test du chargement de l'objet.
     * @depends testSave
     * @param Algo_Model_Condition_Elementary_Boolean $o
     */
    function testLoad(Algo_Model_Condition_Elementary_Boolean $o)
    {
        $id = $o->id;
        $checkBox = Algo_Model_Condition_Elementary_Boolean::load($id);

        $this->assertTrue($checkBox instanceof  Algo_Model_Condition_Elementary_Boolean);
        $this->assertEquals($o, $checkBox);
    }// end testLoad()


    /**
     * Test de la suppression de l'objet.
     * @depends testSave
     * @param Algo_Model_Condition_Elementary_Boolean $o
     * @expectedException Core_Exception_NotFound
     */
    function testDelete(Algo_Model_Condition_Elementary_Boolean $o)
    {
        $id = $o->id;
        $o->delete();
        $this->assertEquals(null, $o->id);
        // Le chargement doit lever une exception
        Algo_Model_Condition_Elementary_Boolean::load($id);
    }// end testDelete()

    /**
     * Méthode appelée à la fin de chaque test
     */
    protected function tearDown()
    {

    }// end tearDown()

    /**
     * Méthode appelée à la fin de la classe de test
     */
    public static function tearDownAfterClass()
    {
        if (! Algo_Model_DAO_Algo::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Algo n'est pas vide après les tests\n";
        }
        if (! Algo_Model_Condition_DAO_Elementary::getInstance()->unitTestsIsTableEmpty() ) {
            echo "\nLa table Elementary n'est pas vide après les tests\n";
        }
        if (! Algo_Model_Condition_Elementary_DAO_Checkbox::getInstance()->unitTestsIsTableEmpty() ) {
            echo "\nLa table Checkbox n'est pas vide après les tests\n";
        }
    }

}//end class Condition_Elementary_BooleanSetUpTest

/**
 * Condition_Elementary_BooleanLogiqueMetierTest
 * @package Algo
 */
class Condition_Elementary_BooleanLogiqueMetierTest extends PHPUnit_Framework_TestCase
{
    protected $_conditionCheckbox;
    protected $_input;

    /**
     * Méthode appelée avant l'appel à la classe de test
     */
    public static function setUpBeforeClass()
    {
        Algo_Model_DAO_Algo::getInstance()->unitTestsClearTable();
        Algo_Model_Condition_DAO_Elementary::getInstance()->unitTestsClearTable();
        Algo_Model_Condition_Elementary_DAO_Checkbox::getInstance()->unitTestsClearTable();
    }

    /**
     * Méthode appelée avant chaque méthode de test.
     */
    function setUp()
    {
        $this->_conditionCheckbox = Condition_Elementary_BooleanTest::generateObject();
        $this->_input = array(
            'algoCheckBox' => true
        );
    }

    /**
     * Test de la méthode execute()
     */
    function testExecute()
    {
        $result = $this->_conditionCheckbox->execute($this->_input);
        $this->assertTrue(is_bool($result));
        $this->assertEquals($result, true);

        //Test erreur 1: l'input est un tableau vide
        $input = array();
        try {
            $result = $this->_conditionCheckbox->execute($input);
        } catch (Core_Exception_InvalidArgument $e) {
            $this->assertEquals($e->getMessage(), 'Le tableau d\'input ne peut être nul dans le cas d\'un conditionCheckBox');
        }

        // Test erreur 2: dans le tableau d'input le ref est associé à un objet non booleen.
        $input = array('algoCheckBox' => 'truee');
        try {
            $result = $this->_conditionCheckbox->execute($input);
        } catch (Core_Exception_InvalidArgument $e) {
            $this->assertEquals($e->getMessage(), 'Booleen requis');
        }

        // Test erreur 3: le ref n'est associé à aucun input.
        $input = array('algoCheckBox2' => null);
        try {
            $result = $this->_conditionCheckbox->execute($input);
        } catch (Core_Exception_NotFound $e) {
            $this->assertEquals($e->getMessage(), 'Il n\'y a pas d\'input avec le ref algoCheckBox');
        }

        // Test erreur 4: Relation non gérée
        $this->_conditionCheckbox->relation = Algo_Model_Condition_Elementary::RELATION_GT;
        try {
            $result = $this->_conditionCheckbox->execute($this->_input);
        } catch (Core_Exception_InvalidArgument $e) {
            $this->assertEquals($e->getMessage(), 'Relation non gérée');
        }
    }

    /**
     * Méthode appelée à la fin de chaque test
     */
    protected function tearDown()
    {
        Condition_Elementary_BooleanTest::deleteObject($this->_conditionCheckbox);
    }

    /**
     * Méthode appelée à la fin de la classe de test
     */
    public static function tearDownAfterClass()
    {
        if (! Algo_Model_DAO_Algo::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Algo n'est pas vide après les tests\n";
        }
        if (! Algo_Model_Condition_DAO_Elementary::getInstance()->unitTestsIsTableEmpty() ) {
            echo "\nLa table Elementary n'est pas vide après les tests\n";
        }
        if (! Algo_Model_Condition_Elementary_DAO_Checkbox::getInstance()->unitTestsIsTableEmpty() ) {
            echo "\nLa table Checkbox n'est pas vide après les tests\n";
        }
    }

}//end class Condition_Elementary_BooleanLogiqueMetierTest
