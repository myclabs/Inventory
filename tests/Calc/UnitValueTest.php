<?php
/**
 * @author hugo.charbonnier
 * @author yoann.croizer
 * @package Calc
 */

/**
 * @package Calc
 */
class Calc_Test_UnitValueTest extends PHPUnit_Framework_TestCase
{

    /**
     * Test de la fonction calculateProduct()
     */
    public function testCalculateProduct()
    {
        //Test multiplication ok
        $unitValue = new Calc_Calculation_UnitValue();
        $unit1 = new Unit_API('j^2.animal^-1');
        $unit2 = new Unit_API('t^2');

        $calcUnitValue1 = new Calc_UnitValue($unit1, 3, 10);
        $calcUnitValue2 = new Calc_UnitValue($unit2, 4, 0.08);

        $unitValue->addComponents($calcUnitValue1, Calc_Calculation::PRODUCT);
        $unitValue->addComponents($calcUnitValue2, Calc_Calculation::DIVISION);

        $unitValue->setOperation(Calc_Calculation::MULTIPLY_OPERATION);
        $unitValue->calculate();
        $result = $unitValue->calculate();

        $this->assertEquals(0.00000075, $result->getDigitalValue());
        $this->assertEquals('m^4.animal^-1.s^-4', $result->getUnit()->getRef());

    }

    /**
     * Test de la fonction calculateSum()
     */
    public function testCalculateSum()
    {
        $unitValue = new Calc_Calculation_UnitValue();
        $unitValue->setOperation(Calc_Calculation::ADD_OPERATION);
        $unit1  = new Unit_API('kg.j');
        $unit2  = new Unit_API('g.j');

        $calcUnitValue1 = new Calc_UnitValue($unit1, 4, 0.04);
        $calcUnitValue2 = new Calc_UnitValue($unit2, 1500, 0.05);

        $unitValue->addComponents($calcUnitValue1, Calc_Calculation::SUM);
        $unitValue->addComponents($calcUnitValue2, Calc_Calculation::SUBSTRACTION);

        $result = $unitValue->calculate();

        $this->assertEquals(2.5, $result->getDigitalValue());
        $this->assertEquals('m^2.kg^2.s^-2', $result->getUnit()->getRef());


         //Test somme d'unité non compatible.

        $unitValue2 = new Calc_Calculation_UnitValue();
        $unitValue2->setOperation(Calc_Calculation::ADD_OPERATION);

        $unite3 = new Unit_API('g.animal');
        $unite4 = new Unit_API('g^2.animal');

        $calcUnitValue3 = new Calc_UnitValue($unite3, 4, 0.04);
        $calcUnitValue4 = new Calc_UnitValue($unite4, 1500, 0.05);

        $unitValue2->addComponents($calcUnitValue3, Calc_Calculation::SUM);
        $unitValue2->addComponents($calcUnitValue4, Calc_Calculation::SUBSTRACTION);

        try {
             $unitValue2->calculate();
        } catch (Unit_Exception_IncompatibleUnits $e) {
             $this->assertEquals('Units for the sum are incompatible', $e->getMessage());
        }

        //Test somme d'unité ionexistante.

        $unitValue3 = new Calc_Calculation_UnitValue();
        $unitValue3->setOperation(Calc_Calculation::ADD_OPERATION);

        $unite5 = new Unit_API('gramme.animal');
        $unite6 = new Unit_API('g^2.animal');

        $calcUnitValue5 = new Calc_UnitValue($unite5, 4, 0.04);
        $calcUnitValue6 = new Calc_UnitValue($unite6, 1500, 0.05);

        $unitValue3->addComponents($calcUnitValue5, Calc_Calculation::SUM);
        $unitValue3->addComponents($calcUnitValue6, Calc_Calculation::SUBSTRACTION);

        try {
             $unitValue3->calculate();
        } catch (Core_Exception_NotFound $e) {
            $this->assertEquals("No 'Unit_Model_Unit' matching (ref == gramme)", $e->getMessage());
        }
    }

    /**
     * Test des exceptions
     */
    public function testExceptions()
    {
        $unitValue = new Calc_Calculation_UnitValue();
        try {
            $unitValue->calculate();
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
     * @dataProvider valueProvider
     * @param Calc_UnitValue $value
     */
    public function testExportToString(Calc_UnitValue $value)
    {
        $str = $value->exportToString();

        $unserialized = Calc_UnitValue::createFromString($str);

        $this->assertInstanceOf('Calc_UnitValue', $unserialized);
        $this->assertEquals($value->getUnit()->getRef(), $unserialized->getUnit()->getRef(), "String: '$str'");
        $this->assertSame($value->getDigitalValue(), $unserialized->getDigitalValue(), "String: '$str'");
        $this->assertSame($value->getRelativeUncertainty(), $unserialized->getRelativeUncertainty(), "String: '$str'");
    }

    public function valueProvider()
    {
        return [
            [new Calc_UnitValue(new Unit_API('g'), 0, 0)],
            [new Calc_UnitValue()],
            [new Calc_UnitValue(new Unit_API('g'))],
            [new Calc_UnitValue(new Unit_API('g'), 0)],
            [new Calc_UnitValue(new Unit_API())],
            [new Calc_UnitValue(new Unit_API('g'), '0.1', '0.1')],
        ];
    }

    /**
     * @dataProvider invalidStrings
     * @expectedException InvalidArgumentException
     * @param string $str
     */
    public function testCreateFromStringInvalid($str)
    {
        Calc_UnitValue::createFromString($str);
    }

    public function invalidStrings()
    {
        return [
            [''],
            ['foo'],
            [';'],
            ['|'],
        ];
    }

}
