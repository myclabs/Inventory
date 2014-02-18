<?php

namespace Tests\Classification;

use Classification\Domain\ContextIndicator;
use Classification\Domain\IndicatorAxis;
use Core\Test\TestCase;

/**
 * @covers \Classification\Domain\ContextIndicator
 */
class ContextIndicatorTest extends TestCase
{
    public function testManageAxes()
    {
        $contextIndicator = new ContextIndicator();

        $axis1 = new IndicatorAxis();
        $axis2 = new IndicatorAxis();
        $axis3 = new IndicatorAxis();

        $this->assertFalse($contextIndicator->hasAxes());
        $this->assertFalse($contextIndicator->hasAxis($axis1));
        $this->assertFalse($contextIndicator->hasAxis($axis2));
        $this->assertFalse($contextIndicator->hasAxis($axis3));
        $this->assertEmpty($contextIndicator->getAxes());

        $contextIndicator->addAxis($axis1);
        $contextIndicator->addAxis($axis2);

        $this->assertTrue($contextIndicator->hasAxes());
        $this->assertTrue($contextIndicator->hasAxis($axis1));
        $this->assertTrue($contextIndicator->hasAxis($axis2));
        $this->assertFalse($contextIndicator->hasAxis($axis3));
        $this->assertEquals([$axis1, $axis2], $contextIndicator->getAxes());

        $contextIndicator->removeAxis($axis1);

        $this->assertTrue($contextIndicator->hasAxes());
        $this->assertFalse($contextIndicator->hasAxis($axis1));
        $this->assertTrue($contextIndicator->hasAxis($axis2));
        $this->assertFalse($contextIndicator->hasAxis($axis3));
        $this->assertEquals([1 => $axis2], $contextIndicator->getAxes());

        $contextIndicator->removeAxis($axis2);

        $this->assertFalse($contextIndicator->hasAxes());
        $this->assertFalse($contextIndicator->hasAxis($axis1));
        $this->assertFalse($contextIndicator->hasAxis($axis2));
        $this->assertFalse($contextIndicator->hasAxis($axis3));
        $this->assertEmpty($contextIndicator->getAxes());
    }
}
