<?php

namespace Tests\Classification;

use Classification\Domain\Axis;
use Classification\Domain\ClassificationLibrary;
use Core\Test\TestCase;

/**
 * @covers \Classification\Domain\Axis
 */
class AxisTest extends TestCase
{
    public function testSetGetNarrower()
    {
        $library = $this->getMock(ClassificationLibrary::class, [], [], '', false);

        $axis = new Axis($library);
        $narrower = new Axis($library);

        $axis->setDirectNarrower($narrower);
        $this->assertSame($axis->getDirectNarrower(), $narrower);
        $axis->setDirectNarrower();
        $this->assertNull($axis->getDirectNarrower());
    }

    public function testManageBroaders()
    {
        $library = $this->getMock(ClassificationLibrary::class, [], [], '', false);

        $axis = new Axis($library);

        $broader1 = new Axis($library);
        $broader11 = new Axis($library);
        $broader2 = new Axis($library);
        $broader3 = new Axis($library);

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
