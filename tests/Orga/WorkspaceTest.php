<?php

use Account\Domain\Account;
use Core\Test\TestCase;
use Orga\Domain\Axis;
use Orga\Domain\Granularity;
use Orga\Domain\Workspace;

/**
 * Test Workspace Class.
 */
class Orga_Test_WorkspaceTest
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Orga_Test_WorkspaceAxes');
        $suite->addTestSuite('Orga_Test_WorkspaceGranularities');
        return $suite;
    }
}

class Orga_Test_WorkspaceAxes extends TestCase
{
    /**
     * @var Workspace
     */
    protected $workspace;
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
    protected $axis121;
    /**
     * @var Axis
     */
    protected $axis122;
    /**
     * @var Axis
     */
    protected $axis123;
    /**
     * @var Axis
     */
    protected $axis2;
    /**
     * @var Axis
     */
    protected $axis21;
    /**
     * @var Axis
     */
    protected $axis3;
    /**
     * @var Axis
     */
    protected $axis31;
    /**
     * @var Axis
     */
    protected $axis311;
    /**
     * @var Axis
     */
    protected $axis312;
    /**
     * @var Axis
     */
    protected $axis32;
    /**
     * @var Axis
     */
    protected $axis33;
    /**
     * @var Axis
     */
    protected $axis331;
    /**
     * @var Axis
     */
    protected $axis332;

    public function setUp()
    {
        parent::setUp();

        $this->workspace = new Workspace(
            $this->getMockBuilder(Account::class)->disableOriginalConstructor()->getMock()
        );

        $this->axis1 = new Axis($this->workspace, 'ref_1');
        $this->axis11 = new Axis($this->workspace, 'ref_11', $this->axis1);
        $this->axis111 = new Axis($this->workspace, 'ref_111', $this->axis11);
        $this->axis12 = new Axis($this->workspace, 'ref_12', $this->axis1);
        $this->axis121 = new Axis($this->workspace, 'ref_121', $this->axis12);
        $this->axis122 = new Axis($this->workspace, 'ref_122', $this->axis12);
        $this->axis123 = new Axis($this->workspace, 'ref_123', $this->axis12);
        $this->axis2 = new Axis($this->workspace, 'ref_2');
        $this->axis21 = new Axis($this->workspace, 'ref_21', $this->axis2);
        $this->axis3 = new Axis($this->workspace, 'ref_3');
        $this->axis31 = new Axis($this->workspace, 'ref_31', $this->axis3);
        $this->axis311 = new Axis($this->workspace, 'ref_311', $this->axis31);
        $this->axis312 = new Axis($this->workspace, 'ref_312', $this->axis31);
        $this->axis32 = new Axis($this->workspace, 'ref_32', $this->axis3);
        $this->axis33 = new Axis($this->workspace, 'ref_33', $this->axis3);
        $this->axis331 = new Axis($this->workspace, 'ref_331', $this->axis33);
        $this->axis332 = new Axis($this->workspace, 'ref_332', $this->axis33);
    }

    public function testGetAxisByRef()
    {
        $axis1 = $this->workspace->getAxisByRef('ref_1');
        $this->assertSame($this->axis1, $axis1);

        $axis312 = $this->workspace->getAxisByRef('ref_312');
        $this->assertSame($this->axis312, $axis312);
    }

    /**
     * @expectedException Core_Exception_NotFound
     * @expectedExceptionMessage No Axis in Workspace matching ref "RefNotFound".
     */
    public function testGetAxisByRefNotFound()
    {
        $this->workspace->getAxisByRef('RefNotFound');
    }

    public function testGetAxes()
    {
        $axes = $this->workspace->getAxes()->toArray();

        $this->assertCount(17, $axes);

        $this->assertSame($this->axis1, $axes[0]);
        $this->assertSame($this->axis11, $axes[1]);
        $this->assertSame($this->axis111, $axes[2]);
        $this->assertSame($this->axis12, $axes[3]);
        $this->assertSame($this->axis121, $axes[4]);
        $this->assertSame($this->axis122, $axes[5]);
        $this->assertSame($this->axis123, $axes[6]);
        $this->assertSame($this->axis2, $axes[7]);
        $this->assertSame($this->axis21, $axes[8]);
        $this->assertSame($this->axis3, $axes[9]);
        $this->assertSame($this->axis31, $axes[10]);
        $this->assertSame($this->axis311, $axes[11]);
        $this->assertSame($this->axis312, $axes[12]);
        $this->assertSame($this->axis32, $axes[13]);
        $this->assertSame($this->axis33, $axes[14]);
        $this->assertSame($this->axis331, $axes[15]);
        $this->assertSame($this->axis332, $axes[16]);
    }

    public function testGetRootAxes()
    {
        $rootAxes = $this->workspace->getRootAxes();

        $this->assertCount(3, $rootAxes);

        $this->assertSame($this->axis1, $rootAxes[0]);
        $this->assertSame($this->axis2, $rootAxes[1]);
        $this->assertSame($this->axis3, $rootAxes[2]);
    }

    public function testGetFirstOrderedAxes()
    {
        $axes = $this->workspace->getFirstOrderedAxes();

        $this->assertCount(17, $axes);

        $this->assertSame($this->axis1, $axes[0]);
        $this->assertSame($this->axis11, $axes[1]);
        $this->assertSame($this->axis111, $axes[2]);
        $this->assertSame($this->axis12, $axes[3]);
        $this->assertSame($this->axis121, $axes[4]);
        $this->assertSame($this->axis122, $axes[5]);
        $this->assertSame($this->axis123, $axes[6]);
        $this->assertSame($this->axis2, $axes[7]);
        $this->assertSame($this->axis21, $axes[8]);
        $this->assertSame($this->axis3, $axes[9]);
        $this->assertSame($this->axis31, $axes[10]);
        $this->assertSame($this->axis311, $axes[11]);
        $this->assertSame($this->axis312, $axes[12]);
        $this->assertSame($this->axis32, $axes[13]);
        $this->assertSame($this->axis33, $axes[14]);
        $this->assertSame($this->axis331, $axes[15]);
        $this->assertSame($this->axis332, $axes[16]);
    }

    public function testGetLastOrderedAxes()
    {
        $axes = $this->workspace->getLastOrderedAxes();

        $this->assertCount(17, $axes);

        $this->assertSame($this->axis111, $axes[0]);
        $this->assertSame($this->axis11, $axes[1]);
        $this->assertSame($this->axis121, $axes[2]);
        $this->assertSame($this->axis122, $axes[3]);
        $this->assertSame($this->axis123, $axes[4]);
        $this->assertSame($this->axis12, $axes[5]);
        $this->assertSame($this->axis1, $axes[6]);
        $this->assertSame($this->axis21, $axes[7]);
        $this->assertSame($this->axis2, $axes[8]);
        $this->assertSame($this->axis311, $axes[9]);
        $this->assertSame($this->axis312, $axes[10]);
        $this->assertSame($this->axis31, $axes[11]);
        $this->assertSame($this->axis32, $axes[12]);
        $this->assertSame($this->axis331, $axes[13]);
        $this->assertSame($this->axis332, $axes[14]);
        $this->assertSame($this->axis33, $axes[15]);
        $this->assertSame($this->axis3, $axes[16]);
    }

    public function testRemoveAxis()
    {
        $this->workspace->removeAxis($this->axis111);

        $axes = $this->workspace->getFirstOrderedAxes();

        $this->assertCount(16, $axes);

        $this->assertSame($this->axis1, $axes[0]);
        $this->assertSame($this->axis11, $axes[1]);
        $this->assertSame($this->axis12, $axes[2]);
        $this->assertSame($this->axis121, $axes[3]);
        $this->assertSame($this->axis122, $axes[4]);
        $this->assertSame($this->axis123, $axes[5]);
        $this->assertSame($this->axis2, $axes[6]);
        $this->assertSame($this->axis21, $axes[7]);
        $this->assertSame($this->axis3, $axes[8]);
        $this->assertSame($this->axis31, $axes[9]);
        $this->assertSame($this->axis311, $axes[10]);
        $this->assertSame($this->axis312, $axes[11]);
        $this->assertSame($this->axis32, $axes[12]);
        $this->assertSame($this->axis33, $axes[13]);
        $this->assertSame($this->axis331, $axes[14]);
        $this->assertSame($this->axis332, $axes[15]);

        $this->workspace->removeAxis($this->axis31);

        $axes = $this->workspace->getFirstOrderedAxes();

        $this->assertCount(15, $axes);

        $this->assertSame($this->axis1, $axes[0]);
        $this->assertSame($this->axis11, $axes[1]);
        $this->assertSame($this->axis12, $axes[2]);
        $this->assertSame($this->axis121, $axes[3]);
        $this->assertSame($this->axis122, $axes[4]);
        $this->assertSame($this->axis123, $axes[5]);
        $this->assertSame($this->axis2, $axes[6]);
        $this->assertSame($this->axis21, $axes[7]);
        $this->assertSame($this->axis3, $axes[8]);
        $this->assertSame($this->axis311, $axes[9]);
        $this->assertSame($this->axis312, $axes[10]);
        $this->assertSame($this->axis32, $axes[11]);
        $this->assertSame($this->axis33, $axes[12]);
        $this->assertSame($this->axis331, $axes[13]);
        $this->assertSame($this->axis332, $axes[14]);
    }
}

class Orga_Test_WorkspaceGranularities extends TestCase
{
    /**
     * @var Workspace
     */
    protected $workspace;
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
    protected $axis121;
    /**
     * @var Axis
     */
    protected $axis122;
    /**
     * @var Axis
     */
    protected $axis123;
    /**
     * @var Axis
     */
    protected $axis2;
    /**
     * @var Axis
     */
    protected $axis21;
    /**
     * @var Axis
     */
    protected $axis3;
    /**
     * @var Axis
     */
    protected $axis31;
    /**
     * @var Axis
     */
    protected $axis311;
    /**
     * @var Axis
     */
    protected $axis312;
    /**
     * @var Axis
     */
    protected $axis32;
    /**
     * @var Axis
     */
    protected $axis33;
    /**
     * @var Axis
     */
    protected $axis331;
    /**
     * @var Axis
     */
    protected $axis332;
    /**
     * @var Granularity
     */
    protected $granularity0;
    /**
     * @var Granularity
     */
    protected $granularity1;
    /**
     * @var Granularity
     */
    protected $granularity2;
    /**
     * @var Granularity
     */
    protected $granularity3;
    /**
     * @var Granularity
     */
    protected $granularity4;
    /**
     * @var Granularity
     */
    protected $granularity5;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->workspace = new Workspace(
            $this->getMockBuilder(Account::class)->disableOriginalConstructor()->getMock()
        );

        $this->axis1 = new Axis($this->workspace, 'ref_1');
        $this->axis11 = new Axis($this->workspace, 'ref_11', $this->axis1);
        $this->axis111 = new Axis($this->workspace, 'ref_111', $this->axis11);
        $this->axis12 = new Axis($this->workspace, 'ref_12', $this->axis1);
        $this->axis121 = new Axis($this->workspace, 'ref_121', $this->axis12);
        $this->axis122 = new Axis($this->workspace, 'ref_122', $this->axis12);
        $this->axis123 = new Axis($this->workspace, 'ref_123', $this->axis12);
        $this->axis2 = new Axis($this->workspace, 'ref_2');
        $this->axis21 = new Axis($this->workspace, 'ref_21', $this->axis2);
        $this->axis3 = new Axis($this->workspace, 'ref_3');
        $this->axis31 = new Axis($this->workspace, 'ref_31', $this->axis3);
        $this->axis311 = new Axis($this->workspace, 'ref_311', $this->axis31);
        $this->axis312 = new Axis($this->workspace, 'ref_312', $this->axis31);
        $this->axis32 = new Axis($this->workspace, 'ref_32', $this->axis3);
        $this->axis33 = new Axis($this->workspace, 'ref_33', $this->axis3);
        $this->axis331 = new Axis($this->workspace, 'ref_331', $this->axis33);
        $this->axis332 = new Axis($this->workspace, 'ref_332', $this->axis33);

        $this->granularity0 = new Granularity($this->workspace);
        $this->granularity1 = new Granularity($this->workspace, [$this->axis11, $this->axis122, $this->axis311]);
        $this->granularity2 = new Granularity($this->workspace, [$this->axis1, $this->axis31]);
        $this->granularity3 = new Granularity($this->workspace, [$this->axis2]);
        $this->granularity4 = new Granularity($this->workspace, [$this->axis1, $this->axis3]);
        $this->granularity5 = new Granularity($this->workspace, [$this->axis12, $this->axis21, $this->axis33]);
    }

    public function testGetGranularityByRef()
    {
        $granularity1 = $this->workspace->getGranularityByRef('global');
        $this->assertSame($this->granularity0, $granularity1);

        $granularity5 = $this->workspace->getGranularityByRef('ref_12|ref_21|ref_33');
        $this->assertSame($this->granularity5, $granularity5);
    }

    /**
     * @expectedException Core_Exception_NotFound
     * @expectedExceptionMessage No Granularity in Workspace matching ref "RefNotFound".
     */
    public function testGetGranularityByRefNotFound()
    {
        $this->workspace->getGranularityByRef('RefNotFound');
    }

    public function testGetGranularities()
    {
        $granularities = $this->workspace->getOrderedGranularities()->toArray();

        $this->assertCount(6, $granularities);

        $this->assertSame($this->granularity0, $granularities[0]);
        $this->assertSame($this->granularity1, $granularities[3]);
        $this->assertSame($this->granularity2, $granularities[4]);
        $this->assertSame($this->granularity3, $granularities[1]);
        $this->assertSame($this->granularity4, $granularities[5]);
        $this->assertSame($this->granularity5, $granularities[2]);
    }

    public function testRemoveGranularity()
    {
        $this->workspace->removeGranularity($this->granularity0);

        $granularities = $this->workspace->getOrderedGranularities()->toArray();

        $this->assertCount(5, $granularities);

        $this->assertSame($this->granularity1, $granularities[2]);
        $this->assertSame($this->granularity2, $granularities[3]);
        $this->assertSame($this->granularity3, $granularities[0]);
        $this->assertSame($this->granularity4, $granularities[4]);
        $this->assertSame($this->granularity5, $granularities[1]);

        $this->workspace->removeAxis($this->axis1);

        $granularities = $this->workspace->getOrderedGranularities()->toArray();

        $this->assertCount(3, $granularities);

        $this->assertSame($this->granularity1, $granularities[2]);
        $this->assertSame($this->granularity3, $granularities[0]);
        $this->assertSame($this->granularity5, $granularities[1]);
    }
}
