<?php
/**
 * @author yoann.croizer
 * @author hugo.charbonnier
 * @package Algo
 */

/**
 * @package Algo
 */
class Condition_Elementary_NumericTest
{
    /**
     * Lance les autre classe de tests.
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
//        $suite->addTestSuite('Condition_Elementary_NumericSetUpTest');
//        $suite->addTestSuite('Condition_Elementary_NumericLogiqueMetierTest');
        return $suite;
    }//end suite()

    /**
     * Permet de générer un objet de base sur lequel on pourra travailler
     */
    public static function generateObject()
    {
        $unitValue = self::generateUnitValue();
        $o = new Algo_Model_Condition_Elementary_Numeric();
        $o->ref      = 'NumericCondition';
        $o->relation = Algo_Model_Condition_Elementary::RELATION_EQUAL;
        $o->inputRef = 'Algo1';
        $o->value    = $unitValue;
        $o->save();
        return $o;
    }//end generateObject()

    /**
     * Permet de générer un objet Calc_UnitValue sur lequel on pourra travaille
     * @return Calc_UnitValue $unitValue
     */
    public static function generateUnitValue()
    {
        $unit = new Unit_Model_APIUnit('g');

        $value = new Calc_Value();
        $value->digitalValue = 12;
        $value->relativeUncertainty = 25;

        $unitValue = new Calc_UnitValue();
        $unitValue->unit  = $unit;
        $unitValue->value = $value;
        return $unitValue;
    }//end generateUnitValue()

    /**
     * Supprime un objet utilisé dans les tests
     * @param Algo_Model_Condition_Elementary_Numeric $o
     */
    public static function deleteObject(Algo_Model_Condition_Elementary_Numeric $o)
    {
        $o->delete();
    }


}//end class Condition_Elementary_NumericTest

/**
 * numericSetUpTest
 * @package Algo
 */
class Condition_Elementary_NumericSetUpTest extends PHPUnit_Framework_TestCase
{
    protected $_unitValue1;

    /**
     * Méthode appelée avant l'appel à la classe de test
     */
    public static function setUpBeforeClass()
    {
        Algo_Model_DAO_Algo::getInstance()->unitTestsClearTable();
        Algo_Model_Condition_DAO_Elementary::getInstance()->unitTestsClearTable();
        Algo_Model_Condition_Elementary_DAO_Numeric::getInstance()->unitTestsClearTable();

    }// end setUpBeforeClass()

    /**
     * Méthode appelée avant chaque méthode de test.
     */
    function setUp()
    {
        $this->_unitValue1 = Condition_Elementary_NumericTest::generateUnitValue();

    }// end setUp()


    /**
     * Test du constructeur
     * @return Algo_Model_Condition_Elementary_Numeric $o
     */
    function testConstruct()
    {
        $o = new Algo_Model_Condition_Elementary_Numeric();
        $this->assertTrue($o instanceof Algo_Model_Condition_Elementary_Numeric);
        $this->assertEquals('Algo_Model_Condition_Elementary_Numeric', $o->type);
        return $o;
    }// end testConstruct()

    /**
     * Test la sauvegarde de l'objet.
     * @depends testConstruct
     * @param Algo_Model_Condition_Elementary_Numeric $o
     * @return Algo_Model_Condition_Elementary_Numeric $o
     */
    function testSave(Algo_Model_Condition_Elementary_Numeric $o)
    {
        $o->ref = 'testNumeric';
        $o->inputRef = 'nbPassagers';
        $o->relation = Algo_Model_Condition_Elementary::RELATION_EQUAL;
        $o->value = $this->_unitValue1;
        $o->save();

        $this->assertNotNull($o->id, 'Object is not defined');
        $this->assertEquals($o->relation, Algo_Model_Condition_Elementary::RELATION_EQUAL);

        // Test du cas ou 'input ref' n'est pas correctement renseigné.
        $a = new Algo_Model_Condition_Elementary_Numeric();
        $a->ref = 'testNumeric2';
        $a->relation = Algo_Model_Condition_Elementary::RELATION_EQUAL;
        $a->value = $this->_unitValue1;
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
     * @param Algo_Model_Condition_Elementary_Numeric $o
     */
    function testLoad(Algo_Model_Condition_Elementary_Numeric $o)
    {
        $id = $o->id;
        $numeric = Algo_Model_Condition_Elementary_Numeric::load($id);

        $this->assertTrue($numeric instanceof Algo_Model_Condition_Elementary_Numeric);
        $this->assertEquals($numeric, $o);
    }// end testLoad()


    /**
     * Test de la suppression de l'objet.
     * @depends testSave
     * @param Algo_Model_Condition_Elementary_Numeric $o
     * @expectedException Core_Exception_NotFound
     */
    function testDelete(Algo_Model_Condition_Elementary_Numeric $o)
    {
        $id = $o->id;
        $o->delete();
        $this->assertEquals(null, $o->id);

        // Le load doit lever une exception.
        Algo_Model_Condition_Elementary_Numeric::load($id);
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
            echo 'La table Algo n\'est pas vide après les tests !';
        }
        if (! Algo_Model_Condition_DAO_Elementary::getInstance()->unitTestsIsTableEmpty()) {
            echo 'La table Elementary n\'est pas vide après les tests !';
        }
        if (! Algo_Model_Condition_Elementary_DAO_Numeric::getInstance()->unitTestsIsTableEmpty()) {
            echo 'La table Numeric n\'est pas vide après les tests !';
        }

    }// end tearDownAfterClass()

}//end class Condition_Elementary_NumericSetUpTest

/**
 * Condition_Elementary_NumericLogiqueMetierTest
 * @package Algo
 */
class Condition_Elementary_NumericLogiqueMetierTest extends PHPUnit_Framework_TestCase
{

    protected $_numericCondition;
    protected $_unitValue;
    protected $_input;

    protected $_grandeurPhysiqueMasse;
    protected $_systemeUnite;
    protected $_uniteRefMasse;
    protected $_unite;

    /**
     * Méthode appelée avant l'appel à la classe de test
     */
    public static function setUpBeforeClass()
    {
         Algo_Model_DAO_Algo::getInstance()->unitTestsClearTable();
         Algo_Model_Condition_DAO_Elementary::getInstance()->unitTestsClearTable();
         Algo_Model_Condition_Elementary_DAO_Numeric::getInstance()->unitTestsClearTable();

         Unit_Model_DAO_GrandeurPhysique::getInstance()->unitTestsClearTable();
         Unit_Model_PhysicalQuantity_DAO_ComposantGrandeurPhysique::getInstance()->unitTestsClearTable();
         Unit_Model_Unit_DAO_SystemeUnite::getInstance()->unitTestsClearTable();
         Unit_Model_Unit_DAO_UniteStandard::getInstance()->unitTestsClearTable();
         Unit_Model_DAO_Unit::getInstance()->unitTestsClearTable();
    }

    /**
     * Méthode appelée avant chaque méthode de test.
     */
    function setUp()
    {

        $unit = new Unit_Model_APIUnit('kg');

        $value = new Calc_Value();
        $value->digitalValue = 11;
        $value->relativeUncertainty = 25;

        $unitValue = new Calc_UnitValue();
        $unitValue->unit  = $unit;
        $unitValue->value = $value;


        $this->_numericCondition = Condition_Elementary_NumericTest::generateObject();
        $this->_unitValue = Condition_Elementary_NumericTest::generateUnitValue();
//        $this->_unitValue = $unitValue;
        $this->_input = array("Algo1"  => $this->_unitValue);

        //On créer la grandeurs physiques de base.
        $this->_grandeurPhysiqueMasse = new Unit_Model_GrandeurPhysique();
        $this->_grandeurPhysiqueMasse->nom = "masse";
        $this->_grandeurPhysiqueMasse->ref = "m";
        $this->_grandeurPhysiqueMasse->symbole = "M";
        $this->_grandeurPhysiqueMasse->isBase = "1";
        $this->_grandeurPhysiqueMasse->save();

        //On créer un système d'unité (obligatoire pour une unité standard).
        $this->_systemeUnite = new Unit_Model_Unit_SystemeUnite();
        $this->_systemeUnite->ref = "international";
        $this->_systemeUnite->nom = "International";
        $this->_systemeUnite->save();

        //On créer les unités de références des grandeurs physique de base
        $this->_uniteRefMasse = new Unit_Model_Unit_UniteStandard();
        $this->_uniteRefMasse->coeffMultiplicateur = 1;
        $this->_uniteRefMasse->nom = "kilogramme";
        $this->_uniteRefMasse->symbole = "kg";
        $this->_uniteRefMasse->ref = "kg";
        $this->_uniteRefMasse->setGrandeurPhysique($this->_grandeurPhysiqueMasse);
        $this->_uniteRefMasse->setSystemeUnite($this->_systemeUnite);
        $this->_uniteRefMasse->save();

        $this->_unite = new Unit_Model_Unit_UniteStandard();
        $this->_unite->coeffMultiplicateur = 0.001;
        $this->_unite->nom = "gramme";
        $this->_unite->symbole = "g";
        $this->_unite->ref = "g";
        $this->_unite->setGrandeurPhysique($this->_grandeurPhysiqueMasse);
        $this->_unite->setSystemeUnite($this->_systemeUnite);
        $this->_unite->save();

        $this->_grandeurPhysiqueMasse->setCompositionGrandeurPhysique($this->_grandeurPhysiqueMasse, 1);
        $this->_grandeurPhysiqueMasse->setUniteReference($this->_uniteRefMasse);
        $this->_grandeurPhysiqueMasse->save();
    }

    /**
     * Test de la méthode execute()
     */
    function testExecute()
    {
        $result = $this->_numericCondition->execute($this->_input);
        $this->assertTrue($result);

        //Test erreur 1: l'input est un tableau vide
        $input = array();
        try {
            $result = $this->_numericCondition->execute($input);
        } catch (Core_Exception_InvalidArgument $e) {
            $this->assertEquals($e->getMessage(), 'Le tableau d\'input ne peut être nul dans le cas d\'un conditionNumeric');
        }

        // Test erreur 2: dans le tableau d'input le ref est associé à un objet non unitValue.
        $input = array('Algo1' => 'la');
        try {
            $result = $this->_numericCondition->execute($input);
        } catch (Core_Exception_InvalidArgument $e) {
            $this->assertEquals($e->getMessage(), 'UnitValue requis');
        }

        // Test erreur 3: le ref n'est associé a aucun input.
        $input = array('Algo2' => null);
        try {
            $result = $this->_numericCondition->execute($input);
        } catch (Core_Exception_NotFound $e) {
            $this->assertEquals($e->getMessage(), 'Il n\'y a pas d\'input avec le ref Algo1');
        }
    }

    /**
     * Méthode appelée à la fin de chaque test.
     */
    protected function tearDown()
    {
        $this->_grandeurPhysiqueMasse->delete();
        $this->_systemeUnite->delete();
        $this->_uniteRefMasse->delete();
        $this->_unite->delete();

        Condition_Elementary_NumericTest::deleteObject($this->_numericCondition);
    }

    /**
     * Méthode appelée à la fin de la classe de test.
     */
    public static function tearDownAfterClass()
    {
        // Tables de Algo.
        if (! Algo_Model_DAO_Algo::getInstance()->unitTestsIsTableEmpty()) {
            echo 'La table Algo n\'est pas vide après les tests !';
        }
        if (! Algo_Model_Condition_DAO_Elementary::getInstance()->unitTestsIsTableEmpty()) {
            echo 'La table Elementary n\'est pas vide après les tests !';
        }
        if (! Algo_Model_Condition_Elementary_DAO_Numeric::getInstance()->unitTestsIsTableEmpty()) {
            echo 'La table Numeric n\'est pas vide après les tests !';
        }

        // Tables de Unit.
        if (! Unit_Model_DAO_GrandeurPhysique::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table GrandeurPhysique n'est pas vide après les tests\n";
        }
        if (! Unit_Model_PhysicalQuantity_DAO_ComposantGrandeurPhysique::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table ComposantGrandeurPhysique n'est pas vide après les tests\n";
        }
        if (! Unit_Model_Unit_DAO_SystemeUnite::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table SystemeUnite n'est pas vide après les tests\n";
        }
        if (! Unit_Model_Unit_DAO_UniteStandard::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table UniteStandard n'est pas vide après les tests\n";
        }
        if (! Unit_Model_DAO_Unit::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Unit n'est pas vide après les tests\n";
        }
    }

}//end class Condition_Elementary_NumericLogiqueMetierTest
