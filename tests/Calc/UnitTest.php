<?php
/**
 * @author valentin.claras
 * @author hugo.charbonnier
 * @author yoann.croizer
 * @package Calc
 */
use Unit\IncompatibleUnitsException;
use Unit\UnitAPI;

/**
 * @package Calc
 */
class Calc_Test_UnitTest extends PHPUnit_Framework_TestCase
{
    /**
     * Lance les autre classe de tests.
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Calc_Test_Calculation_UnitSetUp');
        $suite->addTestSuite('Calc_Test_Calculation_UnitOthers');
        return $suite;
    }

}

/**
 * @package Calc
 */
class Calc_Test_Calculation_UnitSetUp extends PHPUnit_Framework_TestCase
{

    /**
     * Test du constructeur d'une Calc_Calculation_Unit.
     */
    function testConstructCalculation()
    {
        $o = new Calc_Calculation_Unit();
        $this->assertEquals(true, $o instanceof Calc_Calculation_Unit);
    }

    /**
     * Test du constructeur d'une Calc_Unit.
     */
    function testConstructUnitValue()
    {
        $o = new Calc_UnitValue();
        $this->assertInstanceOf('Calc_UnitValue', $o);
        $this->assertInstanceOf('Calc_Value', $o->value);
        $this->assertInstanceOf('Unit\UnitAPI', $o->unit);
    }

}

/**
 * @package Calc
 */
class Calc_Test_Calculation_UnitOthers extends PHPUnit_Framework_TestCase
{
    /**
     * Méthode appelée avant chaque méthode de test.
     */
    function setUp()
    {
    }

    /**
     * Test de la méthode calculate.
     */
    function testCalculate()
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
        $this->assertEquals('animal.m^2.kg.s^-2', $result->getRef());


        //Test somme d'unité non compatible.
        $unit1 = new UnitAPI('g.animal');
        $unit2 = new UnitAPI('g^2.animal');

        $o2 = new Calc_Calculation_Unit();
        $o2->setOperation(Calc_Calculation::ADD_OPERATION);
        $o2->addComponents($unit1, Calc_Calculation::SUM);
        $o2->addComponents($unit2, Calc_Calculation::SUBSTRACTION);
        try {
            $result = $o2->calculate();
        } catch (IncompatibleUnitsException $e) {
             $this->assertEquals('Units for the sum are incompatible', $e->getMessage());
        }

        //Test somme avec unité inéxistante.
        $unit1 = new UnitAPI('gramme.animal');
        $unit2 = new UnitAPI('g^2.animal');

        $o3 = new Calc_Calculation_Unit();
        $o3->setOperation(Calc_Calculation::ADD_OPERATION);
        $o3->addComponents($unit1, Calc_Calculation::SUM);
        $o3->addComponents($unit2, Calc_Calculation::SUBSTRACTION);
        try {
            $result = $o3->calculate();
        } catch (Core_Exception_NotFound $e) {
            $this->assertEquals("No 'Unit_Model_Unit' matching (ref == gramme)", $e->getMessage());
        }

        //Test opération inconnue.
        $unit1 = new UnitAPI('g.animal');
        $unit2 = new UnitAPI('g^2.animal');

        $o = new Calc_Calculation_Unit();
        $o->addComponents($unit1, 1);
        $o->addComponents($unit2, -1);

        try {
            $result = $o->calculate();
        } catch (Core_Exception_InvalidArgument $e) {
            $this->assertEquals('Unknow operation', $e->getMessage());
        }
    }

    /**
     * fonction apellée après chaque méthode de test
     */
    function tearDown()
    {
    }

    /**
     * Méthode appelée à la fin de la classe de test
     */
    public static function tearDownAfterClass()
    {
    }

}
