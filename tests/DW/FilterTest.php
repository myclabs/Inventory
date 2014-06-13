<?php

namespace Tests\DW;

use DW\Domain\Axis;
use DW\Domain\Filter;
use DW\Domain\Report;
use DW\Domain\Cube;
use Core\Test\TestCase;

/**
 * @covers \Classification\Domain\Report
 */
class FilterTest extends TestCase
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
     * @var Axis
     */
    protected $axis;
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->cube = new Cube();

        $this->report = new Report($this->cube);

        $this->axis = new Axis($this->cube);

        $this->filter = new Filter($this->report, $this->axis);
    }

    /**
     * @expectedException        \Core_Exception_InvalidArgument
     * @expectedExceptionMessage The Report and the Axis must come from the same Cube.
     */
    public function testCreateFilterReportAndAxisFromDifferentCubes()
    {
        $newCube1 = new Cube();
        $report = new Report($newCube1);

        $newCube2 = new Cube();
        $axis = new axis($newCube2);

        $filter = new Filter($report, $axis);

        $this->fail('"Core_Exception_InvalidArgument" expected.');
    }

}
