<?php

namespace Tests\Classification;

use Classification\Domain\IndicatorAxis;
use Core\Test\TestCase;

/**
 * @covers \Classification\Domain\IndicatorAxis
 */
class AxisTest extends TestCase
{
    public function testSetGetNarrower()
    {
        $axis = new IndicatorAxis();
        $narrower = new IndicatorAxis();

        $axis->setDirectNarrower($narrower);
        $this->assertSame($axis->getDirectNarrower(), $narrower);
        $axis->setDirectNarrower();
        $this->assertNull($axis->getDirectNarrower());
    }

    public function testManageBroaders()
    {
        $axis = new IndicatorAxis();

        $broader1 = new IndicatorAxis();
        $broader11 = new IndicatorAxis();
        $broader2 = new IndicatorAxis();
        $broader3 = new IndicatorAxis();

        $this->assertFalse($axis->hasDirectBroaders());
        $this->assertFalse($axis->hasDirectBroader($broader1));
        $this->assertFalse($axis->hasDirectBroader($broader11));
        $this->assertFalse($axis->hasDirectBroader($broader2));
        $this->assertFalse($axis->hasDirectBroader($broader3));
        $this->assertEmpty($axis->getDirectBroaders());
        $this->assertEmpty($axis->getAllBroaders());

        $axis->addDirectBroader($broader1);
        $axis->addDirectBroader($broader2);

        $this->assertTrue($axis->hasDirectBroaders());
        $this->assertTrue($axis->hasDirectBroader($broader1));
        $this->assertFalse($axis->hasDirectBroader($broader11));
        $this->assertTrue($axis->hasDirectBroader($broader2));
        $this->assertFalse($axis->hasDirectBroader($broader3));
        $this->assertEquals([$broader1, $broader2], $axis->getDirectBroaders());
        $this->assertEquals([$broader1, $broader2], $axis->getAllBroaders());

        $broader1->addDirectBroader($broader11);

        $this->assertTrue($axis->hasDirectBroaders());
        $this->assertTrue($axis->hasDirectBroader($broader1));
        $this->assertFalse($axis->hasDirectBroader($broader11));
        $this->assertTrue($axis->hasDirectBroader($broader2));
        $this->assertFalse($axis->hasDirectBroader($broader3));
        $this->assertEquals([$broader1, $broader2], $axis->getDirectBroaders());
        $this->assertEquals([$broader11, $broader1, $broader2], $axis->getAllBroaders());

        $axis->removeDirectBroader($broader2);

        $this->assertTrue($axis->hasDirectBroaders());
        $this->assertTrue($axis->hasDirectBroader($broader1));
        $this->assertFalse($axis->hasDirectBroader($broader11));
        $this->assertFalse($axis->hasDirectBroader($broader2));
        $this->assertFalse($axis->hasDirectBroader($broader3));
        $this->assertEquals([$broader1], $axis->getDirectBroaders());
        $this->assertEquals([$broader11, $broader1], $axis->getAllBroaders());
    }
}
