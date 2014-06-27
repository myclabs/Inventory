<?php

namespace Tests\DW;

use DW\Domain\Axis;
use DW\Domain\Cube;
use Core\Test\TestCase;
use DW\Domain\Member;

/**
 * @covers \Classification\Domain\Axis
 */
class AxisTest extends TestCase
{
    /**
     * @var Cube
     */
    protected $cube;
    /**
     * @var Axis
     */
    protected $axis;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->cube = new Cube();

        $this->axis = new Axis($this->cube);
        $this->axis->setRef('test');
    }

    /**
     * @expectedException        \Core_Exception_InvalidArgument
     * @expectedExceptionMessage The given axis can't be broader than this.
     */
    public function testSetDirectNarrowerWithBroader()
    {
        $axis1 = new Axis($this->cube);
        $axis1->setRef('test1');
        $axis1->setDirectNarrower($this->axis);
        $axis11 = new Axis($this->cube);
        $axis11->setRef('test11');
        $axis11->setDirectNarrower($axis1);
        $axis111 = new Axis($this->cube);
        $axis111->setRef('test111');
        $axis111->setDirectNarrower($axis11);

        $axis1->setDirectNarrower($axis111);

        $this->fail('"Core_Exception_InvalidArgument" expected.');
    }

    public function testGetAllNarrowers()
    {
        $axis1 = new Axis($this->cube);
        $axis1->setRef('test1');
        $axis1->setDirectNarrower($this->axis);
        $axis11 = new Axis($this->cube);
        $axis11->setRef('test11');
        $axis11->setDirectNarrower($axis1);
        $axis111 = new Axis($this->cube);
        $axis111->setRef('test111');
        $axis111->setDirectNarrower($axis11);

        $this->assertSame([], $this->axis->getAllNarrowers());
        $this->assertSame([$this->axis], $axis1->getAllNarrowers());
        $this->assertSame([$axis1, $this->axis], $axis11->getAllNarrowers());
        $this->assertSame([$axis11, $axis1, $this->axis], $axis111->getAllNarrowers());
    }

    public function testGetAllBroadersFirstOrdered()
    {
        $axis1 = new Axis($this->cube);
        $axis1->setRef('test1');
        $axis1->setDirectNarrower($this->axis);
        $axis11 = new Axis($this->cube);
        $axis11->setRef('test11');
        $axis11->setDirectNarrower($axis1);
        $axis111 = new Axis($this->cube);
        $axis111->setRef('test111');
        $axis111->setDirectNarrower($axis11);
        $axis12 = new Axis($this->cube);
        $axis12->setRef('test12');
        $axis12->setDirectNarrower($axis1);
        $axis2 = new Axis($this->cube);
        $axis2->setRef('test2');
        $axis2->setDirectNarrower($this->axis);

        $this->assertSame([$axis1, $axis11, $axis111, $axis12, $axis2], $this->axis->getAllBroadersFirstOrdered());
        $this->assertSame([$axis11, $axis111, $axis12], $axis1->getAllBroadersFirstOrdered());
        $this->assertSame([$axis111], $axis11->getAllBroadersFirstOrdered());
        $this->assertSame([], $axis111->getAllBroadersFirstOrdered());
        $this->assertSame([], $axis12->getAllBroadersFirstOrdered());
        $this->assertSame([], $axis2->getAllBroadersFirstOrdered());
    }

    public function testGetAllBroadersLastOrdered()
    {
        $axis1 = new Axis($this->cube);
        $axis1->setRef('test1');
        $axis1->setDirectNarrower($this->axis);
        $axis11 = new Axis($this->cube);
        $axis11->setRef('test11');
        $axis11->setDirectNarrower($axis1);
        $axis111 = new Axis($this->cube);
        $axis111->setRef('test111');
        $axis111->setDirectNarrower($axis11);
        $axis12 = new Axis($this->cube);
        $axis12->setRef('test12');
        $axis12->setDirectNarrower($axis1);
        $axis2 = new Axis($this->cube);
        $axis2->setRef('test2');
        $axis2->setDirectNarrower($this->axis);

        $this->assertSame([$axis111, $axis11, $axis12, $axis1, $axis2], $this->axis->getAllBroadersLastOrdered());
        $this->assertSame([$axis111, $axis11, $axis12], $axis1->getAllBroadersLastOrdered());
        $this->assertSame([$axis111], $axis11->getAllBroadersLastOrdered());
        $this->assertSame([], $axis111->getAllBroadersLastOrdered());
        $this->assertSame([], $axis12->getAllBroadersLastOrdered());
        $this->assertSame([], $axis2->getAllBroadersLastOrdered());
    }

    public function testIsTransverseWith()
    {
        $axis1 = new Axis($this->cube);
        $axis1->setRef('test1');
        $axis1->setDirectNarrower($this->axis);
        $axis11 = new Axis($this->cube);
        $axis11->setRef('test11');
        $axis11->setDirectNarrower($axis1);
        $axis111 = new Axis($this->cube);
        $axis111->setRef('test111');
        $axis111->setDirectNarrower($axis11);
        $axis12 = new Axis($this->cube);
        $axis12->setRef('test12');
        $axis12->setDirectNarrower($axis1);
        $axis2 = new Axis($this->cube);
        $axis2->setRef('test2');
        $axis2->setDirectNarrower($this->axis);

        $this->assertFalse($this->axis->isTransverseWith($this->axis));
        $this->assertFalse($this->axis->isTransverseWith($axis1));
        $this->assertFalse($this->axis->isTransverseWith($axis11));
        $this->assertFalse($this->axis->isTransverseWith($axis111));
        $this->assertFalse($this->axis->isTransverseWith($axis12));
        $this->assertFalse($this->axis->isTransverseWith($axis2));

        $this->assertFalse($axis1->isTransverseWith($this->axis));
        $this->assertFalse($axis1->isTransverseWith($axis1));
        $this->assertFalse($axis1->isTransverseWith($axis11));
        $this->assertFalse($axis1->isTransverseWith($axis111));
        $this->assertFalse($axis1->isTransverseWith($axis12));
        $this->assertTrue($axis1->isTransverseWith($axis2));

        $this->assertFalse($axis11->isTransverseWith($this->axis));
        $this->assertFalse($axis11->isTransverseWith($axis1));
        $this->assertFalse($axis11->isTransverseWith($axis11));
        $this->assertFalse($axis11->isTransverseWith($axis111));
        $this->assertTrue($axis11->isTransverseWith($axis12));
        $this->assertTrue($axis11->isTransverseWith($axis2));

        $this->assertFalse($axis111->isTransverseWith($this->axis));
        $this->assertFalse($axis111->isTransverseWith($axis1));
        $this->assertFalse($axis111->isTransverseWith($axis11));
        $this->assertFalse($axis111->isTransverseWith($axis111));
        $this->assertTrue($axis111->isTransverseWith($axis12));
        $this->assertTrue($axis111->isTransverseWith($axis2));

        $this->assertFalse($axis12->isTransverseWith($this->axis));
        $this->assertFalse($axis12->isTransverseWith($axis1));
        $this->assertTrue($axis12->isTransverseWith($axis11));
        $this->assertTrue($axis12->isTransverseWith($axis111));
        $this->assertFalse($axis12->isTransverseWith($axis12));
        $this->assertTrue($axis12->isTransverseWith($axis2));

        $this->assertFalse($axis2->isTransverseWith($this->axis));
        $this->assertTrue($axis2->isTransverseWith($axis1));
        $this->assertTrue($axis2->isTransverseWith($axis11));
        $this->assertTrue($axis2->isTransverseWith($axis111));
        $this->assertTrue($axis2->isTransverseWith($axis12));
        $this->assertFalse($axis2->isTransverseWith($axis2));
    }
    
    public function testGetMemberByRef()
    {
        $member1 = new Member($this->axis);
        $member1->setRef('test1');
        $member2 = new Member($this->axis);
        $member2->setRef('test2');

        $this->assertSame($member1, $this->axis->getMemberByRef('test1'));
        $this->assertSame($member2, $this->axis->getMemberByRef('test2'));
    }

    /**
     * @expectedException        \Core_Exception_NotFound
     * @expectedExceptionMessage No "Member" matching "test".
     */
    public function testGetMemberByRefNotFound()
    {
        $member1 = new Member($this->axis);
        $member1->setRef('test1');
        $member2 = new Member($this->axis);
        $member2->setRef('test2');

        $this->axis->getMemberByRef('test');

        $this->fail('"Core_Exception_NotFound" expected.');
    }

    /**
     * @expectedException        \Core_Exception_TooMany
     * @expectedExceptionMessage Too many "Member" matching "test".
     */
    public function testGetMemberByRefTooMany()
    {
        $member1 = new Member($this->axis);
        $member1->setRef('test');
        $member2 = new Member($this->axis);
        $member2->setRef('test');

        $this->axis->getMemberByRef('test');

        $this->fail('"Core_Exception_TooMany" expected.');
    }
}
