<?php

namespace Tests\Classification;

use Classification\Domain\ClassificationLibrary;
use Classification\Domain\Context;
use Classification\Domain\ContextIndicator;
use Classification\Domain\Axis;
use Classification\Domain\Indicator;
use Core\Test\TestCase;

/**
 * @covers \Classification\Domain\ContextIndicator
 */
class ContextIndicatorTest extends TestCase
{
    public function testManageAxes()
    {
        $library = $this->getMock(ClassificationLibrary::class, [], [], '', false);

        $context = new Context($library);
        $indicator = $this->getMock(Indicator::class, [], [], '', false);

        $contextIndicator = new ContextIndicator($library, $context, $indicator);

        $axis1 = new Axis($library);
        $axis2 = new Axis($library);
        $axis3 = new Axis($library);

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
