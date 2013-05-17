<?php
/**
 * @author  matthieu.napoli
 * @author  yoann.croizer
 * @author  hugo.charbonnier
 * @package Algo
 */

/**
 * Creation of the Test Suite.
 * @package Algo
 */
class Condition_ElementaryTest
{

    /**
     * Lance les autre classe de tests.
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        //        $suite->addTestSuite('Condition_ElementarySetUpTest');
        //        $suite->addTestSuite('Condition_ElementaryLogiqueMetierTest');
        return $suite;
    }

}


/**
 * Condition_ElementarySetUpTest
 * @package Algo
 */
class Condition_ElementarySetUpTest extends Core_Test_TestCase
{

    /**
     * Méthode appelée avant l'appel à la classe de test
     */
    public static function setUpBeforeClass()
    {
        Algo_Model_DAO_Algo::getInstance()->unitTestsClearTable();
        Algo_Model_Condition_DAO_Elementary::getInstance()->unitTestsClearTable();
    }

    /**
     * Test du constructeur
     * @return Algo_Model_Condition_Elementary $o
     */
    function testConstruct()
    {
        $o = new Algo_Model_Condition_Elementary();
        $this->assertTrue($o instanceof Algo_Model_Condition_Elementary);

        return $o;
    }

    /**
     * Test de la sauvegarde de l'objet.
     * @depends testConstruct
     * @param Algo_Model_Condition_Elementary $o
     * @return Algo_Model_Condition_Elementary $o
     */
    function testSave(Algo_Model_Condition_Elementary $o)
    {
        $o->ref = 'test';
        $o->type = 'Algo_Model_Condition_Elementary';
        $o->relation = Algo_Model_Condition_Elementary::RELATION_EQUAL;
        $o->inputRef = 'nbTonneKmBateau';
        $id = $o->save();
        $this->assertNotNull($o->id, 'Object id is not defined');
        $this->assertEquals($o->relation, Algo_Model_Condition_Elementary::RELATION_EQUAL);
        $this->assertEquals($o->inputRef, 'nbTonneKmBateau');

        return $o;
    }


    /**
     * Test du chargement de l'objet.
     * @depends testSave
     * @param Algo_Model_Condition_Elementary $o
     * @return Algo_Model_Condition_Elementary $o
     */
    function testLoad(Algo_Model_Condition_Elementary $o)
    {
        $id = $o->id;
        $elementary = Algo_Model_Condition_Elementary::load($id);
        $this->assertTrue($elementary instanceof Algo_Model_Condition_Elementary);

        return $o;
    }


    /**
     * Test de la suppression de l'objet.
     * @depends testLoad
     * @param Algo_Model_Condition_Elementary $o
     * @expectedException Core_Exception_NotFound
     */
    function testDelete(Algo_Model_Condition_Elementary $o)
    {
        $id = $o->id;
        $o->delete();
        $this->assertEquals(null, $o->id);
        // Doit lever une exception
        $elementary = Algo_Model_Condition_Elementary::load($id);
    }

    /**
     * Méthode appelée à la fin de la classe de test
     */
    public static function tearDownAfterClass()
    {
        if (!Algo_Model_DAO_Algo::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Algo n'est pas vide après les tests\n";
        }
        if (!Algo_Model_Condition_DAO_Elementary::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Elementary n'est pas vide après les tests\n";
        }
    }

}

//end class Condition_ElementarySetUpTest

/**
 * Condition_ElementaryLogiqueMetierTest
 * @package Algo
 */
class Condition_ElementaryLogiqueMetierTest extends PHPUnit_Framework_TestCase
{

    /**
     * Méthode appelée avant l'appel à la classe de test
     */
    public static function setUpBeforeClass()
    {

    }

    /**
     * Méthode appelée avant chaque méthode de test.
     */
    function setUp()
    {

    }

    /**
     * Méthode appelée à la fin de chaque test
     */
    protected function tearDown()
    {

    }

    /**
     * Méthode appelée à la fin de la classe de test
     */
    public static function tearDownAfterClass()
    {

    }

}//end class Condition_ElementaryLogiqueMetierTest
