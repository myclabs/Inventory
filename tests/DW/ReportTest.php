<?php

namespace Tests\DW;

use DW\Domain\Axis;
use DW\Domain\Filter;
use DW\Domain\Indicator;
use DW\Domain\Report;
use DW\Domain\Cube;
use Core\Test\TestCase;

/**
 * @covers \Classification\Domain\Report
 */
class ReportTest extends TestCase
{
    /**
     * @var Cube
     */
    protected $cube;
    /**
     * @var Report
     */
    protected $report;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->cube = new Cube();

        $this->report = new Report($this->cube);
    }

    /**
     * @expectedException        \Core_Exception_InvalidArgument
     * @expectedExceptionMessage The chart type must be a class constant "CHART_" .
     */
    public function testSetChartTypeInvalid()
    {
        $this->report->setChartType('test');

        $this->fail('"Core_Exception_InvalidArgument" expected.');
    }

    /**
     * @expectedException        \Core_Exception_InvalidArgument
     * @expectedExceptionMessage The sort type must be a class constant "SORT_" .
     */
    public function testSetSortTypeInvalid()
    {
        $this->report->setSortType('test');

        $this->fail('"Core_Exception_InvalidArgument" expected.');
    }

    /**
     * @expectedException        \Core_Exception_InvalidArgument
     * @expectedExceptionMessage A Report Numerator Indicator must comes from the same Cube as the Report.
     */
    public function testSetNumeratorIndicatorFromAnotherCube()
    {
        $newCube = new Cube();
        $indicator = new Indicator($newCube);

        $this->report->setNumeratorIndicator($indicator);

        $this->fail('"Core_Exception_InvalidArgument" expected.');
    }

    /**
     * @expectedException        \Core_Exception_InvalidArgument
     * @expectedExceptionMessage A Report Numerator Axis 1 must comes from the same Cube as the Report.
     */
    public function testSetNumeratorAxis1FromAnotherCube()
    {
        $newCube = new Cube();
        $axis = new Axis($newCube);

        $this->report->setNumeratorAxis1($axis);

        $this->fail('"Core_Exception_InvalidArgument" expected.');
    }

    /**
     * @expectedException        \Core_Exception_InvalidArgument
     * @expectedExceptionMessage A Report Numerator Axis 2 must comes from the same Cube as the Report.
     */
    public function testSetNumeratorAxis2FromAnotherCube()
    {
        $newCube = new Cube();
        $axis = new Axis($newCube);

        $this->report->setNumeratorAxis2($axis);

        $this->fail('"Core_Exception_InvalidArgument" expected.');
    }

    /**
     * @expectedException        \Core_Exception_InvalidArgument
     * @expectedExceptionMessage Axis 1 for numerator needs to be set first.
     */
    public function testSetNumeratorAxis2WithoutAxis1()
    {
        $axis = new Axis($this->cube);

        $this->report->setNumeratorAxis2($axis);

        $this->fail('"Core_Exception_InvalidArgument" expected.');
    }

    /**
     * @expectedException        \Core_Exception_InvalidArgument
     * @expectedExceptionMessage A Report Denominator Indicator must comes from the same Cube as the Report.
     */
    public function testSetDenominatorIndicatorFromAnotherCube()
    {
        $newCube = new Cube();
        $indicator = new Indicator($newCube);

        $this->report->setDenominatorIndicator($indicator);

        $this->fail('"Core_Exception_InvalidArgument" expected.');
    }

    /**
     * @expectedException        \Core_Exception_InvalidArgument
     * @expectedExceptionMessage A Report Denominator Axis 1 must comes from the same Cube as the Report.
     */
    public function testSetDenominatorAxis1FromAnotherCube()
    {
        $newCube = new Cube();
        $axis = new Axis($newCube);

        $this->report->setDenominatorAxis1($axis);

        $this->fail('"Core_Exception_InvalidArgument" expected.');
    }

    /**
     * @expectedException        \Core_Exception_InvalidArgument
     * @expectedExceptionMessage Axis 1 for numerator needs to be set first.
     */
    public function testSetDenominatorAxis1WithoutNumeratorAxis1()
    {
        $axis = new Axis($this->cube);

        $this->report->setDenominatorAxis1($axis);

        $this->fail('"Core_Exception_InvalidArgument" expected.');
    }

    /**
     * @expectedException        \Core_Exception_InvalidArgument
     * @expectedExceptionMessage A Report Denominator Axis 2 must comes from the same Cube as the Report.
     */
    public function testSetDenominatorAxis2FromAnotherCube()
    {
        $newCube = new Cube();
        $axis = new Axis($newCube);

        $this->report->setDenominatorAxis2($axis);

        $this->fail('"Core_Exception_InvalidArgument" expected.');
    }

    /**
     * @expectedException        \Core_Exception_InvalidArgument
     * @expectedExceptionMessage Axis 2 for numerator needs to be set first.
     */
    public function testSetDenominatorAxis2WithoutAxis1()
    {
        $axis = new Axis($this->cube);

        $this->report->setDenominatorAxis2($axis);

        $this->fail('"Core_Exception_InvalidArgument" expected.');
    }
    
    public function testGetFilterForAxis()
    {
        $axis1 = new Axis($this->cube);
        $axis1->setRef('test1');
        $axis2 = new Axis($this->cube);
        $axis2->setRef('test2');
        $axis3 = new Axis($this->cube);
        $axis3->setRef('test3');
        
        $filter1 = new Filter($this->report, $axis1);
        $filter2 = new Filter($this->report, $axis2);
        $filter3 = new Filter($this->report, $axis3);
        
        $this->assertSame($filter1, $this->report->getFilterForAxis($axis1));
        $this->assertSame($filter2, $this->report->getFilterForAxis($axis2));
        $this->assertSame($filter3, $this->report->getFilterForAxis($axis3));
    }

    /**
     * @expectedException        \Core_Exception_TooMany
     * @expectedExceptionMessage Too many Filters found for Axis "test".
     */
    public function testGetFilterForAxisTooMany()
    {
        $axis = new Axis($this->cube);
        $axis->setRef('test');
        
        $filter1 = new Filter($this->report, $axis);
        $filter2 = new Filter($this->report, $axis);
        $filter3 = new Filter($this->report, $axis);
        
        $this->report->getFilterForAxis($axis);

        $this->fail('"Core_Exception_TooMany" expected.');
    }

}
