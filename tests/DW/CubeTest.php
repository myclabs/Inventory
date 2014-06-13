<?php

namespace Tests\DW;

use DW\Domain\Axis;
use DW\Domain\Cube;
use Core\Test\TestCase;
use DW\Domain\Indicator;

/**
 * @covers \Classification\Domain\Axis
 */
class CubeTest extends TestCase
{
    /**
     * @var Cube
     */
    protected $cube;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->cube = new Cube();
    }
    
    
    public function testGetAxisByRef()
    {
        $axis1 = new Axis($this->cube);
        $axis1->setRef('test1');
        $axis2 = new Axis($this->cube);
        $axis2->setRef('test2');

        $this->assertSame($axis1, $this->cube->getAxisByRef('test1'));
        $this->assertSame($axis2, $this->cube->getAxisByRef('test2'));
    }

    /**
     * @expectedException        \Core_Exception_NotFound
     * @expectedExceptionMessage No "Axis" matching "test".
     */
    public function testGetAxisByRefNotFound()
    {
        $axis1 = new Axis($this->cube);
        $axis1->setRef('test1');
        $axis2 = new Axis($this->cube);
        $axis2->setRef('test2');

        $this->cube->getAxisByRef('test');

        $this->fail('"Core_Exception_NotFound" expected.');
    }

    /**
     * @expectedException        \Core_Exception_TooMany
     * @expectedExceptionMessage Too many "Axis" matching "test".
     */
    public function testGetAxisByRefTooMany()
    {
        $axis1 = new Axis($this->cube);
        $axis1->setRef('test');
        $axis2 = new Axis($this->cube);
        $axis2->setRef('test');

        $this->cube->getAxisByRef('test');

        $this->fail('"Core_Exception_TooMany" expected.');
    }

    public function testGetRootAxes()
    {
        $axis1 = new Axis($this->cube);
        $axis1->setRef('test1');
        $axis11 = new Axis($this->cube);
        $axis11->setRef('test11');
        $axis11->setDirectNarrower($axis1);
        $axis111 = new Axis($this->cube);
        $axis111->setRef('test111');
        $axis111->setDirectNarrower($axis11);
        $axis2 = new Axis($this->cube);
        $axis2->setRef('test2');
        
        $this->assertSame([$axis1, $axis2], $this->cube->getRootAxes());
    }

    public function testGetFirstOrderedAxes()
    {
        $axis1 = new Axis($this->cube);
        $axis1->setRef('test1');
        $axis11 = new Axis($this->cube);
        $axis11->setRef('test11');
        $axis11->setDirectNarrower($axis1);
        $axis111 = new Axis($this->cube);
        $axis111->setRef('test111');
        $axis111->setDirectNarrower($axis11);
        $axis12 = new Axis($this->cube);
        $axis12->setRef('test11');
        $axis12->setDirectNarrower($axis1);
        $axis2 = new Axis($this->cube);
        $axis2->setRef('test2');
        
        $this->assertSame([$axis1, $axis11, $axis111, $axis12, $axis2], $this->cube->getFirstOrderedAxes());
    }

    public function testGetLastOrderedAxes()
    {
        $axis1 = new Axis($this->cube);
        $axis1->setRef('test1');
        $axis11 = new Axis($this->cube);
        $axis11->setRef('test11');
        $axis11->setDirectNarrower($axis1);
        $axis111 = new Axis($this->cube);
        $axis111->setRef('test111');
        $axis111->setDirectNarrower($axis11);
        $axis12 = new Axis($this->cube);
        $axis12->setRef('test11');
        $axis12->setDirectNarrower($axis1);
        $axis2 = new Axis($this->cube);
        $axis2->setRef('test2');

        $this->assertSame([$axis111, $axis11, $axis12, $axis1, $axis2], $this->cube->getLastOrderedAxes());
    }

    public function testGetIndicatorByRef()
    {
        $indicator1 = new Indicator($this->cube);
        $indicator1->setRef('test1');
        $indicator2 = new Indicator($this->cube);
        $indicator2->setRef('test2');

        $this->assertSame($indicator1, $this->cube->getIndicatorByRef('test1'));
        $this->assertSame($indicator2, $this->cube->getIndicatorByRef('test2'));
    }

    /**
     * @expectedException        \Core_Exception_NotFound
     * @expectedExceptionMessage No "Indicator" matching "test".
     */
    public function testGetIndicatorByRefNotFound()
    {
        $indicator1 = new Indicator($this->cube);
        $indicator1->setRef('test1');
        $indicator2 = new Indicator($this->cube);
        $indicator2->setRef('test2');

        $this->cube->getIndicatorByRef('test');

        $this->fail('"Core_Exception_NotFound" expected.');
    }

    /**
     * @expectedException        \Core_Exception_TooMany
     * @expectedExceptionMessage Too many "Indicator" matching "test".
     */
    public function testGetIndicatorByRefTooMany()
    {
        $indicator1 = new Indicator($this->cube);
        $indicator1->setRef('test');
        $indicator2 = new Indicator($this->cube);
        $indicator2->setRef('test');

        $this->cube->getIndicatorByRef('test');

        $this->fail('"Core_Exception_TooMany" expected.');
    }

}
