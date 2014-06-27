<?php

namespace Tests\DW;

use DW\Domain\Axis;
use DW\Domain\Member;
use DW\Domain\Cube;
use Core\Test\TestCase;
use DW\Domain\Result;

/**
 * @covers \Classification\Domain\Member
 */
class ResultTest extends TestCase
{
    /**
     * @var Cube
     */
    protected $cube;
    /**
     * @var Axis
     */
    protected $axis1;
    /**
     * @var Axis
     */
    protected $axis11;
    /**
     * @var Axis
     */
    protected $axis111;
    /**
     * @var Axis
     */
    protected $axis12;
    /**
     * @var Axis
     */
    protected $axis2;
    /**
     * @var Member
     */
    protected $member;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->cube = new Cube();

        $this->axis1 = new Axis($this->cube);
        $this->axis1->setRef('test1');

        $this->axis11 = new Axis($this->cube);
        $this->axis11->setRef('test11');
        $this->axis11->setDirectNarrower($this->axis1);

        $this->axis111 = new Axis($this->cube);
        $this->axis111->setRef('test111');
        $this->axis111->setDirectNarrower($this->axis11);

        $this->axis12 = new Axis($this->cube);
        $this->axis12->setRef('test12');
        $this->axis12->setDirectNarrower($this->axis1);

        $this->axis2 = new Axis($this->cube);
        $this->axis2->setRef('test2');

        $this->member = new Member($this->axis1);
        $this->member->setRef('test');
    }

    public function testGetAllParents()
    {
        $member111A = new Member($this->axis111);
        $member111A->setRef('test111A');
        $member111B = new Member($this->axis111);
        $member111B->setRef('test111B');
        
        $member11A = new Member($this->axis11);
        $member11A->setRef('test11A');
        $member11A->setDirectParentForAxis($member111A);
        $member11B = new Member($this->axis11);
        $member11B->setRef('test11B');
        $member11B->setDirectParentForAxis($member111B);
        
        $member12A = new Member($this->axis12);
        $member12A->setRef('test12A');
        $member12B = new Member($this->axis12);
        $member12B->setRef('test12B');
        
        $member1A = new Member($this->axis1);
        $member1A->setRef('test1A');
        $member1A->setDirectParentForAxis($member11A);
        $member1A->setDirectParentForAxis($member12A);
        $member1B = new Member($this->axis1);
        $member1B->setRef('test1B');
        $member1B->setDirectParentForAxis($member11B);
        $member1B->setDirectParentForAxis($member12B);
        
        $member2A = new Member($this->axis2);
        $member2A->setRef('test2A');
        $member2B = new Member($this->axis2);
        $member2B->setRef('test2B');
        
        $this->assertSame([$member11A, $member111A, $member12A], $member1A->getAllParents());
        $this->assertSame([$member11B, $member111B, $member12B], $member1B->getAllParents());
        $this->assertSame([$member111A], $member11A->getAllParents());
        $this->assertSame([$member111B], $member11B->getAllParents());
        $this->assertSame([], $member111A->getAllParents());
        $this->assertSame([], $member111B->getAllParents());
        $this->assertSame([], $member12A->getAllParents());
        $this->assertSame([], $member12B->getAllParents());
        $this->assertSame([], $member2A->getAllParents());
        $this->assertSame([], $member2B->getAllParents());
    }

    public function testGetParentForAxis()
    {
        $member111A = new Member($this->axis111);
        $member111A->setRef('test111A');
        $member111B = new Member($this->axis111);
        $member111B->setRef('test111B');

        $member11A = new Member($this->axis11);
        $member11A->setRef('test11A');
        $member11A->setDirectParentForAxis($member111A);
        $member11B = new Member($this->axis11);
        $member11B->setRef('test11B');
        $member11B->setDirectParentForAxis($member111B);

        $member12A = new Member($this->axis12);
        $member12A->setRef('test12A');
        $member12B = new Member($this->axis12);
        $member12B->setRef('test12B');

        $member1A = new Member($this->axis1);
        $member1A->setRef('test1A');
        $member1A->setDirectParentForAxis($member11A);
        $member1A->setDirectParentForAxis($member12A);
        $member1B = new Member($this->axis1);
        $member1B->setRef('test1B');
        $member1B->setDirectParentForAxis($member11B);
        $member1B->setDirectParentForAxis($member12B);

        $member2A = new Member($this->axis2);
        $member2A->setRef('test2A');
        $member2B = new Member($this->axis2);
        $member2B->setRef('test2B');

        $this->assertSame($member11A, $member1A->getParentForAxis($this->axis11));
        $this->assertSame($member111A, $member1A->getParentForAxis($this->axis111));
        $this->assertSame($member12A, $member1A->getParentForAxis($this->axis12));
        $this->assertSame($member11B, $member1B->getParentForAxis($this->axis11));
        $this->assertSame($member111B, $member1B->getParentForAxis($this->axis111));
        $this->assertSame($member12B, $member1B->getParentForAxis($this->axis12));
        $this->assertSame($member111A, $member11A->getParentForAxis($this->axis111));
        $this->assertSame($member111B, $member11B->getParentForAxis($this->axis111));
    }

    /**
     * @expectedException        \Core_Exception_NotFound
     * @expectedExceptionMessage There is no parent member for the given axis.
     */
    public function testGetParentForAxisNotFound()
    {
        $this->member->getParentForAxis($this->axis111);

        $this->fail('"Core_Exception_InvalidArgument" expected.');
    }

    /**
     * @expectedException        \Core_Exception_NotFound
     * @expectedExceptionMessage There is no parent member for the given axis.
     */
    public function testGetParentForAxisNotBroader()
    {
        $this->member->getParentForAxis($this->axis2);

        $this->fail('"Core_Exception_InvalidArgument" expected.');
    }

    public function testGetAllChildren()
    {
        $member111A = new Member($this->axis111);
        $member111A->setRef('test111A');
        $member111B = new Member($this->axis111);
        $member111B->setRef('test111B');

        $member11A = new Member($this->axis11);
        $member11A->setRef('test11A');
        $member11A->setDirectParentForAxis($member111A);
        $member11B = new Member($this->axis11);
        $member11B->setRef('test11B');
        $member11B->setDirectParentForAxis($member111B);

        $member12A = new Member($this->axis12);
        $member12A->setRef('test12A');
        $member12B = new Member($this->axis12);
        $member12B->setRef('test12B');

        $member1A = new Member($this->axis1);
        $member1A->setRef('test1A');
        $member1A->setDirectParentForAxis($member11A);
        $member1A->setDirectParentForAxis($member12A);
        $member1B = new Member($this->axis1);
        $member1B->setRef('test1B');
        $member1B->setDirectParentForAxis($member11B);
        $member1B->setDirectParentForAxis($member12B);

        $member2A = new Member($this->axis2);
        $member2A->setRef('test2A');
        $member2B = new Member($this->axis2);
        $member2B->setRef('test2B');
        
        $this->assertSame([], $member1A->getAllChildren());
        $this->assertSame([], $member1B->getAllChildren());
        $this->assertSame([$member1A], $member11A->getAllChildren());
        $this->assertSame([$member1B], $member11B->getAllChildren());
        $this->assertSame([$member11A, $member1A], $member111A->getAllChildren());
        $this->assertSame([$member11B, $member1B], $member111B->getAllChildren());
        $this->assertSame([$member1A], $member12A->getAllChildren());
        $this->assertSame([$member1B], $member12B->getAllChildren());
        $this->assertSame([], $member2A->getAllChildren());
        $this->assertSame([], $member2B->getAllChildren());
    }

    public function testGetChildrenForAxis()
    {
        $member111A = new Member($this->axis111);
        $member111A->setRef('test111A');
        $member111B = new Member($this->axis111);
        $member111B->setRef('test111B');

        $member11A = new Member($this->axis11);
        $member11A->setRef('test11A');
        $member11A->setDirectParentForAxis($member111A);
        $member11B = new Member($this->axis11);
        $member11B->setRef('test11B');
        $member11B->setDirectParentForAxis($member111B);

        $member12A = new Member($this->axis12);
        $member12A->setRef('test12A');
        $member12B = new Member($this->axis12);
        $member12B->setRef('test12B');

        $member1A = new Member($this->axis1);
        $member1A->setRef('test1A');
        $member1A->setDirectParentForAxis($member11A);
        $member1A->setDirectParentForAxis($member12A);
        $member1B = new Member($this->axis1);
        $member1B->setRef('test1B');
        $member1B->setDirectParentForAxis($member11B);
        $member1B->setDirectParentForAxis($member12B);

        $member2A = new Member($this->axis2);
        $member2A->setRef('test2A');
        $member2B = new Member($this->axis2);
        $member2B->setRef('test2B');

        $this->assertSame([$member1A], $member11A->getChildrenForAxis($this->axis1));
        $this->assertSame([$member1B], $member11B->getChildrenForAxis($this->axis1));
        $this->assertSame([$member11A], $member111A->getChildrenForAxis($this->axis11));
        $this->assertSame([$member1A], $member111A->getChildrenForAxis($this->axis1));
        $this->assertSame([$member11B], $member111B->getChildrenForAxis($this->axis11));
        $this->assertSame([$member1B], $member111B->getChildrenForAxis($this->axis1));
        $this->assertSame([$member1A], $member12A->getChildrenForAxis($this->axis1));
        $this->assertSame([$member1B], $member12B->getChildrenForAxis($this->axis1));
    }

}
