<?php
/**
 * @author yoann.croizer
 * @author hugo.charbonnier
 * @package Algo
 */

/**
 * @package Algo
 * @subpackage Condition
 */
class Condition_Elementary_Select_MultiTest
{
    /**
     * Lance les autres classe de tests.
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
//        $suite->addTestSuite('Condition_Elementary_MultiSetUpTest');
//        $suite->addTestSuite('Condition_Elementary_MultiLogiqueMetierTest');
        return $suite;
    }//end suite()

    /**
     * Permet de générer un objet de base sur lequel on pourra travailler
     */
    public static function generateObject()
    {
        $o = new Algo_Model_Condition_Elementary_Select_Multi();
        $o->ref = 'ConditionMulti';
        $o->inputRef = 'algoMulti';
        $o->relation = Algo_Model_Condition_Elementary::RELATION_NOTEQUAL;
        $o->value = 'testEqual';
        $o->save();
        return $o;
    }//end generateObject()

    /**
     * Supprime un objet utilisé dans les tests
     * @param Algo_Model_Condition_Elementary_Select_Multi $o
     */
    public static function deleteObject(Algo_Model_Condition_Elementary_Select_Multi $o)
    {
        $o->delete();
    }


}//end class Condition_Elementary_Select_MultiTest

/**
 * Condition_Elementary_MultiSetUpTest
 * @package Algo
 */
class Condition_Elementary_MultiSetUpTest extends PHPUnit_Framework_TestCase
{
    /**
     * Méthode appelée avant l'appel à la classe de test
     */
    public static function setUpBeforeClass()
    {
        Algo_Model_DAO_Algo::getInstance()->unitTestsClearTable();
        Algo_Model_Condition_DAO_Elementary::getInstance()->unitTestsClearTable();
        Algo_Model_Condition_Elementary_DAO_Multi::getInstance()->unitTestsClearTable();
    }// end setUpBeforeClass()

    /**
     * Méthode appelée avant chaque méthode de test.
     */
    function setUp()
    {

    }// end setUp()


    /**
     * Test du constructeur.
     * @return Algo_Model_Condition_Elementary_Select_Multi $o
     */
    function testConstruct()
    {
        $o = new Algo_Model_Condition_Elementary_Select_Multi();
        $this->assertTrue($o instanceof Algo_Model_Condition_Elementary_Select_Multi);
        $this->assertEquals('Algo_Model_Condition_Elementary_Select_Multi', $o->type);
        return $o;
    }// end testConstruct


    /**
     * Test la sauvegarde de l'objet.
     * @depends testConstruct
     * @param Algo_Model_Condition_Elementary_Select_Multi $o
     * @return Algo_Model_Condition_Elementary_Select_Multi $o
     */
    function testSave(Algo_Model_Condition_Elementary_Select_Multi $o)
    {
        $o->ref = 'testMulti';
        $o->inputRef = 'camion';
        $o->relation = Algo_Model_Condition_Elementary::RELATION_EQUAL;
        $o->value = 'test';
        $id = $o->save();

        $this->assertNotNull($o->id, 'Object is not defined');
        $this->assertEquals($o->relation, Algo_Model_Condition_Elementary::RELATION_EQUAL);

        // Test du cas ou 'input ref' n'est pas correctement renseigné.
        $a = new Algo_Model_Condition_Elementary_Select_Multi();
        $a->ref = 'testMulti2';
        $a->relation = Algo_Model_Condition_Elementary::RELATION_EQUAL;
        $a->value = 'test';
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
     * Test le chargement de l'objet.
     * @depends testSave
     * @param Algo_Model_Condition_Elementary_Select_Multi $o
     */
    function testLoad(Algo_Model_Condition_Elementary_Select_Multi $o)
    {
        $id = $o->id;
        $multi = Algo_Model_Condition_Elementary_Select_Multi::load($id);

        $this->asserttrue($multi instanceof Algo_Model_Condition_Elementary_Select_Multi);
        $this->assertEquals($o, $multi);
    }//end testLoad()

    /**
     * Test la suppression de l'objet
     * @depends testSave
     * @param Algo_Model_Condition_Elementary_Select_Multi $o
     * @expectedException Core_Exception_NotFound
     */
    function testDelete(Algo_Model_Condition_Elementary_Select_Multi $o)
    {
        $id = $o->id;
        $o->delete();
        $this->assertEquals(null, $o->id);
        // Le load doit lever une exception
        Algo_Model_Condition_Elementary_Select_Multi::load($id);
    }//end testDelete()

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
        if (! Algo_Model_Condition_Elementary_DAO_Multi::getInstance()->unitTestsIsTableEmpty() ) {
            echo "\nLa table Multi n'est pas vide après les tests\n";
        }
    }// end tearDownAfterClass()

}//end class Condition_Elementary_MultiSetUpTest

/**
 * Condition_Elementary_MultiLogiqueMetierTest
 * @package Algo
 */
class Condition_Elementary_MultiLogiqueMetierTest extends PHPUnit_Framework_TestCase
{
    protected $_conditionMulti;
    protected $_input;

    /**
     * Méthode appelée avant l'appel à la classe de test
     */
    public static function setUpBeforeClass()
    {
        Algo_Model_DAO_Algo::getInstance()->unitTestsClearTable();
        Algo_Model_Condition_DAO_Elementary::getInstance()->unitTestsClearTable();
        Algo_Model_Condition_Elementary_DAO_Multi::getInstance()->unitTestsClearTable();
    }

    /**
     * Méthode appelée avant chaque méthode de test.
     */
    function setUp()
    {
        $this->_conditionMulti = Condition_Elementary_Select_MultiTest::generateObject();
        $this->_input = array(
            'algoMulti' => 'testEqual'
        );
    }

    /**
     * Test de la méthode execute()
     */
    function testExecute()
    {
        $result = $this->_conditionMulti->execute($this->_input);
        $this->assertTrue(is_bool($result));
        $this->assertFalse($result);

        //Test erreur 1: l'input est un tableau vide
        $input = array();
        try {
            $result = $this->_conditionMulti->execute($input);
        } catch (Core_Exception_InvalidArgument $e) {
            $this->assertEquals($e->getMessage(), 'Le tableau d\'input ne peut être nul dans le cas d\'un conditionMulti');
        }

        // Test erreur 2: dans le tableau d'input le ref est associé à un objet non string.
        $input = array('algoMulti' => true);
        try {
            $result = $this->_conditionMulti->execute($input);
        } catch (Core_Exception_InvalidArgument $e) {
            $this->assertEquals($e->getMessage(), 'Undefined attribute');
        }

        // Test erreur 3: le ref n'est associé a aucun input.
        $input = array('algoMulti2' => null);
        try {
            $result = $this->_conditionMulti->execute($input);
        } catch (Core_Exception_NotFound $e) {
            $this->assertEquals($e->getMessage(), 'Il n\'y a pas d\'input avec le ref algoMulti');
        }

        // Test erreur 4: Relation non gérée
        $this->_conditionMulti->relation = Algo_Model_Condition_Elementary::RELATION_GT;
        try {
            $result = $this->_conditionMulti->execute($this->_input);
        } catch (Core_Exception_InvalidArgument $e) {
            $this->assertEquals($e->getMessage(), 'Relation incorrecte');
        }
    }


    /**
     * Méthode appelée à la fin de chaque test
     */
    protected function tearDown()
    {
        Condition_Elementary_Select_MultiTest::deleteObject($this->_conditionMulti);
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
        if (! Algo_Model_Condition_Elementary_DAO_Multi::getInstance()->unitTestsIsTableEmpty() ) {
            echo "\nLa table Multi n'est pas vide après les tests\n";
        }
    }

}//end class Condition_Elementary_MultiLogiqueMetierTest
