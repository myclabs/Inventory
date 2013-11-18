<?php
use Core\Test\TestCase;

/**
 * Class Orga_Test_OrganizationTest
 * @author valentin.claras
 * @package    Orga
 * @subpackage Test
 */

/**
 * Test Organization Class.
 * @package    Orga
 * @subpackage Test
 */
class Orga_Test_OrganizationTest
{
    /**
     * Creation of the test suite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Orga_Test_OrganizationAxes');
        $suite->addTestSuite('Orga_Test_OrganizationGranularities');
        return $suite;
    }

}

class Orga_Test_OrganizationAxes extends TestCase
{
    /**
     * @var Orga_Model_Organization
     */
    protected $organization;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis1;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis11;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis111;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis12;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis121;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis122;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis123;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis2;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis21;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis3;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis31;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis311;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis312;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis32;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis33;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis331;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis332;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->organization = new Orga_Model_Organization();

        $this->axis1 = new Orga_Model_Axis($this->organization, 'ref_1');
        $this->axis1->setLabel ('Label 1');

        $this->axis11 = new Orga_Model_Axis($this->organization, 'ref_11', $this->axis1);
        $this->axis11->setLabel ('Label 11');

        $this->axis111 = new Orga_Model_Axis($this->organization, 'ref_111', $this->axis11);
        $this->axis111->setLabel ('Label 111');

        $this->axis12 = new Orga_Model_Axis($this->organization, 'ref_12', $this->axis1);
        $this->axis12->setLabel ('Label 12');

        $this->axis121 = new Orga_Model_Axis($this->organization, 'ref_121', $this->axis12);
        $this->axis121->setLabel ('Label 121');

        $this->axis122 = new Orga_Model_Axis($this->organization, 'ref_122', $this->axis12);
        $this->axis122->setLabel ('Label 122');

        $this->axis123 = new Orga_Model_Axis($this->organization, 'ref_123', $this->axis12);
        $this->axis123->setLabel ('Label 123');

        $this->axis2 = new Orga_Model_Axis($this->organization, 'ref_2');
        $this->axis2->setLabel ('Label 2');

        $this->axis21 = new Orga_Model_Axis($this->organization, 'ref_21', $this->axis2);
        $this->axis21->setLabel ('Label 21');

        $this->axis3 = new Orga_Model_Axis($this->organization, 'ref_3');
        $this->axis3->setLabel ('Label 3');

        $this->axis31 = new Orga_Model_Axis($this->organization, 'ref_31', $this->axis3);
        $this->axis31->setLabel ('Label 31');

        $this->axis311 = new Orga_Model_Axis($this->organization, 'ref_311', $this->axis31);
        $this->axis311->setLabel ('Label 311');

        $this->axis312 = new Orga_Model_Axis($this->organization, 'ref_312', $this->axis31);
        $this->axis312->setLabel ('Label 312');

        $this->axis32 = new Orga_Model_Axis($this->organization, 'ref_32', $this->axis3);
        $this->axis32->setLabel ('Label 32');

        $this->axis33 = new Orga_Model_Axis($this->organization, 'ref_33', $this->axis3);
        $this->axis33->setLabel ('Label 33');

        $this->axis331 = new Orga_Model_Axis($this->organization, 'ref_331', $this->axis33);
        $this->axis331->setLabel ('Label 331');

        $this->axis332 = new Orga_Model_Axis($this->organization, 'ref_332', $this->axis33);
        $this->axis332->setLabel ('Label 332');
    }

    public function testGetAxisByRef()
    {
        $axis1 = $this->organization->getAxisByRef('ref_1');
        $this->assertSame($this->axis1, $axis1);

        $axis312 = $this->organization->getAxisByRef('ref_312');
        $this->assertSame($this->axis312, $axis312);
    }

    /**
     * @expectedException Core_Exception_NotFound
     * @expectedExceptionMessage No Axis in Organization matching ref "RefNotFound".
     */
    public function testGetAxisByRefNotFound()
    {
        $axisNotFound = $this->organization->getAxisByRef('RefNotFound');
    }

    public function testGetAxes()
    {
        $axes = $this->organization->getAxes()->toArray();

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
        $rootAxes = $this->organization->getRootAxes();

        $this->assertCount(3, $rootAxes);

        $this->assertSame($this->axis1, $rootAxes[0]);
        $this->assertSame($this->axis2, $rootAxes[1]);
        $this->assertSame($this->axis3, $rootAxes[2]);
    }

    public function testGetFirstOrderedAxes()
    {
        $axes = $this->organization->getFirstOrderedAxes();

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
        $axes = $this->organization->getLastOrderedAxes();

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

}

class Orga_Test_OrganizationGranularities extends TestCase
{
    /**
     * @var Orga_Model_Organization
     */
    protected $organization;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis1;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis11;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis111;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis12;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis121;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis122;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis123;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis2;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis21;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis3;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis31;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis311;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis312;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis32;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis33;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis331;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis332;
    /**
     * @var Orga_Model_Granularity
     */
    protected $granularity0;
    /**
     * @var Orga_Model_Granularity
     */
    protected $granularity1;
    /**
     * @var Orga_Model_Granularity
     */
    protected $granularity2;
    /**
     * @var Orga_Model_Granularity
     */
    protected $granularity3;
    /**
     * @var Orga_Model_Granularity
     */
    protected $granularity4;
    /**
     * @var Orga_Model_Granularity
     */
    protected $granularity5;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->organization = new Orga_Model_Organization();

        $this->axis1 = new Orga_Model_Axis($this->organization, 'ref_1');
        $this->axis1->setLabel ('Label 1');

        $this->axis11 = new Orga_Model_Axis($this->organization, 'ref_11', $this->axis1);
        $this->axis11->setLabel ('Label 11');

        $this->axis111 = new Orga_Model_Axis($this->organization, 'ref_111', $this->axis11);
        $this->axis111->setLabel ('Label 111');

        $this->axis12 = new Orga_Model_Axis($this->organization, 'ref_12', $this->axis1);
        $this->axis12->setLabel ('Label 12');

        $this->axis121 = new Orga_Model_Axis($this->organization, 'ref_121', $this->axis12);
        $this->axis121->setLabel ('Label 121');

        $this->axis122 = new Orga_Model_Axis($this->organization, 'ref_122', $this->axis12);
        $this->axis122->setLabel ('Label 122');

        $this->axis123 = new Orga_Model_Axis($this->organization, 'ref_123', $this->axis12);
        $this->axis123->setLabel ('Label 123');

        $this->axis2 = new Orga_Model_Axis($this->organization, 'ref_2');
        $this->axis2->setLabel ('Label 2');

        $this->axis21 = new Orga_Model_Axis($this->organization, 'ref_21', $this->axis2);
        $this->axis21->setLabel ('Label 21');

        $this->axis3 = new Orga_Model_Axis($this->organization, 'ref_3');
        $this->axis3->setLabel ('Label 3');

        $this->axis31 = new Orga_Model_Axis($this->organization, 'ref_31', $this->axis3);
        $this->axis31->setLabel ('Label 31');

        $this->axis311 = new Orga_Model_Axis($this->organization, 'ref_311', $this->axis31);
        $this->axis311->setLabel ('Label 311');

        $this->axis312 = new Orga_Model_Axis($this->organization, 'ref_312', $this->axis31);
        $this->axis312->setLabel ('Label 312');

        $this->axis32 = new Orga_Model_Axis($this->organization, 'ref_32', $this->axis3);
        $this->axis32->setLabel ('Label 32');

        $this->axis33 = new Orga_Model_Axis($this->organization, 'ref_33', $this->axis3);
        $this->axis33->setLabel ('Label 33');

        $this->axis331 = new Orga_Model_Axis($this->organization, 'ref_331', $this->axis33);
        $this->axis331->setLabel ('Label 331');

        $this->axis332 = new Orga_Model_Axis($this->organization, 'ref_332', $this->axis33);
        $this->axis332->setLabel ('Label 332');

        $this->granularity0 = new Orga_Model_Granularity($this->organization);
        $this->granularity1 = new Orga_Model_Granularity($this->organization, [$this->axis11, $this->axis122, $this->axis311]);
        $this->granularity2 = new Orga_Model_Granularity($this->organization, [$this->axis1, $this->axis31]);
        $this->granularity3 = new Orga_Model_Granularity($this->organization, [$this->axis2]);
        $this->granularity4 = new Orga_Model_Granularity($this->organization, [$this->axis1, $this->axis3]);
        $this->granularity5 = new Orga_Model_Granularity($this->organization, [$this->axis12, $this->axis21, $this->axis33]);
    }

    public function testGetOrganizationByRef()
    {
        $granularity1 = $this->organization->getGranularityByRef('global');
        $this->assertSame($this->granularity0, $granularity1);

        $granularity5 = $this->organization->getGranularityByRef('ref_12|ref_21|ref_33');
        $this->assertSame($this->granularity5, $granularity5);
    }

    /**
     * @expectedException Core_Exception_NotFound
     * @expectedExceptionMessage No Granularity in Organization matching ref "RefNotFound".
     */
    public function testGetGranularityByRefNotFound()
    {
        $granularityNotFound = $this->organization->getGranularityByRef('RefNotFound');
    }

    public function testGetGranularities()
    {
        $granularities = $this->organization->getGranularities()->toArray();

        $this->assertCount(6, $granularities);

        $this->assertSame($this->granularity0, $granularities[0]);
        $this->assertSame($this->granularity1, $granularities[2]);
        $this->assertSame($this->granularity2, $granularities[3]);
        $this->assertSame($this->granularity3, $granularities[1]);
        $this->assertSame($this->granularity4, $granularities[4]);
        $this->assertSame($this->granularity5, $granularities[5]);
    }

}