<?php
/**
 * @author valentin.claras
 * @author hugo.charbonnier
 * @author yoann.croizer
 * @package Exec
 */

use Unit\UnitAPI;
use TEC\Expression;

/**
 * @package Exec
 */
class Exec_Test_CalcTest extends PHPUnit_Framework_TestCase
{
    // Expression utilisée
    protected $expression;
    protected $expressionParticulier;

    /**
     * Méthode appelée avant chaque méthode de test.
     */
    function setUp()
    {
        $this->expression = new Expression('a+b*c/d-e+f');
        $this->expressionParticulier = new Expression('o-(a+b)');
    }

    /**
     * Test de la méthode ExecuteExpression() pour des c    lculs de valeurs uniquement
     */
    function testExecuteExpressionValue()
    {
        $value1 = new Calc_Value(1, 0.1);

        $value2 = new Calc_Value(2, 0.2);

        $value3 = new Calc_Value(3, 0.3);

        $value4 = new Calc_Value(4, 0.4);

        $value5 = new Calc_Value(5, 0.5);

        $value6 = new Calc_Value(6, 0.6);

        $tab = array(
            "a" => $value1,
            "b" => $value2,
            "c" => $value3,
            "d" => $value4,
            "e" => $value5,
            "f" => $value6,
        );

        $valueProvider = new Inventory_Model_ValueProviderEntity($tab);

        $calc = new Exec_Execution_Calc($this->expression);
        $calc->setCalculType(Exec_Execution_Calc::CALC_VALUE);
        $this->assertEquals($calc->getExpression(), $this->expression);

        /** @var Calc_Value $result */
        $result = $calc->executeExpression($valueProvider);

        $this->assertTrue($result instanceof Calc_Value);
        $this->assertEquals(3.5, $result->getDigitalValue());
    }

    /**
     * Test de la méthode executeExpression pour des calculs d'unités uniquement
     */
    function testExecuteExpressionUnit()
    {
        $unite1 = new UnitAPI('g');
        $unite2 = new UnitAPI('j.animal');
        $unite3 = new UnitAPI('kg');
        $unite4 = new UnitAPI('kg.m^2.s^-2.animal');
        $unite5 = new UnitAPI('kg');
        $unite6 = new UnitAPI('g');

        $tab = array(
            "a" => $unite1,
            "b" => $unite2,
            "c" => $unite3,
            "d" => $unite4,
            "e" => $unite5,
            "f" => $unite6,
        );

        $valueProvider = new Inventory_Model_ValueProviderEntity($tab);

        $calc = new Exec_Execution_Calc($this->expression);
        $calc->setCalculType(Exec_Execution_Calc::CALC_UNIT);

        /** @var Unit_API $result */
        $result = $calc->executeExpression($valueProvider);

        $this->assertTrue($result instanceof UnitAPI);
        $this->assertEquals('kg', $result->getRef());
    }

    /**
     * Test de la méthode executeExpression pour des calcils d'unitValue uniquement
     */
    function testExecuteExpressionUnitValue()
    {
        $unite1 = new UnitAPI('g');
        $unite2 = new UnitAPI('j.animal');
        $unite3 = new UnitAPI('kg');
        $unite4 = new UnitAPI('kg.m^2.s^-2.animal');
        $unite5 = new UnitAPI('kg');
        $unite6 = new UnitAPI('g');

        $unitValue1 = new Calc_UnitValue($unite1, 1, 0.1);
        $unitValue2 = new Calc_UnitValue($unite2, 2, 0.2);
        $unitValue3 = new Calc_UnitValue($unite3, 3, 0.3);
        $unitValue4 = new Calc_UnitValue($unite4, 4, 0.4);
        $unitValue5 = new Calc_UnitValue($unite5, 5, 0.5);
        $unitValue6 = new Calc_UnitValue($unite6, 6, 0.6);

        $tab = array(
            "a" => $unitValue1,
            "b" => $unitValue2,
            "c" => $unitValue3,
            "d" => $unitValue4,
            "e" => $unitValue5,
            "f" => $unitValue6,
        );

        $valueProvider = new Inventory_Model_ValueProviderEntity($tab);

        $calc = new Exec_Execution_Calc($this->expression);
        $calc->setCalculType(Exec_Execution_Calc::CALC_UNITVALUE);

        /** @var Calc_UnitValue $result */
        $result = $calc->executeExpression($valueProvider);

        $this->assertTrue($result instanceof Calc_UnitValue);
        $this->assertTrue($result->getUnit() instanceof UnitAPI);
        $this->assertEquals('kg', $result->getUnit()->getRef());
    }

    /**
     * Permet de tester qu'une erreure est renvoyée dans le cas ou les valeurs ne sont
     * pas homogènes.
     */
    function testExecuteExpressionMixed()
    {
        $unite1 = new UnitAPI('g');
        $unite2 = new UnitAPI('j.animal');

        $value1 = new Calc_Value(1, 0.1);
        $value2 = new Calc_Value(2, 0.2);

        $unitValue1 = new Calc_UnitValue($unite1, 1, 0.1);
        $unitValue2 = new Calc_UnitValue($unite2, 2, 0.2);

        $tab = array(
            "a" => $unite1,
            "b" => $unite2,
            "c" => $value1,
            "d" => $value2,
            "e" => $unitValue1,
            "f" => $unitValue2,
        );

        $valueProvider = new Inventory_Model_ValueProviderEntity($tab);

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
        $unite1 = new UnitAPI('g');
        $unite2 = new UnitAPI('kg');

        $tab = array(
            "o" => $unite2,
            "a" => $unite1,
            "b" => $unite2,
        );

        $valueProvider = new Inventory_Model_ValueProviderEntity($tab);

        $calc = new Exec_Execution_Calc($this->expressionParticulier);
        $calc->setCalculType(Exec_Execution_Calc::CALC_UNIT);

        /** @var Unit_API $result */
        $result = $calc->executeExpression($valueProvider);

        $this->assertTrue($result instanceof UnitAPI);
        $this->assertEquals('kg', $result->getRef());
    }

}