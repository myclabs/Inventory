<?php

namespace Tests\Calc;

use Calc_Calculation;
use Calc_Calculation_Unit;
use Calc_UnitValue;
use Core\Test\TestCase;
use Core_Exception_InvalidArgument;
use Core_Exception_NotFound;
use Unit\IncompatibleUnitsException;
use Unit\UnitAPI;

class UnitTest extends TestCase
{
    public function testCalculate()
    {
        //Test produit d'unité OK.
        $unit1 = new UnitAPI('j.animal');
        $unit2 = new UnitAPI('g^2.animal');

        $o = new Calc_Calculation_Unit();
        $o->setOperation(Calc_Calculation::MULTIPLY_OPERATION);
        $o->addComponents($unit1, Calc_Calculation::PRODUCT);
        $o->addComponents($unit2, Calc_Calculation::DIVISION);
        $result = $o->calculate();

        $this->assertEquals(true, $result instanceof UnitAPI);
        $this->assertEquals('m^2.kg^-1.s^-2', $result->getRef());

        //Test somme d'unité OK.
        $unit1 = new UnitAPI('t');
        $unit2 = new UnitAPI('g');

        $o1 = new Calc_Calculation_Unit();
        $o1->setOperation(Calc_Calculation::ADD_OPERATION);
        $o1->addComponents($unit1, Calc_Calculation::SUM);
        $o1->addComponents($unit2, Calc_Calculation::SUM);
        $result = $o1->calculate();

        $this->assertEquals(true, $result instanceof UnitAPI);
        $this->assertEquals('kg', $result->getRef());

        //Test somme d'unité OK.
        $unit1 = new UnitAPI('j.animal');
        $unit2 = new UnitAPI('animal.m^2.kg^1.s^-2');

        $o1 = new Calc_Calculation_Unit();
        $o1->setOperation(Calc_Calculation::ADD_OPERATION);
        $o1->addComponents($unit1, Calc_Calculation::SUM);
        $o1->addComponents($unit2, Calc_Calculation::SUBSTRACTION);
        $result = $o1->calculate();

        $this->assertEquals(true, $result instanceof UnitAPI);
        $this->assertEquals('m^2.animal.kg.s^-2', $result->getRef());
    }

    /**
     * @expectedException \MyCLabs\UnitAPI\Exception\IncompatibleUnitsException
     */
    public function testIncompatibleUnits()
    {
        $o = new Calc_Calculation_Unit();
        $o->setOperation(Calc_Calculation::ADD_OPERATION);
        $o->addComponents(new UnitAPI('g.animal'), Calc_Calculation::SUM);
        $o->addComponents(new UnitAPI('g^2.animal'), Calc_Calculation::SUBSTRACTION);
        $o->calculate();
    }

    /**
     * @expectedException \MyCLabs\UnitAPI\Exception\UnknownUnitException
     */
    public function testUnknownUnit()
    {
        $o = new Calc_Calculation_Unit();
        $o->setOperation(Calc_Calculation::ADD_OPERATION);
        $o->addComponents(new UnitAPI('gramme.animal'), Calc_Calculation::SUM);
        $o->addComponents(new UnitAPI('g^2.animal'), Calc_Calculation::SUBSTRACTION);
        $o->calculate();
    }
}
