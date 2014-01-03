<?php
/**
 * @author hugo.charbonnier
 * @author yoann.croizer
 * @package Calc
 */

use Unit\UnitAPI;

class Calc_Test_UnitValueTest extends PHPUnit_Framework_TestCase
{
    public function testConversion()
    {
        $m = new UnitAPI('m');
        $km = new UnitAPI('km');
        $centkm = new UnitAPI('100km');

        $value = new Calc_UnitValue($km, 3, 10);

        $mValue = $value->convertTo($m);

        $this->assertEquals(3000, $mValue->getDigitalValue());
        $this->assertEquals(10, $mValue->getRelativeUncertainty());
        $this->assertSame($m, $mValue->getUnit());

        $centkmValue = $value->convertTo($centkm);

        $this->assertEquals(0.03, $centkmValue->getDigitalValue());
        $this->assertEquals(10, $centkmValue->getRelativeUncertainty());
        $this->assertSame($centkm, $centkmValue->getUnit());

        $this->assertEquals($value->convertTo($centkm), $mValue->convertTo($centkm));
    }

    public function testCalculateProduct()
    {
        //Test multiplication ok
        $unitValue = new Calc_Calculation_UnitValue();
        $unit1 = new UnitAPI('j^2.animal^-1');
        $unit2 = new UnitAPI('t^2');

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

    public function testCalculateSum()
    {
        $unitValue = new Calc_Calculation_UnitValue();
        $unitValue->setOperation(Calc_Calculation::ADD_OPERATION);
        $unit1  = new UnitAPI('kg.j');
        $unit2  = new UnitAPI('g.j');

        $calcUnitValue1 = new Calc_UnitValue($unit1, 4, 0.04);
        $calcUnitValue2 = new Calc_UnitValue($unit2, 1500, 0.05);

        $unitValue->addComponents($calcUnitValue1, Calc_Calculation::SUM);
        $unitValue->addComponents($calcUnitValue2, Calc_Calculation::SUBSTRACTION);

        $result = $unitValue->calculate();

        $this->assertEquals(2.5, $result->getDigitalValue());
        $this->assertEquals('m^2.kg^2.s^-2', $result->getUnit()->getRef());
    }

    /**
     * @expectedException \MyCLabs\UnitAPI\Exception\IncompatibleUnitsException
     */
    public function testCalculateSumIncompatibleUnits()
    {
        $unitValue2 = new Calc_Calculation_UnitValue();
        $unitValue2->setOperation(Calc_Calculation::ADD_OPERATION);

        $unite3 = new UnitAPI('g.animal');
        $unite4 = new UnitAPI('g^2.animal');

        $calcUnitValue3 = new Calc_UnitValue($unite3, 4, 0.04);
        $calcUnitValue4 = new Calc_UnitValue($unite4, 1500, 0.05);

        $unitValue2->addComponents($calcUnitValue3, Calc_Calculation::SUM);
        $unitValue2->addComponents($calcUnitValue4, Calc_Calculation::SUBSTRACTION);
        $unitValue2->calculate();
    }

    /**
     * @expectedException \MyCLabs\UnitAPI\Exception\UnknownUnitException
     */
    public function testCalculateSumUnknownUnit()
    {
        $unitValue3 = new Calc_Calculation_UnitValue();
        $unitValue3->setOperation(Calc_Calculation::ADD_OPERATION);

        $unite5 = new UnitAPI('gramme.animal');
        $unite6 = new UnitAPI('g^2.animal');

        $calcUnitValue5 = new Calc_UnitValue($unite5, 4, 0.04);
        $calcUnitValue6 = new Calc_UnitValue($unite6, 1500, 0.05);

        $unitValue3->addComponents($calcUnitValue5, Calc_Calculation::SUM);
        $unitValue3->addComponents($calcUnitValue6, Calc_Calculation::SUBSTRACTION);
        $unitValue3->calculate();
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
            [new Calc_UnitValue(new UnitAPI('g'), 0, 0)],
            [new Calc_UnitValue()],
            [new Calc_UnitValue(new UnitAPI('g'))],
            [new Calc_UnitValue(new UnitAPI('g'), 0)],
            [new Calc_UnitValue(new UnitAPI())],
            [new Calc_UnitValue(new UnitAPI('g'), '0.1', '0.1')],
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
