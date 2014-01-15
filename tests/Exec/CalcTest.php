<?php

namespace Tests\Exec;

use Calc_UnitValue;
use Calc_Value;
use Core\Test\TestCase;
use Core_Exception_InvalidArgument;
use Exec\Execution\Calc;
use Inventory_Model_ValueProviderEntity;
use Unit\UnitAPI;
use TEC\Expression;

class CalcTest extends TestCase
{
    protected $expression;
    protected $expressionParticulier;

    public function setUp()
    {
        parent::setUp();

        $this->expression = new Expression('a+b*c/d-e+f');
        $this->expressionParticulier = new Expression('o-(a+b)');
    }

    /**
     * Test de la méthode ExecuteExpression() pour des calculs de valeurs uniquement
     */
    public function testExecuteExpressionValue()
    {
        $tab = [
            "a" => new Calc_Value(1, 0.1),
            "b" => new Calc_Value(2, 0.2),
            "c" => new Calc_Value(3, 0.3),
            "d" => new Calc_Value(4, 0.4),
            "e" => new Calc_Value(5, 0.5),
            "f" => new Calc_Value(6, 0.6),
        ];

        $valueProvider = new Inventory_Model_ValueProviderEntity($tab);

        $calc = new Calc($this->expression);
        $calc->setCalculType(Calc::CALC_VALUE);
        $this->assertEquals($calc->getExpression(), $this->expression);

        $result = $calc->executeExpression($valueProvider);

        $this->assertTrue($result instanceof Calc_Value);
        $this->assertEquals(3.5, $result->getDigitalValue());
    }

    /**
     * Test de la méthode executeExpression pour des calculs d'unités uniquement
     */
    public function testExecuteExpressionUnit()
    {
        $tab = array(
            "a" => new UnitAPI('g'),
            "b" => new UnitAPI('j.animal'),
            "c" => new UnitAPI('kg'),
            "d" => new UnitAPI('kg.m^2.s^-2.animal'),
            "e" => new UnitAPI('kg'),
            "f" => new UnitAPI('g'),
        );

        $valueProvider = new Inventory_Model_ValueProviderEntity($tab);

        $calc = new Calc($this->expression);
        $calc->setCalculType(Calc::CALC_UNIT);

        /** @var UnitAPI $result */
        $result = $calc->executeExpression($valueProvider);

        $this->assertTrue($result instanceof UnitAPI);
        $this->assertEquals('kg', $result->getRef());
    }

    /**
     * Test de la méthode executeExpression pour des calcils d'unitValue uniquement
     */
    public function testExecuteExpressionUnitValue()
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

        $calc = new Calc($this->expression);
        $calc->setCalculType(Calc::CALC_UNITVALUE);

        /** @var Calc_UnitValue $result */
        $result = $calc->executeExpression($valueProvider);

        $this->assertTrue($result instanceof Calc_UnitValue);
        $this->assertTrue($result->getUnit() instanceof UnitAPI);
        $this->assertEquals('kg', $result->getUnit()->getRef());
    }

    /**
     * Permet de tester qu'une erreure est renvoyée dans le cas ou les valeurs ne sont
     * pas homogènes.
     * @expectedException \Core_Exception_InvalidArgument
     * @expectedExceptionMessage Calculation expects an array of Calc_UnitValue, Unit\UnitAPI given
     */
    public function testExecuteExpressionMixed()
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

        $calc = new Calc($this->expression);
        $calc->setCalculType(Calc::CALC_UNITVALUE);
        $calc->executeExpression($valueProvider);
    }

    /**
     * Test de la méthode ExecuteExpression()
     *  Dans le cas ou le noeud racine à comme noeud enfant directe un Composite.
     *  Dans le cas ou le type d'éléments envoyé au valueProviderEntity n'existe pas
     */
    public function testExecuteExpressionCasParticulier()
    {
        $unite1 = new UnitAPI('g');
        $unite2 = new UnitAPI('kg');

        $tab = array(
            "o" => $unite2,
            "a" => $unite1,
            "b" => $unite2,
        );

        $valueProvider = new Inventory_Model_ValueProviderEntity($tab);

        $calc = new Calc($this->expressionParticulier);
        $calc->setCalculType(Calc::CALC_UNIT);

        /** @var UnitAPI $result */
        $result = $calc->executeExpression($valueProvider);

        $this->assertTrue($result instanceof UnitAPI);
        $this->assertEquals('kg', $result->getRef());
    }
}
