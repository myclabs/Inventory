<?php

namespace Tests\Calc;

use Calc_Calculation;
use Calc_Calculation_Value;
use Calc_Value;
use Core\Test\TestCase;
use Core_Exception_InvalidArgument;

class ValueTest extends TestCase
{
    public function testConstructValue()
    {
        $o = new Calc_Value();
        $this->assertEquals($o->getDigitalValue(), 0);
        $this->assertEquals($o->getRelativeUncertainty(), null);
    }

    public function testCalculate()
    {
         // Test somme OK
         $value1 = new Calc_Value(2, 0.1);

         $value2 = new Calc_Value(5, 0.3);

         $calcValue = new Calc_Calculation_Value();
         $calcValue->setOperation(Calc_Calculation::ADD_OPERATION);
         $calcValue->addComponents($value1, Calc_Calculation::SUM);
         $calcValue->addComponents($value2, Calc_Calculation::SUBSTRACTION);

         $result = $calcValue->calculate();
         $this->assertEquals(-3, $result->getDigitalValue());

         // Test multiplication OK
         $calcValue1 = new Calc_Calculation_Value();
         $calcValue1->setOperation(Calc_Calculation::MULTIPLY_OPERATION);
         $calcValue1->addComponents($value1, Calc_Calculation::PRODUCT);
         $calcValue1->addComponents($value2, Calc_Calculation::DIVISION);

         $result = $calcValue1->calculate();

         $this->assertEquals(0.4, $result->getDigitalValue());
    }

    public function testExceptions()
    {
        $value = new Calc_Calculation_Value();
        try {
            $value->calculate();
        } catch (Core_Exception_InvalidArgument $e) {
            $this->assertEquals('Unknow operation', $e->getMessage());
        }
        try {
            $value->setOperation(2);
        } catch (Core_Exception_InvalidArgument $e) {
            $this->assertEquals('The operation must be a class constant', $e->getMessage());
        }
    }

    public function testFloatAndNullValues()
    {
        $value = new Calc_Value(0, 0);
        $this->assertSame(0., $value->getDigitalValue());
        $this->assertSame(0, $value->getRelativeUncertainty());

        $value = new Calc_Value('0', '0');
        $this->assertSame(0., $value->getDigitalValue());
        $this->assertSame(0, $value->getRelativeUncertainty());

        $value = new Calc_Value();
        $this->assertSame(null, $value->getDigitalValue());
        $this->assertSame(null, $value->getRelativeUncertainty());

        $value = new Calc_Value('', '');
        $this->assertSame(null, $value->getDigitalValue());
        $this->assertSame(null, $value->getRelativeUncertainty());
    }

    /**
     * @dataProvider valueProvider
     * @param Calc_Value $value
     */
    public function testExportToString(Calc_Value $value)
    {
        $str = $value->exportToString();

        $unserialized = Calc_Value::createFromString($str);

        $this->assertInstanceOf('Calc_Value', $unserialized);
        $this->assertSame($value->getDigitalValue(), $unserialized->getDigitalValue(), "String: '$str'");
        $this->assertSame($value->getRelativeUncertainty(), $unserialized->getRelativeUncertainty(), "String: '$str'");
    }

    public function valueProvider()
    {
        return [
            [new Calc_Value(0, 0)],
            [new Calc_Value(0., 0.)],
            [new Calc_Value(1, 10)],
            [new Calc_Value(1.0, 10.0)],
            [new Calc_Value(1., 10.)],
            [new Calc_Value(0.1, 0.1)],
            [new Calc_Value(5, null)],
            [new Calc_Value(null, null)],
            [new Calc_Value(null, 20)],
            [new Calc_Value(2)],
            [new Calc_Value()],
            [new Calc_Value('', '')],
            [new Calc_Value(' ', ' ')],
            [new Calc_Value('1', '12')],
            [new Calc_Value('1.0', '12.0')],
        ];
    }

    /**
     * @dataProvider invalidStrings
     * @expectedException \InvalidArgumentException
     * @param string $str
     */
    public function testCreateFromStringInvalid($str)
    {
        Calc_Value::createFromString($str);
    }

    public function invalidStrings()
    {
        return [
            [''],
            ['foo'],
        ];
    }
}
