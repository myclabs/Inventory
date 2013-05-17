<?php
/**
 * @author hugo.charbonnier
 * @author yoann.croizer
 * @package Algo
 */

/**
 * @package Algo
 */
class Numeric_InputTest
{
    /**
     * Lance les autre classe de tests.
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
//        $suite->addTestSuite('Numeric_InputSetUpTest');
//        $suite->addTestSuite('Numeric_InputLogiqueMetierTest');
        return $suite;
    }//end suite()

    /**
     * Permet de générer un objet de base sur lequel on pourra travailler
     */
    public static function generateObjet()
    {
        $o = new Algo_Model_Numeric_Input();
        $o->ref = 'NumericNumericInputt';
        $o->inputRef = 'Algo1';
        $o->unit = new Unit_API('kg');
        $o->setClassifIndicator("3");
        $o->setLabel('labelNumericNumericInputt');
        $o->save();
        return $o;
    }//end generateObject()

    /**
     * Permet de générer un objet de base sur lequel on pourra travailler
     */
    public static function generateUnitValue()
    {
        $value = new Calc_Value();
        $value->digitalValue = 1;
        $value->relativeUncertainty = 0.1;

        $unit = new Unit_API('g');

        $unitValue = new Calc_UnitValue();
        $unitValue->value = $value;
        $unitValue->unit = $unit;

        return $unitValue;
    }//end generateObject()

    /**
     * Supprime un objet utilisé dans les tests
     * @param Algo_Model_Numeric_Input $o
     */
    public static function deleteObject(Algo_Model_Numeric_Input $o)
    {
        $o->delete();
    }

}//end class Numeric_InputTest

/**
 * Numeric_InputSetUpTest
 * @package Algo
 */
class Numeric_InputSetUpTest extends PHPUnit_Framework_TestCase
{
    /**
     * Méthode appelée avant l'appel à la classe de test
     */
    public static function setUpBeforeClass()
    {
        Algo_Model_DAO_Algo::getInstance()->unitTestsClearTable();
        Algo_Model_DAO_Numeric::getInstance()->unitTestsClearTable();
        Algo_Model_Numeric_DAO_NumericInput::getInstance()->unitTestsClearTable();
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
        $o = new Algo_Model_Numeric_Input();
        $this->assertTrue($o instanceof Algo_Model_Numeric_Input);
        $this->assertEquals('Algo_Model_Numeric_Input', $o->type);
    }

    /**
     * Test de la sauvegarde en bdd
     * @return Algo_Model_Numeric_Input $o
     */
    function testSave()
    {
        // Test de l'insertion
        $o = new Algo_Model_Numeric_Input();
        $o->ref = 'AlgoNumericNumericInputtSave';
        $o->inputRef = 'testtttt';
        $o->unit = new Unit_API('g');
        $o->setClassifIndicator("2");
        $o->setLabel('labelAlgoNumericNumericInputtSave');
        $o->save();

        $firstId = $o->id;
        $firstRef = $o->ref;

        $this->assertTrue($firstId > 0);

        // Test de l'update
        $o->ref = 'AlgoNumericNumericInputtUpdate';
        $o->save();

        $secondId = $o->id;
        $secondRef = $o->ref;

        $this->assertTrue($firstId === $secondId && $firstRef !== $secondRef);
        $this->assertTrue($secondRef === 'AlgoNumericNumericInputtUpdate');

        // Test de l'erreur générée si on sauvegarde un algo sans unit
        $a = new Algo_Model_Numeric_Input();
        $a->ref = 'NumericInputErreurSave';
        $a->inputRef = 'testtttt';
        $a->setClassifIndicator("1");
        try {
            $a->save();
        } catch (Core_Exception_InvalidArgument $e) {
            $this->assertEquals(null, $a->id);
        }

        // Test de l'erreur générée si on sauvegarde un algo sans inputRef
        $b = new Algo_Model_Numeric_Input();
        $b->ref = 'NumericInputErreurSave';
        $b->setClassifIndicator("1");
        try {
            $b->save();
        } catch (Core_Exception_InvalidArgument $e) {
            $this->assertEquals(null, $b->id);
        }
        return $o;
    }

    /**
     * @depends testSave
     * @param Algo_Model_Numeric_Input $o
     * @expectedException Core_Exception_NotFound
     */
    function testLoad(Algo_Model_Numeric_Input $o)
    {
        $a = Algo_Model_Numeric_Input::getMapper()->load($o->id);
        $this->assertEquals($a, $o);

        // Test erreur de chargement
        $b = Algo_Model_Numeric_Constant::getMapper()->load(0);
    }


    /**
     * @depends testSave
     * @param Algo_Model_Numeric_Constant $o
     * @expectedException Core_Exception_Systeme
     */
    function testDelete(Algo_Model_Numeric_Input $o)
    {
        $o->delete();
        $this->assertEquals(null, $o->id);
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
            echo "\nLa table Algo_Algo n'est pas vide après les tests\n";
        }
        if (! Algo_Model_DAO_Numeric::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Algo_Numeric n'est pas vide après les tests\n";
        }
        if (! Algo_Model_Numeric_DAO_NumericInput::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Algo_Numeric_NumericInput n'est pas vide après les tests\n";
        }
    }

}//end class Numeric_InputSetUpTest

/**
 * Numeric_InputLogiqueMetierTest
 * @package Algo
 */
class Numeric_InputLogiqueMetierTest extends PHPUnit_Framework_TestCase
{
    protected $_NumericNumericInput;
    protected $_unitValue;
    protected $_input;

    /**
     * Méthode appelée avant l'appel à la classe de test
     */
    public static function setUpBeforeClass()
    {
        Algo_Model_DAO_Algo::getInstance()->unitTestsClearTable();
        Algo_Model_Numeric_DAO_NumericInput::getInstance()->unitTestsClearTable();
        Algo_Model_DAO_Numeric::getInstance()->unitTestsClearTable();
    }

    /**
     * Méthode appelée avant chaque méthode de test.
     */
    function setUp()
    {
        $this->_unitValue = Numeric_InputTest::generateUnitValue();
        $this->_input = array(
            'Algo1' => $this->_unitValue
        );
        $this->_NumericNumericInput = Numeric_InputTest::generateObjet();
    }


    /**
     * Test de la méthode execute()
     */
    function testExecute()
    {
        $result = $this->_NumericNumericInput->execute($this->_input);

        $this->assertTrue($result instanceof Calc_UnitValue);
        $this->assertTrue($result->unit instanceof Unit_Model_APIUnit);
        $this->assertTrue($result->value instanceof Calc_Value);

        $this->assertEquals($result, $this->_unitValue);

        // Test de l'erreur générée lorsque l'on passe un tableau vide à la méthode execute.
        $input = array();
        try {
            $result = $this->_NumericNumericInput->execute($input);
        } catch (Core_Exception_InvalidArgument $e) {
            $this->assertEquals($e->getMessage(), 'Le tableau d\'input ne peut être nul dans le cas d\'un NumericNumericInput');
        }

        // Test de l'erreur générée lorsque l'inputRef est associé à un objet qui n'est pas de resultType Calc_UnitValue.
        $input = array('Algo1' => 'unitValue');
        try {
            $result = $this->_NumericNumericInput->execute($input);
        } catch (Core_Exception_UndefinedAttribute $e) {
            $this->assertEquals($e->getMessage(), 'Le résultat est incorrecte');
        }

        // Test de l'erreur générée lorsque l'inputRef n'est pas présent dans le tableay.
        $input = array('Algo2' => 'unitValue');
        try {
            $result = $this->_NumericNumericInput->execute($input);
        } catch (Core_Exception_InvalidArgument $e) {
            $this->assertEquals($e->getMessage(), 'Il n\'y a pas d\input avec le ref Algo1');
        }
    }

    /**
     * Méthode appelée à la fin de chaque test
     */
    protected function tearDown()
    {
        Numeric_InputTest::deleteObject($this->_NumericNumericInput);
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
        if (! Algo_Model_Numeric_DAO_NumericInput::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table NumericNumericInput n'est pas vide après les tests\n";
        }
        if (! Algo_Model_DAO_Numeric::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Numeric n'est pas vide après les tests\n";
        }
    }

}//end class Numeric_InputLogiqueMetierTest
