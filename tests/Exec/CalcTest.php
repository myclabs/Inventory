<?php
/**
 * @author valentin.claras
 * @author hugo.charbonnier
 * @author yoann.croizer
 * @package Exec
 */

/**
 * @package Exec
 */
class Exec_Test_CalcTest
{
    /**
     * lance les autre classe de tests
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Exec_Test_CalcSetUp');
        $suite->addTestSuite('Exec_Test_CalcOthers');
        return $suite;
    }

}

/**
 * @package Exec
 */
class Exec_Test_CalcSetUp extends PHPUnit_Framework_TestCase
{
    /**
     * test si l'objet renvoyé est bien du type demandé
     */
    function testConstruct()
    {
        $tecExpression = new TEC_Model_Expression();
        $executionCalc = new Exec_Execution_Calc($tecExpression);
        $this->assertInstanceOf('Exec_Execution_Calc', $executionCalc);
    }

}

/**
 * calcSetUpTest
 * @package Exec
 */
class Exec_Test_CalcOthers extends PHPUnit_Framework_TestCase
{
    // Expression utilisée
    protected $expression;
    protected $expressionParticulier;

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

        $this->expression = new TEC_Model_Expression('a+b*c/d-e+f');
        $this->expression->buildTree();
        $this->expressionParticulier = new TEC_Model_Expression('o-(a+b)');
        $this->expressionParticulier->buildTree();
    }

    /**
     * Test de la méthode ExecuteExpression() pour des c    lculs de valeurs uniquement
     */
    function testExecuteExpressionValue()
    {
        $value1 = new Calc_Value();
        $value1->digitalValue = 1;
        $value1->relativeUncertainty = 0.1;

        $value2 = new Calc_Value();
        $value2->digitalValue = 2;
        $value2->relativeUncertainty = 0.2;

        $value3 = new Calc_Value();
        $value3->digitalValue = 3;
        $value3->relativeUncertainty = 0.3;

        $value4 = new Calc_Value();
        $value4->digitalValue = 4;
        $value4->relativeUncertainty = 0.4;

        $value5 = new Calc_Value();
        $value5->digitalValue = 5;
        $value5->relativeUncertainty = 0.5;

        $value6 = new Calc_Value();
        $value6->digitalValue = 6;
        $value6->relativeUncertainty = 0.6;

        $tab = array(
                   "a" => $value1 ,
                   "b" => $value2 ,
                   "c" => $value3 ,
                   "d" => $value4 ,
                   "e" => $value5 ,
                   "f" => $value6
                   );

        $valueProvider = new Default_Model_ValueProviderEntity($tab);

        $calc = new Exec_Execution_Calc($this->expression);
        $calc->setCalculType(Exec_Execution_Calc::CALC_VALUE);
        $this->assertEquals($calc->getExpression(), $this->expression);

        $result = $calc->executeExpression($valueProvider);

        $this->assertTrue($result instanceof Calc_Value);
        $this->assertEquals(3.5, $result->digitalValue);
    }

    /**
     * Test de la méthode executeExpression pour des calculs d'unités uniquement
     */
    function testExecuteExpressionUnit()
    {
        $unite1 = new Unit_API('g');
        $unite2 = new Unit_API('j.animal');
        $unite3 = new Unit_API('kg');
        $unite4 = new Unit_API('kg.m^2.s^-2.animal');
        $unite5 = new Unit_API('kg');
        $unite6 = new Unit_API('g');

        $tab = array(
                       "a" => $unite1 ,
                       "b" => $unite2 ,
                       "c" => $unite3 ,
                       "d" => $unite4 ,
                       "e" => $unite5 ,
                       "f" => $unite6
                   );

        $valueProvider = new Default_Model_ValueProviderEntity($tab);

        $calc = new Exec_Execution_Calc($this->expression);
        $calc->setCalculType(Exec_Execution_Calc::CALC_UNIT);

        $result = $calc->executeExpression($valueProvider);

        $this->assertTrue($result instanceof Unit_API);
        $this->assertEquals('kg', $result->getRef());
    }

    /**
     * Test de la méthode executeExpression pour des calcils d'unitValue uniquement
     */
    function testExecuteExpressionUnitValue()
    {
        $unite1 = new Unit_API('g');
        $unite2 = new Unit_API('j.animal');
        $unite3 = new Unit_API('kg');
        $unite4 = new Unit_API('kg.m^2.s^-2.animal');
        $unite5 = new Unit_API('kg');
        $unite6 = new Unit_API('g');

        $value1 = new Calc_Value();
        $value1->digitalValue = 1;
        $value1->relativeUncertainty = 0.1;

        $value2 = new Calc_Value();
        $value2->digitalValue = 2;
        $value2->relativeUncertainty = 0.2;

        $value3 = new Calc_Value();
        $value3->digitalValue = 3;
        $value3->relativeUncertainty = 0.3;

        $value4 = new Calc_Value();
        $value4->digitalValue = 4;
        $value4->relativeUncertainty = 0.4;

        $value5 = new Calc_Value();
        $value5->digitalValue = 5;
        $value5->relativeUncertainty = 0.5;

        $value6 = new Calc_Value();
        $value6->digitalValue = 6;
        $value6->relativeUncertainty = 0.6;

        $unitValue1 = new Calc_UnitValue();
        $unitValue2 = new Calc_UnitValue();
        $unitValue3 = new Calc_UnitValue();
        $unitValue4 = new Calc_UnitValue();
        $unitValue5 = new Calc_UnitValue();
        $unitValue6 = new Calc_UnitValue();

        $unitValue1->unit  = $unite1;
        $unitValue1->value = $value1;

        $unitValue2->unit  = $unite2;
        $unitValue2->value = $value2;

        $unitValue3->unit  = $unite3;
        $unitValue3->value = $value3;

        $unitValue4->unit  = $unite4;
        $unitValue4->value = $value4;

        $unitValue5->unit  = $unite5;
        $unitValue5->value = $value5;

        $unitValue6->unit  = $unite6;
        $unitValue6->value = $value6;

        $tab = array(
                   "a" => $unitValue1 ,
                   "b" => $unitValue2 ,
                   "c" => $unitValue3 ,
                   "d" => $unitValue4 ,
                   "e" => $unitValue5 ,
                   "f" => $unitValue6
                   );

        $valueProvider = new Default_Model_ValueProviderEntity($tab);

        $calc = new Exec_Execution_Calc($this->expression);
        $calc->setCalculType(Exec_Execution_Calc::CALC_UNITVALUE);

        $result = $calc->executeExpression($valueProvider);

        $this->assertTrue($result instanceof Calc_UnitValue);
        $this->assertTrue($result->unit instanceof Unit_API);
        $this->assertTrue($result->value instanceof Calc_Value);
        $this->assertEquals('kg', $result->unit->getRef());
    }

    /**
     * Permet de tester qu'une erreure est renvoyée dans le cas ou les valeurs ne sont
     * pas homogènes.
     */
    function testExecuteExpressionMixed()
    {
        $unite1 = new Unit_API('g');
        $unite2 = new Unit_API('j.animal');

        $value1 = new Calc_Value();
        $value1->digitalValue = 1;
        $value1->relativeUncertainty = 0.1;

        $value2 = new Calc_Value();
        $value2->digitalValue = 2;
        $value2->relativeUncertainty = 0.2;

        $unitValue1 = new Calc_UnitValue();
        $unitValue1->unit  = $unite1;
        $unitValue1->value = $value1;

        $unitValue2 = new Calc_UnitValue();
        $unitValue2->unit  = $unite2;
        $unitValue2->value = $value2;

        $tab = array(
           "a" => $unite1 ,
           "b" => $unite2 ,
           "c" => $value1 ,
           "d" => $value2 ,
           "e" => $unitValue1 ,
           "f" => $unitValue2
           );

        $valueProvider = new Default_Model_ValueProviderEntity($tab);

        $calc = new Exec_Execution_Calc($this->expression);
        $calc->setCalculType(Exec_Execution_Calc::CALC_UNITVALUE);
        try {
            $calc->executeExpression($valueProvider);
            $this->fail("Erreur d'exception");
        } catch (Core_Exception_InvalidArgument $e) {
            $this->assertEquals($e->getMessage(), 'Array of components is not coherent.');
        }
    }

    /**
     * Test de la méthode ExecuteExpression()
     *  Dans le cas ou le noeud racine à comme noeud enfant directe un Composite.
     *  Dans le cas ou le type d'éléments envoyé au valueProviderEntity n'existe pas
     *
     */
    function testExecuteExpressionCasParticulier()
    {
        $unite1 = new Unit_API('g');
        $unite2 = new Unit_API('kg');

        $tab = array(
            "o" => $unite2,
            "a" => $unite1,
            "b" => $unite2,
        );

        $valueProvider = new Default_Model_ValueProviderEntity($tab);

        $calc = new Exec_Execution_Calc($this->expressionParticulier);
        $calc->setCalculType(Exec_Execution_Calc::CALC_UNIT);

        $result = $calc->executeExpression($valueProvider);

        $this->assertTrue($result instanceof Unit_API);
        $this->assertEquals('kg', $result->getRef());
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

}