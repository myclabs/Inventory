<?php
/**
 * @author hugo.charbonnier
 * @author yoann.croizer
 * @package Calc
 */
use Unit\IncompatibleUnitsException;
use Unit\UnitAPI;

/**
 * @package Calc
 */
class Calc_Test_UnitValueTest extends PHPUnit_Framework_TestCase
{
    /**
     * Lance les autre classe de tests.
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Calc_Test_Calculation_UnitValueSetUp');
        $suite->addTestSuite('Calc_Test_Calculation_UnitValueOthers');
        return $suite;
    }

}

/**
 * UnitValueSetUpTest
 * @package Calc
 */
class Calc_Test_Calculation_UnitValueSetUp extends PHPUnit_Framework_TestCase
{
    /**
     * test du constructeur
     */
    public function testConstruct()
    {
         $o = new Calc_Calculation_UnitValue();
         $this->assertEquals(true, $o instanceof Calc_Calculation_UnitValue);
    }

}

/**
 * UnitValueLogiqueMetierTest
 * @package Calc
 */
class Calc_Test_Calculation_UnitValueOthers extends PHPUnit_Framework_TestCase
{
    /**
     * Méthode appelée avant l'appel à la classe de test
     */
    public static function setUpBeforeClass()
    {
    }

    /**
     * fontion apellée avant chaque méthode de test
     */
    function setUp()
    {
    }

    /**
     * Test de la fonction calculateProduct()
     */
    public function testCalculateProduct()
    {
        //Test multiplication ok
        $unitValue = new Calc_Calculation_UnitValue();
        $unit1 = new UnitAPI('j^2.animal^-1');
        $unit2 = new UnitAPI('t^2');

        $value1 = new Calc_Value();
        $value2 = new Calc_Value();
        $value1->digitalValue = 3;
        $value1->relativeUncertainty = 10;
        $value2->digitalValue = 4;
        $value2->relativeUncertainty = 0.08;

        $calcUnitValue1 = new Calc_UnitValue();
        $calcUnitValue2 = new Calc_UnitValue();

        $calcUnitValue1->unit = $unit1;
        $calcUnitValue1->value = $value1;

        $calcUnitValue2->unit = $unit2;
        $calcUnitValue2->value = $value2;

        $unitValue->addComponents($calcUnitValue1, Calc_Calculation::PRODUCT);
        $unitValue->addComponents($calcUnitValue2, Calc_Calculation::DIVISION);

        $unitValue->setOperation(Calc_Calculation::MULTIPLY_OPERATION);
        $unitValue->calculate();
        $result = $unitValue->calculate();

        $this->assertEquals(0.00000075, $result->value->digitalValue);
        $this->assertEquals('m^4.animal^-1.s^-4', $result->unit->getRef());

    }

    /**
     * Test de la fonction calculateSum()
     */
    public function testCalculateSum()
    {
        $unitValue = new Calc_Calculation_UnitValue();
        $unitValue->setOperation(Calc_Calculation::ADD_OPERATION);
        $unit1  = new UnitAPI('kg.j');
        $unit2  = new UnitAPI('g.j');
        $value1 = new Calc_Value();
        $value2 = new Calc_Value();
        $value1->digitalValue = 4;
        $value1->relativeUncertainty = 0.04;
        $value2->digitalValue = 1500;
        $value2->relativeUncertainty = 0.05;

        $calcUnitValue1 = new Calc_UnitValue();
        $calcUnitValue2 = new Calc_UnitValue();

        $calcUnitValue1->unit  = $unit1;
        $calcUnitValue1->value = $value1;

        $calcUnitValue2->unit  = $unit2;
        $calcUnitValue2->value = $value2;

        $unitValue->addComponents($calcUnitValue1, Calc_Calculation::SUM);
        $unitValue->addComponents($calcUnitValue2, Calc_Calculation::SUBSTRACTION);

        $result = $unitValue->calculate();

        $this->assertEquals(2.5, $result->value->digitalValue);
        $this->assertEquals('m^2.kg^2.s^-2', $result->unit->getRef());


         //Test somme d'unité non compatible.

        $unitValue2 = new Calc_Calculation_UnitValue();
        $unitValue2->setOperation(Calc_Calculation::ADD_OPERATION);

        $unite3 = new UnitAPI('g.animal');
        $unite4 = new UnitAPI('g^2.animal');

        $calcUnitValue3 = new Calc_UnitValue();
        $calcUnitValue4 = new Calc_UnitValue();

        $calcUnitValue3->unit = $unite3;
        $calcUnitValue3->value = $value1;

        $calcUnitValue4->unit = $unite4;
        $calcUnitValue4->value = $value2;

        $unitValue2->addComponents($calcUnitValue3, Calc_Calculation::SUM);
        $unitValue2->addComponents($calcUnitValue4, Calc_Calculation::SUBSTRACTION);

        try {
             $result = $unitValue2->calculate();
        } catch (IncompatibleUnitsException $e) {
             $this->assertEquals('Units for the sum are incompatible', $e->getMessage());
        }

        //Test somme d'unité ionexistante.

        $unitValue3 = new Calc_Calculation_UnitValue();
        $unitValue3->setOperation(Calc_Calculation::ADD_OPERATION);

        $unite5 = new UnitAPI('gramme.animal');
        $unite6 = new UnitAPI('g^2.animal');

        $calcUnitValue5 = new Calc_UnitValue();
        $calcUnitValue6 = new Calc_UnitValue();

        $calcUnitValue5->unit = $unite5;
        $calcUnitValue5->value = $value1;

        $calcUnitValue6->unit = $unite6;
        $calcUnitValue6->value = $value2;

        $unitValue3->addComponents($calcUnitValue5, Calc_Calculation::SUM);
        $unitValue3->addComponents($calcUnitValue6, Calc_Calculation::SUBSTRACTION);

        try {
             $result = $unitValue3->calculate();
        } catch (Core_Exception_NotFound $e) {
            $this->assertEquals("No 'Unit\\Domain\\Unit' matching (ref == gramme)", $e->getMessage());
        }
    }

    /**
     * Test des exceptions
     */
    public function testExceptions()
    {
        $unitValue = new Calc_Calculation_UnitValue();
        try {
            $result = $unitValue->calculate();
        } catch (Core_Exception_InvalidArgument $e) {
            $this->assertEquals('Unknow operation', $e->getMessage());
        }
        try {
            $unitValue->setOperation(2);
        } catch (Core_Exception_InvalidArgument $e) {
            $this->assertEquals('The operation must be a class constant', $e->getMessage());
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
