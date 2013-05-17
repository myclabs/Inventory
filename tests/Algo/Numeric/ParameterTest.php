<?php
/**
 * @author hugo.charbonnier
 * @package Algo
 */

/**
 * @package Algo
 */
class Numeric_ParameterTest
{
    /**
     * Lance les autre classe de tests.
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
//        $suite->addTestSuite('Numeric_ParameterSetUpTest');
//        $suite->addTestSuite('Numeric_ParameterLogiqueMetierTest');
        return $suite;
    }

    /**
     * Permet de générer un objet de base sur lequel on pourra travailler
     */
    public static function generateObjet()
    {
        $o = new Algo_Model_Numeric_Parameter();
        $o->ref = 'NumericDbparameter';
        $o->setLabel('labelNumericDbparameter');
        $o->family = 'testFamily';
        $o->setClassifIndicator(1);
        $o->save();

        return $o;
    }

    /**
     * Supprime un objet utilisé dans les tests
     * @param Algo_Model_Numeric_Parameter $o
     */
    public static function deleteObject(Algo_Model_Numeric_Parameter $o)
    {
        $o->delete();
    }

}//end class Numeric_ParameterTest

/**
 * dbparameterSetUpTest
 * @package Algo
 */
class Numeric_ParameterSetUpTest extends PHPUnit_Framework_TestCase
{
    /**
     * Méthode appelée avant l'appel à la classe de test
     */
    public static function setUpBeforeClass()
    {
        Algo_Model_DAO_Algo::getInstance()->unitTestsClearTable();
        Algo_Model_Numeric_DAO_DBParameter::getInstance()->unitTestsClearTable();
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
        $o = new Algo_Model_Numeric_Parameter();
        $this->assertTrue($o instanceof Algo_Model_Numeric_Parameter);
    }

    /**
     * Test de la sauvegarde en bdd
     * @return Algo_Model_Numeric_Parameter $o
     */
    function testSave()
    {
        // Test de l'insertion
        $o = new Algo_Model_Numeric_Parameter();
        $o->ref = 'AlgoNumericDBParameterSave';
        $o->setClassifIndicator("1");
        $o->setLabel('labelAlgoNumericDBParameterSave');
        $o->save();

        $firstId  = $o->id;
        $firstRef = $o->ref;

        $this->assertTrue($firstId > 0);

        // Test de l'update
        $o->ref = 'AlgoNumericDBParameterUpdate';
        $o->save();

        $secondId  = $o->id;
        $secondRef = $o->ref;

        $this->assertTrue($firstId === $secondId && $firstRef !== $secondRef);
        $this->assertTrue($secondRef === 'AlgoNumericDBParameterUpdate');

        return $o;
    }

    /**
     * @depends testSave
     * @param Algo_Model_Numeric_Parameter $o
     */
    function testLoad(Algo_Model_Numeric_Parameter $o)
    {
        $a = Algo_Model_Numeric_Parameter::load($o->getKey());
        $this->assertEquals($a, $o);

        return $a;
    }

    /**
     * @depends testLoad
     * @param Algo_Model_Numeric_Parameter $o
     * @expectedException Core_Exception_NotFound
     */
    function testDelete(Algo_Model_Numeric_Parameter $o)
    {
        $o->delete();
        $this->assertEquals(null, $o->getKey());
        // Test de l'exception.
        $o->delete();
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
        // Check tables are empty
        if (! Algo_Model_DAO_Algo::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Algo n'est pas vide après les tests\n";
        }
        if (! Algo_Model_Numeric_DAO_DBParameter::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table NumericDbparameter n'est pas vide après les tests\n";
        }
    }
}//end class Numeric_ConstantSetUpTest

/**
 * Numeric_ConstantLogiqueMetierTest
 * @package Algo
 */
class Numeric_ParameterLogiqueMetierTest extends PHPUnit_Framework_TestCase
{

    protected $_numericDbparameter;

    /**
     * Méthode appelée avant l'appel à la classe de test
     */
    public static function setUpBeforeClass()
    {
        Algo_Model_DAO_Algo::getInstance()->unitTestsClearTable();
        Algo_Model_Numeric_DAO_DBParameter::getInstance()->unitTestsClearTable();
    }

    /**
     * Méthode appelée avant chaque méthode de test.
     */
    function setUp()
    {
         $this->_numericDbparameter = Numeric_ParameterTest::generateObjet();
    }

    /**
     * Test de la méthode execute()
     */
    function testExecute()
    {
        // @todo pas facile à mettre en place
        // Il faudrait peut être une base de technoDb spécifique aux tests unitaires ?

       /* $input  = array();

        // On ouvre une connexion avec la base de techno
        $refTechnoDB = 'ref_branch_1';
        TechnoDB_API::setDefaultTechno($refTechnoDB);

        $value = $this->_numericDbparameter->execute($input);
        $this->assertTrue($value instanceof Calc_UnitValue);
        $this->assertTrue($value->unit instanceof Unit_Model_APIUnit);
        $this->assertTrue($value->value instanceof Calc_Value);
        */
    }

    /**
     * Méthode appelée à la fin de chaque test
     */
    protected function tearDown()
    {
        Numeric_ParameterTest::deleteObject($this->_numericDbparameter);
    }

    /**
     * Méthode appelée à la fin de la classe de test
     */
    public static function tearDownAfterClass()
    {
        // Check tables are empty
        if (! Algo_Model_DAO_Algo::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Algo n'est pas vide après les tests\n";
        }
        if (! Algo_Model_Numeric_DAO_DBParameter::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table NumericDbparameter n'est pas vide après les tests\n";
        }
    }

}//end class Numeric_ConstantLogiqueMetierTest
