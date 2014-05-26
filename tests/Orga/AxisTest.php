<?php
use Account\Domain\Account;
use Core\Test\TestCase;

/**
 * Class Orga_Test_AxisTest
 * @author valentin.claras
 * @package    Orga
 * @subpackage Test
 */

/**
 * Test Axis class.
 * @package    Orga
 * @subpackage Test
 */
class Orga_Test_AxisTest
{
    /**
     * Creation of the test suite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Orga_Test_AxisAttributes');
        $suite->addTestSuite('Orga_Test_AxisTag');
        $suite->addTestSuite('Orga_Test_AxisHierarchy');
        $suite->addTestSuite('Orga_Test_AxisMembers');
        return $suite;
    }

}

class Orga_Test_AxisAttributes extends TestCase
{
    /**
     * @var Orga_Model_Organization
     */
    protected $organization;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->organization = new Orga_Model_Organization($this->getMockBuilder(Account::class)->disableOriginalConstructor()->getMock());

        $this->axis = new Orga_Model_Axis($this->organization, 'ref');
        $this->axis->getLabel()->set('Label', 'fr');
    }

    public function testSetGetRef()
    {
        $newAxis = new Orga_Model_Axis($this->organization, 'new');

        $this->assertSame('ref', $this->axis->getRef());
        $this->assertSame('new', $newAxis->getRef());
    }

    /**
     * @expectedException Core_Exception_InvalidArgument
     * @expectedExceptionMessage An Axis ref cannot be "global".
     */
    public function testSetRefGlobal()
    {
        $newAxis = new Orga_Model_Axis($this->organization, 'global');
    }

    /**
     * @expectedException Core_Exception_Duplicate
     * @expectedExceptionMessage An Axis with ref "ref" already exists in the Organization
     */
    public function testSetRefDuplicate()
    {
        $newAxis = new Orga_Model_Axis($this->organization, 'ref');
    }

}

class Orga_Test_AxisTag extends TestCase
{
    /**
     * @var Orga_Model_Organization
     */
    protected $organization;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->organization = new Orga_Model_Organization($this->getMockBuilder(Account::class)->disableOriginalConstructor()->getMock());

        $this->axis = new Orga_Model_Axis($this->organization, 'ref');
    }

    public function testGetAxisTag()
    {
        $newAxis = new Orga_Model_Axis($this->organization, 'new');

        $this->assertSame('1-ref',$this->axis->getAxisTag());
        $this->assertSame('2-new',$newAxis->getAxisTag());
    }

    public function testGetNarrowerTag()
    {
        $newAxis = new Orga_Model_Axis($this->organization, 'new');

        $this->assertSame('/1-ref/', $this->axis->getNarrowerTag());
        $this->assertSame('/2-new/', $newAxis->getNarrowerTag());

        $axisA = new Orga_Model_Axis($this->organization, 'ref_a', $this->axis);

        $axisB = new Orga_Model_Axis($this->organization, 'ref_b', $this->axis);

        $this->assertSame('/1-ref/', $this->axis->getNarrowerTag());
        $this->assertSame('/2-new/', $newAxis->getNarrowerTag());
        $this->assertSame('/1-ref/1-ref_a/', $axisA->getNarrowerTag());
        $this->assertSame('/1-ref/2-ref_b/', $axisB->getNarrowerTag());

        $axisB1 = new Orga_Model_Axis($this->organization, 'ref_b1', $axisB);

        $axis0 = new Orga_Model_Axis($this->organization, 'ref_0');

        $this->assertSame('/1-ref/', $this->axis->getNarrowerTag());
        $this->assertSame('/2-new/', $newAxis->getNarrowerTag());
        $this->assertSame('/1-ref/1-ref_a/', $axisA->getNarrowerTag());
        $this->assertSame('/1-ref/2-ref_b/', $axisB->getNarrowerTag());
        $this->assertSame('/1-ref/2-ref_b/1-ref_b1/', $axisB1->getNarrowerTag());
        $this->assertSame('/3-ref_0/', $axis0->getNarrowerTag());
    }

    public function testGetBroaderTag()
    {
        $newAxis = new Orga_Model_Axis($this->organization, 'new');

        $this->assertSame('/1-ref/', $this->axis->getBroaderTag());
        $this->assertSame('/2-new/', $newAxis->getBroaderTag());

        $axisA = new Orga_Model_Axis($this->organization, 'ref_a', $this->axis);

        $axisB = new Orga_Model_Axis($this->organization, 'ref_b', $this->axis);

        $this->assertSame('/1-ref_a/1-ref/&/2-ref_b/1-ref/', $this->axis->getBroaderTag());
        $this->assertSame('/2-new/', $newAxis->getBroaderTag());
        $this->assertSame('/1-ref_a/', $axisA->getBroaderTag());
        $this->assertSame('/2-ref_b/', $axisB->getBroaderTag());

        $axisB1 = new Orga_Model_Axis($this->organization, 'ref_b1', $axisB);

        $axis0 = new Orga_Model_Axis($this->organization, 'ref_0');

        $this->assertSame('/1-ref_a/1-ref/&/1-ref_b1/2-ref_b/1-ref/', $this->axis->getBroaderTag());
        $this->assertSame('/2-new/', $newAxis->getBroaderTag());
        $this->assertSame('/1-ref_a/', $axisA->getBroaderTag());
        $this->assertSame('/1-ref_b1/2-ref_b/', $axisB->getBroaderTag());
        $this->assertSame('/1-ref_b1/', $axisB1->getBroaderTag());
        $this->assertSame('/3-ref_0/', $axis0->getBroaderTag());
    }

}

class Orga_Test_AxisHierarchy extends TestCase
{
    /**
     * @var Orga_Model_Organization
     */
    protected $organization;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis;
    /**
     * @var Orga_Model_Axis
     */
    protected $newAxis;
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
    protected $axis112;
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
    protected $axis2;
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
    protected $axis32;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis321;

    public function setUp()
    {
        parent::setUp();

        $this->organization = new Orga_Model_Organization(
            $this->getMockBuilder(Account::class)->disableOriginalConstructor()->getMock()
        );

        $this->axis = new Orga_Model_Axis($this->organization, 'ref');
        $this->axis->getLabel()->set('Label', 'fr');

        $this->newAxis = new Orga_Model_Axis($this->organization, 'new');
        $this->newAxis->getLabel()->set('New', 'fr');

        $this->axis1 = new Orga_Model_Axis($this->organization, 'ref_1');
        $this->axis1->getLabel()->set('Label 1', 'fr');

        $this->axis11 = new Orga_Model_Axis($this->organization, 'ref_11', $this->axis1);
        $this->axis11->getLabel()->set('Label 11', 'fr');

        $this->axis111 = new Orga_Model_Axis($this->organization, 'ref_111', $this->axis11);
        $this->axis111->getLabel()->set('Label 111', 'fr');

        $this->axis112 = new Orga_Model_Axis($this->organization, 'ref_112', $this->axis11);
        $this->axis112->getLabel()->set('Label 112', 'fr');

        $this->axis12 = new Orga_Model_Axis($this->organization, 'ref_12', $this->axis1);
        $this->axis12->getLabel()->set('Label 12', 'fr');

        $this->axis121 = new Orga_Model_Axis($this->organization, 'ref_121', $this->axis12);
        $this->axis121->getLabel()->set('Label 121', 'fr');

        $this->axis2 = new Orga_Model_Axis($this->organization, 'ref_2');
        $this->axis2->getLabel()->set('Label 2', 'fr');

        $this->axis3 = new Orga_Model_Axis($this->organization, 'ref_3');
        $this->axis3->getLabel()->set('Label 3', 'fr');

        $this->axis31 = new Orga_Model_Axis($this->organization, 'ref_31', $this->axis3);
        $this->axis31->getLabel()->set('Label 31', 'fr');

        $this->axis32 = new Orga_Model_Axis($this->organization, 'ref_32', $this->axis3);
        $this->axis32->getLabel()->set('Label 32', 'fr');

        $this->axis321 = new Orga_Model_Axis($this->organization, 'ref_321', $this->axis32);
        $this->axis321->getLabel()->set('Label 321', 'fr');
    }

    public function testFirstAndLastOrder()
    {
        $axesArray = [$this->axis112, $this->axis31, $this->axis1, $this->axis12, $this->axis3,
            $this->axis2, $this->axis32, $this->axis11, $this->axis121, $this->axis321, $this->axis111];

        $this->assertCount(11, $axesArray);
        $this->assertSame($this->axis112, $axesArray[0]);
        $this->assertSame($this->axis31, $axesArray[1]);
        $this->assertSame($this->axis1, $axesArray[2]);
        $this->assertSame($this->axis12, $axesArray[3]);
        $this->assertSame($this->axis3, $axesArray[4]);
        $this->assertSame($this->axis2, $axesArray[5]);
        $this->assertSame($this->axis32, $axesArray[6]);
        $this->assertSame($this->axis11, $axesArray[7]);
        $this->assertSame($this->axis121, $axesArray[8]);
        $this->assertSame($this->axis321, $axesArray[9]);
        $this->assertSame($this->axis111, $axesArray[10]);

        usort($axesArray, ['Orga_Model_Axis', 'firstOrderAxes']);
        $this->assertCount(11, $axesArray);
        $this->assertSame($this->axis1, $axesArray[0]);
        $this->assertSame($this->axis11, $axesArray[1]);
        $this->assertSame($this->axis111, $axesArray[2]);
        $this->assertSame($this->axis112, $axesArray[3]);
        $this->assertSame($this->axis12, $axesArray[4]);
        $this->assertSame($this->axis121, $axesArray[5]);
        $this->assertSame($this->axis2, $axesArray[6]);
        $this->assertSame($this->axis3, $axesArray[7]);
        $this->assertSame($this->axis31, $axesArray[8]);
        $this->assertSame($this->axis32, $axesArray[9]);
        $this->assertSame($this->axis321, $axesArray[10]);

        usort($axesArray, ['Orga_Model_Axis', 'lastOrderAxes']);
        $this->assertCount(11, $axesArray);
        $this->assertSame($this->axis111, $axesArray[0]);
        $this->assertSame($this->axis112, $axesArray[1]);
        $this->assertSame($this->axis11, $axesArray[2]);
        $this->assertSame($this->axis121, $axesArray[3]);
        $this->assertSame($this->axis12, $axesArray[4]);
        $this->assertSame($this->axis1, $axesArray[5]);
        $this->assertSame($this->axis2, $axesArray[6]);
        $this->assertSame($this->axis31, $axesArray[7]);
        $this->assertSame($this->axis321, $axesArray[8]);
        $this->assertSame($this->axis32, $axesArray[9]);
        $this->assertSame($this->axis3, $axesArray[10]);
    }

    public function testGetAllNarrowers()
    {
        $this->assertSame([], $this->axis->getAllNarrowers());

        $this->assertSame([], $this->newAxis->getAllNarrowers());

        $this->assertSame([], $this->axis1->getAllNarrowers());
        $this->assertSame([$this->axis1], $this->axis11->getAllNarrowers());
        $this->assertSame([$this->axis11, $this->axis1], $this->axis111->getAllNarrowers());
        $this->assertSame([$this->axis11, $this->axis1], $this->axis112->getAllNarrowers());
        $this->assertSame([$this->axis1], $this->axis12->getAllNarrowers());
        $this->assertSame([$this->axis12, $this->axis1], $this->axis121->getAllNarrowers());

        $this->assertSame([], $this->axis2->getAllNarrowers());

        $this->assertSame([], $this->axis3->getAllNarrowers());
        $this->assertSame([$this->axis3], $this->axis31->getAllNarrowers());
        $this->assertSame([$this->axis3], $this->axis32->getAllNarrowers());
        $this->assertSame([$this->axis32, $this->axis3], $this->axis321->getAllNarrowers());
    }

    public function testGetAllBroadersFirstOrdered()
    {
        $this->assertSame([], $this->axis->getAllBroadersFirstOrdered());

        $this->assertSame([], $this->newAxis->getAllBroadersFirstOrdered());

        $this->assertSame([$this->axis11, $this->axis111, $this->axis112, $this->axis12, $this->axis121], $this->axis1->getAllBroadersFirstOrdered());
        $this->assertSame([$this->axis111, $this->axis112], $this->axis11->getAllBroadersFirstOrdered());
        $this->assertSame([], $this->axis111->getAllBroadersFirstOrdered());
        $this->assertSame([], $this->axis112->getAllBroadersFirstOrdered());
        $this->assertSame([$this->axis121], $this->axis12->getAllBroadersFirstOrdered());
        $this->assertSame([], $this->axis121->getAllBroadersFirstOrdered());

        $this->assertSame([], $this->axis2->getAllBroadersFirstOrdered());

        $this->assertSame([$this->axis31, $this->axis32, $this->axis321], $this->axis3->getAllBroadersFirstOrdered());
        $this->assertSame([], $this->axis31->getAllBroadersFirstOrdered());
        $this->assertSame([$this->axis321], $this->axis32->getAllBroadersFirstOrdered());
        $this->assertSame([], $this->axis321->getAllBroadersFirstOrdered());
    }

    public function testGetAllBroadersLastOrdered()
    {
        $this->assertSame([], $this->axis->getAllBroadersLastOrdered());

        $this->assertSame([], $this->newAxis->getAllBroadersLastOrdered());

        $this->assertSame([$this->axis111, $this->axis112, $this->axis11, $this->axis121, $this->axis12], $this->axis1->getAllBroadersLastOrdered());
        $this->assertSame([$this->axis111, $this->axis112], $this->axis11->getAllBroadersLastOrdered());
        $this->assertSame([], $this->axis111->getAllBroadersLastOrdered());
        $this->assertSame([], $this->axis112->getAllBroadersLastOrdered());
        $this->assertSame([$this->axis121], $this->axis12->getAllBroadersLastOrdered());
        $this->assertSame([], $this->axis121->getAllBroadersLastOrdered());

        $this->assertSame([], $this->axis2->getAllBroadersLastOrdered());

        $this->assertSame([$this->axis31, $this->axis321, $this->axis32], $this->axis3->getAllBroadersLastOrdered());
        $this->assertSame([], $this->axis31->getAllBroadersLastOrdered());
        $this->assertSame([$this->axis321], $this->axis32->getAllBroadersLastOrdered());
        $this->assertSame([], $this->axis321->getAllBroadersLastOrdered());
    }

    public function testMoveTo()
    {
        // Narrower Tag.
        $this->assertSame('/3-ref_1/', $this->axis1->getNarrowerTag());
        $this->assertSame('/3-ref_1/1-ref_11/', $this->axis11->getNarrowerTag());
        $this->assertSame('/3-ref_1/1-ref_11/1-ref_111/', $this->axis111->getNarrowerTag());
        $this->assertSame('/3-ref_1/1-ref_11/2-ref_112/', $this->axis112->getNarrowerTag());
        $this->assertSame('/3-ref_1/2-ref_12/', $this->axis12->getNarrowerTag());
        $this->assertSame('/3-ref_1/2-ref_12/1-ref_121/', $this->axis121->getNarrowerTag());
        $this->assertSame('/4-ref_2/', $this->axis2->getNarrowerTag());

        // Narrowers.
        $this->assertSame([], $this->axis1->getAllNarrowers());
        $this->assertSame([$this->axis1], $this->axis11->getAllNarrowers());
        $this->assertSame([$this->axis11, $this->axis1], $this->axis111->getAllNarrowers());
        $this->assertSame([$this->axis11, $this->axis1], $this->axis112->getAllNarrowers());
        $this->assertSame([$this->axis1], $this->axis12->getAllNarrowers());
        $this->assertSame([$this->axis12, $this->axis1], $this->axis121->getAllNarrowers());
        $this->assertSame([], $this->axis2->getAllNarrowers());

        // Broader Tag.
        $this->assertSame('/1-ref_111/1-ref_11/3-ref_1/&/2-ref_112/1-ref_11/3-ref_1/&/1-ref_121/2-ref_12/3-ref_1/', $this->axis1->getBroaderTag());
        $this->assertSame('/1-ref_111/1-ref_11/&/2-ref_112/1-ref_11/', $this->axis11->getBroaderTag());
        $this->assertSame('/1-ref_111/', $this->axis111->getBroaderTag());
        $this->assertSame('/2-ref_112/', $this->axis112->getBroaderTag());
        $this->assertSame('/1-ref_121/2-ref_12/', $this->axis12->getBroaderTag());
        $this->assertSame('/1-ref_121/', $this->axis121->getBroaderTag());
        $this->assertSame('/4-ref_2/', $this->axis2->getBroaderTag());

        // Broaders.
        $this->assertSame([$this->axis11, $this->axis111, $this->axis112, $this->axis12, $this->axis121], $this->axis1->getAllBroadersFirstOrdered());
        $this->assertSame([$this->axis111, $this->axis112], $this->axis11->getAllBroadersFirstOrdered());
        $this->assertSame([], $this->axis111->getAllBroadersFirstOrdered());
        $this->assertSame([], $this->axis112->getAllBroadersFirstOrdered());
        $this->assertSame([$this->axis121], $this->axis12->getAllBroadersFirstOrdered());
        $this->assertSame([], $this->axis121->getAllBroadersFirstOrdered());
        $this->assertSame([], $this->axis2->getAllBroadersFirstOrdered());

        $this->assertSame($this->axis1, $this->axis11->getDirectNarrower());

        $this->axis11->moveTo($this->axis2);

        $this->assertSame($this->axis2, $this->axis11->getDirectNarrower());

        // Narrower Tag.
        $this->assertSame('/3-ref_1/', $this->axis1->getNarrowerTag());
        $this->assertSame('/4-ref_2/1-ref_11/', $this->axis11->getNarrowerTag());
        $this->assertSame('/4-ref_2/1-ref_11/1-ref_111/', $this->axis111->getNarrowerTag());
        $this->assertSame('/4-ref_2/1-ref_11/2-ref_112/', $this->axis112->getNarrowerTag());
        $this->assertSame('/3-ref_1/1-ref_12/', $this->axis12->getNarrowerTag());
        $this->assertSame('/3-ref_1/1-ref_12/1-ref_121/', $this->axis121->getNarrowerTag());
        $this->assertSame('/4-ref_2/', $this->axis2->getNarrowerTag());

        // Narrowers.
        $this->assertSame([], $this->axis1->getAllNarrowers());
        $this->assertSame([$this->axis2], $this->axis11->getAllNarrowers());
        $this->assertSame([$this->axis11, $this->axis2], $this->axis111->getAllNarrowers());
        $this->assertSame([$this->axis11, $this->axis2], $this->axis112->getAllNarrowers());
        $this->assertSame([$this->axis1], $this->axis12->getAllNarrowers());
        $this->assertSame([$this->axis12, $this->axis1], $this->axis121->getAllNarrowers());
        $this->assertSame([], $this->axis2->getAllNarrowers());

        // Broader Tag.
        $this->assertSame('/1-ref_121/1-ref_12/3-ref_1/', $this->axis1->getBroaderTag());
        $this->assertSame('/1-ref_111/1-ref_11/&/2-ref_112/1-ref_11/', $this->axis11->getBroaderTag());
        $this->assertSame('/1-ref_111/', $this->axis111->getBroaderTag());
        $this->assertSame('/2-ref_112/', $this->axis112->getBroaderTag());
        $this->assertSame('/1-ref_121/1-ref_12/', $this->axis12->getBroaderTag());
        $this->assertSame('/1-ref_121/', $this->axis121->getBroaderTag());
        $this->assertSame('/1-ref_111/1-ref_11/4-ref_2/&/2-ref_112/1-ref_11/4-ref_2/', $this->axis2->getBroaderTag());

        // Broaders.
        $this->assertSame([$this->axis12, $this->axis121], $this->axis1->getAllBroadersFirstOrdered());
        $this->assertSame([$this->axis111, $this->axis112], $this->axis11->getAllBroadersFirstOrdered());
        $this->assertSame([], $this->axis111->getAllBroadersFirstOrdered());
        $this->assertSame([], $this->axis112->getAllBroadersFirstOrdered());
        $this->assertSame([$this->axis121], $this->axis12->getAllBroadersFirstOrdered());
        $this->assertSame([], $this->axis121->getAllBroadersFirstOrdered());
        $this->assertSame([$this->axis11, $this->axis111, $this->axis112], $this->axis2->getAllBroadersFirstOrdered());
    }

    public function testRemoveAxisFromOrganization()
    {
        // Narrower Tag.
        $this->assertSame('/3-ref_1/', $this->axis1->getNarrowerTag());
        $this->assertSame('/3-ref_1/1-ref_11/', $this->axis11->getNarrowerTag());
        $this->assertSame('/3-ref_1/1-ref_11/1-ref_111/', $this->axis111->getNarrowerTag());
        $this->assertSame('/3-ref_1/1-ref_11/2-ref_112/', $this->axis112->getNarrowerTag());
        $this->assertSame('/3-ref_1/2-ref_12/', $this->axis12->getNarrowerTag());
        $this->assertSame('/3-ref_1/2-ref_12/1-ref_121/', $this->axis121->getNarrowerTag());
        $this->assertSame('/4-ref_2/', $this->axis2->getNarrowerTag());
        $this->assertSame('/5-ref_3/', $this->axis3->getNarrowerTag());
        $this->assertSame('/5-ref_3/1-ref_31/', $this->axis31->getNarrowerTag());
        $this->assertSame('/5-ref_3/2-ref_32/', $this->axis32->getNarrowerTag());
        $this->assertSame('/5-ref_3/2-ref_32/1-ref_321/', $this->axis321->getNarrowerTag());

        // Narrowers.
        $this->assertSame([], $this->axis1->getAllNarrowers());
        $this->assertSame([$this->axis1], $this->axis11->getAllNarrowers());
        $this->assertSame([$this->axis11, $this->axis1], $this->axis111->getAllNarrowers());
        $this->assertSame([$this->axis11, $this->axis1], $this->axis112->getAllNarrowers());
        $this->assertSame([$this->axis1], $this->axis12->getAllNarrowers());
        $this->assertSame([$this->axis12, $this->axis1], $this->axis121->getAllNarrowers());
        $this->assertSame([], $this->axis2->getAllNarrowers());
        $this->assertSame([], $this->axis3->getAllNarrowers());
        $this->assertSame([$this->axis3], $this->axis31->getAllNarrowers());
        $this->assertSame([$this->axis3], $this->axis32->getAllNarrowers());
        $this->assertSame([$this->axis32, $this->axis3], $this->axis321->getAllNarrowers());

        // Broader Tag.
        $this->assertSame('/1-ref_111/1-ref_11/3-ref_1/&/2-ref_112/1-ref_11/3-ref_1/&/1-ref_121/2-ref_12/3-ref_1/', $this->axis1->getBroaderTag());
        $this->assertSame('/1-ref_111/1-ref_11/&/2-ref_112/1-ref_11/', $this->axis11->getBroaderTag());
        $this->assertSame('/1-ref_111/', $this->axis111->getBroaderTag());
        $this->assertSame('/2-ref_112/', $this->axis112->getBroaderTag());
        $this->assertSame('/1-ref_121/2-ref_12/', $this->axis12->getBroaderTag());
        $this->assertSame('/1-ref_121/', $this->axis121->getBroaderTag());
        $this->assertSame('/4-ref_2/', $this->axis2->getBroaderTag());
        $this->assertSame('/1-ref_31/5-ref_3/&/1-ref_321/2-ref_32/5-ref_3/', $this->axis3->getBroaderTag());
        $this->assertSame('/1-ref_31/', $this->axis31->getBroaderTag());
        $this->assertSame('/1-ref_321/2-ref_32/', $this->axis32->getBroaderTag());
        $this->assertSame('/1-ref_321/', $this->axis321->getBroaderTag());

        // Broaders.
        $this->assertSame([$this->axis11, $this->axis111, $this->axis112, $this->axis12, $this->axis121], $this->axis1->getAllBroadersFirstOrdered());
        $this->assertSame([$this->axis111, $this->axis112], $this->axis11->getAllBroadersFirstOrdered());
        $this->assertSame([], $this->axis111->getAllBroadersFirstOrdered());
        $this->assertSame([], $this->axis112->getAllBroadersFirstOrdered());
        $this->assertSame([$this->axis121], $this->axis12->getAllBroadersFirstOrdered());
        $this->assertSame([], $this->axis121->getAllBroadersFirstOrdered());
        $this->assertSame([], $this->axis2->getAllBroadersFirstOrdered());
        $this->assertSame([$this->axis31, $this->axis32, $this->axis321], $this->axis3->getAllBroadersFirstOrdered());
        $this->assertSame([], $this->axis31->getAllBroadersFirstOrdered());
        $this->assertSame([$this->axis321], $this->axis32->getAllBroadersFirstOrdered());
        $this->assertSame([], $this->axis321->getAllBroadersFirstOrdered());

        $this->organization->removeAxis($this->axis2);

        // Narrower Tag.
        $this->assertSame('/3-ref_1/', $this->axis1->getNarrowerTag());
        $this->assertSame('/3-ref_1/1-ref_11/', $this->axis11->getNarrowerTag());
        $this->assertSame('/3-ref_1/1-ref_11/1-ref_111/', $this->axis111->getNarrowerTag());
        $this->assertSame('/3-ref_1/1-ref_11/2-ref_112/', $this->axis112->getNarrowerTag());
        $this->assertSame('/3-ref_1/2-ref_12/', $this->axis12->getNarrowerTag());
        $this->assertSame('/3-ref_1/2-ref_12/1-ref_121/', $this->axis121->getNarrowerTag());
        $this->assertSame('/5-ref_2/', $this->axis2->getNarrowerTag());
        $this->assertSame('/4-ref_3/', $this->axis3->getNarrowerTag());
        $this->assertSame('/4-ref_3/1-ref_31/', $this->axis31->getNarrowerTag());
        $this->assertSame('/4-ref_3/2-ref_32/', $this->axis32->getNarrowerTag());
        $this->assertSame('/4-ref_3/2-ref_32/1-ref_321/', $this->axis321->getNarrowerTag());

        // Narrowers.
        $this->assertSame([], $this->axis1->getAllNarrowers());
        $this->assertSame([$this->axis1], $this->axis11->getAllNarrowers());
        $this->assertSame([$this->axis11, $this->axis1], $this->axis111->getAllNarrowers());
        $this->assertSame([$this->axis11, $this->axis1], $this->axis112->getAllNarrowers());
        $this->assertSame([$this->axis1], $this->axis12->getAllNarrowers());
        $this->assertSame([$this->axis12, $this->axis1], $this->axis121->getAllNarrowers());
        $this->assertSame([], $this->axis2->getAllNarrowers());
        $this->assertSame([], $this->axis3->getAllNarrowers());
        $this->assertSame([$this->axis3], $this->axis31->getAllNarrowers());
        $this->assertSame([$this->axis3], $this->axis32->getAllNarrowers());
        $this->assertSame([$this->axis32, $this->axis3], $this->axis321->getAllNarrowers());

        // Broader Tag.
        $this->assertSame('/1-ref_111/1-ref_11/3-ref_1/&/2-ref_112/1-ref_11/3-ref_1/&/1-ref_121/2-ref_12/3-ref_1/', $this->axis1->getBroaderTag());
        $this->assertSame('/1-ref_111/1-ref_11/&/2-ref_112/1-ref_11/', $this->axis11->getBroaderTag());
        $this->assertSame('/1-ref_111/', $this->axis111->getBroaderTag());
        $this->assertSame('/2-ref_112/', $this->axis112->getBroaderTag());
        $this->assertSame('/1-ref_121/2-ref_12/', $this->axis12->getBroaderTag());
        $this->assertSame('/1-ref_121/', $this->axis121->getBroaderTag());
        $this->assertSame('/5-ref_2/', $this->axis2->getBroaderTag());
        $this->assertSame('/1-ref_31/4-ref_3/&/1-ref_321/2-ref_32/4-ref_3/', $this->axis3->getBroaderTag());
        $this->assertSame('/1-ref_31/', $this->axis31->getBroaderTag());
        $this->assertSame('/1-ref_321/2-ref_32/', $this->axis32->getBroaderTag());
        $this->assertSame('/1-ref_321/', $this->axis321->getBroaderTag());

        // Broaders.
        $this->assertSame([$this->axis11, $this->axis111, $this->axis112, $this->axis12, $this->axis121], $this->axis1->getAllBroadersFirstOrdered());
        $this->assertSame([$this->axis111, $this->axis112], $this->axis11->getAllBroadersFirstOrdered());
        $this->assertSame([], $this->axis111->getAllBroadersFirstOrdered());
        $this->assertSame([], $this->axis112->getAllBroadersFirstOrdered());
        $this->assertSame([$this->axis121], $this->axis12->getAllBroadersFirstOrdered());
        $this->assertSame([], $this->axis121->getAllBroadersFirstOrdered());
        $this->assertSame([], $this->axis2->getAllBroadersFirstOrdered());
        $this->assertSame([$this->axis31, $this->axis32, $this->axis321], $this->axis3->getAllBroadersFirstOrdered());
        $this->assertSame([], $this->axis31->getAllBroadersFirstOrdered());
        $this->assertSame([$this->axis321], $this->axis32->getAllBroadersFirstOrdered());
        $this->assertSame([], $this->axis321->getAllBroadersFirstOrdered());

        $this->organization->removeAxis($this->axis11);

        // Narrower Tag.
        $this->assertSame('/3-ref_1/', $this->axis1->getNarrowerTag());
        $this->assertSame('/5-ref_11/', $this->axis11->getNarrowerTag());
        $this->assertSame('/3-ref_1/2-ref_111/', $this->axis111->getNarrowerTag());
        $this->assertSame('/3-ref_1/3-ref_112/', $this->axis112->getNarrowerTag());
        $this->assertSame('/3-ref_1/1-ref_12/', $this->axis12->getNarrowerTag());
        $this->assertSame('/3-ref_1/1-ref_12/1-ref_121/', $this->axis121->getNarrowerTag());
        $this->assertSame('/5-ref_2/', $this->axis2->getNarrowerTag());
        $this->assertSame('/4-ref_3/', $this->axis3->getNarrowerTag());
        $this->assertSame('/4-ref_3/1-ref_31/', $this->axis31->getNarrowerTag());
        $this->assertSame('/4-ref_3/2-ref_32/', $this->axis32->getNarrowerTag());
        $this->assertSame('/4-ref_3/2-ref_32/1-ref_321/', $this->axis321->getNarrowerTag());

        // Narrowers.
        $this->assertSame([], $this->axis1->getAllNarrowers());
        $this->assertSame([], $this->axis11->getAllNarrowers());
        $this->assertSame([$this->axis1], $this->axis111->getAllNarrowers());
        $this->assertSame([$this->axis1], $this->axis112->getAllNarrowers());
        $this->assertSame([$this->axis1], $this->axis12->getAllNarrowers());
        $this->assertSame([$this->axis12, $this->axis1], $this->axis121->getAllNarrowers());
        $this->assertSame([], $this->axis2->getAllNarrowers());
        $this->assertSame([], $this->axis3->getAllNarrowers());
        $this->assertSame([$this->axis3], $this->axis31->getAllNarrowers());
        $this->assertSame([$this->axis3], $this->axis32->getAllNarrowers());
        $this->assertSame([$this->axis32, $this->axis3], $this->axis321->getAllNarrowers());

        // Broader Tag.
        $this->assertSame('/1-ref_121/1-ref_12/3-ref_1/&/2-ref_111/3-ref_1/&/3-ref_112/3-ref_1/', $this->axis1->getBroaderTag());
        $this->assertSame('/5-ref_11/', $this->axis11->getBroaderTag());
        $this->assertSame('/2-ref_111/', $this->axis111->getBroaderTag());
        $this->assertSame('/3-ref_112/', $this->axis112->getBroaderTag());
        $this->assertSame('/1-ref_121/1-ref_12/', $this->axis12->getBroaderTag());
        $this->assertSame('/1-ref_121/', $this->axis121->getBroaderTag());
        $this->assertSame('/5-ref_2/', $this->axis2->getBroaderTag());
        $this->assertSame('/1-ref_31/4-ref_3/&/1-ref_321/2-ref_32/4-ref_3/', $this->axis3->getBroaderTag());
        $this->assertSame('/1-ref_31/', $this->axis31->getBroaderTag());
        $this->assertSame('/1-ref_321/2-ref_32/', $this->axis32->getBroaderTag());
        $this->assertSame('/1-ref_321/', $this->axis321->getBroaderTag());

        // Broaders.
        $this->assertSame([$this->axis12, $this->axis121, $this->axis111, $this->axis112], $this->axis1->getAllBroadersFirstOrdered());
        $this->assertSame([], $this->axis11->getAllBroadersFirstOrdered());
        $this->assertSame([], $this->axis111->getAllBroadersFirstOrdered());
        $this->assertSame([], $this->axis112->getAllBroadersFirstOrdered());
        $this->assertSame([$this->axis121], $this->axis12->getAllBroadersFirstOrdered());
        $this->assertSame([], $this->axis121->getAllBroadersFirstOrdered());
        $this->assertSame([], $this->axis2->getAllBroadersFirstOrdered());
        $this->assertSame([$this->axis31, $this->axis32, $this->axis321], $this->axis3->getAllBroadersFirstOrdered());
        $this->assertSame([], $this->axis31->getAllBroadersFirstOrdered());
        $this->assertSame([$this->axis321], $this->axis32->getAllBroadersFirstOrdered());
        $this->assertSame([], $this->axis321->getAllBroadersFirstOrdered());
    }

    public function testIsNarrowerThan()
    {
        $this->assertFalse($this->axis1->isNarrowerThan($this->axis1));
        $this->assertTrue($this->axis1->isNarrowerThan($this->axis11));
        $this->assertTrue($this->axis1->isNarrowerThan($this->axis111));
        $this->assertTrue($this->axis1->isNarrowerThan($this->axis112));
        $this->assertTrue($this->axis1->isNarrowerThan($this->axis12));
        $this->assertTrue($this->axis1->isNarrowerThan($this->axis121));
        $this->assertFalse($this->axis1->isNarrowerThan($this->axis2));
        $this->assertFalse($this->axis1->isNarrowerThan($this->axis3));
        $this->assertFalse($this->axis1->isNarrowerThan($this->axis31));
        $this->assertFalse($this->axis1->isNarrowerThan($this->axis32));
        $this->assertFalse($this->axis1->isNarrowerThan($this->axis321));

        $this->assertFalse($this->axis11->isNarrowerThan($this->axis1));
        $this->assertFalse($this->axis11->isNarrowerThan($this->axis11));
        $this->assertTrue($this->axis11->isNarrowerThan($this->axis111));
        $this->assertTrue($this->axis11->isNarrowerThan($this->axis112));
        $this->assertFalse($this->axis11->isNarrowerThan($this->axis12));
        $this->assertFalse($this->axis11->isNarrowerThan($this->axis121));
        $this->assertFalse($this->axis11->isNarrowerThan($this->axis2));
        $this->assertFalse($this->axis11->isNarrowerThan($this->axis3));
        $this->assertFalse($this->axis11->isNarrowerThan($this->axis31));
        $this->assertFalse($this->axis11->isNarrowerThan($this->axis32));
        $this->assertFalse($this->axis11->isNarrowerThan($this->axis321));

        $this->assertFalse($this->axis111->isNarrowerThan($this->axis1));
        $this->assertFalse($this->axis111->isNarrowerThan($this->axis11));
        $this->assertFalse($this->axis111->isNarrowerThan($this->axis111));
        $this->assertFalse($this->axis111->isNarrowerThan($this->axis112));
        $this->assertFalse($this->axis111->isNarrowerThan($this->axis12));
        $this->assertFalse($this->axis111->isNarrowerThan($this->axis121));
        $this->assertFalse($this->axis111->isNarrowerThan($this->axis2));
        $this->assertFalse($this->axis111->isNarrowerThan($this->axis3));
        $this->assertFalse($this->axis111->isNarrowerThan($this->axis31));
        $this->assertFalse($this->axis111->isNarrowerThan($this->axis32));
        $this->assertFalse($this->axis111->isNarrowerThan($this->axis321));

        $this->assertFalse($this->axis112->isNarrowerThan($this->axis1));
        $this->assertFalse($this->axis112->isNarrowerThan($this->axis11));
        $this->assertFalse($this->axis112->isNarrowerThan($this->axis111));
        $this->assertFalse($this->axis112->isNarrowerThan($this->axis112));
        $this->assertFalse($this->axis112->isNarrowerThan($this->axis12));
        $this->assertFalse($this->axis112->isNarrowerThan($this->axis121));
        $this->assertFalse($this->axis112->isNarrowerThan($this->axis2));
        $this->assertFalse($this->axis112->isNarrowerThan($this->axis3));
        $this->assertFalse($this->axis112->isNarrowerThan($this->axis31));
        $this->assertFalse($this->axis112->isNarrowerThan($this->axis32));
        $this->assertFalse($this->axis112->isNarrowerThan($this->axis321));

        $this->assertFalse($this->axis12->isNarrowerThan($this->axis1));
        $this->assertFalse($this->axis12->isNarrowerThan($this->axis11));
        $this->assertFalse($this->axis12->isNarrowerThan($this->axis111));
        $this->assertFalse($this->axis12->isNarrowerThan($this->axis112));
        $this->assertFalse($this->axis12->isNarrowerThan($this->axis12));
        $this->assertTrue($this->axis12->isNarrowerThan($this->axis121));
        $this->assertFalse($this->axis12->isNarrowerThan($this->axis2));
        $this->assertFalse($this->axis12->isNarrowerThan($this->axis3));
        $this->assertFalse($this->axis12->isNarrowerThan($this->axis31));
        $this->assertFalse($this->axis12->isNarrowerThan($this->axis32));
        $this->assertFalse($this->axis12->isNarrowerThan($this->axis321));

        $this->assertFalse($this->axis121->isNarrowerThan($this->axis1));
        $this->assertFalse($this->axis121->isNarrowerThan($this->axis11));
        $this->assertFalse($this->axis121->isNarrowerThan($this->axis111));
        $this->assertFalse($this->axis121->isNarrowerThan($this->axis112));
        $this->assertFalse($this->axis121->isNarrowerThan($this->axis12));
        $this->assertFalse($this->axis121->isNarrowerThan($this->axis121));
        $this->assertFalse($this->axis121->isNarrowerThan($this->axis2));
        $this->assertFalse($this->axis121->isNarrowerThan($this->axis3));
        $this->assertFalse($this->axis121->isNarrowerThan($this->axis31));
        $this->assertFalse($this->axis121->isNarrowerThan($this->axis32));
        $this->assertFalse($this->axis121->isNarrowerThan($this->axis321));

        $this->assertFalse($this->axis2->isNarrowerThan($this->axis1));
        $this->assertFalse($this->axis2->isNarrowerThan($this->axis11));
        $this->assertFalse($this->axis2->isNarrowerThan($this->axis111));
        $this->assertFalse($this->axis2->isNarrowerThan($this->axis112));
        $this->assertFalse($this->axis2->isNarrowerThan($this->axis12));
        $this->assertFalse($this->axis2->isNarrowerThan($this->axis121));
        $this->assertFalse($this->axis2->isNarrowerThan($this->axis2));
        $this->assertFalse($this->axis2->isNarrowerThan($this->axis3));
        $this->assertFalse($this->axis2->isNarrowerThan($this->axis31));
        $this->assertFalse($this->axis2->isNarrowerThan($this->axis32));
        $this->assertFalse($this->axis2->isNarrowerThan($this->axis321));

        $this->assertFalse($this->axis3->isNarrowerThan($this->axis1));
        $this->assertFalse($this->axis3->isNarrowerThan($this->axis11));
        $this->assertFalse($this->axis3->isNarrowerThan($this->axis111));
        $this->assertFalse($this->axis3->isNarrowerThan($this->axis112));
        $this->assertFalse($this->axis3->isNarrowerThan($this->axis12));
        $this->assertFalse($this->axis3->isNarrowerThan($this->axis121));
        $this->assertFalse($this->axis3->isNarrowerThan($this->axis2));
        $this->assertFalse($this->axis3->isNarrowerThan($this->axis3));
        $this->assertTrue($this->axis3->isNarrowerThan($this->axis31));
        $this->assertTrue($this->axis3->isNarrowerThan($this->axis32));
        $this->assertTrue($this->axis3->isNarrowerThan($this->axis321));

        $this->assertFalse($this->axis31->isNarrowerThan($this->axis1));
        $this->assertFalse($this->axis31->isNarrowerThan($this->axis11));
        $this->assertFalse($this->axis31->isNarrowerThan($this->axis111));
        $this->assertFalse($this->axis31->isNarrowerThan($this->axis112));
        $this->assertFalse($this->axis31->isNarrowerThan($this->axis12));
        $this->assertFalse($this->axis31->isNarrowerThan($this->axis121));
        $this->assertFalse($this->axis31->isNarrowerThan($this->axis2));
        $this->assertFalse($this->axis31->isNarrowerThan($this->axis3));
        $this->assertFalse($this->axis31->isNarrowerThan($this->axis31));
        $this->assertFalse($this->axis31->isNarrowerThan($this->axis32));
        $this->assertFalse($this->axis31->isNarrowerThan($this->axis321));

        $this->assertFalse($this->axis32->isNarrowerThan($this->axis1));
        $this->assertFalse($this->axis32->isNarrowerThan($this->axis11));
        $this->assertFalse($this->axis32->isNarrowerThan($this->axis111));
        $this->assertFalse($this->axis32->isNarrowerThan($this->axis112));
        $this->assertFalse($this->axis32->isNarrowerThan($this->axis12));
        $this->assertFalse($this->axis32->isNarrowerThan($this->axis121));
        $this->assertFalse($this->axis32->isNarrowerThan($this->axis2));
        $this->assertFalse($this->axis32->isNarrowerThan($this->axis3));
        $this->assertFalse($this->axis32->isNarrowerThan($this->axis31));
        $this->assertFalse($this->axis32->isNarrowerThan($this->axis32));
        $this->assertTrue($this->axis32->isNarrowerThan($this->axis321));

        $this->assertFalse($this->axis321->isNarrowerThan($this->axis1));
        $this->assertFalse($this->axis321->isNarrowerThan($this->axis11));
        $this->assertFalse($this->axis321->isNarrowerThan($this->axis111));
        $this->assertFalse($this->axis321->isNarrowerThan($this->axis112));
        $this->assertFalse($this->axis321->isNarrowerThan($this->axis12));
        $this->assertFalse($this->axis321->isNarrowerThan($this->axis121));
        $this->assertFalse($this->axis321->isNarrowerThan($this->axis2));
        $this->assertFalse($this->axis321->isNarrowerThan($this->axis3));
        $this->assertFalse($this->axis321->isNarrowerThan($this->axis31));
        $this->assertFalse($this->axis321->isNarrowerThan($this->axis32));
        $this->assertFalse($this->axis321->isNarrowerThan($this->axis321));
    }

    public function testIsBroaderThan()
    {
        $this->assertFalse($this->axis1->isBroaderThan($this->axis1));
        $this->assertFalse($this->axis1->isBroaderThan($this->axis11));
        $this->assertFalse($this->axis1->isBroaderThan($this->axis111));
        $this->assertFalse($this->axis1->isBroaderThan($this->axis112));
        $this->assertFalse($this->axis1->isBroaderThan($this->axis12));
        $this->assertFalse($this->axis1->isBroaderThan($this->axis121));
        $this->assertFalse($this->axis1->isBroaderThan($this->axis2));
        $this->assertFalse($this->axis1->isBroaderThan($this->axis3));
        $this->assertFalse($this->axis1->isBroaderThan($this->axis31));
        $this->assertFalse($this->axis1->isBroaderThan($this->axis32));
        $this->assertFalse($this->axis1->isBroaderThan($this->axis321));

        $this->assertTrue($this->axis11->isBroaderThan($this->axis1));
        $this->assertFalse($this->axis11->isBroaderThan($this->axis11));
        $this->assertFalse($this->axis11->isBroaderThan($this->axis111));
        $this->assertFalse($this->axis11->isBroaderThan($this->axis112));
        $this->assertFalse($this->axis11->isBroaderThan($this->axis12));
        $this->assertFalse($this->axis11->isBroaderThan($this->axis121));
        $this->assertFalse($this->axis11->isBroaderThan($this->axis2));
        $this->assertFalse($this->axis11->isBroaderThan($this->axis3));
        $this->assertFalse($this->axis11->isBroaderThan($this->axis31));
        $this->assertFalse($this->axis11->isBroaderThan($this->axis32));
        $this->assertFalse($this->axis11->isBroaderThan($this->axis321));

        $this->assertTrue($this->axis111->isBroaderThan($this->axis1));
        $this->assertTrue($this->axis111->isBroaderThan($this->axis11));
        $this->assertFalse($this->axis111->isBroaderThan($this->axis111));
        $this->assertFalse($this->axis111->isBroaderThan($this->axis112));
        $this->assertFalse($this->axis111->isBroaderThan($this->axis12));
        $this->assertFalse($this->axis111->isBroaderThan($this->axis121));
        $this->assertFalse($this->axis111->isBroaderThan($this->axis2));
        $this->assertFalse($this->axis111->isBroaderThan($this->axis3));
        $this->assertFalse($this->axis111->isBroaderThan($this->axis31));
        $this->assertFalse($this->axis111->isBroaderThan($this->axis32));
        $this->assertFalse($this->axis111->isBroaderThan($this->axis321));

        $this->assertTrue($this->axis112->isBroaderThan($this->axis1));
        $this->assertTrue($this->axis112->isBroaderThan($this->axis11));
        $this->assertFalse($this->axis112->isBroaderThan($this->axis111));
        $this->assertFalse($this->axis112->isBroaderThan($this->axis112));
        $this->assertFalse($this->axis112->isBroaderThan($this->axis12));
        $this->assertFalse($this->axis112->isBroaderThan($this->axis121));
        $this->assertFalse($this->axis112->isBroaderThan($this->axis2));
        $this->assertFalse($this->axis112->isBroaderThan($this->axis3));
        $this->assertFalse($this->axis112->isBroaderThan($this->axis31));
        $this->assertFalse($this->axis112->isBroaderThan($this->axis32));
        $this->assertFalse($this->axis112->isBroaderThan($this->axis321));

        $this->assertTrue($this->axis12->isBroaderThan($this->axis1));
        $this->assertFalse($this->axis12->isBroaderThan($this->axis11));
        $this->assertFalse($this->axis12->isBroaderThan($this->axis111));
        $this->assertFalse($this->axis12->isBroaderThan($this->axis112));
        $this->assertFalse($this->axis12->isBroaderThan($this->axis12));
        $this->assertFalse($this->axis12->isBroaderThan($this->axis121));
        $this->assertFalse($this->axis12->isBroaderThan($this->axis2));
        $this->assertFalse($this->axis12->isBroaderThan($this->axis3));
        $this->assertFalse($this->axis12->isBroaderThan($this->axis31));
        $this->assertFalse($this->axis12->isBroaderThan($this->axis32));
        $this->assertFalse($this->axis12->isBroaderThan($this->axis321));

        $this->assertTrue($this->axis121->isBroaderThan($this->axis1));
        $this->assertFalse($this->axis121->isBroaderThan($this->axis11));
        $this->assertFalse($this->axis121->isBroaderThan($this->axis111));
        $this->assertFalse($this->axis121->isBroaderThan($this->axis112));
        $this->assertTrue($this->axis121->isBroaderThan($this->axis12));
        $this->assertFalse($this->axis121->isBroaderThan($this->axis121));
        $this->assertFalse($this->axis121->isBroaderThan($this->axis2));
        $this->assertFalse($this->axis121->isBroaderThan($this->axis3));
        $this->assertFalse($this->axis121->isBroaderThan($this->axis31));
        $this->assertFalse($this->axis121->isBroaderThan($this->axis32));
        $this->assertFalse($this->axis121->isBroaderThan($this->axis321));

        $this->assertFalse($this->axis2->isBroaderThan($this->axis1));
        $this->assertFalse($this->axis2->isBroaderThan($this->axis11));
        $this->assertFalse($this->axis2->isBroaderThan($this->axis111));
        $this->assertFalse($this->axis2->isBroaderThan($this->axis112));
        $this->assertFalse($this->axis2->isBroaderThan($this->axis12));
        $this->assertFalse($this->axis2->isBroaderThan($this->axis121));
        $this->assertFalse($this->axis2->isBroaderThan($this->axis2));
        $this->assertFalse($this->axis2->isBroaderThan($this->axis3));
        $this->assertFalse($this->axis2->isBroaderThan($this->axis31));
        $this->assertFalse($this->axis2->isBroaderThan($this->axis32));
        $this->assertFalse($this->axis2->isBroaderThan($this->axis321));

        $this->assertFalse($this->axis3->isBroaderThan($this->axis1));
        $this->assertFalse($this->axis3->isBroaderThan($this->axis11));
        $this->assertFalse($this->axis3->isBroaderThan($this->axis111));
        $this->assertFalse($this->axis3->isBroaderThan($this->axis112));
        $this->assertFalse($this->axis3->isBroaderThan($this->axis12));
        $this->assertFalse($this->axis3->isBroaderThan($this->axis121));
        $this->assertFalse($this->axis3->isBroaderThan($this->axis2));
        $this->assertFalse($this->axis3->isBroaderThan($this->axis3));
        $this->assertFalse($this->axis3->isBroaderThan($this->axis31));
        $this->assertFalse($this->axis3->isBroaderThan($this->axis32));
        $this->assertFalse($this->axis3->isBroaderThan($this->axis321));

        $this->assertFalse($this->axis31->isBroaderThan($this->axis1));
        $this->assertFalse($this->axis31->isBroaderThan($this->axis11));
        $this->assertFalse($this->axis31->isBroaderThan($this->axis111));
        $this->assertFalse($this->axis31->isBroaderThan($this->axis112));
        $this->assertFalse($this->axis31->isBroaderThan($this->axis12));
        $this->assertFalse($this->axis31->isBroaderThan($this->axis121));
        $this->assertFalse($this->axis31->isBroaderThan($this->axis2));
        $this->assertTrue($this->axis31->isBroaderThan($this->axis3));
        $this->assertFalse($this->axis31->isBroaderThan($this->axis31));
        $this->assertFalse($this->axis31->isBroaderThan($this->axis32));
        $this->assertFalse($this->axis31->isBroaderThan($this->axis321));

        $this->assertFalse($this->axis32->isBroaderThan($this->axis1));
        $this->assertFalse($this->axis32->isBroaderThan($this->axis11));
        $this->assertFalse($this->axis32->isBroaderThan($this->axis111));
        $this->assertFalse($this->axis32->isBroaderThan($this->axis112));
        $this->assertFalse($this->axis32->isBroaderThan($this->axis12));
        $this->assertFalse($this->axis32->isBroaderThan($this->axis121));
        $this->assertFalse($this->axis32->isBroaderThan($this->axis2));
        $this->assertTrue($this->axis32->isBroaderThan($this->axis3));
        $this->assertFalse($this->axis32->isBroaderThan($this->axis31));
        $this->assertFalse($this->axis32->isBroaderThan($this->axis32));
        $this->assertFalse($this->axis32->isBroaderThan($this->axis321));

        $this->assertFalse($this->axis321->isBroaderThan($this->axis1));
        $this->assertFalse($this->axis321->isBroaderThan($this->axis11));
        $this->assertFalse($this->axis321->isBroaderThan($this->axis111));
        $this->assertFalse($this->axis321->isBroaderThan($this->axis112));
        $this->assertFalse($this->axis321->isBroaderThan($this->axis12));
        $this->assertFalse($this->axis321->isBroaderThan($this->axis121));
        $this->assertFalse($this->axis321->isBroaderThan($this->axis2));
        $this->assertTrue($this->axis321->isBroaderThan($this->axis3));
        $this->assertFalse($this->axis321->isBroaderThan($this->axis31));
        $this->assertTrue($this->axis321->isBroaderThan($this->axis32));
        $this->assertFalse($this->axis321->isBroaderThan($this->axis321));
    }

    public function testIsTransverse()
    {
        $this->assertFalse($this->axis1->isTransverse([$this->axis1]));
        $this->assertFalse($this->axis1->isTransverse([$this->axis11]));
        $this->assertFalse($this->axis1->isTransverse([$this->axis111]));
        $this->assertFalse($this->axis1->isTransverse([$this->axis112]));
        $this->assertFalse($this->axis1->isTransverse([$this->axis12]));
        $this->assertFalse($this->axis1->isTransverse([$this->axis121]));
        $this->assertTrue($this->axis1->isTransverse([$this->axis2, $this->axis3, $this->axis31, $this->axis32, $this->axis321]));

        $this->assertFalse($this->axis11->isTransverse([$this->axis1]));
        $this->assertFalse($this->axis11->isTransverse([$this->axis11]));
        $this->assertFalse($this->axis11->isTransverse([$this->axis111]));
        $this->assertFalse($this->axis11->isTransverse([$this->axis112]));
        $this->assertTrue($this->axis11->isTransverse([$this->axis12]));
        $this->assertTrue($this->axis11->isTransverse([$this->axis121]));
        $this->assertTrue($this->axis11->isTransverse([$this->axis2, $this->axis3, $this->axis31, $this->axis32, $this->axis321]));

        $this->assertFalse($this->axis111->isTransverse([$this->axis1]));
        $this->assertFalse($this->axis111->isTransverse([$this->axis11]));
        $this->assertFalse($this->axis111->isTransverse([$this->axis111]));
        $this->assertTrue($this->axis111->isTransverse([$this->axis112]));
        $this->assertTrue($this->axis111->isTransverse([$this->axis12]));
        $this->assertTrue($this->axis111->isTransverse([$this->axis121]));
        $this->assertTrue($this->axis111->isTransverse([$this->axis2, $this->axis3, $this->axis31, $this->axis32, $this->axis321]));

        $this->assertFalse($this->axis112->isTransverse([$this->axis1]));
        $this->assertFalse($this->axis112->isTransverse([$this->axis11]));
        $this->assertTrue($this->axis112->isTransverse([$this->axis111]));
        $this->assertFalse($this->axis112->isTransverse([$this->axis112]));
        $this->assertTrue($this->axis112->isTransverse([$this->axis12]));
        $this->assertTrue($this->axis112->isTransverse([$this->axis121]));
        $this->assertTrue($this->axis112->isTransverse([$this->axis2, $this->axis3, $this->axis31, $this->axis32, $this->axis321]));

        $this->assertFalse($this->axis12->isTransverse([$this->axis1]));
        $this->assertTrue($this->axis12->isTransverse([$this->axis11]));
        $this->assertTrue($this->axis12->isTransverse([$this->axis111]));
        $this->assertTrue($this->axis12->isTransverse([$this->axis112]));
        $this->assertFalse($this->axis12->isTransverse([$this->axis12]));
        $this->assertFalse($this->axis12->isTransverse([$this->axis121]));
        $this->assertTrue($this->axis12->isTransverse([$this->axis2, $this->axis3, $this->axis31, $this->axis32, $this->axis321]));

        $this->assertFalse($this->axis121->isTransverse([$this->axis1]));
        $this->assertTrue($this->axis121->isTransverse([$this->axis11]));
        $this->assertTrue($this->axis121->isTransverse([$this->axis111]));
        $this->assertTrue($this->axis121->isTransverse([$this->axis112]));
        $this->assertFalse($this->axis121->isTransverse([$this->axis12]));
        $this->assertFalse($this->axis121->isTransverse([$this->axis121]));
        $this->assertTrue($this->axis121->isTransverse([$this->axis2, $this->axis3, $this->axis31, $this->axis32, $this->axis321]));

        $this->assertTrue($this->axis2->isTransverse([$this->axis1, $this->axis11, $this->axis111, $this->axis112, $this->axis12, $this->axis121]));
        $this->assertFalse($this->axis2->isTransverse([$this->axis2]));
        $this->assertTrue($this->axis2->isTransverse([$this->axis3, $this->axis31, $this->axis32, $this->axis321]));

        $this->assertTrue($this->axis3->isTransverse([$this->axis1, $this->axis11, $this->axis111, $this->axis112, $this->axis12, $this->axis121, $this->axis2]));
        $this->assertFalse($this->axis3->isTransverse([$this->axis3]));
        $this->assertFalse($this->axis3->isTransverse([$this->axis31]));
        $this->assertFalse($this->axis3->isTransverse([$this->axis32]));
        $this->assertFalse($this->axis3->isTransverse([$this->axis321]));

        $this->assertTrue($this->axis31->isTransverse([$this->axis1, $this->axis11, $this->axis111, $this->axis112, $this->axis12, $this->axis121, $this->axis2]));
        $this->assertFalse($this->axis31->isTransverse([$this->axis3]));
        $this->assertFalse($this->axis31->isTransverse([$this->axis31]));
        $this->assertTrue($this->axis31->isTransverse([$this->axis32]));
        $this->assertTrue($this->axis31->isTransverse([$this->axis321]));

        $this->assertTrue($this->axis32->isTransverse([$this->axis1, $this->axis11, $this->axis111, $this->axis112, $this->axis12, $this->axis121, $this->axis2]));
        $this->assertFalse($this->axis32->isTransverse([$this->axis3]));
        $this->assertTrue($this->axis32->isTransverse([$this->axis31]));
        $this->assertFalse($this->axis32->isTransverse([$this->axis32]));
        $this->assertFalse($this->axis32->isTransverse([$this->axis321]));

        $this->assertTrue($this->axis321->isTransverse([$this->axis1, $this->axis11, $this->axis111, $this->axis112, $this->axis12, $this->axis121, $this->axis2]));
        $this->assertFalse($this->axis321->isTransverse([$this->axis3]));
        $this->assertTrue($this->axis321->isTransverse([$this->axis31]));
        $this->assertFalse($this->axis321->isTransverse([$this->axis32]));
        $this->assertFalse($this->axis321->isTransverse([$this->axis321]));
    }

}

class Orga_Test_AxisMembers extends TestCase
{
    /**
     * @var Orga_Model_Organization
     */
    protected $organization;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis;
    /**
     * @var Orga_Model_Member
     */
    protected $member1;
    /**
     * @var Orga_Model_Member
     */
    protected $member2;
    /**
     * @var Orga_Model_Member
     */
    protected $member3;

    public function setUp()
    {
        parent::setUp();

        $this->organization = new Orga_Model_Organization(
            $this->getMockBuilder(Account::class)->disableOriginalConstructor()->getMock()
        );

        $this->axis = new Orga_Model_Axis($this->organization, 'ref');
        $this->axis->getLabel()->set('Label', 'fr');

        $this->member1 = new Orga_Model_Member($this->axis, 'ref_1');
        $this->member1->getLabel()->set('Label 1', 'fr');

        $this->member2 = new Orga_Model_Member($this->axis, 'ref_2');
        $this->member1->getLabel()->set('Label 2', 'fr');

        $this->member3 = new Orga_Model_Member($this->axis, 'ref_3');
        $this->member1->getLabel()->set('Label 3', 'fr');
    }

    public function testGetMemberByCompleteRef()
    {
        $this->assertSame($this->member1, $this->axis->getMemberByCompleteRef('ref_1'.Orga_Model_Member::COMPLETEREF_JOIN.Orga_Model_Member::buildParentMembersHashKey([])));
        $this->assertSame($this->member2, $this->axis->getMemberByCompleteRef('ref_2'.Orga_Model_Member::COMPLETEREF_JOIN.Orga_Model_Member::buildParentMembersHashKey([])));
        $this->assertSame($this->member3, $this->axis->getMemberByCompleteRef('ref_3'.Orga_Model_Member::COMPLETEREF_JOIN.Orga_Model_Member::buildParentMembersHashKey([])));
    }

    /**
     * @expectedException Core_Exception_NotFound
     * @expectedExceptionMessage No Member matching ref "ref".
     */
    public function testGetMemberByCompleteRefNotFound()
    {
        $this->axis->getMemberByCompleteRef('ref'.Orga_Model_Member::COMPLETEREF_JOIN.Orga_Model_Member::buildParentMembersHashKey([]));
    }

    public function testGetMembers()
    {
        $members = $this->axis->getOrderedMembers()->toArray();
        $this->assertSame($this->member1, $members[0]);
        $this->assertSame($this->member2, $members[1]);
        $this->assertSame($this->member3, $members[2]);

        $this->member2->setRef('ref_0');

        $members = $this->axis->getOrderedMembers();
        $this->assertSame($this->member2, $members[0]);
        $this->assertSame($this->member1, $members[1]);
        $this->assertSame($this->member3, $members[2]);
    }

    public function testGetMembersPositioning()
    {
        $this->axis->setMemberPositioning(true);

        $members = $this->axis->getOrderedMembers()->toArray();
        $this->assertSame($this->member1, $members[0]);
        $this->assertSame($this->member2, $members[1]);
        $this->assertSame($this->member3, $members[2]);

        $this->member2->setRef('ref_0');

        $members = $this->axis->getOrderedMembers();
        $this->assertSame($this->member1, $members[0]);
        $this->assertSame($this->member2, $members[1]);
        $this->assertSame($this->member3, $members[2]);

        $this->member2->setPosition(3);

        $members = $this->axis->getOrderedMembers();
        $this->assertSame($this->member1, $members[0]);
        $this->assertSame($this->member3, $members[1]);
        $this->assertSame($this->member2, $members[2]);

        $this->axis->setMemberPositioning(false);

        $members = $this->axis->getOrderedMembers();
        $this->assertSame($this->member2, $members[0]);
        $this->assertSame($this->member1, $members[1]);
        $this->assertSame($this->member3, $members[2]);
    }

    public function testGetMembersPositioningContextualized()
    {
        $this->axis->setMemberPositioning(true);

        $contextualizingBroaderAxis = new Orga_Model_Axis($this->organization, 'new', $this->axis);
        $contextualizingBroaderAxis->setContextualize(true);

        $memberA = new Orga_Model_Member($contextualizingBroaderAxis, 'ref_a');
        $memberB = new Orga_Model_Member($contextualizingBroaderAxis, 'ref_b');

        $this->member1->setDirectParentForAxis($memberA);
        $this->member2->setDirectParentForAxis($memberB);
        $this->member3->setDirectParentForAxis($memberA);

        $members = $this->axis->getOrderedMembers()->toArray();
        $this->assertSame($this->member1, $members[1]);
        $this->assertSame($this->member2, $members[0]);
        $this->assertSame($this->member3, $members[2]);

        $this->member3->setPosition(1);

        $members = $this->axis->getOrderedMembers()->toArray();
        $this->assertSame($this->member1, $members[2]);
        $this->assertSame($this->member2, $members[0]);
        $this->assertSame($this->member3, $members[1]);
    }
}
