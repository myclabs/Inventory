<?php
/**
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage Test
 */

/**
 * Test de la classe Core_Tools
 * @package    Core
 * @subpackage Test
 */
class Core_Test_EnumTest extends PHPUnit_Framework_TestCase
{

    /**
     * getValue()
     */
    public function testGetValue()
    {
        $value = new Core_Test_EnumFixture(Core_Test_EnumFixture::FOO);
        $this->assertEquals(Core_Test_EnumFixture::FOO, $value->getValue());

        $value = new Core_Test_EnumFixture(Core_Test_EnumFixture::BAR);
        $this->assertEquals(Core_Test_EnumFixture::BAR, $value->getValue());

        $value = new Core_Test_EnumFixture(Core_Test_EnumFixture::NUMBER);
        $this->assertEquals(Core_Test_EnumFixture::NUMBER, $value->getValue());
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testInvalidValue1()
    {
        new Core_Test_EnumFixture("test");
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testInvalidValue2()
    {
        new Core_Test_EnumFixture(1234);
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testInvalidValue3()
    {
        new Core_Test_EnumFixture(null);
    }

    /**
     * __toString()
     */
    public function testToString()
    {
        $value = new Core_Test_EnumFixture(Core_Test_EnumFixture::FOO);
        $this->assertEquals(Core_Test_EnumFixture::FOO, (string) $value);

        $value = new Core_Test_EnumFixture(Core_Test_EnumFixture::BAR);
        $this->assertEquals(Core_Test_EnumFixture::BAR, (string) $value);

        $value = new Core_Test_EnumFixture(Core_Test_EnumFixture::NUMBER);
        $this->assertEquals((string) Core_Test_EnumFixture::NUMBER, (string) $value);
    }

    /**
     * toArray()
     */
    public function testToArray()
    {
        $values = Core_Test_EnumFixture::toArray();
        $this->assertInternalType("array", $values);
        $expectedValues = [
            "FOO" => Core_Test_EnumFixture::FOO,
            "BAR" => Core_Test_EnumFixture::BAR,
            "NUMBER" => Core_Test_EnumFixture::NUMBER,
        ];
        $this->assertEquals($expectedValues, $values);
    }

}

/**
 * Fixture class
 * @package    Core
 * @subpackage Test
 */
class Core_Test_EnumFixture extends Core_Enum
{

    const FOO = "foo";
    const BAR = "bar";
    const NUMBER = 42;

}
