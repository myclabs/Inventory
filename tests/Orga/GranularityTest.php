<?php
use Account\Domain\Account;
use Core\Test\TestCase;
use Doc\Domain\Library;
use DW\Domain\Cube;
use Orga\Domain\Axis;
use Orga\Domain\Cell;
use Orga\Domain\Granularity;
use Orga\Domain\Member;
use Orga\Domain\Workspace;

/**
 * Class Orga_Test_GranularityTest
 * @author valentin.claras
 * @package    Orga
 * @subpackage Test
 */

/**
 * Test Granularity class.
 * @package    Orga
 * @subpackage Test
 */
class Orga_Test_GranularityTest
{
    /**
     * Creation de la suite de test
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Orga_Test_GranularityAttributes');
        $suite->addTestSuite('Orga_Test_GranularityHierarchy');
        $suite->addTestSuite('Orga_Test_GranularityCells');
        return $suite;
    }

}

class Orga_Test_GranularityAttributes extends TestCase
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
    protected $axis12;
    /**
     * @var Axis
     */
    protected $axis2;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->workspace = new Workspace($this->getMockBuilder(Account::class)->disableOriginalConstructor()->getMock());

        $this->axis1 = new Axis($this->workspace, 'ref_1');
        $this->axis1->getLabel()->set('Label 1', 'fr');

        $this->axis11 = new Axis($this->workspace, 'ref_11', $this->axis1);
        $this->axis11->getLabel()->set('Label 11', 'fr');

        $this->axis12 = new Axis($this->workspace, 'ref_12', $this->axis1);
        $this->axis12->getLabel()->set('Label 12', 'fr');

        $this->axis2 = new Axis($this->workspace, 'ref_2');
        $this->axis2->getLabel()->set('Label 2', 'fr');
    }

    function testConstruct()
    {
        $granularity = new Granularity($this->workspace, [$this->axis11, $this->axis12]);
        $this->assertSame($this->workspace, $granularity->getWorkspace());
        $this->assertCount(2, $granularity->getAxes());
        $this->assertTrue($granularity->hasAxis($this->axis11));
        $this->assertTrue($granularity->hasAxis($this->axis12));

        $granularity = new Granularity($this->workspace, [$this->axis2, $this->axis12, $this->axis11, $this->axis2]);
        $this->assertSame($this->workspace, $granularity->getWorkspace());
        $this->assertCount(3, $granularity->getAxes());
        $this->assertTrue($granularity->hasAxis($this->axis11));
        $this->assertTrue($granularity->hasAxis($this->axis12));
        $this->assertTrue($granularity->hasAxis($this->axis2));
    }

    /**
     * @expectedException Core_Exception_InvalidArgument
     * @expectedExceptionMessage Each given Axis must be transverse with each other axes
     */
    function testConstructLinedAxes()
    {
        $granularity = new Granularity($this->workspace, [$this->axis1, $this->axis11]);
    }

    function testGetRef()
    {
        $granularity0 = new Granularity($this->workspace, []);
        $this->assertSame('global', $granularity0->getRef());

        $granularity1 = new Granularity($this->workspace, [$this->axis11, $this->axis12]);
        $this->assertSame('ref_11|ref_12', $granularity1->getRef());

        $granularity2 = new Granularity($this->workspace, [$this->axis2, $this->axis12, $this->axis11, $this->axis2]);
        $this->assertSame('ref_11|ref_12|ref_2', $granularity2->getRef());

        $this->axis11->setRef('ref_11_updated');
        $this->assertSame('ref_11_updated|ref_12', $granularity1->getRef());
        $this->assertSame('ref_11_updated|ref_12|ref_2', $granularity2->getRef());
    }

    function testGetLabel()
    {
        $granularity0 = new Granularity($this->workspace, []);
        $this->assertSame(__('Orga', 'granularity', 'labelGlobalGranularity'), $granularity0->getLabel()->get('fr'));

        $granularity1 = new Granularity($this->workspace, [$this->axis11, $this->axis12]);
        $this->assertSame('Label 11 | Label 12', $granularity1->getLabel()->get('fr'));

        $granularity2 = new Granularity($this->workspace, [$this->axis2, $this->axis12, $this->axis11, $this->axis2]);
        $this->assertSame('Label 11 | Label 12 | Label 2', $granularity2->getLabel()->get('fr'));

        $this->axis11->getLabel()->set('Label 11 updated', 'fr');
        $this->assertSame('Label 11 updated | Label 12', $granularity1->getLabel()->get('fr'));
        $this->assertSame('Label 11 updated | Label 12 | Label 2', $granularity2->getLabel()->get('fr'));
    }

}

class Orga_Test_GranularityHierarchy extends TestCase
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
    protected $axis2;
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
     * @var Granularity
     */
    protected $granularity6;
    /**
     * @var Granularity
     */
    protected $granularity7;

    public function setUp()
    {
        parent::setUp();

        $this->workspace = new Workspace(
            $this->getMockBuilder(Account::class)->disableOriginalConstructor()->getMock()
        );

        $this->axis1 = new Axis($this->workspace, 'ref_1');
        $this->axis1->getLabel()->set('Label 1', 'fr');

        $this->axis11 = new Axis($this->workspace, 'ref_11', $this->axis1);
        $this->axis11->getLabel()->set('Label 11', 'fr');

        $this->axis111 = new Axis($this->workspace, 'ref_111', $this->axis11);
        $this->axis111->getLabel()->set('Label 111', 'fr');

        $this->axis12 = new Axis($this->workspace, 'ref_12', $this->axis1);
        $this->axis12->getLabel()->set('Label 12', 'fr');

        $this->axis2 = new Axis($this->workspace, 'ref_2');
        $this->axis2->getLabel()->set('Label 2', 'fr');

        $this->granularity0 = new Granularity($this->workspace);
        $this->granularity1 = new Granularity($this->workspace, [$this->axis111]);
        $this->granularity2 = new Granularity($this->workspace, [$this->axis11, $this->axis12]);
        $this->granularity3 = new Granularity($this->workspace, [$this->axis11, $this->axis2]);
        $this->granularity4 = new Granularity($this->workspace, [$this->axis12, $this->axis2]);
        $this->granularity5 = new Granularity($this->workspace, [$this->axis11, $this->axis12, $this->axis2]);
        $this->granularity6 = new Granularity($this->workspace, [$this->axis1, $this->axis2]);
        $this->granularity7 = new Granularity($this->workspace, [$this->axis2]);
    }

    function testGetTag()
    {
        $this->assertSame('/', $this->granularity0->getTag());
        $this->assertSame('/1-ref_111/', $this->granularity1->getTag());
        $this->assertSame('/1-ref_111/1-ref_11/&/2-ref_12/', $this->granularity2->getTag());
        $this->assertSame('/1-ref_111/1-ref_11/&/2-ref_2/', $this->granularity3->getTag());
        $this->assertSame('/2-ref_12/&/2-ref_2/', $this->granularity4->getTag());
        $this->assertSame('/1-ref_111/1-ref_11/&/2-ref_12/&/2-ref_2/', $this->granularity5->getTag());
        $this->assertSame('/1-ref_111/1-ref_11/1-ref_1/&/2-ref_12/1-ref_1/&/2-ref_2/', $this->granularity6->getTag());
        $this->assertSame('/2-ref_2/', $this->granularity7->getTag());

        $this->axis11->setRef('ref_11_updated');

        $this->assertSame('/', $this->granularity0->getTag());
        $this->assertSame('/1-ref_111/', $this->granularity1->getTag());
        $this->assertSame('/1-ref_111/1-ref_11_updated/&/2-ref_12/', $this->granularity2->getTag());
        $this->assertSame('/1-ref_111/1-ref_11_updated/&/2-ref_2/', $this->granularity3->getTag());
        $this->assertSame('/2-ref_12/&/2-ref_2/', $this->granularity4->getTag());
        $this->assertSame('/1-ref_111/1-ref_11_updated/&/2-ref_12/&/2-ref_2/', $this->granularity5->getTag());
        $this->assertSame('/1-ref_111/1-ref_11_updated/1-ref_1/&/2-ref_12/1-ref_1/&/2-ref_2/', $this->granularity6->getTag());
        $this->assertSame('/2-ref_2/', $this->granularity7->getTag());

        $this->axis12->setPosition(1);
        $this->axis2->setPosition(1);

        $this->assertSame('/', $this->granularity0->getTag());
        $this->assertSame('/1-ref_111/', $this->granularity1->getTag());
        $this->assertSame('/1-ref_12/&/1-ref_111/2-ref_11_updated/', $this->granularity2->getTag());
        $this->assertSame('/1-ref_2/&/1-ref_111/2-ref_11_updated/', $this->granularity3->getTag());
        $this->assertSame('/1-ref_2/&/1-ref_12/', $this->granularity4->getTag());
        $this->assertSame('/1-ref_2/&/1-ref_12/&/1-ref_111/2-ref_11_updated/', $this->granularity5->getTag());
        $this->assertSame('/1-ref_2/&/1-ref_12/2-ref_1/&/1-ref_111/2-ref_11_updated/2-ref_1/', $this->granularity6->getTag());
        $this->assertSame('/1-ref_2/', $this->granularity7->getTag());
    }

    function testIsNarrowerThan()
    {
        $this->assertFalse($this->granularity0->isNarrowerThan($this->granularity0));
        $this->assertFalse($this->granularity0->isNarrowerThan($this->granularity1));
        $this->assertFalse($this->granularity0->isNarrowerThan($this->granularity2));
        $this->assertFalse($this->granularity0->isNarrowerThan($this->granularity3));
        $this->assertFalse($this->granularity0->isNarrowerThan($this->granularity4));
        $this->assertFalse($this->granularity0->isNarrowerThan($this->granularity5));
        $this->assertFalse($this->granularity0->isNarrowerThan($this->granularity6));
        $this->assertFalse($this->granularity0->isNarrowerThan($this->granularity7));

        $this->assertTrue($this->granularity1->isNarrowerThan($this->granularity0));
        $this->assertFalse($this->granularity1->isNarrowerThan($this->granularity1));
        $this->assertFalse($this->granularity1->isNarrowerThan($this->granularity2));
        $this->assertFalse($this->granularity1->isNarrowerThan($this->granularity3));
        $this->assertFalse($this->granularity1->isNarrowerThan($this->granularity4));
        $this->assertFalse($this->granularity1->isNarrowerThan($this->granularity5));
        $this->assertFalse($this->granularity1->isNarrowerThan($this->granularity6));
        $this->assertFalse($this->granularity1->isNarrowerThan($this->granularity7));

        $this->assertTrue($this->granularity2->isNarrowerThan($this->granularity0));
        $this->assertTrue($this->granularity2->isNarrowerThan($this->granularity1));
        $this->assertFalse($this->granularity2->isNarrowerThan($this->granularity2));
        $this->assertFalse($this->granularity2->isNarrowerThan($this->granularity3));
        $this->assertFalse($this->granularity2->isNarrowerThan($this->granularity4));
        $this->assertFalse($this->granularity2->isNarrowerThan($this->granularity5));
        $this->assertFalse($this->granularity2->isNarrowerThan($this->granularity6));
        $this->assertFalse($this->granularity2->isNarrowerThan($this->granularity7));

        $this->assertTrue($this->granularity3->isNarrowerThan($this->granularity0));
        $this->assertTrue($this->granularity3->isNarrowerThan($this->granularity1));
        $this->assertFalse($this->granularity3->isNarrowerThan($this->granularity2));
        $this->assertFalse($this->granularity3->isNarrowerThan($this->granularity3));
        $this->assertFalse($this->granularity3->isNarrowerThan($this->granularity4));
        $this->assertFalse($this->granularity3->isNarrowerThan($this->granularity5));
        $this->assertFalse($this->granularity3->isNarrowerThan($this->granularity6));
        $this->assertTrue($this->granularity3->isNarrowerThan($this->granularity7));

        $this->assertTrue($this->granularity4->isNarrowerThan($this->granularity0));
        $this->assertFalse($this->granularity4->isNarrowerThan($this->granularity1));
        $this->assertFalse($this->granularity4->isNarrowerThan($this->granularity2));
        $this->assertFalse($this->granularity4->isNarrowerThan($this->granularity3));
        $this->assertFalse($this->granularity4->isNarrowerThan($this->granularity4));
        $this->assertFalse($this->granularity4->isNarrowerThan($this->granularity5));
        $this->assertFalse($this->granularity4->isNarrowerThan($this->granularity6));
        $this->assertTrue($this->granularity4->isNarrowerThan($this->granularity7));

        $this->assertTrue($this->granularity5->isNarrowerThan($this->granularity0));
        $this->assertTrue($this->granularity5->isNarrowerThan($this->granularity1));
        $this->assertTrue($this->granularity5->isNarrowerThan($this->granularity2));
        $this->assertTrue($this->granularity5->isNarrowerThan($this->granularity3));
        $this->assertTrue($this->granularity5->isNarrowerThan($this->granularity4));
        $this->assertFalse($this->granularity5->isNarrowerThan($this->granularity5));
        $this->assertFalse($this->granularity5->isNarrowerThan($this->granularity6));
        $this->assertTrue($this->granularity5->isNarrowerThan($this->granularity7));

        $this->assertTrue($this->granularity6->isNarrowerThan($this->granularity0));
        $this->assertTrue($this->granularity6->isNarrowerThan($this->granularity1));
        $this->assertTrue($this->granularity6->isNarrowerThan($this->granularity2));
        $this->assertTrue($this->granularity6->isNarrowerThan($this->granularity3));
        $this->assertTrue($this->granularity6->isNarrowerThan($this->granularity4));
        $this->assertTrue($this->granularity6->isNarrowerThan($this->granularity5));
        $this->assertFalse($this->granularity6->isNarrowerThan($this->granularity6));
        $this->assertTrue($this->granularity6->isNarrowerThan($this->granularity7));

        $this->assertTrue($this->granularity7->isNarrowerThan($this->granularity0));
        $this->assertFalse($this->granularity7->isNarrowerThan($this->granularity1));
        $this->assertFalse($this->granularity7->isNarrowerThan($this->granularity2));
        $this->assertFalse($this->granularity7->isNarrowerThan($this->granularity3));
        $this->assertFalse($this->granularity7->isNarrowerThan($this->granularity4));
        $this->assertFalse($this->granularity7->isNarrowerThan($this->granularity5));
        $this->assertFalse($this->granularity7->isNarrowerThan($this->granularity6));
        $this->assertFalse($this->granularity7->isNarrowerThan($this->granularity7));
    }

    function testIsBroaderThan()
    {
        $this->assertFalse($this->granularity0->isBroaderThan($this->granularity0));
        $this->assertTrue($this->granularity0->isBroaderThan($this->granularity1));
        $this->assertTrue($this->granularity0->isBroaderThan($this->granularity2));
        $this->assertTrue($this->granularity0->isBroaderThan($this->granularity3));
        $this->assertTrue($this->granularity0->isBroaderThan($this->granularity4));
        $this->assertTrue($this->granularity0->isBroaderThan($this->granularity5));
        $this->assertTrue($this->granularity0->isBroaderThan($this->granularity6));
        $this->assertTrue($this->granularity0->isBroaderThan($this->granularity7));

        $this->assertFalse($this->granularity1->isBroaderThan($this->granularity0));
        $this->assertFalse($this->granularity1->isBroaderThan($this->granularity1));
        $this->assertTrue($this->granularity1->isBroaderThan($this->granularity2));
        $this->assertTrue($this->granularity1->isBroaderThan($this->granularity3));
        $this->assertFalse($this->granularity1->isBroaderThan($this->granularity4));
        $this->assertTrue($this->granularity1->isBroaderThan($this->granularity5));
        $this->assertTrue($this->granularity1->isBroaderThan($this->granularity6));
        $this->assertFalse($this->granularity1->isBroaderThan($this->granularity7));

        $this->assertFalse($this->granularity2->isBroaderThan($this->granularity0));
        $this->assertFalse($this->granularity2->isBroaderThan($this->granularity1));
        $this->assertFalse($this->granularity2->isBroaderThan($this->granularity2));
        $this->assertFalse($this->granularity2->isBroaderThan($this->granularity3));
        $this->assertFalse($this->granularity2->isBroaderThan($this->granularity4));
        $this->assertTrue($this->granularity2->isBroaderThan($this->granularity5));
        $this->assertTrue($this->granularity2->isBroaderThan($this->granularity6));
        $this->assertFalse($this->granularity2->isBroaderThan($this->granularity7));

        $this->assertFalse($this->granularity3->isBroaderThan($this->granularity0));
        $this->assertFalse($this->granularity3->isBroaderThan($this->granularity1));
        $this->assertFalse($this->granularity3->isBroaderThan($this->granularity2));
        $this->assertFalse($this->granularity3->isBroaderThan($this->granularity3));
        $this->assertFalse($this->granularity3->isBroaderThan($this->granularity4));
        $this->assertTrue($this->granularity3->isBroaderThan($this->granularity5));
        $this->assertTrue($this->granularity3->isBroaderThan($this->granularity6));
        $this->assertFalse($this->granularity3->isBroaderThan($this->granularity7));

        $this->assertFalse($this->granularity4->isBroaderThan($this->granularity0));
        $this->assertFalse($this->granularity4->isBroaderThan($this->granularity1));
        $this->assertFalse($this->granularity4->isBroaderThan($this->granularity2));
        $this->assertFalse($this->granularity4->isBroaderThan($this->granularity3));
        $this->assertFalse($this->granularity4->isBroaderThan($this->granularity4));
        $this->assertTrue($this->granularity4->isBroaderThan($this->granularity5));
        $this->assertTrue($this->granularity4->isBroaderThan($this->granularity6));
        $this->assertFalse($this->granularity4->isBroaderThan($this->granularity7));

        $this->assertFalse($this->granularity5->isBroaderThan($this->granularity0));
        $this->assertFalse($this->granularity5->isBroaderThan($this->granularity1));
        $this->assertFalse($this->granularity5->isBroaderThan($this->granularity2));
        $this->assertFalse($this->granularity5->isBroaderThan($this->granularity3));
        $this->assertFalse($this->granularity5->isBroaderThan($this->granularity4));
        $this->assertFalse($this->granularity5->isBroaderThan($this->granularity5));
        $this->assertTrue($this->granularity5->isBroaderThan($this->granularity6));
        $this->assertFalse($this->granularity5->isBroaderThan($this->granularity7));

        $this->assertFalse($this->granularity6->isBroaderThan($this->granularity0));
        $this->assertFalse($this->granularity6->isBroaderThan($this->granularity1));
        $this->assertFalse($this->granularity6->isBroaderThan($this->granularity2));
        $this->assertFalse($this->granularity6->isBroaderThan($this->granularity3));
        $this->assertFalse($this->granularity6->isBroaderThan($this->granularity4));
        $this->assertFalse($this->granularity6->isBroaderThan($this->granularity5));
        $this->assertFalse($this->granularity6->isBroaderThan($this->granularity6));
        $this->assertFalse($this->granularity6->isBroaderThan($this->granularity7));

        $this->assertFalse($this->granularity7->isBroaderThan($this->granularity0));
        $this->assertFalse($this->granularity7->isBroaderThan($this->granularity1));
        $this->assertFalse($this->granularity7->isBroaderThan($this->granularity2));
        $this->assertTrue($this->granularity7->isBroaderThan($this->granularity3));
        $this->assertTrue($this->granularity7->isBroaderThan($this->granularity4));
        $this->assertTrue($this->granularity7->isBroaderThan($this->granularity5));
        $this->assertTrue($this->granularity7->isBroaderThan($this->granularity6));
        $this->assertFalse($this->granularity7->isBroaderThan($this->granularity7));
    }

    function testGetNarrowerGranularities()
    {
        $this->assertSame([$this->granularity7, $this->granularity4, $this->granularity1, $this->granularity3, $this->granularity2, $this->granularity5, $this->granularity6], $this->granularity0->getNarrowerGranularities());
        $this->assertSame([$this->granularity3, $this->granularity2, $this->granularity5, $this->granularity6], $this->granularity1->getNarrowerGranularities());
        $this->assertSame([$this->granularity5, $this->granularity6], $this->granularity2->getNarrowerGranularities());
        $this->assertSame([$this->granularity5, $this->granularity6], $this->granularity3->getNarrowerGranularities());
        $this->assertSame([$this->granularity5, $this->granularity6], $this->granularity4->getNarrowerGranularities());
        $this->assertSame([$this->granularity6], $this->granularity5->getNarrowerGranularities());
        $this->assertSame([], $this->granularity6->getNarrowerGranularities());
        $this->assertSame([$this->granularity4, $this->granularity3, $this->granularity5, $this->granularity6], $this->granularity7->getNarrowerGranularities());
    }

    function testGetBroaderGranularities()
    {
        $this->assertSame([], $this->granularity0->getBroaderGranularities());
        $this->assertSame([$this->granularity0], $this->granularity1->getBroaderGranularities());
        $this->assertSame([$this->granularity0, $this->granularity1], $this->granularity2->getBroaderGranularities());
        $this->assertSame([$this->granularity0, $this->granularity7, $this->granularity1,], $this->granularity3->getBroaderGranularities());
        $this->assertSame([$this->granularity0, $this->granularity7], $this->granularity4->getBroaderGranularities());
        $this->assertSame([$this->granularity0, $this->granularity7, $this->granularity4, $this->granularity1, $this->granularity3, $this->granularity2,], $this->granularity5->getBroaderGranularities());
        $this->assertSame([$this->granularity0, $this->granularity7, $this->granularity4, $this->granularity1, $this->granularity3, $this->granularity2, $this->granularity5], $this->granularity6->getBroaderGranularities());
        $this->assertSame([$this->granularity0], $this->granularity7->getBroaderGranularities());
    }

}

class Orga_Test_GranularityCells extends TestCase
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
    protected $axis2;
    /**
     * @var Member
     */
    protected $member1a;
    /**
     * @var Member
     */
    protected $member1b;
    /**
     * @var Member
     */
    protected $member1c;
    /**
     * @var Member
     */
    protected $member1d;
    /**
     * @var Member
     */
    protected $member1e;
    /**
     * @var Member
     */
    protected $member1f;
    /**
     * @var Member
     */
    protected $member11a;
    /**
     * @var Member
     */
    protected $member11b;
    /**
     * @var Member
     */
    protected $member11c;
    /**
     * @var Member
     */
    protected $member111a;
    /**
     * @var Member
     */
    protected $member111b;
    /**
     * @var Member
     */
    protected $member12a;
    /**
     * @var Member
     */
    protected $member12b;
    /**
     * @var Member
     */
    protected $member2a;
    /**
     * @var Member
     */
    protected $member2b;

    public function setUp()
    {
        parent::setUp();

        $this->workspace = new Workspace($this->getMockBuilder(Account::class)->disableOriginalConstructor()->getMock());

        $this->axis1 = new Axis($this->workspace, 'ref_1');
        $this->axis1->getLabel()->set('Label 1', 'fr');

        $this->axis11 = new Axis($this->workspace, 'ref_11', $this->axis1);
        $this->axis11->getLabel()->set('Label 11', 'fr');

        $this->axis111 = new Axis($this->workspace, 'ref_111', $this->axis11);
        $this->axis111->getLabel()->set('Label 111', 'fr');

        $this->axis12 = new Axis($this->workspace, 'ref_12', $this->axis1);
        $this->axis12->getLabel()->set('Label 12', 'fr');

        $this->axis2 = new Axis($this->workspace, 'ref_2');
        $this->axis2->getLabel()->set('Label 2', 'fr');

        $this->member111a = new Member($this->axis111, 'ref111_a');
        $this->member111b = new Member($this->axis111, 'ref111_b');

        $this->member11a = new Member($this->axis11, 'ref11_a', [$this->member111a]);
        $this->member11b = new Member($this->axis11, 'ref11_b', [$this->member111b]);
        $this->member11c = new Member($this->axis11, 'ref11_c', [$this->member111b]);

        $this->member12a = new Member($this->axis12, 'ref12_a');
        $this->member12b = new Member($this->axis12, 'ref12_b');

        $this->member1a = new Member($this->axis1, 'ref1_a', [$this->member11a, $this->member12a]);
        $this->member1b = new Member($this->axis1, 'ref1_b', [$this->member11a, $this->member12b]);
        $this->member1c = new Member($this->axis1, 'ref1_c', [$this->member11b, $this->member12a]);
        $this->member1d = new Member($this->axis1, 'ref1_d', [$this->member11b, $this->member12b]);
        $this->member1e = new Member($this->axis1, 'ref1_e', [$this->member11c, $this->member12a]);
        $this->member1f = new Member($this->axis1, 'ref1_f', [$this->member11c, $this->member12b]);

        $this->member2a = new Member($this->axis2, 'ref2_a');
        $this->member2b = new Member($this->axis2, 'ref2_b');
    }

    public function testGenerateCells()
    {
        $granularity0 = new Granularity($this->workspace, []);

        $granularity0Cells = $granularity0->getOrderedCells();
        $this->assertCount(1, $granularity0Cells);
        $this->assertSame([], $granularity0Cells[0]->getMembers());

        $granularity1 = new Granularity($this->workspace, [$this->axis111]);

        $granularity1Cells = $granularity1->getOrderedCells();
        $this->assertCount(2, $granularity1Cells);
        $this->assertSame([$this->member111a], $granularity1Cells[0]->getMembers());
        $this->assertSame([$this->member111b], $granularity1Cells[1]->getMembers());

        $granularity2 = new Granularity($this->workspace, [$this->axis11, $this->axis2]);

        $granularity2Cells = $granularity2->getOrderedCells();
        $this->assertCount(6, $granularity2Cells);
        $this->assertSame([$this->member11a, $this->member2a], $granularity2Cells[0]->getMembers());
        $this->assertSame([$this->member11b, $this->member2a], $granularity2Cells[2]->getMembers());
        $this->assertSame([$this->member11c, $this->member2a], $granularity2Cells[4]->getMembers());
        $this->assertSame([$this->member11a, $this->member2b], $granularity2Cells[1]->getMembers());
        $this->assertSame([$this->member11b, $this->member2b], $granularity2Cells[3]->getMembers());
        $this->assertSame([$this->member11c, $this->member2b], $granularity2Cells[5]->getMembers());

        $granularity3 = new Granularity($this->workspace, [$this->axis1, $this->axis2]);

        $granularity3Cells = $granularity3->getOrderedCells();
        $this->assertCount(12, $granularity3Cells);
        $this->assertSame([$this->member1a, $this->member2a], $granularity3Cells[0]->getMembers());
        $this->assertSame([$this->member1b, $this->member2a], $granularity3Cells[2]->getMembers());
        $this->assertSame([$this->member1c, $this->member2a], $granularity3Cells[4]->getMembers());
        $this->assertSame([$this->member1d, $this->member2a], $granularity3Cells[6]->getMembers());
        $this->assertSame([$this->member1e, $this->member2a], $granularity3Cells[8]->getMembers());
        $this->assertSame([$this->member1f, $this->member2a], $granularity3Cells[10]->getMembers());
        $this->assertSame([$this->member1a, $this->member2b], $granularity3Cells[1]->getMembers());
        $this->assertSame([$this->member1b, $this->member2b], $granularity3Cells[3]->getMembers());
        $this->assertSame([$this->member1c, $this->member2b], $granularity3Cells[5]->getMembers());
        $this->assertSame([$this->member1d, $this->member2b], $granularity3Cells[7]->getMembers());
        $this->assertSame([$this->member1e, $this->member2b], $granularity3Cells[9]->getMembers());
        $this->assertSame([$this->member1f, $this->member2b], $granularity3Cells[11]->getMembers());
    }

    public function testGetCellsByMembers()
    {
        $granularity = new Granularity($this->workspace, [$this->axis11, $this->axis2]);

        $granularityCells2a = $granularity->getCellsByMembers([$this->member2a]);
        $this->assertCount(3, $granularityCells2a);
        $this->assertSame([$this->member11a, $this->member2a], $granularityCells2a[0]->getMembers());
        $this->assertSame([$this->member11b, $this->member2a], $granularityCells2a[1]->getMembers());
        $this->assertSame([$this->member11c, $this->member2a], $granularityCells2a[2]->getMembers());

        $granularityCells11a11c = $granularity->getCellsByMembers([$this->member11a, $this->member11c]);
        $this->assertCount(4, $granularityCells11a11c);
        $this->assertSame([$this->member11a, $this->member2a], $granularityCells11a11c[0]->getMembers());
        $this->assertSame([$this->member11c, $this->member2a], $granularityCells11a11c[2]->getMembers());
        $this->assertSame([$this->member11a, $this->member2b], $granularityCells11a11c[1]->getMembers());
        $this->assertSame([$this->member11c, $this->member2b], $granularityCells11a11c[3]->getMembers());

        $granularityCells11a11c2b = $granularity->getCellsByMembers([$this->member11a, $this->member11c, $this->member2b]);
        $this->assertCount(2, $granularityCells11a11c2b);
        $this->assertSame([$this->member11a, $this->member2b], $granularityCells11a11c2b[0]->getMembers());
        $this->assertSame([$this->member11c, $this->member2b], $granularityCells11a11c2b[1]->getMembers());

        $granularityCells111a = $granularity->getCellsByMembers([$this->member111a]);
        $this->assertCount(2, $granularityCells111a);
        $this->assertSame([$this->member11a, $this->member2a], $granularityCells111a[0]->getMembers());
        $this->assertSame([$this->member11a, $this->member2b], $granularityCells111a[1]->getMembers());
    }

    public function testGetCellByMembers()
    {
        $granularity = new Granularity($this->workspace, [$this->axis11, $this->axis2]);

        $cells11a2a = $granularity->getCellByMembers([$this->member11a, $this->member2a]);
        $this->assertInstanceOf(Cell::class, $cells11a2a);
        $this->assertSame($granularity, $cells11a2a->getGranularity());
        $this->assertCount(2, $cells11a2a->getMembers());
        $this->assertTrue($cells11a2a->hasMember($this->member11a));
        $this->assertTrue($cells11a2a->hasMember($this->member2a));

        $cells11c2b = $granularity->getCellByMembers([$this->member11c, $this->member2b]);
        $this->assertInstanceOf(Cell::class, $cells11c2b);
        $this->assertSame($granularity, $cells11c2b->getGranularity());
        $this->assertCount(2, $cells11c2b->getMembers());
        $this->assertTrue($cells11c2b->hasMember($this->member11c));
        $this->assertTrue($cells11c2b->hasMember($this->member2b));
    }


    /**
     * @expectedException Core_Exception_NotFound
     * @expectedExceptionMessage No Cell matching members "ref1_a" for "ref_11|ref_2".
     */
    public function testGetCellByMembersNotFound()
    {
        $granularity = new Granularity($this->workspace, [$this->axis11, $this->axis2]);

        $cell1a = $granularity->getCellByMembers([$this->member1a]);
    }

    /**
     * @expectedException Core_Exception_TooMany
     * @expectedExceptionMessage Too many Cell matching members "ref11_a" for "ref_11|ref_2".
     */
    public function testGetCellByMembersTooMany()
    {
        $granularity = new Granularity($this->workspace, [$this->axis11, $this->axis2]);

        $cell111a = $granularity->getCellByMembers([$this->member11a]);
    }

    public function testSetCellsControlRelevance()
    {
        $granularity = new Granularity($this->workspace, [$this->axis11, $this->axis2]);
        $cells11a2a = $granularity->getCellByMembers([$this->member11a, $this->member2a]);

        $this->assertFalse($granularity->getCellsControlRelevance());

        $granularity->setCellsControlRelevance(true);
        $this->assertTrue($granularity->getCellsControlRelevance());

        $cells11a2a->setRelevant(false);
        $this->assertFalse($cells11a2a->getRelevant());

        $granularity->setCellsControlRelevance(false);
        $this->assertFalse($granularity->getCellsControlRelevance());

        $this->assertTrue($cells11a2a->getRelevant());
    }

    public function testSetInputConfigGranularity()
    {
        $granularityInput = new Granularity($this->workspace, [$this->axis11, $this->axis2]);
        $granularityConfig = new Granularity($this->workspace, [$this->axis2]);
        $cells11a2a = $granularityInput->getCellByMembers([$this->member11a, $this->member2a]);

        $this->assertFalse($granularityInput->isInput());
        try {
            $cells11a2a->getDocLibraryForAFInputSetPrimary();
        } catch (Core_Exception_UndefinedAttribute $e) {
            $this->assertEquals('The Doc library for the cell has not be set.', $e->getMessage());
        }

        $granularityInput->setInputConfigGranularity($granularityConfig);

        $this->assertTrue($granularityInput->isInput());
        $this->assertInstanceOf(Library::class, $cells11a2a->getDocLibraryForAFInputSetPrimary());

        $granularityInput->setInputConfigGranularity();

        $this->assertFalse($granularityInput->isInput());
        try {
            $cells11a2a->getDocLibraryForAFInputSetPrimary();
        } catch (Core_Exception_UndefinedAttribute $e) {
            $this->assertEquals('The Doc library for the cell has not be set.', $e->getMessage());
        }
    }

    /**
     * @expectedException Core_Exception_InvalidArgument
     * @expectedExceptionMessage The config Granularity needs to be broader than this Granularity.
     */
    public function testSetInputConfigGranularityNotBroader()
    {
        $granularityInput = new Granularity($this->workspace, [$this->axis1]);
        $granularityConfig = new Granularity($this->workspace, [$this->axis2]);

        $granularityInput->setInputConfigGranularity($granularityConfig);
    }

    public function testSetCellsGenerateDWCubes()
    {
        $granularity = new Granularity($this->workspace, [$this->axis11, $this->axis2]);
        $cells11a2a = $granularity->getCellByMembers([$this->member11a, $this->member2a]);

        $this->assertFalse($granularity->getCellsGenerateDWCubes());
        try {
            $granularity->getDWCube();
        } catch (Core_Exception_UndefinedAttribute $e) {
            $this->assertEquals('The Granularity does not generate DW Cube.', $e->getMessage());
        }
        try {
            $cells11a2a->getDWCube();
        } catch (Core_Exception_UndefinedAttribute $e) {
            $this->assertEquals('DW Cube has not be defined.', $e->getMessage());
        }

        $granularity->setCellsGenerateDWCubes(true);

        $this->assertTrue($granularity->getCellsGenerateDWCubes());
        $this->assertInstanceOf(Cube::class, $cells11a2a->getDWCube());

        $granularity->setCellsGenerateDWCubes(false);

        $this->assertFalse($granularity->getCellsGenerateDWCubes());
        try {
            $granularity->getDWCube();
        } catch (Core_Exception_UndefinedAttribute $e) {
            $this->assertEquals('The Granularity does not generate DW Cube.', $e->getMessage());
        }
        try {
            $cells11a2a->getDWCube();
        } catch (Core_Exception_UndefinedAttribute $e) {
            $this->assertEquals('DW Cube has not be defined.', $e->getMessage());
        }
    }

}
