<?php
use Account\Domain\Account;
use Core\Test\TestCase;
use Orga\Domain\Axis;
use Orga\Domain\Cell;
use Orga\Domain\Granularity;
use Orga\Domain\Member;
use Orga\Domain\Workspace;

/**
 * Class Orga_Test_CellTest
 * @author valentin.claras
 * @package    Orga
 * @subpackage Test
 */

/**
 * Creation de la suite de test
 * @package    Orga
 * @subpackage Test
 */
class Orga_Test_CellTest
{

    /**
     * Creation de la suite de test
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Orga_Test_CellAttributes');
        $suite->addTestSuite('Orga_Test_CellHierarchy');
        $suite->addTestSuite('Orga_Test_CellAdjacent');
        return $suite;
    }

}


/**
 * Tests de la classe Workspace
 * @package Workspace
 * @subpackage Test
 */
class Orga_Test_CellAttributes extends TestCase
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
     * @var Cell
     */
    protected $cell0_0;
    /**
     * @var Cell
     */
    protected $cell1_111a;
    /**
     * @var Cell
     */
    protected $cell1_111b;
    /**
     * @var Cell
     */
    protected $cell2_11a12a;
    /**
     * @var Cell
     */
    protected $cell2_11b12a;
    /**
     * @var Cell
     */
    protected $cell2_11c12a;
    /**
     * @var Cell
     */
    protected $cell2_11a12b;
    /**
     * @var Cell
     */
    protected $cell2_11b12b;
    /**
     * @var Cell
     */
    protected $cell2_11c12b;
    /**
     * @var Cell
     */
    protected $cell3_11a12a2a;
    /**
     * @var Cell
     */
    protected $cell3_11b12a2a;
    /**
     * @var Cell
     */
    protected $cell3_11c12a2a;
    /**
     * @var Cell
     */
    protected $cell3_11a12b2a;
    /**
     * @var Cell
     */
    protected $cell3_11b12b2a;
    /**
     * @var Cell
     */
    protected $cell3_11c12b2a;
    /**
     * @var Cell
     */
    protected $cell3_11a12a2b;
    /**
     * @var Cell
     */
    protected $cell3_11b12a2b;
    /**
     * @var Cell
     */
    protected $cell3_11c12a2b;
    /**
     * @var Cell
     */
    protected $cell3_11a12b2b;
    /**
     * @var Cell
     */
    protected $cell3_11b12b2b;
    /**
     * @var Cell
     */
    protected $cell3_11c12b2b;
    /**
     * @var Cell
     */
    protected $cell4_1a2a;
    /**
     * @var Cell
     */
    protected $cell4_1b2a;
    /**
     * @var Cell
     */
    protected $cell4_1c2a;
    /**
     * @var Cell
     */
    protected $cell4_1d2a;
    /**
     * @var Cell
     */
    protected $cell4_1e2a;
    /**
     * @var Cell
     */
    protected $cell4_1f2a;
    /**
     * @var Cell
     */
    protected $cell4_1a2b;
    /**
     * @var Cell
     */
    protected $cell4_1b2b;
    /**
     * @var Cell
     */
    protected $cell4_1c2b;
    /**
     * @var Cell
     */
    protected $cell4_1d2b;
    /**
     * @var Cell
     */
    protected $cell4_1e2b;
    /**
     * @var Cell
     */
    protected $cell4_1f2b;
    /**
     * @var Cell
     */
    protected $cell5_2a;
    /**
     * @var Cell
     */
    protected $cell5_2b;

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

        $this->member111a = new Member($this->axis111, 'ref111_a');
        $this->member111a->getLabel()->set('Label 111 A', 'fr');
        $this->member111b = new Member($this->axis111, 'ref111_b');
        $this->member111b->getLabel()->set('Label 111 B', 'fr');

        $this->member11a = new Member($this->axis11, 'ref11_a', [$this->member111a]);
        $this->member11a->getLabel()->set('Label 11 A', 'fr');
        $this->member11b = new Member($this->axis11, 'ref11_b', [$this->member111b]);
        $this->member11b->getLabel()->set('Label 11 B', 'fr');
        $this->member11c = new Member($this->axis11, 'ref11_c', [$this->member111b]);
        $this->member11c->getLabel()->set('Label 11 C', 'fr');

        $this->member12a = new Member($this->axis12, 'ref12_a');
        $this->member12a->getLabel()->set('Label 12 A', 'fr');
        $this->member12b = new Member($this->axis12, 'ref12_b');
        $this->member12b->getLabel()->set('Label 12 B', 'fr');

        $this->member1a = new Member($this->axis1, 'ref1_a', [$this->member11a, $this->member12a]);
        $this->member1a->getLabel()->set('Label 1 A', 'fr');
        $this->member1b = new Member($this->axis1, 'ref1_b', [$this->member11a, $this->member12b]);
        $this->member1b->getLabel()->set('Label 1 B', 'fr');
        $this->member1c = new Member($this->axis1, 'ref1_c', [$this->member11b, $this->member12a]);
        $this->member1c->getLabel()->set('Label 1 C', 'fr');
        $this->member1d = new Member($this->axis1, 'ref1_d', [$this->member11b, $this->member12b]);
        $this->member1d->getLabel()->set('Label 1 D', 'fr');
        $this->member1e = new Member($this->axis1, 'ref1_e', [$this->member11c, $this->member12a]);
        $this->member1e->getLabel()->set('Label 1 E', 'fr');
        $this->member1f = new Member($this->axis1, 'ref1_f', [$this->member11c, $this->member12b]);
        $this->member1f->getLabel()->set('Label 1 F', 'fr');

        $this->member2a = new Member($this->axis2, 'ref2_a');
        $this->member2a->getLabel()->set('Label 2 A', 'fr');
        $this->member2b = new Member($this->axis2, 'ref2_b');
        $this->member2b->getLabel()->set('Label 2 B', 'fr');

        $this->granularity0 = new Granularity($this->workspace, []);
        $this->granularity0->setCellsControlRelevance(true);
        $this->granularity1 = new Granularity($this->workspace, [$this->axis111]);
        $this->granularity1->setCellsControlRelevance(true);
        $this->granularity2 = new Granularity($this->workspace, [$this->axis11, $this->axis12]);
        $this->granularity2->setCellsControlRelevance(true);
        $this->granularity3 = new Granularity($this->workspace, [$this->axis11, $this->axis12, $this->axis2]);
        $this->granularity3->setCellsControlRelevance(true);
        $this->granularity4 = new Granularity($this->workspace, [$this->axis1, $this->axis2]);
        $this->granularity4->setCellsControlRelevance(true);
        $this->granularity5 = new Granularity($this->workspace, [$this->axis2]);
        $this->granularity5->setCellsControlRelevance(true);

        $this->cell0_0 = $this->granularity0->getCellByMembers([]);

        $this->cell1_111a = $this->granularity1->getCellByMembers([$this->member111a]);
        $this->cell1_111b = $this->granularity1->getCellByMembers([$this->member111b]);

        $this->cell2_11a12a = $this->granularity2->getCellByMembers([$this->member11a, $this->member12a]);
        $this->cell2_11b12a = $this->granularity2->getCellByMembers([$this->member11b, $this->member12a]);
        $this->cell2_11c12a = $this->granularity2->getCellByMembers([$this->member11c, $this->member12a]);
        $this->cell2_11a12b = $this->granularity2->getCellByMembers([$this->member11a, $this->member12b]);
        $this->cell2_11b12b = $this->granularity2->getCellByMembers([$this->member11b, $this->member12b]);
        $this->cell2_11c12b = $this->granularity2->getCellByMembers([$this->member11c, $this->member12b]);

        $this->cell3_11a12a2a = $this->granularity3->getCellByMembers([$this->member11a, $this->member12a, $this->member2a]);
        $this->cell3_11b12a2a = $this->granularity3->getCellByMembers([$this->member11b, $this->member12a, $this->member2a]);
        $this->cell3_11c12a2a = $this->granularity3->getCellByMembers([$this->member11c, $this->member12a, $this->member2a]);
        $this->cell3_11a12b2a = $this->granularity3->getCellByMembers([$this->member11a, $this->member12b, $this->member2a]);
        $this->cell3_11b12b2a = $this->granularity3->getCellByMembers([$this->member11b, $this->member12b, $this->member2a]);
        $this->cell3_11c12b2a = $this->granularity3->getCellByMembers([$this->member11c, $this->member12b, $this->member2a]);
        $this->cell3_11a12a2b = $this->granularity3->getCellByMembers([$this->member11a, $this->member12a, $this->member2b]);
        $this->cell3_11b12a2b = $this->granularity3->getCellByMembers([$this->member11b, $this->member12a, $this->member2b]);
        $this->cell3_11c12a2b = $this->granularity3->getCellByMembers([$this->member11c, $this->member12a, $this->member2b]);
        $this->cell3_11a12b2b = $this->granularity3->getCellByMembers([$this->member11a, $this->member12b, $this->member2b]);
        $this->cell3_11b12b2b = $this->granularity3->getCellByMembers([$this->member11b, $this->member12b, $this->member2b]);
        $this->cell3_11c12b2b = $this->granularity3->getCellByMembers([$this->member11c, $this->member12b, $this->member2b]);

        $this->cell4_1a2a = $this->granularity4->getCellByMembers([$this->member1a, $this->member2a]);
        $this->cell4_1b2a = $this->granularity4->getCellByMembers([$this->member1b, $this->member2a]);
        $this->cell4_1c2a = $this->granularity4->getCellByMembers([$this->member1c, $this->member2a]);
        $this->cell4_1d2a = $this->granularity4->getCellByMembers([$this->member1d, $this->member2a]);
        $this->cell4_1e2a = $this->granularity4->getCellByMembers([$this->member1e, $this->member2a]);
        $this->cell4_1f2a = $this->granularity4->getCellByMembers([$this->member1f, $this->member2a]);
        $this->cell4_1a2b = $this->granularity4->getCellByMembers([$this->member1a, $this->member2b]);
        $this->cell4_1b2b = $this->granularity4->getCellByMembers([$this->member1b, $this->member2b]);
        $this->cell4_1c2b = $this->granularity4->getCellByMembers([$this->member1c, $this->member2b]);
        $this->cell4_1d2b = $this->granularity4->getCellByMembers([$this->member1d, $this->member2b]);
        $this->cell4_1e2b = $this->granularity4->getCellByMembers([$this->member1e, $this->member2b]);
        $this->cell4_1f2b = $this->granularity4->getCellByMembers([$this->member1f, $this->member2b]);

        $this->cell5_2a = $this->granularity5->getCellByMembers([$this->member2a]);
        $this->cell5_2b = $this->granularity5->getCellByMembers([$this->member2b]);
    }

    public function testGetLabel()
    {
        $this->assertSame(__('Orga', 'navigation', 'labelGlobalCell'), $this->cell0_0->getLabel()->get('fr'));

        $this->assertSame('Label 111 A', $this->cell1_111a->getLabel()->get('fr'));
        $this->assertSame('Label 111 B', $this->cell1_111b->getLabel()->get('fr'));

        $this->assertSame('Label 11 A | Label 12 A', $this->cell2_11a12a->getLabel()->get('fr'));
        $this->assertSame('Label 11 B | Label 12 A', $this->cell2_11b12a->getLabel()->get('fr'));
        $this->assertSame('Label 11 C | Label 12 A', $this->cell2_11c12a->getLabel()->get('fr'));
        $this->assertSame('Label 11 A | Label 12 B', $this->cell2_11a12b->getLabel()->get('fr'));
        $this->assertSame('Label 11 B | Label 12 B', $this->cell2_11b12b->getLabel()->get('fr'));
        $this->assertSame('Label 11 C | Label 12 B', $this->cell2_11c12b->getLabel()->get('fr'));

        $this->assertSame('Label 11 A | Label 12 A | Label 2 A', $this->cell3_11a12a2a->getLabel()->get('fr'));
        $this->assertSame('Label 11 B | Label 12 A | Label 2 A', $this->cell3_11b12a2a->getLabel()->get('fr'));
        $this->assertSame('Label 11 C | Label 12 A | Label 2 A', $this->cell3_11c12a2a->getLabel()->get('fr'));
        $this->assertSame('Label 11 A | Label 12 B | Label 2 A', $this->cell3_11a12b2a->getLabel()->get('fr'));
        $this->assertSame('Label 11 B | Label 12 B | Label 2 A', $this->cell3_11b12b2a->getLabel()->get('fr'));
        $this->assertSame('Label 11 C | Label 12 B | Label 2 A', $this->cell3_11c12b2a->getLabel()->get('fr'));
        $this->assertSame('Label 11 A | Label 12 A | Label 2 B', $this->cell3_11a12a2b->getLabel()->get('fr'));
        $this->assertSame('Label 11 B | Label 12 A | Label 2 B', $this->cell3_11b12a2b->getLabel()->get('fr'));
        $this->assertSame('Label 11 C | Label 12 A | Label 2 B', $this->cell3_11c12a2b->getLabel()->get('fr'));
        $this->assertSame('Label 11 A | Label 12 B | Label 2 B', $this->cell3_11a12b2b->getLabel()->get('fr'));
        $this->assertSame('Label 11 B | Label 12 B | Label 2 B', $this->cell3_11b12b2b->getLabel()->get('fr'));
        $this->assertSame('Label 11 C | Label 12 B | Label 2 B', $this->cell3_11c12b2b->getLabel()->get('fr'));

        $this->assertSame('Label 1 A | Label 2 A', $this->cell4_1a2a->getLabel()->get('fr'));
        $this->assertSame('Label 1 B | Label 2 A', $this->cell4_1b2a->getLabel()->get('fr'));
        $this->assertSame('Label 1 C | Label 2 A', $this->cell4_1c2a->getLabel()->get('fr'));
        $this->assertSame('Label 1 D | Label 2 A', $this->cell4_1d2a->getLabel()->get('fr'));
        $this->assertSame('Label 1 E | Label 2 A', $this->cell4_1e2a->getLabel()->get('fr'));
        $this->assertSame('Label 1 F | Label 2 A', $this->cell4_1f2a->getLabel()->get('fr'));
        $this->assertSame('Label 1 A | Label 2 B', $this->cell4_1a2b->getLabel()->get('fr'));
        $this->assertSame('Label 1 B | Label 2 B', $this->cell4_1b2b->getLabel()->get('fr'));
        $this->assertSame('Label 1 C | Label 2 B', $this->cell4_1c2b->getLabel()->get('fr'));
        $this->assertSame('Label 1 D | Label 2 B', $this->cell4_1d2b->getLabel()->get('fr'));
        $this->assertSame('Label 1 E | Label 2 B', $this->cell4_1e2b->getLabel()->get('fr'));
        $this->assertSame('Label 1 F | Label 2 B', $this->cell4_1f2b->getLabel()->get('fr'));

        $this->assertSame('Label 2 A', $this->cell5_2a->getLabel()->get('fr'));
        $this->assertSame('Label 2 B', $this->cell5_2b->getLabel()->get('fr'));

        $this->axis2->setPosition(1);
        $this->axis12->setPosition(1);

        $this->assertSame(__('Orga', 'navigation', 'labelGlobalCell'), $this->cell0_0->getLabel()->get('fr'));

        $this->assertSame('Label 111 A', $this->cell1_111a->getLabel()->get('fr'));
        $this->assertSame('Label 111 B', $this->cell1_111b->getLabel()->get('fr'));

        $this->assertSame('Label 12 A | Label 11 A', $this->cell2_11a12a->getLabel()->get('fr'));
        $this->assertSame('Label 12 A | Label 11 B', $this->cell2_11b12a->getLabel()->get('fr'));
        $this->assertSame('Label 12 A | Label 11 C', $this->cell2_11c12a->getLabel()->get('fr'));
        $this->assertSame('Label 12 B | Label 11 A', $this->cell2_11a12b->getLabel()->get('fr'));
        $this->assertSame('Label 12 B | Label 11 B', $this->cell2_11b12b->getLabel()->get('fr'));
        $this->assertSame('Label 12 B | Label 11 C', $this->cell2_11c12b->getLabel()->get('fr'));

        $this->assertSame('Label 2 A | Label 12 A | Label 11 A', $this->cell3_11a12a2a->getLabel()->get('fr'));
        $this->assertSame('Label 2 A | Label 12 A | Label 11 B', $this->cell3_11b12a2a->getLabel()->get('fr'));
        $this->assertSame('Label 2 A | Label 12 A | Label 11 C', $this->cell3_11c12a2a->getLabel()->get('fr'));
        $this->assertSame('Label 2 A | Label 12 B | Label 11 A', $this->cell3_11a12b2a->getLabel()->get('fr'));
        $this->assertSame('Label 2 A | Label 12 B | Label 11 B', $this->cell3_11b12b2a->getLabel()->get('fr'));
        $this->assertSame('Label 2 A | Label 12 B | Label 11 C', $this->cell3_11c12b2a->getLabel()->get('fr'));
        $this->assertSame('Label 2 B | Label 12 A | Label 11 A', $this->cell3_11a12a2b->getLabel()->get('fr'));
        $this->assertSame('Label 2 B | Label 12 A | Label 11 B', $this->cell3_11b12a2b->getLabel()->get('fr'));
        $this->assertSame('Label 2 B | Label 12 A | Label 11 C', $this->cell3_11c12a2b->getLabel()->get('fr'));
        $this->assertSame('Label 2 B | Label 12 B | Label 11 A', $this->cell3_11a12b2b->getLabel()->get('fr'));
        $this->assertSame('Label 2 B | Label 12 B | Label 11 B', $this->cell3_11b12b2b->getLabel()->get('fr'));
        $this->assertSame('Label 2 B | Label 12 B | Label 11 C', $this->cell3_11c12b2b->getLabel()->get('fr'));

        $this->assertSame('Label 2 A | Label 1 A', $this->cell4_1a2a->getLabel()->get('fr'));
        $this->assertSame('Label 2 A | Label 1 B', $this->cell4_1b2a->getLabel()->get('fr'));
        $this->assertSame('Label 2 A | Label 1 C', $this->cell4_1c2a->getLabel()->get('fr'));
        $this->assertSame('Label 2 A | Label 1 D', $this->cell4_1d2a->getLabel()->get('fr'));
        $this->assertSame('Label 2 A | Label 1 E', $this->cell4_1e2a->getLabel()->get('fr'));
        $this->assertSame('Label 2 A | Label 1 F', $this->cell4_1f2a->getLabel()->get('fr'));
        $this->assertSame('Label 2 B | Label 1 A', $this->cell4_1a2b->getLabel()->get('fr'));
        $this->assertSame('Label 2 B | Label 1 B', $this->cell4_1b2b->getLabel()->get('fr'));
        $this->assertSame('Label 2 B | Label 1 C', $this->cell4_1c2b->getLabel()->get('fr'));
        $this->assertSame('Label 2 B | Label 1 D', $this->cell4_1d2b->getLabel()->get('fr'));
        $this->assertSame('Label 2 B | Label 1 E', $this->cell4_1e2b->getLabel()->get('fr'));
        $this->assertSame('Label 2 B | Label 1 F', $this->cell4_1f2b->getLabel()->get('fr'));

        $this->assertSame('Label 2 A', $this->cell5_2a->getLabel()->get('fr'));
        $this->assertSame('Label 2 B', $this->cell5_2b->getLabel()->get('fr'));
    }

    function testGetExtendedLabel()
    {
        $this->assertSame(__('Orga', 'navigation', 'labelGlobalCellExtended'), $this->cell0_0->getExtendedLabel()->get('fr'));

        $this->assertSame('Label 111 A', $this->cell1_111a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 111 B', $this->cell1_111b->getExtendedLabel()->get('fr'));

        $this->assertSame('Label 11 A | Label 12 A', $this->cell2_11a12a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 11 B | Label 12 A', $this->cell2_11b12a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 11 C | Label 12 A', $this->cell2_11c12a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 11 A | Label 12 B', $this->cell2_11a12b->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 11 B | Label 12 B', $this->cell2_11b12b->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 11 C | Label 12 B', $this->cell2_11c12b->getExtendedLabel()->get('fr'));

        $this->assertSame('Label 11 A | Label 12 A | Label 2 A', $this->cell3_11a12a2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 11 B | Label 12 A | Label 2 A', $this->cell3_11b12a2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 11 C | Label 12 A | Label 2 A', $this->cell3_11c12a2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 11 A | Label 12 B | Label 2 A', $this->cell3_11a12b2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 11 B | Label 12 B | Label 2 A', $this->cell3_11b12b2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 11 C | Label 12 B | Label 2 A', $this->cell3_11c12b2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 11 A | Label 12 A | Label 2 B', $this->cell3_11a12a2b->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 11 B | Label 12 A | Label 2 B', $this->cell3_11b12a2b->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 11 C | Label 12 A | Label 2 B', $this->cell3_11c12a2b->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 11 A | Label 12 B | Label 2 B', $this->cell3_11a12b2b->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 11 B | Label 12 B | Label 2 B', $this->cell3_11b12b2b->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 11 C | Label 12 B | Label 2 B', $this->cell3_11c12b2b->getExtendedLabel()->get('fr'));

        $this->assertSame('Label 1 A | Label 2 A', $this->cell4_1a2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 1 B | Label 2 A', $this->cell4_1b2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 1 C | Label 2 A', $this->cell4_1c2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 1 D | Label 2 A', $this->cell4_1d2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 1 E | Label 2 A', $this->cell4_1e2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 1 F | Label 2 A', $this->cell4_1f2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 1 A | Label 2 B', $this->cell4_1a2b->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 1 B | Label 2 B', $this->cell4_1b2b->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 1 C | Label 2 B', $this->cell4_1c2b->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 1 D | Label 2 B', $this->cell4_1d2b->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 1 E | Label 2 B', $this->cell4_1e2b->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 1 F | Label 2 B', $this->cell4_1f2b->getExtendedLabel()->get('fr'));

        $this->assertSame('Label 2 A', $this->cell5_2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 2 B', $this->cell5_2b->getExtendedLabel()->get('fr'));

        $this->axis111->setContextualize(true);

        $this->assertSame(__('Orga', 'navigation', 'labelGlobalCellExtended'), $this->cell0_0->getExtendedLabel()->get('fr'));

        $this->assertSame('Label 111 A', $this->cell1_111a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 111 B', $this->cell1_111b->getExtendedLabel()->get('fr'));

        $this->assertSame('Label 11 A (Label 111 A) | Label 12 A', $this->cell2_11a12a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 11 B (Label 111 B) | Label 12 A', $this->cell2_11b12a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 11 C (Label 111 B) | Label 12 A', $this->cell2_11c12a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 11 A (Label 111 A) | Label 12 B', $this->cell2_11a12b->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 11 B (Label 111 B) | Label 12 B', $this->cell2_11b12b->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 11 C (Label 111 B) | Label 12 B', $this->cell2_11c12b->getExtendedLabel()->get('fr'));

        $this->assertSame('Label 11 A (Label 111 A) | Label 12 A | Label 2 A', $this->cell3_11a12a2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 11 B (Label 111 B) | Label 12 A | Label 2 A', $this->cell3_11b12a2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 11 C (Label 111 B) | Label 12 A | Label 2 A', $this->cell3_11c12a2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 11 A (Label 111 A) | Label 12 B | Label 2 A', $this->cell3_11a12b2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 11 B (Label 111 B) | Label 12 B | Label 2 A', $this->cell3_11b12b2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 11 C (Label 111 B) | Label 12 B | Label 2 A', $this->cell3_11c12b2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 11 A (Label 111 A) | Label 12 A | Label 2 B', $this->cell3_11a12a2b->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 11 B (Label 111 B) | Label 12 A | Label 2 B', $this->cell3_11b12a2b->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 11 C (Label 111 B) | Label 12 A | Label 2 B', $this->cell3_11c12a2b->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 11 A (Label 111 A) | Label 12 B | Label 2 B', $this->cell3_11a12b2b->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 11 B (Label 111 B) | Label 12 B | Label 2 B', $this->cell3_11b12b2b->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 11 C (Label 111 B) | Label 12 B | Label 2 B', $this->cell3_11c12b2b->getExtendedLabel()->get('fr'));

        $this->assertSame('Label 1 A (Label 111 A) | Label 2 A', $this->cell4_1a2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 1 B (Label 111 A) | Label 2 A', $this->cell4_1b2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 1 C (Label 111 B) | Label 2 A', $this->cell4_1c2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 1 D (Label 111 B) | Label 2 A', $this->cell4_1d2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 1 E (Label 111 B) | Label 2 A', $this->cell4_1e2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 1 F (Label 111 B) | Label 2 A', $this->cell4_1f2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 1 A (Label 111 A) | Label 2 B', $this->cell4_1a2b->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 1 B (Label 111 A) | Label 2 B', $this->cell4_1b2b->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 1 C (Label 111 B) | Label 2 B', $this->cell4_1c2b->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 1 D (Label 111 B) | Label 2 B', $this->cell4_1d2b->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 1 E (Label 111 B) | Label 2 B', $this->cell4_1e2b->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 1 F (Label 111 B) | Label 2 B', $this->cell4_1f2b->getExtendedLabel()->get('fr'));

        $this->assertSame('Label 2 A', $this->cell5_2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 2 B', $this->cell5_2b->getExtendedLabel()->get('fr'));

        $this->axis2->setPosition(1);
        $this->axis12->setPosition(1);

        $this->assertSame(__('Orga', 'navigation', 'labelGlobalCellExtended'), $this->cell0_0->getExtendedLabel()->get('fr'));

        $this->assertSame('Label 111 A', $this->cell1_111a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 111 B', $this->cell1_111b->getExtendedLabel()->get('fr'));

        $this->assertSame('Label 12 A | Label 11 A (Label 111 A)', $this->cell2_11a12a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 12 A | Label 11 B (Label 111 B)', $this->cell2_11b12a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 12 A | Label 11 C (Label 111 B)', $this->cell2_11c12a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 12 B | Label 11 A (Label 111 A)', $this->cell2_11a12b->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 12 B | Label 11 B (Label 111 B)', $this->cell2_11b12b->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 12 B | Label 11 C (Label 111 B)', $this->cell2_11c12b->getExtendedLabel()->get('fr'));

        $this->assertSame('Label 2 A | Label 12 A | Label 11 A (Label 111 A)', $this->cell3_11a12a2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 2 A | Label 12 A | Label 11 B (Label 111 B)', $this->cell3_11b12a2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 2 A | Label 12 A | Label 11 C (Label 111 B)', $this->cell3_11c12a2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 2 A | Label 12 B | Label 11 A (Label 111 A)', $this->cell3_11a12b2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 2 A | Label 12 B | Label 11 B (Label 111 B)', $this->cell3_11b12b2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 2 A | Label 12 B | Label 11 C (Label 111 B)', $this->cell3_11c12b2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 2 B | Label 12 A | Label 11 A (Label 111 A)', $this->cell3_11a12a2b->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 2 B | Label 12 A | Label 11 B (Label 111 B)', $this->cell3_11b12a2b->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 2 B | Label 12 A | Label 11 C (Label 111 B)', $this->cell3_11c12a2b->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 2 B | Label 12 B | Label 11 A (Label 111 A)', $this->cell3_11a12b2b->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 2 B | Label 12 B | Label 11 B (Label 111 B)', $this->cell3_11b12b2b->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 2 B | Label 12 B | Label 11 C (Label 111 B)', $this->cell3_11c12b2b->getExtendedLabel()->get('fr'));

        $this->assertSame('Label 2 A | Label 1 A (Label 111 A)', $this->cell4_1a2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 2 A | Label 1 B (Label 111 A)', $this->cell4_1b2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 2 A | Label 1 C (Label 111 B)', $this->cell4_1c2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 2 A | Label 1 D (Label 111 B)', $this->cell4_1d2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 2 A | Label 1 E (Label 111 B)', $this->cell4_1e2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 2 A | Label 1 F (Label 111 B)', $this->cell4_1f2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 2 B | Label 1 A (Label 111 A)', $this->cell4_1a2b->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 2 B | Label 1 B (Label 111 A)', $this->cell4_1b2b->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 2 B | Label 1 C (Label 111 B)', $this->cell4_1c2b->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 2 B | Label 1 D (Label 111 B)', $this->cell4_1d2b->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 2 B | Label 1 E (Label 111 B)', $this->cell4_1e2b->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 2 B | Label 1 F (Label 111 B)', $this->cell4_1f2b->getExtendedLabel()->get('fr'));

        $this->assertSame('Label 2 A', $this->cell5_2a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 2 B', $this->cell5_2b->getExtendedLabel()->get('fr'));
    }

    function testIsRelevant()
    {
        $this->assertTrue($this->cell0_0->isRelevant());

        $this->assertTrue($this->cell1_111a->isRelevant());
        $this->assertTrue($this->cell1_111b->isRelevant());

        $this->assertTrue($this->cell2_11a12a->isRelevant());
        $this->assertTrue($this->cell2_11b12a->isRelevant());
        $this->assertTrue($this->cell2_11c12a->isRelevant());
        $this->assertTrue($this->cell2_11a12b->isRelevant());
        $this->assertTrue($this->cell2_11b12b->isRelevant());
        $this->assertTrue($this->cell2_11c12b->isRelevant());

        $this->assertTrue($this->cell3_11a12a2a->isRelevant());
        $this->assertTrue($this->cell3_11b12a2a->isRelevant());
        $this->assertTrue($this->cell3_11c12a2a->isRelevant());
        $this->assertTrue($this->cell3_11a12b2a->isRelevant());
        $this->assertTrue($this->cell3_11b12b2a->isRelevant());
        $this->assertTrue($this->cell3_11c12b2a->isRelevant());
        $this->assertTrue($this->cell3_11a12a2b->isRelevant());
        $this->assertTrue($this->cell3_11b12a2b->isRelevant());
        $this->assertTrue($this->cell3_11c12a2b->isRelevant());
        $this->assertTrue($this->cell3_11a12b2b->isRelevant());
        $this->assertTrue($this->cell3_11b12b2b->isRelevant());
        $this->assertTrue($this->cell3_11c12b2b->isRelevant());

        $this->assertTrue($this->cell4_1a2a->isRelevant());
        $this->assertTrue($this->cell4_1b2a->isRelevant());
        $this->assertTrue($this->cell4_1c2a->isRelevant());
        $this->assertTrue($this->cell4_1d2a->isRelevant());
        $this->assertTrue($this->cell4_1e2a->isRelevant());
        $this->assertTrue($this->cell4_1f2a->isRelevant());
        $this->assertTrue($this->cell4_1a2b->isRelevant());
        $this->assertTrue($this->cell4_1b2b->isRelevant());
        $this->assertTrue($this->cell4_1c2b->isRelevant());
        $this->assertTrue($this->cell4_1d2b->isRelevant());
        $this->assertTrue($this->cell4_1e2b->isRelevant());
        $this->assertTrue($this->cell4_1f2b->isRelevant());

        $this->assertTrue($this->cell5_2a->isRelevant());
        $this->assertTrue($this->cell5_2b->isRelevant());

        $this->cell1_111b->setRelevant(false);

        $this->assertTrue($this->cell0_0->isRelevant());

        $this->assertTrue($this->cell1_111a->isRelevant());
        $this->assertFalse($this->cell1_111b->isRelevant());

        $this->assertTrue($this->cell2_11a12a->isRelevant());
        $this->assertFalse($this->cell2_11b12a->isRelevant());
        $this->assertFalse($this->cell2_11c12a->isRelevant());
        $this->assertTrue($this->cell2_11a12b->isRelevant());
        $this->assertFalse($this->cell2_11b12b->isRelevant());
        $this->assertFalse($this->cell2_11c12b->isRelevant());

        $this->assertTrue($this->cell3_11a12a2a->isRelevant());
        $this->assertFalse($this->cell3_11b12a2a->isRelevant());
        $this->assertFalse($this->cell3_11c12a2a->isRelevant());
        $this->assertTrue($this->cell3_11a12b2a->isRelevant());
        $this->assertFalse($this->cell3_11b12b2a->isRelevant());
        $this->assertFalse($this->cell3_11c12b2a->isRelevant());
        $this->assertTrue($this->cell3_11a12a2b->isRelevant());
        $this->assertFalse($this->cell3_11b12a2b->isRelevant());
        $this->assertFalse($this->cell3_11c12a2b->isRelevant());
        $this->assertTrue($this->cell3_11a12b2b->isRelevant());
        $this->assertFalse($this->cell3_11b12b2b->isRelevant());
        $this->assertFalse($this->cell3_11c12b2b->isRelevant());

        $this->assertTrue($this->cell4_1a2a->isRelevant());
        $this->assertTrue($this->cell4_1b2a->isRelevant());
        $this->assertFalse($this->cell4_1c2a->isRelevant());
        $this->assertFalse($this->cell4_1d2a->isRelevant());
        $this->assertFalse($this->cell4_1e2a->isRelevant());
        $this->assertFalse($this->cell4_1f2a->isRelevant());
        $this->assertTrue($this->cell4_1a2b->isRelevant());
        $this->assertTrue($this->cell4_1b2b->isRelevant());
        $this->assertFalse($this->cell4_1c2b->isRelevant());
        $this->assertFalse($this->cell4_1d2b->isRelevant());
        $this->assertFalse($this->cell4_1e2b->isRelevant());
        $this->assertFalse($this->cell4_1f2b->isRelevant());

        $this->assertTrue($this->cell5_2a->isRelevant());
        $this->assertTrue($this->cell5_2b->isRelevant());

        $this->cell5_2a->setRelevant(false);
        $this->cell2_11b12a->setRelevant(false);

        $this->assertTrue($this->cell0_0->isRelevant());

        $this->assertTrue($this->cell1_111a->isRelevant());
        $this->assertFalse($this->cell1_111b->isRelevant());

        $this->assertTrue($this->cell2_11a12a->isRelevant());
        $this->assertFalse($this->cell2_11b12a->isRelevant());
        $this->assertFalse($this->cell2_11c12a->isRelevant());
        $this->assertTrue($this->cell2_11a12b->isRelevant());
        $this->assertFalse($this->cell2_11b12b->isRelevant());
        $this->assertFalse($this->cell2_11c12b->isRelevant());

        $this->assertFalse($this->cell3_11a12a2a->isRelevant());
        $this->assertFalse($this->cell3_11b12a2a->isRelevant());
        $this->assertFalse($this->cell3_11c12a2a->isRelevant());
        $this->assertFalse($this->cell3_11a12b2a->isRelevant());
        $this->assertFalse($this->cell3_11b12b2a->isRelevant());
        $this->assertFalse($this->cell3_11c12b2a->isRelevant());
        $this->assertTrue($this->cell3_11a12a2b->isRelevant());
        $this->assertFalse($this->cell3_11b12a2b->isRelevant());
        $this->assertFalse($this->cell3_11c12a2b->isRelevant());
        $this->assertTrue($this->cell3_11a12b2b->isRelevant());
        $this->assertFalse($this->cell3_11b12b2b->isRelevant());
        $this->assertFalse($this->cell3_11c12b2b->isRelevant());

        $this->assertFalse($this->cell4_1a2a->isRelevant());
        $this->assertFalse($this->cell4_1b2a->isRelevant());
        $this->assertFalse($this->cell4_1c2a->isRelevant());
        $this->assertFalse($this->cell4_1d2a->isRelevant());
        $this->assertFalse($this->cell4_1e2a->isRelevant());
        $this->assertFalse($this->cell4_1f2a->isRelevant());
        $this->assertTrue($this->cell4_1a2b->isRelevant());
        $this->assertTrue($this->cell4_1b2b->isRelevant());
        $this->assertFalse($this->cell4_1c2b->isRelevant());
        $this->assertFalse($this->cell4_1d2b->isRelevant());
        $this->assertFalse($this->cell4_1e2b->isRelevant());
        $this->assertFalse($this->cell4_1f2b->isRelevant());

        $this->assertFalse($this->cell5_2a->isRelevant());
        $this->assertTrue($this->cell5_2b->isRelevant());

        $this->cell1_111b->setRelevant(true);

        $this->assertTrue($this->cell0_0->isRelevant());

        $this->assertTrue($this->cell1_111a->isRelevant());
        $this->assertTrue($this->cell1_111b->isRelevant());

        $this->assertTrue($this->cell2_11a12a->isRelevant());
        $this->assertFalse($this->cell2_11b12a->isRelevant());
        $this->assertTrue($this->cell2_11c12a->isRelevant());
        $this->assertTrue($this->cell2_11a12b->isRelevant());
        $this->assertTrue($this->cell2_11b12b->isRelevant());
        $this->assertTrue($this->cell2_11c12b->isRelevant());

        $this->assertFalse($this->cell3_11a12a2a->isRelevant());
        $this->assertFalse($this->cell3_11b12a2a->isRelevant());
        $this->assertFalse($this->cell3_11c12a2a->isRelevant());
        $this->assertFalse($this->cell3_11a12b2a->isRelevant());
        $this->assertFalse($this->cell3_11b12b2a->isRelevant());
        $this->assertFalse($this->cell3_11c12b2a->isRelevant());
        $this->assertTrue($this->cell3_11a12a2b->isRelevant());
        $this->assertFalse($this->cell3_11b12a2b->isRelevant());
        $this->assertTrue($this->cell3_11c12a2b->isRelevant());
        $this->assertTrue($this->cell3_11a12b2b->isRelevant());
        $this->assertTrue($this->cell3_11b12b2b->isRelevant());
        $this->assertTrue($this->cell3_11c12b2b->isRelevant());

        $this->assertFalse($this->cell4_1a2a->isRelevant());
        $this->assertFalse($this->cell4_1b2a->isRelevant());
        $this->assertFalse($this->cell4_1c2a->isRelevant());
        $this->assertFalse($this->cell4_1d2a->isRelevant());
        $this->assertFalse($this->cell4_1e2a->isRelevant());
        $this->assertFalse($this->cell4_1f2a->isRelevant());
        $this->assertTrue($this->cell4_1a2b->isRelevant());
        $this->assertTrue($this->cell4_1b2b->isRelevant());
        $this->assertFalse($this->cell4_1c2b->isRelevant());
        $this->assertTrue($this->cell4_1d2b->isRelevant());
        $this->assertTrue($this->cell4_1e2b->isRelevant());
        $this->assertTrue($this->cell4_1f2b->isRelevant());

        $this->assertFalse($this->cell5_2a->isRelevant());
        $this->assertTrue($this->cell5_2b->isRelevant());
    }

    /**
     * @expectedException Core_Exception
     * @expectedexceptionMessage Relevance can only be defined if the granularity permits it.
     */
    function testSetRelevantWrongGranularity()
    {
        // Désactivation de la partie member12b pour vérifier les mises à jour vers Relevant et NotRelevant.
        $granularity6 = new Granularity($this->workspace, [$this->axis12]);
        $granularity6->getCellByMembers([$this->member12b])->setRelevant(false);
    }

    function testEnableDisable()
    {
        $granularity6 = new Granularity($this->workspace, [$this->axis12]);
        $granularity6->setCellsControlRelevance(true);

        $this->assertTrue($this->cell0_0->isRelevant());

        $this->assertTrue($this->cell1_111a->isRelevant());
        $this->assertTrue($this->cell1_111b->isRelevant());

        $this->assertTrue($this->cell2_11a12a->isRelevant());
        $this->assertTrue($this->cell2_11b12a->isRelevant());
        $this->assertTrue($this->cell2_11c12a->isRelevant());
        $this->assertTrue($this->cell2_11a12b->isRelevant());
        $this->assertTrue($this->cell2_11b12b->isRelevant());
        $this->assertTrue($this->cell2_11c12b->isRelevant());

        $this->assertTrue($this->cell3_11a12a2a->isRelevant());
        $this->assertTrue($this->cell3_11b12a2a->isRelevant());
        $this->assertTrue($this->cell3_11c12a2a->isRelevant());
        $this->assertTrue($this->cell3_11a12b2a->isRelevant());
        $this->assertTrue($this->cell3_11b12b2a->isRelevant());
        $this->assertTrue($this->cell3_11c12b2a->isRelevant());
        $this->assertTrue($this->cell3_11a12a2b->isRelevant());
        $this->assertTrue($this->cell3_11b12a2b->isRelevant());
        $this->assertTrue($this->cell3_11c12a2b->isRelevant());
        $this->assertTrue($this->cell3_11a12b2b->isRelevant());
        $this->assertTrue($this->cell3_11b12b2b->isRelevant());
        $this->assertTrue($this->cell3_11c12b2b->isRelevant());

        $this->assertTrue($this->cell4_1a2a->isRelevant());
        $this->assertTrue($this->cell4_1b2a->isRelevant());
        $this->assertTrue($this->cell4_1c2a->isRelevant());
        $this->assertTrue($this->cell4_1d2a->isRelevant());
        $this->assertTrue($this->cell4_1e2a->isRelevant());
        $this->assertTrue($this->cell4_1f2a->isRelevant());
        $this->assertTrue($this->cell4_1a2b->isRelevant());
        $this->assertTrue($this->cell4_1b2b->isRelevant());
        $this->assertTrue($this->cell4_1c2b->isRelevant());
        $this->assertTrue($this->cell4_1d2b->isRelevant());
        $this->assertTrue($this->cell4_1e2b->isRelevant());
        $this->assertTrue($this->cell4_1f2b->isRelevant());

        $this->assertTrue($this->cell5_2a->isRelevant());
        $this->assertTrue($this->cell5_2b->isRelevant());

        // Désactivation de la partie member12b pour vérifier les mises à jour vers Relevant et NotRelevant.
        $granularity6->getCellByMembers([$this->member12b])->setRelevant(false);

        $this->assertTrue($this->cell0_0->isRelevant());

        $this->assertTrue($this->cell1_111a->isRelevant());
        $this->assertTrue($this->cell1_111b->isRelevant());

        $this->assertTrue($this->cell2_11a12a->isRelevant());
        $this->assertTrue($this->cell2_11b12a->isRelevant());
        $this->assertTrue($this->cell2_11c12a->isRelevant());
        $this->assertFalse($this->cell2_11a12b->isRelevant());
        $this->assertFalse($this->cell2_11b12b->isRelevant());
        $this->assertFalse($this->cell2_11c12b->isRelevant());

        $this->assertTrue($this->cell3_11a12a2a->isRelevant());
        $this->assertTrue($this->cell3_11b12a2a->isRelevant());
        $this->assertTrue($this->cell3_11c12a2a->isRelevant());
        $this->assertFalse($this->cell3_11a12b2a->isRelevant());
        $this->assertFalse($this->cell3_11b12b2a->isRelevant());
        $this->assertFalse($this->cell3_11c12b2a->isRelevant());
        $this->assertTrue($this->cell3_11a12a2b->isRelevant());
        $this->assertTrue($this->cell3_11b12a2b->isRelevant());
        $this->assertTrue($this->cell3_11c12a2b->isRelevant());
        $this->assertFalse($this->cell3_11a12b2b->isRelevant());
        $this->assertFalse($this->cell3_11b12b2b->isRelevant());
        $this->assertFalse($this->cell3_11c12b2b->isRelevant());

        $this->assertTrue($this->cell4_1a2a->isRelevant());
        $this->assertFalse($this->cell4_1b2a->isRelevant());
        $this->assertTrue($this->cell4_1c2a->isRelevant());
        $this->assertFalse($this->cell4_1d2a->isRelevant());
        $this->assertTrue($this->cell4_1e2a->isRelevant());
        $this->assertFalse($this->cell4_1f2a->isRelevant());
        $this->assertTrue($this->cell4_1a2b->isRelevant());
        $this->assertFalse($this->cell4_1b2b->isRelevant());
        $this->assertTrue($this->cell4_1c2b->isRelevant());
        $this->assertFalse($this->cell4_1d2b->isRelevant());
        $this->assertTrue($this->cell4_1e2b->isRelevant());
        $this->assertFalse($this->cell4_1f2b->isRelevant());

        $this->assertTrue($this->cell5_2a->isRelevant());
        $this->assertTrue($this->cell5_2b->isRelevant());

        // Suppression d'un parent.
        $this->member11c->removeDirectParentForAxis($this->member111b);

        $this->assertTrue($this->cell0_0->isRelevant());

        $this->assertTrue($this->cell1_111a->isRelevant());
        $this->assertTrue($this->cell1_111b->isRelevant());

        $this->assertTrue($this->cell2_11a12a->isRelevant());
        $this->assertTrue($this->cell2_11b12a->isRelevant());
        $this->assertFalse($this->cell2_11c12a->isRelevant());
        $this->assertFalse($this->cell2_11a12b->isRelevant());
        $this->assertFalse($this->cell2_11b12b->isRelevant());
        $this->assertFalse($this->cell2_11c12b->isRelevant());

        $this->assertTrue($this->cell3_11a12a2a->isRelevant());
        $this->assertTrue($this->cell3_11b12a2a->isRelevant());
        $this->assertFalse($this->cell3_11c12a2a->isRelevant());
        $this->assertFalse($this->cell3_11a12b2a->isRelevant());
        $this->assertFalse($this->cell3_11b12b2a->isRelevant());
        $this->assertFalse($this->cell3_11c12b2a->isRelevant());
        $this->assertTrue($this->cell3_11a12a2b->isRelevant());
        $this->assertTrue($this->cell3_11b12a2b->isRelevant());
        $this->assertFalse($this->cell3_11c12a2b->isRelevant());
        $this->assertFalse($this->cell3_11a12b2b->isRelevant());
        $this->assertFalse($this->cell3_11b12b2b->isRelevant());
        $this->assertFalse($this->cell3_11c12b2b->isRelevant());

        $this->assertTrue($this->cell4_1a2a->isRelevant());
        $this->assertFalse($this->cell4_1b2a->isRelevant());
        $this->assertTrue($this->cell4_1c2a->isRelevant());
        $this->assertFalse($this->cell4_1d2a->isRelevant());
        $this->assertFalse($this->cell4_1e2a->isRelevant());
        $this->assertFalse($this->cell4_1f2a->isRelevant());
        $this->assertTrue($this->cell4_1a2b->isRelevant());
        $this->assertFalse($this->cell4_1b2b->isRelevant());
        $this->assertTrue($this->cell4_1c2b->isRelevant());
        $this->assertFalse($this->cell4_1d2b->isRelevant());
        $this->assertFalse($this->cell4_1e2b->isRelevant());
        $this->assertFalse($this->cell4_1f2b->isRelevant());

        $this->assertTrue($this->cell5_2a->isRelevant());
        $this->assertTrue($this->cell5_2b->isRelevant());

        // Définition d'un nouveau parent.
        $this->member11c->setDirectParentForAxis($this->member111a);

        $this->assertTrue($this->cell0_0->isRelevant());

        $this->assertTrue($this->cell1_111a->isRelevant());
        $this->assertTrue($this->cell1_111b->isRelevant());

        $this->assertTrue($this->cell2_11a12a->isRelevant());
        $this->assertTrue($this->cell2_11b12a->isRelevant());
        $this->assertTrue($this->cell2_11c12a->isRelevant());
        $this->assertFalse($this->cell2_11a12b->isRelevant());
        $this->assertFalse($this->cell2_11b12b->isRelevant());
        $this->assertFalse($this->cell2_11c12b->isRelevant());

        $this->assertTrue($this->cell3_11a12a2a->isRelevant());
        $this->assertTrue($this->cell3_11b12a2a->isRelevant());
        $this->assertTrue($this->cell3_11c12a2a->isRelevant());
        $this->assertFalse($this->cell3_11a12b2a->isRelevant());
        $this->assertFalse($this->cell3_11b12b2a->isRelevant());
        $this->assertFalse($this->cell3_11c12b2a->isRelevant());
        $this->assertTrue($this->cell3_11a12a2b->isRelevant());
        $this->assertTrue($this->cell3_11b12a2b->isRelevant());
        $this->assertTrue($this->cell3_11c12a2b->isRelevant());
        $this->assertFalse($this->cell3_11a12b2b->isRelevant());
        $this->assertFalse($this->cell3_11b12b2b->isRelevant());
        $this->assertFalse($this->cell3_11c12b2b->isRelevant());

        $this->assertTrue($this->cell4_1a2a->isRelevant());
        $this->assertFalse($this->cell4_1b2a->isRelevant());
        $this->assertTrue($this->cell4_1c2a->isRelevant());
        $this->assertFalse($this->cell4_1d2a->isRelevant());
        $this->assertTrue($this->cell4_1e2a->isRelevant());
        $this->assertFalse($this->cell4_1f2a->isRelevant());
        $this->assertTrue($this->cell4_1a2b->isRelevant());
        $this->assertFalse($this->cell4_1b2b->isRelevant());
        $this->assertTrue($this->cell4_1c2b->isRelevant());
        $this->assertFalse($this->cell4_1d2b->isRelevant());
        $this->assertTrue($this->cell4_1e2b->isRelevant());
        $this->assertFalse($this->cell4_1f2b->isRelevant());

        $this->assertTrue($this->cell5_2a->isRelevant());
        $this->assertTrue($this->cell5_2b->isRelevant());

        // Suppression d'un axe.
        $this->workspace->removeAxis($this->axis11);

        $this->assertTrue($this->cell0_0->isRelevant());

        $this->assertTrue($this->cell1_111a->isRelevant());
        $this->assertTrue($this->cell1_111b->isRelevant());

        $this->assertTrue($this->cell4_1a2a->isRelevant());
        $this->assertFalse($this->cell4_1b2a->isRelevant());
        $this->assertTrue($this->cell4_1c2a->isRelevant());
        $this->assertFalse($this->cell4_1d2a->isRelevant());
        $this->assertTrue($this->cell4_1e2a->isRelevant());
        $this->assertFalse($this->cell4_1f2a->isRelevant());
        $this->assertTrue($this->cell4_1a2b->isRelevant());
        $this->assertFalse($this->cell4_1b2b->isRelevant());
        $this->assertTrue($this->cell4_1c2b->isRelevant());
        $this->assertFalse($this->cell4_1d2b->isRelevant());
        $this->assertTrue($this->cell4_1e2b->isRelevant());
        $this->assertFalse($this->cell4_1f2b->isRelevant());

        $this->assertTrue($this->cell5_2a->isRelevant());
        $this->assertTrue($this->cell5_2b->isRelevant());

        // Vérification du nouveau parent défini par la suppression.
        $this->cell1_111a->setRelevant(false);

        $this->assertTrue($this->cell0_0->isRelevant());

        $this->assertFalse($this->cell1_111a->isRelevant());
        $this->assertTrue($this->cell1_111b->isRelevant());

        $this->assertFalse($this->cell4_1a2a->isRelevant());
        $this->assertFalse($this->cell4_1b2a->isRelevant());
        $this->assertTrue($this->cell4_1c2a->isRelevant());
        $this->assertFalse($this->cell4_1d2a->isRelevant());
        $this->assertFalse($this->cell4_1e2a->isRelevant());
        $this->assertFalse($this->cell4_1f2a->isRelevant());
        $this->assertFalse($this->cell4_1a2b->isRelevant());
        $this->assertFalse($this->cell4_1b2b->isRelevant());
        $this->assertTrue($this->cell4_1c2b->isRelevant());
        $this->assertFalse($this->cell4_1d2b->isRelevant());
        $this->assertFalse($this->cell4_1e2b->isRelevant());
        $this->assertFalse($this->cell4_1f2b->isRelevant());

        $this->assertTrue($this->cell5_2a->isRelevant());
        $this->assertTrue($this->cell5_2b->isRelevant());
    }

}


/**
 * Tests de la classe Workspace
 * @package Workspace
 * @subpackage Test
 */
class Orga_Test_CellHierarchy extends TestCase
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
     * @var Cell
     */
    protected $cell0_0;
    /**
     * @var Cell
     */
    protected $cell1_111a;
    /**
     * @var Cell
     */
    protected $cell1_111b;
    /**
     * @var Cell
     */
    protected $cell2_11a12a;
    /**
     * @var Cell
     */
    protected $cell2_11b12a;
    /**
     * @var Cell
     */
    protected $cell2_11c12a;
    /**
     * @var Cell
     */
    protected $cell2_11a12b;
    /**
     * @var Cell
     */
    protected $cell2_11b12b;
    /**
     * @var Cell
     */
    protected $cell2_11c12b;
    /**
     * @var Cell
     */
    protected $cell3_11a12a2a;
    /**
     * @var Cell
     */
    protected $cell3_11b12a2a;
    /**
     * @var Cell
     */
    protected $cell3_11c12a2a;
    /**
     * @var Cell
     */
    protected $cell3_11a12b2a;
    /**
     * @var Cell
     */
    protected $cell3_11b12b2a;
    /**
     * @var Cell
     */
    protected $cell3_11c12b2a;
    /**
     * @var Cell
     */
    protected $cell3_11a12a2b;
    /**
     * @var Cell
     */
    protected $cell3_11b12a2b;
    /**
     * @var Cell
     */
    protected $cell3_11c12a2b;
    /**
     * @var Cell
     */
    protected $cell3_11a12b2b;
    /**
     * @var Cell
     */
    protected $cell3_11b12b2b;
    /**
     * @var Cell
     */
    protected $cell3_11c12b2b;
    /**
     * @var Cell
     */
    protected $cell4_1a2a;
    /**
     * @var Cell
     */
    protected $cell4_1b2a;
    /**
     * @var Cell
     */
    protected $cell4_1c2a;
    /**
     * @var Cell
     */
    protected $cell4_1d2a;
    /**
     * @var Cell
     */
    protected $cell4_1e2a;
    /**
     * @var Cell
     */
    protected $cell4_1f2a;
    /**
     * @var Cell
     */
    protected $cell4_1a2b;
    /**
     * @var Cell
     */
    protected $cell4_1b2b;
    /**
     * @var Cell
     */
    protected $cell4_1c2b;
    /**
     * @var Cell
     */
    protected $cell4_1d2b;
    /**
     * @var Cell
     */
    protected $cell4_1e2b;
    /**
     * @var Cell
     */
    protected $cell4_1f2b;
    /**
     * @var Cell
     */
    protected $cell5_2a;
    /**
     * @var Cell
     */
    protected $cell5_2b;

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

        $this->axis111 = new Axis($this->workspace, 'ref_111', $this->axis11);
        $this->axis111->getLabel()->set('Label 111', 'fr');

        $this->axis12 = new Axis($this->workspace, 'ref_12', $this->axis1);
        $this->axis12->getLabel()->set('Label 12', 'fr');

        $this->axis2 = new Axis($this->workspace, 'ref_2');
        $this->axis2->setRef('ref_2');
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

        $this->granularity0 = new Granularity($this->workspace, []);
        $this->granularity1 = new Granularity($this->workspace, [$this->axis111]);
        $this->granularity2 = new Granularity($this->workspace, [$this->axis11, $this->axis12]);
        $this->granularity3 = new Granularity($this->workspace, [$this->axis11, $this->axis12, $this->axis2]);
        $this->granularity4 = new Granularity($this->workspace, [$this->axis1, $this->axis2]);
        $this->granularity5 = new Granularity($this->workspace, [$this->axis2]);

        $this->cell0_0 = $this->granularity0->getCellByMembers([]);

        $this->cell1_111a = $this->granularity1->getCellByMembers([$this->member111a]);
        $this->cell1_111b = $this->granularity1->getCellByMembers([$this->member111b]);

        $this->cell2_11a12a = $this->granularity2->getCellByMembers([$this->member11a, $this->member12a]);
        $this->cell2_11b12a = $this->granularity2->getCellByMembers([$this->member11b, $this->member12a]);
        $this->cell2_11c12a = $this->granularity2->getCellByMembers([$this->member11c, $this->member12a]);
        $this->cell2_11a12b = $this->granularity2->getCellByMembers([$this->member11a, $this->member12b]);
        $this->cell2_11b12b = $this->granularity2->getCellByMembers([$this->member11b, $this->member12b]);
        $this->cell2_11c12b = $this->granularity2->getCellByMembers([$this->member11c, $this->member12b]);

        $this->cell3_11a12a2a = $this->granularity3->getCellByMembers([$this->member11a, $this->member12a, $this->member2a]);
        $this->cell3_11b12a2a = $this->granularity3->getCellByMembers([$this->member11b, $this->member12a, $this->member2a]);
        $this->cell3_11c12a2a = $this->granularity3->getCellByMembers([$this->member11c, $this->member12a, $this->member2a]);
        $this->cell3_11a12b2a = $this->granularity3->getCellByMembers([$this->member11a, $this->member12b, $this->member2a]);
        $this->cell3_11b12b2a = $this->granularity3->getCellByMembers([$this->member11b, $this->member12b, $this->member2a]);
        $this->cell3_11c12b2a = $this->granularity3->getCellByMembers([$this->member11c, $this->member12b, $this->member2a]);
        $this->cell3_11a12a2b = $this->granularity3->getCellByMembers([$this->member11a, $this->member12a, $this->member2b]);
        $this->cell3_11b12a2b = $this->granularity3->getCellByMembers([$this->member11b, $this->member12a, $this->member2b]);
        $this->cell3_11c12a2b = $this->granularity3->getCellByMembers([$this->member11c, $this->member12a, $this->member2b]);
        $this->cell3_11a12b2b = $this->granularity3->getCellByMembers([$this->member11a, $this->member12b, $this->member2b]);
        $this->cell3_11b12b2b = $this->granularity3->getCellByMembers([$this->member11b, $this->member12b, $this->member2b]);
        $this->cell3_11c12b2b = $this->granularity3->getCellByMembers([$this->member11c, $this->member12b, $this->member2b]);

        $this->cell4_1a2a = $this->granularity4->getCellByMembers([$this->member1a, $this->member2a]);
        $this->cell4_1b2a = $this->granularity4->getCellByMembers([$this->member1b, $this->member2a]);
        $this->cell4_1c2a = $this->granularity4->getCellByMembers([$this->member1c, $this->member2a]);
        $this->cell4_1d2a = $this->granularity4->getCellByMembers([$this->member1d, $this->member2a]);
        $this->cell4_1e2a = $this->granularity4->getCellByMembers([$this->member1e, $this->member2a]);
        $this->cell4_1f2a = $this->granularity4->getCellByMembers([$this->member1f, $this->member2a]);
        $this->cell4_1a2b = $this->granularity4->getCellByMembers([$this->member1a, $this->member2b]);
        $this->cell4_1b2b = $this->granularity4->getCellByMembers([$this->member1b, $this->member2b]);
        $this->cell4_1c2b = $this->granularity4->getCellByMembers([$this->member1c, $this->member2b]);
        $this->cell4_1d2b = $this->granularity4->getCellByMembers([$this->member1d, $this->member2b]);
        $this->cell4_1e2b = $this->granularity4->getCellByMembers([$this->member1e, $this->member2b]);
        $this->cell4_1f2b = $this->granularity4->getCellByMembers([$this->member1f, $this->member2b]);

        $this->cell5_2a = $this->granularity5->getCellByMembers([$this->member2a]);
        $this->cell5_2b = $this->granularity5->getCellByMembers([$this->member2b]);
    }

    function testGetTag()
    {
        $this->assertSame('/', $this->cell0_0->getTag());

        $this->assertSame('/1-ref_111:ref111_a/', $this->cell1_111a->getTag());
        $this->assertSame('/1-ref_111:ref111_b/', $this->cell1_111b->getTag());

        $this->assertSame('/1-ref_111:ref111_a/1-ref_11:ref11_a/&/2-ref_12:ref12_a/', $this->cell2_11a12a->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_b/&/2-ref_12:ref12_a/', $this->cell2_11b12a->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_c/&/2-ref_12:ref12_a/', $this->cell2_11c12a->getTag());
        $this->assertSame('/1-ref_111:ref111_a/1-ref_11:ref11_a/&/2-ref_12:ref12_b/', $this->cell2_11a12b->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_b/&/2-ref_12:ref12_b/', $this->cell2_11b12b->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_c/&/2-ref_12:ref12_b/', $this->cell2_11c12b->getTag());

        $this->assertSame('/1-ref_111:ref111_a/1-ref_11:ref11_a/&/2-ref_12:ref12_a/&/2-ref_2:ref2_a/', $this->cell3_11a12a2a->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_b/&/2-ref_12:ref12_a/&/2-ref_2:ref2_a/', $this->cell3_11b12a2a->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_c/&/2-ref_12:ref12_a/&/2-ref_2:ref2_a/', $this->cell3_11c12a2a->getTag());
        $this->assertSame('/1-ref_111:ref111_a/1-ref_11:ref11_a/&/2-ref_12:ref12_b/&/2-ref_2:ref2_a/', $this->cell3_11a12b2a->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_b/&/2-ref_12:ref12_b/&/2-ref_2:ref2_a/', $this->cell3_11b12b2a->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_c/&/2-ref_12:ref12_b/&/2-ref_2:ref2_a/', $this->cell3_11c12b2a->getTag());
        $this->assertSame('/1-ref_111:ref111_a/1-ref_11:ref11_a/&/2-ref_12:ref12_a/&/2-ref_2:ref2_b/', $this->cell3_11a12a2b->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_b/&/2-ref_12:ref12_a/&/2-ref_2:ref2_b/', $this->cell3_11b12a2b->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_c/&/2-ref_12:ref12_a/&/2-ref_2:ref2_b/', $this->cell3_11c12a2b->getTag());
        $this->assertSame('/1-ref_111:ref111_a/1-ref_11:ref11_a/&/2-ref_12:ref12_b/&/2-ref_2:ref2_b/', $this->cell3_11a12b2b->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_b/&/2-ref_12:ref12_b/&/2-ref_2:ref2_b/', $this->cell3_11b12b2b->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_c/&/2-ref_12:ref12_b/&/2-ref_2:ref2_b/', $this->cell3_11c12b2b->getTag());

        $this->assertSame('/1-ref_111:ref111_a/1-ref_11:ref11_a/1-ref_1:ref1_a/&/2-ref_12:ref12_a/1-ref_1:ref1_a/&/2-ref_2:ref2_a/', $this->cell4_1a2a->getTag());
        $this->assertSame('/1-ref_111:ref111_a/1-ref_11:ref11_a/1-ref_1:ref1_b/&/2-ref_12:ref12_b/1-ref_1:ref1_b/&/2-ref_2:ref2_a/', $this->cell4_1b2a->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_b/1-ref_1:ref1_c/&/2-ref_12:ref12_a/1-ref_1:ref1_c/&/2-ref_2:ref2_a/', $this->cell4_1c2a->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_b/1-ref_1:ref1_d/&/2-ref_12:ref12_b/1-ref_1:ref1_d/&/2-ref_2:ref2_a/', $this->cell4_1d2a->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_c/1-ref_1:ref1_e/&/2-ref_12:ref12_a/1-ref_1:ref1_e/&/2-ref_2:ref2_a/', $this->cell4_1e2a->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_c/1-ref_1:ref1_f/&/2-ref_12:ref12_b/1-ref_1:ref1_f/&/2-ref_2:ref2_a/', $this->cell4_1f2a->getTag());
        $this->assertSame('/1-ref_111:ref111_a/1-ref_11:ref11_a/1-ref_1:ref1_a/&/2-ref_12:ref12_a/1-ref_1:ref1_a/&/2-ref_2:ref2_b/', $this->cell4_1a2b->getTag());
        $this->assertSame('/1-ref_111:ref111_a/1-ref_11:ref11_a/1-ref_1:ref1_b/&/2-ref_12:ref12_b/1-ref_1:ref1_b/&/2-ref_2:ref2_b/', $this->cell4_1b2b->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_b/1-ref_1:ref1_c/&/2-ref_12:ref12_a/1-ref_1:ref1_c/&/2-ref_2:ref2_b/', $this->cell4_1c2b->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_b/1-ref_1:ref1_d/&/2-ref_12:ref12_b/1-ref_1:ref1_d/&/2-ref_2:ref2_b/', $this->cell4_1d2b->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_c/1-ref_1:ref1_e/&/2-ref_12:ref12_a/1-ref_1:ref1_e/&/2-ref_2:ref2_b/', $this->cell4_1e2b->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_c/1-ref_1:ref1_f/&/2-ref_12:ref12_b/1-ref_1:ref1_f/&/2-ref_2:ref2_b/', $this->cell4_1f2b->getTag());

        $this->assertSame('/2-ref_2:ref2_a/', $this->cell5_2a->getTag());
        $this->assertSame('/2-ref_2:ref2_b/', $this->cell5_2b->getTag());

        // Modification de la ref d'un axe.
        $this->axis12->setRef('ref_12_updated');

        $this->assertSame('/', $this->cell0_0->getTag());

        $this->assertSame('/1-ref_111:ref111_a/', $this->cell1_111a->getTag());
        $this->assertSame('/1-ref_111:ref111_b/', $this->cell1_111b->getTag());

        $this->assertSame('/1-ref_111:ref111_a/1-ref_11:ref11_a/&/2-ref_12_updated:ref12_a/', $this->cell2_11a12a->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_b/&/2-ref_12_updated:ref12_a/', $this->cell2_11b12a->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_c/&/2-ref_12_updated:ref12_a/', $this->cell2_11c12a->getTag());
        $this->assertSame('/1-ref_111:ref111_a/1-ref_11:ref11_a/&/2-ref_12_updated:ref12_b/', $this->cell2_11a12b->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_b/&/2-ref_12_updated:ref12_b/', $this->cell2_11b12b->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_c/&/2-ref_12_updated:ref12_b/', $this->cell2_11c12b->getTag());

        $this->assertSame('/1-ref_111:ref111_a/1-ref_11:ref11_a/&/2-ref_12_updated:ref12_a/&/2-ref_2:ref2_a/', $this->cell3_11a12a2a->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_b/&/2-ref_12_updated:ref12_a/&/2-ref_2:ref2_a/', $this->cell3_11b12a2a->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_c/&/2-ref_12_updated:ref12_a/&/2-ref_2:ref2_a/', $this->cell3_11c12a2a->getTag());
        $this->assertSame('/1-ref_111:ref111_a/1-ref_11:ref11_a/&/2-ref_12_updated:ref12_b/&/2-ref_2:ref2_a/', $this->cell3_11a12b2a->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_b/&/2-ref_12_updated:ref12_b/&/2-ref_2:ref2_a/', $this->cell3_11b12b2a->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_c/&/2-ref_12_updated:ref12_b/&/2-ref_2:ref2_a/', $this->cell3_11c12b2a->getTag());
        $this->assertSame('/1-ref_111:ref111_a/1-ref_11:ref11_a/&/2-ref_12_updated:ref12_a/&/2-ref_2:ref2_b/', $this->cell3_11a12a2b->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_b/&/2-ref_12_updated:ref12_a/&/2-ref_2:ref2_b/', $this->cell3_11b12a2b->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_c/&/2-ref_12_updated:ref12_a/&/2-ref_2:ref2_b/', $this->cell3_11c12a2b->getTag());
        $this->assertSame('/1-ref_111:ref111_a/1-ref_11:ref11_a/&/2-ref_12_updated:ref12_b/&/2-ref_2:ref2_b/', $this->cell3_11a12b2b->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_b/&/2-ref_12_updated:ref12_b/&/2-ref_2:ref2_b/', $this->cell3_11b12b2b->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_c/&/2-ref_12_updated:ref12_b/&/2-ref_2:ref2_b/', $this->cell3_11c12b2b->getTag());

        $this->assertSame('/1-ref_111:ref111_a/1-ref_11:ref11_a/1-ref_1:ref1_a/&/2-ref_12_updated:ref12_a/1-ref_1:ref1_a/&/2-ref_2:ref2_a/', $this->cell4_1a2a->getTag());
        $this->assertSame('/1-ref_111:ref111_a/1-ref_11:ref11_a/1-ref_1:ref1_b/&/2-ref_12_updated:ref12_b/1-ref_1:ref1_b/&/2-ref_2:ref2_a/', $this->cell4_1b2a->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_b/1-ref_1:ref1_c/&/2-ref_12_updated:ref12_a/1-ref_1:ref1_c/&/2-ref_2:ref2_a/', $this->cell4_1c2a->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_b/1-ref_1:ref1_d/&/2-ref_12_updated:ref12_b/1-ref_1:ref1_d/&/2-ref_2:ref2_a/', $this->cell4_1d2a->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_c/1-ref_1:ref1_e/&/2-ref_12_updated:ref12_a/1-ref_1:ref1_e/&/2-ref_2:ref2_a/', $this->cell4_1e2a->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_c/1-ref_1:ref1_f/&/2-ref_12_updated:ref12_b/1-ref_1:ref1_f/&/2-ref_2:ref2_a/', $this->cell4_1f2a->getTag());
        $this->assertSame('/1-ref_111:ref111_a/1-ref_11:ref11_a/1-ref_1:ref1_a/&/2-ref_12_updated:ref12_a/1-ref_1:ref1_a/&/2-ref_2:ref2_b/', $this->cell4_1a2b->getTag());
        $this->assertSame('/1-ref_111:ref111_a/1-ref_11:ref11_a/1-ref_1:ref1_b/&/2-ref_12_updated:ref12_b/1-ref_1:ref1_b/&/2-ref_2:ref2_b/', $this->cell4_1b2b->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_b/1-ref_1:ref1_c/&/2-ref_12_updated:ref12_a/1-ref_1:ref1_c/&/2-ref_2:ref2_b/', $this->cell4_1c2b->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_b/1-ref_1:ref1_d/&/2-ref_12_updated:ref12_b/1-ref_1:ref1_d/&/2-ref_2:ref2_b/', $this->cell4_1d2b->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_c/1-ref_1:ref1_e/&/2-ref_12_updated:ref12_a/1-ref_1:ref1_e/&/2-ref_2:ref2_b/', $this->cell4_1e2b->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_c/1-ref_1:ref1_f/&/2-ref_12_updated:ref12_b/1-ref_1:ref1_f/&/2-ref_2:ref2_b/', $this->cell4_1f2b->getTag());

        $this->assertSame('/2-ref_2:ref2_a/', $this->cell5_2a->getTag());
        $this->assertSame('/2-ref_2:ref2_b/', $this->cell5_2b->getTag());

        // Modification de la position d'un axe.
        $this->axis2->setPosition(1);

        $this->assertSame('/', $this->cell0_0->getTag());

        $this->assertSame('/1-ref_111:ref111_a/', $this->cell1_111a->getTag());
        $this->assertSame('/1-ref_111:ref111_b/', $this->cell1_111b->getTag());

        $this->assertSame('/1-ref_111:ref111_a/1-ref_11:ref11_a/&/2-ref_12_updated:ref12_a/', $this->cell2_11a12a->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_b/&/2-ref_12_updated:ref12_a/', $this->cell2_11b12a->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_c/&/2-ref_12_updated:ref12_a/', $this->cell2_11c12a->getTag());
        $this->assertSame('/1-ref_111:ref111_a/1-ref_11:ref11_a/&/2-ref_12_updated:ref12_b/', $this->cell2_11a12b->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_b/&/2-ref_12_updated:ref12_b/', $this->cell2_11b12b->getTag());
        $this->assertSame('/1-ref_111:ref111_b/1-ref_11:ref11_c/&/2-ref_12_updated:ref12_b/', $this->cell2_11c12b->getTag());

        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_a/1-ref_11:ref11_a/&/2-ref_12_updated:ref12_a/', $this->cell3_11a12a2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_b/1-ref_11:ref11_b/&/2-ref_12_updated:ref12_a/', $this->cell3_11b12a2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_b/1-ref_11:ref11_c/&/2-ref_12_updated:ref12_a/', $this->cell3_11c12a2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_a/1-ref_11:ref11_a/&/2-ref_12_updated:ref12_b/', $this->cell3_11a12b2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_b/1-ref_11:ref11_b/&/2-ref_12_updated:ref12_b/', $this->cell3_11b12b2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_b/1-ref_11:ref11_c/&/2-ref_12_updated:ref12_b/', $this->cell3_11c12b2a->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_a/1-ref_11:ref11_a/&/2-ref_12_updated:ref12_a/', $this->cell3_11a12a2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_b/1-ref_11:ref11_b/&/2-ref_12_updated:ref12_a/', $this->cell3_11b12a2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_b/1-ref_11:ref11_c/&/2-ref_12_updated:ref12_a/', $this->cell3_11c12a2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_a/1-ref_11:ref11_a/&/2-ref_12_updated:ref12_b/', $this->cell3_11a12b2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_b/1-ref_11:ref11_b/&/2-ref_12_updated:ref12_b/', $this->cell3_11b12b2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_b/1-ref_11:ref11_c/&/2-ref_12_updated:ref12_b/', $this->cell3_11c12b2b->getTag());

        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_a/1-ref_11:ref11_a/2-ref_1:ref1_a/&/2-ref_12_updated:ref12_a/2-ref_1:ref1_a/', $this->cell4_1a2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_a/1-ref_11:ref11_a/2-ref_1:ref1_b/&/2-ref_12_updated:ref12_b/2-ref_1:ref1_b/', $this->cell4_1b2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_b/1-ref_11:ref11_b/2-ref_1:ref1_c/&/2-ref_12_updated:ref12_a/2-ref_1:ref1_c/', $this->cell4_1c2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_b/1-ref_11:ref11_b/2-ref_1:ref1_d/&/2-ref_12_updated:ref12_b/2-ref_1:ref1_d/', $this->cell4_1d2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_b/1-ref_11:ref11_c/2-ref_1:ref1_e/&/2-ref_12_updated:ref12_a/2-ref_1:ref1_e/', $this->cell4_1e2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_b/1-ref_11:ref11_c/2-ref_1:ref1_f/&/2-ref_12_updated:ref12_b/2-ref_1:ref1_f/', $this->cell4_1f2a->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_a/1-ref_11:ref11_a/2-ref_1:ref1_a/&/2-ref_12_updated:ref12_a/2-ref_1:ref1_a/', $this->cell4_1a2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_a/1-ref_11:ref11_a/2-ref_1:ref1_b/&/2-ref_12_updated:ref12_b/2-ref_1:ref1_b/', $this->cell4_1b2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_b/1-ref_11:ref11_b/2-ref_1:ref1_c/&/2-ref_12_updated:ref12_a/2-ref_1:ref1_c/', $this->cell4_1c2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_b/1-ref_11:ref11_b/2-ref_1:ref1_d/&/2-ref_12_updated:ref12_b/2-ref_1:ref1_d/', $this->cell4_1d2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_b/1-ref_11:ref11_c/2-ref_1:ref1_e/&/2-ref_12_updated:ref12_a/2-ref_1:ref1_e/', $this->cell4_1e2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_b/1-ref_11:ref11_c/2-ref_1:ref1_f/&/2-ref_12_updated:ref12_b/2-ref_1:ref1_f/', $this->cell4_1f2b->getTag());

        $this->assertSame('/1-ref_2:ref2_a/', $this->cell5_2a->getTag());
        $this->assertSame('/1-ref_2:ref2_b/', $this->cell5_2b->getTag());

        // Modification de la ref d'un membre.
        $this->member111b->setRef('ref111_b_updated');

        $this->assertSame('/', $this->cell0_0->getTag());

        $this->assertSame('/1-ref_111:ref111_a/', $this->cell1_111a->getTag());
        $this->assertSame('/1-ref_111:ref111_b_updated/', $this->cell1_111b->getTag());

        $this->assertSame('/1-ref_111:ref111_a/1-ref_11:ref11_a/&/2-ref_12_updated:ref12_a/', $this->cell2_11a12a->getTag());
        $this->assertSame('/1-ref_111:ref111_b_updated/1-ref_11:ref11_b/&/2-ref_12_updated:ref12_a/', $this->cell2_11b12a->getTag());
        $this->assertSame('/1-ref_111:ref111_b_updated/1-ref_11:ref11_c/&/2-ref_12_updated:ref12_a/', $this->cell2_11c12a->getTag());
        $this->assertSame('/1-ref_111:ref111_a/1-ref_11:ref11_a/&/2-ref_12_updated:ref12_b/', $this->cell2_11a12b->getTag());
        $this->assertSame('/1-ref_111:ref111_b_updated/1-ref_11:ref11_b/&/2-ref_12_updated:ref12_b/', $this->cell2_11b12b->getTag());
        $this->assertSame('/1-ref_111:ref111_b_updated/1-ref_11:ref11_c/&/2-ref_12_updated:ref12_b/', $this->cell2_11c12b->getTag());

        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_a/1-ref_11:ref11_a/&/2-ref_12_updated:ref12_a/', $this->cell3_11a12a2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_b_updated/1-ref_11:ref11_b/&/2-ref_12_updated:ref12_a/', $this->cell3_11b12a2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_b_updated/1-ref_11:ref11_c/&/2-ref_12_updated:ref12_a/', $this->cell3_11c12a2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_a/1-ref_11:ref11_a/&/2-ref_12_updated:ref12_b/', $this->cell3_11a12b2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_b_updated/1-ref_11:ref11_b/&/2-ref_12_updated:ref12_b/', $this->cell3_11b12b2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_b_updated/1-ref_11:ref11_c/&/2-ref_12_updated:ref12_b/', $this->cell3_11c12b2a->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_a/1-ref_11:ref11_a/&/2-ref_12_updated:ref12_a/', $this->cell3_11a12a2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_b_updated/1-ref_11:ref11_b/&/2-ref_12_updated:ref12_a/', $this->cell3_11b12a2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_b_updated/1-ref_11:ref11_c/&/2-ref_12_updated:ref12_a/', $this->cell3_11c12a2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_a/1-ref_11:ref11_a/&/2-ref_12_updated:ref12_b/', $this->cell3_11a12b2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_b_updated/1-ref_11:ref11_b/&/2-ref_12_updated:ref12_b/', $this->cell3_11b12b2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_b_updated/1-ref_11:ref11_c/&/2-ref_12_updated:ref12_b/', $this->cell3_11c12b2b->getTag());

        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_a/1-ref_11:ref11_a/2-ref_1:ref1_a/&/2-ref_12_updated:ref12_a/2-ref_1:ref1_a/', $this->cell4_1a2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_a/1-ref_11:ref11_a/2-ref_1:ref1_b/&/2-ref_12_updated:ref12_b/2-ref_1:ref1_b/', $this->cell4_1b2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_b_updated/1-ref_11:ref11_b/2-ref_1:ref1_c/&/2-ref_12_updated:ref12_a/2-ref_1:ref1_c/', $this->cell4_1c2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_b_updated/1-ref_11:ref11_b/2-ref_1:ref1_d/&/2-ref_12_updated:ref12_b/2-ref_1:ref1_d/', $this->cell4_1d2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_b_updated/1-ref_11:ref11_c/2-ref_1:ref1_e/&/2-ref_12_updated:ref12_a/2-ref_1:ref1_e/', $this->cell4_1e2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_b_updated/1-ref_11:ref11_c/2-ref_1:ref1_f/&/2-ref_12_updated:ref12_b/2-ref_1:ref1_f/', $this->cell4_1f2a->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_a/1-ref_11:ref11_a/2-ref_1:ref1_a/&/2-ref_12_updated:ref12_a/2-ref_1:ref1_a/', $this->cell4_1a2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_a/1-ref_11:ref11_a/2-ref_1:ref1_b/&/2-ref_12_updated:ref12_b/2-ref_1:ref1_b/', $this->cell4_1b2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_b_updated/1-ref_11:ref11_b/2-ref_1:ref1_c/&/2-ref_12_updated:ref12_a/2-ref_1:ref1_c/', $this->cell4_1c2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_b_updated/1-ref_11:ref11_b/2-ref_1:ref1_d/&/2-ref_12_updated:ref12_b/2-ref_1:ref1_d/', $this->cell4_1d2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_b_updated/1-ref_11:ref11_c/2-ref_1:ref1_e/&/2-ref_12_updated:ref12_a/2-ref_1:ref1_e/', $this->cell4_1e2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_b_updated/1-ref_11:ref11_c/2-ref_1:ref1_f/&/2-ref_12_updated:ref12_b/2-ref_1:ref1_f/', $this->cell4_1f2b->getTag());

        $this->assertSame('/1-ref_2:ref2_a/', $this->cell5_2a->getTag());
        $this->assertSame('/1-ref_2:ref2_b/', $this->cell5_2b->getTag());

        // Modification du positionnement des membres d'un axe.
        $this->axis11->setMemberPositioning(true);

        $this->assertSame('/', $this->cell0_0->getTag());

        $this->assertSame('/1-ref_111:ref111_a/', $this->cell1_111a->getTag());
        $this->assertSame('/1-ref_111:ref111_b_updated/', $this->cell1_111b->getTag());

        $this->assertSame('/1-ref_111:ref111_a/1-ref_11:1-ref11_a/&/2-ref_12_updated:ref12_a/', $this->cell2_11a12a->getTag());
        $this->assertSame('/1-ref_111:ref111_b_updated/1-ref_11:2-ref11_b/&/2-ref_12_updated:ref12_a/', $this->cell2_11b12a->getTag());
        $this->assertSame('/1-ref_111:ref111_b_updated/1-ref_11:3-ref11_c/&/2-ref_12_updated:ref12_a/', $this->cell2_11c12a->getTag());
        $this->assertSame('/1-ref_111:ref111_a/1-ref_11:1-ref11_a/&/2-ref_12_updated:ref12_b/', $this->cell2_11a12b->getTag());
        $this->assertSame('/1-ref_111:ref111_b_updated/1-ref_11:2-ref11_b/&/2-ref_12_updated:ref12_b/', $this->cell2_11b12b->getTag());
        $this->assertSame('/1-ref_111:ref111_b_updated/1-ref_11:3-ref11_c/&/2-ref_12_updated:ref12_b/', $this->cell2_11c12b->getTag());

        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_a/1-ref_11:1-ref11_a/&/2-ref_12_updated:ref12_a/', $this->cell3_11a12a2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_b_updated/1-ref_11:2-ref11_b/&/2-ref_12_updated:ref12_a/', $this->cell3_11b12a2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_b_updated/1-ref_11:3-ref11_c/&/2-ref_12_updated:ref12_a/', $this->cell3_11c12a2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_a/1-ref_11:1-ref11_a/&/2-ref_12_updated:ref12_b/', $this->cell3_11a12b2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_b_updated/1-ref_11:2-ref11_b/&/2-ref_12_updated:ref12_b/', $this->cell3_11b12b2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_b_updated/1-ref_11:3-ref11_c/&/2-ref_12_updated:ref12_b/', $this->cell3_11c12b2a->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_a/1-ref_11:1-ref11_a/&/2-ref_12_updated:ref12_a/', $this->cell3_11a12a2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_b_updated/1-ref_11:2-ref11_b/&/2-ref_12_updated:ref12_a/', $this->cell3_11b12a2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_b_updated/1-ref_11:3-ref11_c/&/2-ref_12_updated:ref12_a/', $this->cell3_11c12a2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_a/1-ref_11:1-ref11_a/&/2-ref_12_updated:ref12_b/', $this->cell3_11a12b2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_b_updated/1-ref_11:2-ref11_b/&/2-ref_12_updated:ref12_b/', $this->cell3_11b12b2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_b_updated/1-ref_11:3-ref11_c/&/2-ref_12_updated:ref12_b/', $this->cell3_11c12b2b->getTag());

        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_a/1-ref_11:1-ref11_a/2-ref_1:ref1_a/&/2-ref_12_updated:ref12_a/2-ref_1:ref1_a/', $this->cell4_1a2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_a/1-ref_11:1-ref11_a/2-ref_1:ref1_b/&/2-ref_12_updated:ref12_b/2-ref_1:ref1_b/', $this->cell4_1b2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_b_updated/1-ref_11:2-ref11_b/2-ref_1:ref1_c/&/2-ref_12_updated:ref12_a/2-ref_1:ref1_c/', $this->cell4_1c2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_b_updated/1-ref_11:2-ref11_b/2-ref_1:ref1_d/&/2-ref_12_updated:ref12_b/2-ref_1:ref1_d/', $this->cell4_1d2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_b_updated/1-ref_11:3-ref11_c/2-ref_1:ref1_e/&/2-ref_12_updated:ref12_a/2-ref_1:ref1_e/', $this->cell4_1e2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_b_updated/1-ref_11:3-ref11_c/2-ref_1:ref1_f/&/2-ref_12_updated:ref12_b/2-ref_1:ref1_f/', $this->cell4_1f2a->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_a/1-ref_11:1-ref11_a/2-ref_1:ref1_a/&/2-ref_12_updated:ref12_a/2-ref_1:ref1_a/', $this->cell4_1a2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_a/1-ref_11:1-ref11_a/2-ref_1:ref1_b/&/2-ref_12_updated:ref12_b/2-ref_1:ref1_b/', $this->cell4_1b2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_b_updated/1-ref_11:2-ref11_b/2-ref_1:ref1_c/&/2-ref_12_updated:ref12_a/2-ref_1:ref1_c/', $this->cell4_1c2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_b_updated/1-ref_11:2-ref11_b/2-ref_1:ref1_d/&/2-ref_12_updated:ref12_b/2-ref_1:ref1_d/', $this->cell4_1d2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_b_updated/1-ref_11:3-ref11_c/2-ref_1:ref1_e/&/2-ref_12_updated:ref12_a/2-ref_1:ref1_e/', $this->cell4_1e2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_b_updated/1-ref_11:3-ref11_c/2-ref_1:ref1_f/&/2-ref_12_updated:ref12_b/2-ref_1:ref1_f/', $this->cell4_1f2b->getTag());

        $this->assertSame('/1-ref_2:ref2_a/', $this->cell5_2a->getTag());
        $this->assertSame('/1-ref_2:ref2_b/', $this->cell5_2b->getTag());

        // Modification de la position d'un membre.
        $this->member11c->setPosition(2);

        $this->assertSame('/', $this->cell0_0->getTag());

        $this->assertSame('/1-ref_111:ref111_a/', $this->cell1_111a->getTag());
        $this->assertSame('/1-ref_111:ref111_b_updated/', $this->cell1_111b->getTag());

        $this->assertSame('/1-ref_111:ref111_a/1-ref_11:1-ref11_a/&/2-ref_12_updated:ref12_a/', $this->cell2_11a12a->getTag());
        $this->assertSame('/1-ref_111:ref111_b_updated/1-ref_11:3-ref11_b/&/2-ref_12_updated:ref12_a/', $this->cell2_11b12a->getTag());
        $this->assertSame('/1-ref_111:ref111_b_updated/1-ref_11:2-ref11_c/&/2-ref_12_updated:ref12_a/', $this->cell2_11c12a->getTag());
        $this->assertSame('/1-ref_111:ref111_a/1-ref_11:1-ref11_a/&/2-ref_12_updated:ref12_b/', $this->cell2_11a12b->getTag());
        $this->assertSame('/1-ref_111:ref111_b_updated/1-ref_11:3-ref11_b/&/2-ref_12_updated:ref12_b/', $this->cell2_11b12b->getTag());
        $this->assertSame('/1-ref_111:ref111_b_updated/1-ref_11:2-ref11_c/&/2-ref_12_updated:ref12_b/', $this->cell2_11c12b->getTag());

        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_a/1-ref_11:1-ref11_a/&/2-ref_12_updated:ref12_a/', $this->cell3_11a12a2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_b_updated/1-ref_11:3-ref11_b/&/2-ref_12_updated:ref12_a/', $this->cell3_11b12a2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_b_updated/1-ref_11:2-ref11_c/&/2-ref_12_updated:ref12_a/', $this->cell3_11c12a2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_a/1-ref_11:1-ref11_a/&/2-ref_12_updated:ref12_b/', $this->cell3_11a12b2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_b_updated/1-ref_11:3-ref11_b/&/2-ref_12_updated:ref12_b/', $this->cell3_11b12b2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_b_updated/1-ref_11:2-ref11_c/&/2-ref_12_updated:ref12_b/', $this->cell3_11c12b2a->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_a/1-ref_11:1-ref11_a/&/2-ref_12_updated:ref12_a/', $this->cell3_11a12a2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_b_updated/1-ref_11:3-ref11_b/&/2-ref_12_updated:ref12_a/', $this->cell3_11b12a2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_b_updated/1-ref_11:2-ref11_c/&/2-ref_12_updated:ref12_a/', $this->cell3_11c12a2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_a/1-ref_11:1-ref11_a/&/2-ref_12_updated:ref12_b/', $this->cell3_11a12b2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_b_updated/1-ref_11:3-ref11_b/&/2-ref_12_updated:ref12_b/', $this->cell3_11b12b2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_b_updated/1-ref_11:2-ref11_c/&/2-ref_12_updated:ref12_b/', $this->cell3_11c12b2b->getTag());

        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_a/1-ref_11:1-ref11_a/2-ref_1:ref1_a/&/2-ref_12_updated:ref12_a/2-ref_1:ref1_a/', $this->cell4_1a2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_a/1-ref_11:1-ref11_a/2-ref_1:ref1_b/&/2-ref_12_updated:ref12_b/2-ref_1:ref1_b/', $this->cell4_1b2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_b_updated/1-ref_11:3-ref11_b/2-ref_1:ref1_c/&/2-ref_12_updated:ref12_a/2-ref_1:ref1_c/', $this->cell4_1c2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_b_updated/1-ref_11:3-ref11_b/2-ref_1:ref1_d/&/2-ref_12_updated:ref12_b/2-ref_1:ref1_d/', $this->cell4_1d2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_b_updated/1-ref_11:2-ref11_c/2-ref_1:ref1_e/&/2-ref_12_updated:ref12_a/2-ref_1:ref1_e/', $this->cell4_1e2a->getTag());
        $this->assertSame('/1-ref_2:ref2_a/&/1-ref_111:ref111_b_updated/1-ref_11:2-ref11_c/2-ref_1:ref1_f/&/2-ref_12_updated:ref12_b/2-ref_1:ref1_f/', $this->cell4_1f2a->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_a/1-ref_11:1-ref11_a/2-ref_1:ref1_a/&/2-ref_12_updated:ref12_a/2-ref_1:ref1_a/', $this->cell4_1a2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_a/1-ref_11:1-ref11_a/2-ref_1:ref1_b/&/2-ref_12_updated:ref12_b/2-ref_1:ref1_b/', $this->cell4_1b2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_b_updated/1-ref_11:3-ref11_b/2-ref_1:ref1_c/&/2-ref_12_updated:ref12_a/2-ref_1:ref1_c/', $this->cell4_1c2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_b_updated/1-ref_11:3-ref11_b/2-ref_1:ref1_d/&/2-ref_12_updated:ref12_b/2-ref_1:ref1_d/', $this->cell4_1d2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_b_updated/1-ref_11:2-ref11_c/2-ref_1:ref1_e/&/2-ref_12_updated:ref12_a/2-ref_1:ref1_e/', $this->cell4_1e2b->getTag());
        $this->assertSame('/1-ref_2:ref2_b/&/1-ref_111:ref111_b_updated/1-ref_11:2-ref11_c/2-ref_1:ref1_f/&/2-ref_12_updated:ref12_b/2-ref_1:ref1_f/', $this->cell4_1f2b->getTag());

        $this->assertSame('/1-ref_2:ref2_a/', $this->cell5_2a->getTag());
        $this->assertSame('/1-ref_2:ref2_b/', $this->cell5_2b->getTag());
    }

    function testGetParentCellForGranularity()
    {
        $this->assertSame($this->cell0_0, $this->cell1_111a->getParentCellForGranularity($this->granularity0));

        $this->assertSame($this->cell0_0, $this->cell2_11b12a->getParentCellForGranularity($this->granularity0));
        $this->assertSame($this->cell1_111b, $this->cell2_11b12a->getParentCellForGranularity($this->granularity1));

        $this->assertSame($this->cell0_0, $this->cell3_11c12a2b->getParentCellForGranularity($this->granularity0));
        $this->assertSame($this->cell1_111b, $this->cell3_11c12a2b->getParentCellForGranularity($this->granularity1));
        $this->assertSame($this->cell2_11c12a, $this->cell3_11c12a2b->getParentCellForGranularity($this->granularity2));
        $this->assertSame($this->cell5_2b, $this->cell3_11c12a2b->getParentCellForGranularity($this->granularity5));

        $this->assertSame($this->cell0_0, $this->cell4_1d2a->getParentCellForGranularity($this->granularity0));
        $this->assertSame($this->cell1_111b, $this->cell4_1d2a->getParentCellForGranularity($this->granularity1));
        $this->assertSame($this->cell2_11b12b, $this->cell4_1d2a->getParentCellForGranularity($this->granularity2));
        $this->assertSame($this->cell3_11b12b2a, $this->cell4_1d2a->getParentCellForGranularity($this->granularity3));
        $this->assertSame($this->cell5_2a, $this->cell4_1d2a->getParentCellForGranularity($this->granularity5));

        $this->assertSame($this->cell0_0, $this->cell5_2b->getParentCellForGranularity($this->granularity0));
    }

    /**
     * @expectedException Core_Exception_InvalidArgument
     * @expectedexceptionMessage The given granularity is not broader than the current.
     */
    function testGetParentCellForGranularityNotBroader()
    {
        $this->cell0_0->getParentCellForGranularity($this->granularity1);
    }

    function testGetParentCells()
    {
        $this->assertSame([$this->cell0_0], $this->cell1_111a->getParentCells());

        $this->assertSame([$this->cell0_0, $this->cell1_111a], $this->cell2_11a12a->getParentCells());

        $this->assertSame([$this->cell0_0, $this->cell5_2a, $this->cell1_111a, $this->cell2_11a12b], $this->cell3_11a12b2a->getParentCells());

        $this->assertSame([$this->cell0_0, $this->cell5_2b, $this->cell1_111b, $this->cell2_11b12a, $this->cell3_11b12a2b], $this->cell4_1c2b->getParentCells());

        $this->assertSame([$this->cell0_0], $this->cell5_2b->getParentCells());
    }

    function testGetChildCellsForGranularity()
    {
        $this->assertSame([$this->cell1_111a, $this->cell1_111b], $this->cell0_0->getChildCellsForGranularity($this->granularity1)->toArray());
        $this->assertSame([$this->cell2_11a12a, $this->cell2_11a12b, $this->cell2_11b12a, $this->cell2_11b12b, $this->cell2_11c12a, $this->cell2_11c12b], $this->cell0_0->getChildCellsForGranularity($this->granularity2)->toArray());
        $this->assertSame([$this->cell3_11a12a2a, $this->cell3_11a12a2b, $this->cell3_11a12b2a, $this->cell3_11a12b2b, $this->cell3_11b12a2a, $this->cell3_11b12a2b, $this->cell3_11b12b2a, $this->cell3_11b12b2b, $this->cell3_11c12a2a, $this->cell3_11c12a2b, $this->cell3_11c12b2a, $this->cell3_11c12b2b], $this->cell0_0->getChildCellsForGranularity($this->granularity3)->toArray());
        $this->assertSame([$this->cell4_1a2a, $this->cell4_1a2b, $this->cell4_1b2a, $this->cell4_1b2b, $this->cell4_1c2a, $this->cell4_1c2b, $this->cell4_1d2a, $this->cell4_1d2b, $this->cell4_1e2a, $this->cell4_1e2b, $this->cell4_1f2a, $this->cell4_1f2b], $this->cell0_0->getChildCellsForGranularity($this->granularity4)->toArray());
        $this->assertSame([$this->cell5_2a, $this->cell5_2b], $this->cell0_0->getChildCellsForGranularity($this->granularity5)->toArray());

        $this->assertSame([$this->cell2_11a12a, $this->cell2_11a12b], $this->cell1_111a->getChildCellsForGranularity($this->granularity2)->toArray());
        $this->assertSame([$this->cell3_11a12a2a, $this->cell3_11a12a2b, $this->cell3_11a12b2a, $this->cell3_11a12b2b], $this->cell1_111a->getChildCellsForGranularity($this->granularity3)->toArray());
        $this->assertSame([$this->cell4_1a2a, $this->cell4_1a2b, $this->cell4_1b2a, $this->cell4_1b2b], $this->cell1_111a->getChildCellsForGranularity($this->granularity4)->toArray());

        $this->assertSame([$this->cell4_1e2b], $this->cell3_11c12a2b->getChildCellsForGranularity($this->granularity4)->toArray());

        $this->assertSame([$this->cell3_11a12a2b, $this->cell3_11a12b2b, $this->cell3_11b12a2b, $this->cell3_11b12b2b, $this->cell3_11c12a2b, $this->cell3_11c12b2b], $this->cell5_2b->getChildCellsForGranularity($this->granularity3)->toArray());
        $this->assertSame([$this->cell4_1a2b, $this->cell4_1b2b, $this->cell4_1c2b, $this->cell4_1d2b, $this->cell4_1e2b, $this->cell4_1f2b], $this->cell5_2b->getChildCellsForGranularity($this->granularity4)->toArray());
    }

    function testGetChildCells()
    {
        $this->assertSame([$this->cell5_2a, $this->cell5_2b, $this->cell1_111a, $this->cell1_111b, $this->cell2_11a12a, $this->cell2_11a12b, $this->cell2_11b12a, $this->cell2_11b12b, $this->cell2_11c12a, $this->cell2_11c12b, $this->cell3_11a12a2a, $this->cell3_11a12a2b, $this->cell3_11a12b2a, $this->cell3_11a12b2b, $this->cell3_11b12a2a, $this->cell3_11b12a2b, $this->cell3_11b12b2a, $this->cell3_11b12b2b, $this->cell3_11c12a2a, $this->cell3_11c12a2b, $this->cell3_11c12b2a, $this->cell3_11c12b2b, $this->cell4_1a2a, $this->cell4_1a2b, $this->cell4_1b2a, $this->cell4_1b2b, $this->cell4_1c2a, $this->cell4_1c2b, $this->cell4_1d2a, $this->cell4_1d2b, $this->cell4_1e2a, $this->cell4_1e2b, $this->cell4_1f2a, $this->cell4_1f2b], $this->cell0_0->getChildCells());

        $this->assertSame([$this->cell2_11a12a, $this->cell2_11a12b, $this->cell3_11a12a2a, $this->cell3_11a12a2b, $this->cell3_11a12b2a, $this->cell3_11a12b2b, $this->cell4_1a2a, $this->cell4_1a2b, $this->cell4_1b2a, $this->cell4_1b2b], $this->cell1_111a->getChildCells());

        $this->assertSame([$this->cell4_1e2b], $this->cell3_11c12a2b->getChildCells());

        $this->assertSame([$this->cell3_11a12a2b, $this->cell3_11a12b2b, $this->cell3_11b12a2b, $this->cell3_11b12b2b, $this->cell3_11c12a2b, $this->cell3_11c12b2b, $this->cell4_1a2b, $this->cell4_1b2b, $this->cell4_1c2b, $this->cell4_1d2b, $this->cell4_1e2b, $this->cell4_1f2b], $this->cell5_2b->getChildCells());
    }

}


/**
 * Tests de la classe Workspace
 * @package Workspace
 * @subpackage Test
 */
class Orga_Test_CellAdjacent extends TestCase
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
    protected $member1g;
    /**
     * @var Member
     */
    protected $member1h;
    /**
     * @var Member
     */
    protected $member1i;
    /**
     * @var Member
     */
    protected $member1j;
    /**
     * @var Member
     */
    protected $member1k;
    /**
     * @var Member
     */
    protected $member1l;
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
     * @var Cell
     */
    protected $cell0_0;
    /**
     * @var Cell
     */
    protected $cell1_111a;
    /**
     * @var Cell
     */
    protected $cell1_111b;
    /**
     * @var Cell
     */
    protected $cell2_11a12a;
    /**
     * @var Cell
     */
    protected $cell2_11b12a;
    /**
     * @var Cell
     */
    protected $cell2_11c12a;
    /**
     * @var Cell
     */
    protected $cell2_11a12b;
    /**
     * @var Cell
     */
    protected $cell2_11b12b;
    /**
     * @var Cell
     */
    protected $cell2_11c12b;
    /**
     * @var Cell
     */
    protected $cell3_11a12a2a;
    /**
     * @var Cell
     */
    protected $cell3_11b12a2a;
    /**
     * @var Cell
     */
    protected $cell3_11c12a2a;
    /**
     * @var Cell
     */
    protected $cell3_11a12b2a;
    /**
     * @var Cell
     */
    protected $cell3_11b12b2a;
    /**
     * @var Cell
     */
    protected $cell3_11c12b2a;
    /**
     * @var Cell
     */
    protected $cell3_11a12a2b;
    /**
     * @var Cell
     */
    protected $cell3_11b12a2b;
    /**
     * @var Cell
     */
    protected $cell3_11c12a2b;
    /**
     * @var Cell
     */
    protected $cell3_11a12b2b;
    /**
     * @var Cell
     */
    protected $cell3_11b12b2b;
    /**
     * @var Cell
     */
    protected $cell3_11c12b2b;
    /**
     * @var Cell
     */
    protected $cell4_1a2a;
    /**
     * @var Cell
     */
    protected $cell4_1b2a;
    /**
     * @var Cell
     */
    protected $cell4_1c2a;
    /**
     * @var Cell
     */
    protected $cell4_1d2a;
    /**
     * @var Cell
     */
    protected $cell4_1e2a;
    /**
     * @var Cell
     */
    protected $cell4_1f2a;
    /**
     * @var Cell
     */
    protected $cell4_1g2a;
    /**
     * @var Cell
     */
    protected $cell4_1h2a;
    /**
     * @var Cell
     */
    protected $cell4_1i2a;
    /**
     * @var Cell
     */
    protected $cell4_1j2a;
    /**
     * @var Cell
     */
    protected $cell4_1k2a;
    /**
     * @var Cell
     */
    protected $cell4_1l2a;
    /**
     * @var Cell
     */
    protected $cell4_1a2b;
    /**
     * @var Cell
     */
    protected $cell4_1b2b;
    /**
     * @var Cell
     */
    protected $cell4_1c2b;
    /**
     * @var Cell
     */
    protected $cell4_1d2b;
    /**
     * @var Cell
     */
    protected $cell4_1e2b;
    /**
     * @var Cell
     */
    protected $cell4_1f2b;
    /**
     * @var Cell
     */
    protected $cell4_1g2b;
    /**
     * @var Cell
     */
    protected $cell4_1h2b;
    /**
     * @var Cell
     */
    protected $cell4_1i2b;
    /**
     * @var Cell
     */
    protected $cell4_1j2b;
    /**
     * @var Cell
     */
    protected $cell4_1k2b;
    /**
     * @var Cell
     */
    protected $cell4_1l2b;
    /**
     * @var Cell
     */
    protected $cell5_2a;
    /**
     * @var Cell
     */
    protected $cell5_2b;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->workspace = new Workspace($this->getMockBuilder(Account::class)->disableOriginalConstructor()->getMock());

        $this->axis1 = new Axis($this->workspace, 'ref_1');
        $this->axis1->setMemberPositioning(true);
        $this->axis11 = new Axis($this->workspace, 'ref_11', $this->axis1);
        $this->axis11->setMemberPositioning(true);
        $this->axis111 = new Axis($this->workspace, 'ref_111', $this->axis11);
        $this->axis111->setMemberPositioning(true);
        $this->axis111->setContextualize(true);
        $this->axis12 = new Axis($this->workspace, 'ref_12', $this->axis1);
        $this->axis12->setMemberPositioning(true);
        $this->axis12->setContextualize(true);
        $this->axis2 = new Axis($this->workspace, 'ref_2');

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
        $this->member1g = new Member($this->axis1, 'ref1_g', [$this->member11a, $this->member12a]);
        $this->member1h = new Member($this->axis1, 'ref1_h', [$this->member11a, $this->member12b]);
        $this->member1i = new Member($this->axis1, 'ref1_i', [$this->member11a, $this->member12a]);
        $this->member1j = new Member($this->axis1, 'ref1_j', [$this->member11b, $this->member12b]);
        $this->member1k = new Member($this->axis1, 'ref1_k', [$this->member11c, $this->member12a]);
        $this->member1l = new Member($this->axis1, 'ref1_l', [$this->member11c, $this->member12b]);

        $this->member2a = new Member($this->axis2, 'ref2_a');
        $this->member2b = new Member($this->axis2, 'ref2_b');

        $this->granularity0 = new Granularity($this->workspace, []);
        $this->granularity1 = new Granularity($this->workspace, [$this->axis111]);
        $this->granularity2 = new Granularity($this->workspace, [$this->axis11, $this->axis12]);
        $this->granularity3 = new Granularity($this->workspace, [$this->axis11, $this->axis12, $this->axis2]);
        $this->granularity4 = new Granularity($this->workspace, [$this->axis1, $this->axis2]);
        $this->granularity5 = new Granularity($this->workspace, [$this->axis2]);

        $this->cell0_0 = $this->granularity0->getCellByMembers([]);

        $this->cell1_111a = $this->granularity1->getCellByMembers([$this->member111a]);
        $this->cell1_111b = $this->granularity1->getCellByMembers([$this->member111b]);

        $this->cell2_11a12a = $this->granularity2->getCellByMembers([$this->member11a, $this->member12a]);
        $this->cell2_11b12a = $this->granularity2->getCellByMembers([$this->member11b, $this->member12a]);
        $this->cell2_11c12a = $this->granularity2->getCellByMembers([$this->member11c, $this->member12a]);
        $this->cell2_11a12b = $this->granularity2->getCellByMembers([$this->member11a, $this->member12b]);
        $this->cell2_11b12b = $this->granularity2->getCellByMembers([$this->member11b, $this->member12b]);
        $this->cell2_11c12b = $this->granularity2->getCellByMembers([$this->member11c, $this->member12b]);

        $this->cell3_11a12a2a = $this->granularity3->getCellByMembers([$this->member11a, $this->member12a, $this->member2a]);
        $this->cell3_11b12a2a = $this->granularity3->getCellByMembers([$this->member11b, $this->member12a, $this->member2a]);
        $this->cell3_11c12a2a = $this->granularity3->getCellByMembers([$this->member11c, $this->member12a, $this->member2a]);
        $this->cell3_11a12b2a = $this->granularity3->getCellByMembers([$this->member11a, $this->member12b, $this->member2a]);
        $this->cell3_11b12b2a = $this->granularity3->getCellByMembers([$this->member11b, $this->member12b, $this->member2a]);
        $this->cell3_11c12b2a = $this->granularity3->getCellByMembers([$this->member11c, $this->member12b, $this->member2a]);
        $this->cell3_11a12a2b = $this->granularity3->getCellByMembers([$this->member11a, $this->member12a, $this->member2b]);
        $this->cell3_11b12a2b = $this->granularity3->getCellByMembers([$this->member11b, $this->member12a, $this->member2b]);
        $this->cell3_11c12a2b = $this->granularity3->getCellByMembers([$this->member11c, $this->member12a, $this->member2b]);
        $this->cell3_11a12b2b = $this->granularity3->getCellByMembers([$this->member11a, $this->member12b, $this->member2b]);
        $this->cell3_11b12b2b = $this->granularity3->getCellByMembers([$this->member11b, $this->member12b, $this->member2b]);
        $this->cell3_11c12b2b = $this->granularity3->getCellByMembers([$this->member11c, $this->member12b, $this->member2b]);

        $this->cell4_1a2a = $this->granularity4->getCellByMembers([$this->member1a, $this->member2a]);
        $this->cell4_1b2a = $this->granularity4->getCellByMembers([$this->member1b, $this->member2a]);
        $this->cell4_1c2a = $this->granularity4->getCellByMembers([$this->member1c, $this->member2a]);
        $this->cell4_1d2a = $this->granularity4->getCellByMembers([$this->member1d, $this->member2a]);
        $this->cell4_1e2a = $this->granularity4->getCellByMembers([$this->member1e, $this->member2a]);
        $this->cell4_1f2a = $this->granularity4->getCellByMembers([$this->member1f, $this->member2a]);
        $this->cell4_1g2a = $this->granularity4->getCellByMembers([$this->member1g, $this->member2a]);
        $this->cell4_1h2a = $this->granularity4->getCellByMembers([$this->member1h, $this->member2a]);
        $this->cell4_1i2a = $this->granularity4->getCellByMembers([$this->member1i, $this->member2a]);
        $this->cell4_1j2a = $this->granularity4->getCellByMembers([$this->member1j, $this->member2a]);
        $this->cell4_1k2a = $this->granularity4->getCellByMembers([$this->member1k, $this->member2a]);
        $this->cell4_1l2a = $this->granularity4->getCellByMembers([$this->member1l, $this->member2a]);
        $this->cell4_1a2b = $this->granularity4->getCellByMembers([$this->member1a, $this->member2b]);
        $this->cell4_1b2b = $this->granularity4->getCellByMembers([$this->member1b, $this->member2b]);
        $this->cell4_1c2b = $this->granularity4->getCellByMembers([$this->member1c, $this->member2b]);
        $this->cell4_1d2b = $this->granularity4->getCellByMembers([$this->member1d, $this->member2b]);
        $this->cell4_1e2b = $this->granularity4->getCellByMembers([$this->member1e, $this->member2b]);
        $this->cell4_1f2b = $this->granularity4->getCellByMembers([$this->member1f, $this->member2b]);
        $this->cell4_1g2b = $this->granularity4->getCellByMembers([$this->member1g, $this->member2b]);
        $this->cell4_1h2b = $this->granularity4->getCellByMembers([$this->member1h, $this->member2b]);
        $this->cell4_1i2b = $this->granularity4->getCellByMembers([$this->member1i, $this->member2b]);
        $this->cell4_1j2b = $this->granularity4->getCellByMembers([$this->member1j, $this->member2b]);
        $this->cell4_1k2b = $this->granularity4->getCellByMembers([$this->member1k, $this->member2b]);
        $this->cell4_1l2b = $this->granularity4->getCellByMembers([$this->member1l, $this->member2b]);

        $this->cell5_2a = $this->granularity5->getCellByMembers([$this->member2a]);
        $this->cell5_2b = $this->granularity5->getCellByMembers([$this->member2b]);
    }

    /**
     * @expectedException Core_Exception_InvalidArgument
     * @expectedexceptionMessage Given axis needs to be used by this cell's granularity and allows member positioning.
     */
    function testGetPreviousCellForAxisNotUsedByGranularity()
    {
        $this->cell0_0->getPreviousCellForAxis($this->axis1);
    }

    function testGetPreviousForAxis()
    {
        $this->assertNull($this->cell1_111a->getPreviousCellForAxis($this->axis111));
        $this->assertSame($this->cell1_111a, $this->cell1_111b->getPreviousCellForAxis($this->axis111));

        $this->assertNull($this->cell2_11a12a->getPreviousCellForAxis($this->axis11));
        $this->assertNull($this->cell2_11a12b->getPreviousCellForAxis($this->axis11));
        $this->assertNull($this->cell2_11b12a->getPreviousCellForAxis($this->axis11));
        $this->assertNull($this->cell2_11b12b->getPreviousCellForAxis($this->axis11));
        $this->assertSame($this->cell2_11b12a, $this->cell2_11c12a->getPreviousCellForAxis($this->axis11));
        $this->assertSame($this->cell2_11b12b, $this->cell2_11c12b->getPreviousCellForAxis($this->axis11));

        $this->assertNull($this->cell2_11a12a->getPreviousCellForAxis($this->axis12));
        $this->assertSame($this->cell2_11a12a, $this->cell2_11a12b->getPreviousCellForAxis($this->axis12));
        $this->assertNull($this->cell2_11b12a->getPreviousCellForAxis($this->axis12));
        $this->assertSame($this->cell2_11b12a, $this->cell2_11b12b->getPreviousCellForAxis($this->axis12));
        $this->assertNull($this->cell2_11c12a->getPreviousCellForAxis($this->axis12));
        $this->assertSame($this->cell2_11c12a, $this->cell2_11c12b->getPreviousCellForAxis($this->axis12));

        $this->assertNull($this->cell3_11a12a2a->getPreviousCellForAxis($this->axis11));
        $this->assertNull($this->cell3_11a12a2b->getPreviousCellForAxis($this->axis11));
        $this->assertNull($this->cell3_11a12b2a->getPreviousCellForAxis($this->axis11));
        $this->assertNull($this->cell3_11a12b2b->getPreviousCellForAxis($this->axis11));
        $this->assertNull($this->cell3_11b12a2a->getPreviousCellForAxis($this->axis11));
        $this->assertNull($this->cell3_11b12a2b->getPreviousCellForAxis($this->axis11));
        $this->assertNull($this->cell3_11b12b2a->getPreviousCellForAxis($this->axis11));
        $this->assertNull($this->cell3_11b12b2b->getPreviousCellForAxis($this->axis11));
        $this->assertSame($this->cell3_11b12a2a, $this->cell3_11c12a2a->getPreviousCellForAxis($this->axis11));
        $this->assertSame($this->cell3_11b12a2b, $this->cell3_11c12a2b->getPreviousCellForAxis($this->axis11));
        $this->assertSame($this->cell3_11b12b2a, $this->cell3_11c12b2a->getPreviousCellForAxis($this->axis11));
        $this->assertSame($this->cell3_11b12b2b, $this->cell3_11c12b2b->getPreviousCellForAxis($this->axis11));

        $this->assertNull($this->cell3_11a12a2a->getPreviousCellForAxis($this->axis12));
        $this->assertNull($this->cell3_11a12a2b->getPreviousCellForAxis($this->axis12));
        $this->assertSame($this->cell3_11a12a2a, $this->cell3_11a12b2a->getPreviousCellForAxis($this->axis12));
        $this->assertSame($this->cell3_11a12a2b, $this->cell3_11a12b2b->getPreviousCellForAxis($this->axis12));
        $this->assertNull($this->cell3_11b12a2a->getPreviousCellForAxis($this->axis12));
        $this->assertNull($this->cell3_11b12a2b->getPreviousCellForAxis($this->axis12));
        $this->assertSame($this->cell3_11b12a2a, $this->cell3_11b12b2a->getPreviousCellForAxis($this->axis12));
        $this->assertSame($this->cell3_11b12a2b, $this->cell3_11b12b2b->getPreviousCellForAxis($this->axis12));
        $this->assertNull($this->cell3_11c12a2a->getPreviousCellForAxis($this->axis12));
        $this->assertNull($this->cell3_11c12a2b->getPreviousCellForAxis($this->axis12));
        $this->assertSame($this->cell3_11c12a2a, $this->cell3_11c12b2a->getPreviousCellForAxis($this->axis12));
        $this->assertSame($this->cell3_11c12a2b, $this->cell3_11c12b2b->getPreviousCellForAxis($this->axis12));

        $this->assertNull($this->cell4_1a2a->getPreviousCellForAxis($this->axis1));
        $this->assertNull($this->cell4_1a2b->getPreviousCellForAxis($this->axis1));
        $this->assertNull($this->cell4_1b2a->getPreviousCellForAxis($this->axis1));
        $this->assertNull($this->cell4_1b2b->getPreviousCellForAxis($this->axis1));
        $this->assertNull($this->cell4_1c2a->getPreviousCellForAxis($this->axis1));
        $this->assertNull($this->cell4_1c2b->getPreviousCellForAxis($this->axis1));
        $this->assertNull($this->cell4_1d2a->getPreviousCellForAxis($this->axis1));
        $this->assertNull($this->cell4_1d2b->getPreviousCellForAxis($this->axis1));
        $this->assertSame($this->cell4_1c2a, $this->cell4_1e2a->getPreviousCellForAxis($this->axis1));
        $this->assertSame($this->cell4_1c2b, $this->cell4_1e2b->getPreviousCellForAxis($this->axis1));
        $this->assertSame($this->cell4_1d2a, $this->cell4_1f2a->getPreviousCellForAxis($this->axis1));
        $this->assertSame($this->cell4_1d2b, $this->cell4_1f2b->getPreviousCellForAxis($this->axis1));
        $this->assertSame($this->cell4_1a2a, $this->cell4_1g2a->getPreviousCellForAxis($this->axis1));
        $this->assertSame($this->cell4_1a2b, $this->cell4_1g2b->getPreviousCellForAxis($this->axis1));
        $this->assertSame($this->cell4_1b2a, $this->cell4_1h2a->getPreviousCellForAxis($this->axis1));
        $this->assertSame($this->cell4_1b2b, $this->cell4_1h2b->getPreviousCellForAxis($this->axis1));
        $this->assertSame($this->cell4_1g2a, $this->cell4_1i2a->getPreviousCellForAxis($this->axis1));
        $this->assertSame($this->cell4_1g2b, $this->cell4_1i2b->getPreviousCellForAxis($this->axis1));
        $this->assertSame($this->cell4_1f2a, $this->cell4_1j2a->getPreviousCellForAxis($this->axis1));
        $this->assertSame($this->cell4_1f2b, $this->cell4_1j2b->getPreviousCellForAxis($this->axis1));
        $this->assertSame($this->cell4_1e2a, $this->cell4_1k2a->getPreviousCellForAxis($this->axis1));
        $this->assertSame($this->cell4_1e2b, $this->cell4_1k2b->getPreviousCellForAxis($this->axis1));
        $this->assertSame($this->cell4_1j2a, $this->cell4_1l2a->getPreviousCellForAxis($this->axis1));
        $this->assertSame($this->cell4_1j2b, $this->cell4_1l2b->getPreviousCellForAxis($this->axis1));
    }

    /**
     * @expectedException Core_Exception_InvalidArgument
     * @expectedexceptionMessage Given axis needs to be used by this cell's granularity and allows member positioning.
     */
    function testGetPreviousCellForAxisNotPositioning()
    {
        $this->cell4_1a2a->getPreviousCellForAxis($this->axis2);
    }

    /**
     * @expectedException Core_Exception_InvalidArgument
     * @expectedexceptionMessage Given axis needs to be used by this cell's granularity and allows member positioning.
     */
    function testGetNextCellForAxisNotUsedByGranularity()
    {
        $this->cell0_0->getNextCellForAxis($this->axis1);
    }

    function testGetNextForAxis()
    {
        $this->assertSame($this->cell1_111b, $this->cell1_111a->getNextCellForAxis($this->axis111));
        $this->assertNull($this->cell1_111b->getNextCellForAxis($this->axis111));

        $this->assertNull($this->cell2_11a12a->getNextCellForAxis($this->axis11));
        $this->assertNull($this->cell2_11a12b->getNextCellForAxis($this->axis11));
        $this->assertSame($this->cell2_11c12a, $this->cell2_11b12a->getNextCellForAxis($this->axis11));
        $this->assertSame($this->cell2_11c12b, $this->cell2_11b12b->getNextCellForAxis($this->axis11));
        $this->assertNull($this->cell2_11c12a->getNextCellForAxis($this->axis11));
        $this->assertNull($this->cell2_11c12b->getNextCellForAxis($this->axis11));

        $this->assertSame($this->cell2_11a12b, $this->cell2_11a12a->getNextCellForAxis($this->axis12));
        $this->assertNull($this->cell2_11a12b->getNextCellForAxis($this->axis12));
        $this->assertSame($this->cell2_11b12b, $this->cell2_11b12a->getNextCellForAxis($this->axis12));
        $this->assertNull($this->cell2_11b12b->getNextCellForAxis($this->axis12));
        $this->assertSame($this->cell2_11c12b, $this->cell2_11c12a->getNextCellForAxis($this->axis12));
        $this->assertNull($this->cell2_11c12b->getNextCellForAxis($this->axis12));

        $this->assertNull($this->cell3_11a12a2a->getNextCellForAxis($this->axis11));
        $this->assertNull($this->cell3_11a12a2b->getNextCellForAxis($this->axis11));
        $this->assertNull($this->cell3_11a12b2a->getNextCellForAxis($this->axis11));
        $this->assertNull($this->cell3_11a12b2b->getNextCellForAxis($this->axis11));
        $this->assertSame($this->cell3_11c12a2a, $this->cell3_11b12a2a->getNextCellForAxis($this->axis11));
        $this->assertSame($this->cell3_11c12a2b, $this->cell3_11b12a2b->getNextCellForAxis($this->axis11));
        $this->assertSame($this->cell3_11c12b2a, $this->cell3_11b12b2a->getNextCellForAxis($this->axis11));
        $this->assertSame($this->cell3_11c12b2b, $this->cell3_11b12b2b->getNextCellForAxis($this->axis11));
        $this->assertNull($this->cell3_11c12a2a->getNextCellForAxis($this->axis11));
        $this->assertNull($this->cell3_11c12a2b->getNextCellForAxis($this->axis11));
        $this->assertNull($this->cell3_11c12b2a->getNextCellForAxis($this->axis11));
        $this->assertNull($this->cell3_11c12b2b->getNextCellForAxis($this->axis11));

        $this->assertSame($this->cell3_11a12b2a, $this->cell3_11a12a2a->getNextCellForAxis($this->axis12));
        $this->assertSame($this->cell3_11a12b2b, $this->cell3_11a12a2b->getNextCellForAxis($this->axis12));
        $this->assertNull($this->cell3_11a12b2a->getNextCellForAxis($this->axis12));
        $this->assertNull($this->cell3_11a12b2b->getNextCellForAxis($this->axis12));
        $this->assertSame($this->cell3_11b12b2a, $this->cell3_11b12a2a->getNextCellForAxis($this->axis12));
        $this->assertSame($this->cell3_11b12b2b, $this->cell3_11b12a2b->getNextCellForAxis($this->axis12));
        $this->assertNull($this->cell3_11b12b2a->getNextCellForAxis($this->axis12));
        $this->assertNull($this->cell3_11b12b2b->getNextCellForAxis($this->axis12));
        $this->assertSame($this->cell3_11c12b2a, $this->cell3_11c12a2a->getNextCellForAxis($this->axis12));
        $this->assertSame($this->cell3_11c12b2b, $this->cell3_11c12a2b->getNextCellForAxis($this->axis12));
        $this->assertNull($this->cell3_11c12b2a->getNextCellForAxis($this->axis12));
        $this->assertNull($this->cell3_11c12b2b->getNextCellForAxis($this->axis12));

        $this->assertSame($this->cell4_1g2a, $this->cell4_1a2a->getNextCellForAxis($this->axis1));
        $this->assertSame($this->cell4_1g2b, $this->cell4_1a2b->getNextCellForAxis($this->axis1));
        $this->assertSame($this->cell4_1h2a, $this->cell4_1b2a->getNextCellForAxis($this->axis1));
        $this->assertSame($this->cell4_1h2b, $this->cell4_1b2b->getNextCellForAxis($this->axis1));
        $this->assertSame($this->cell4_1e2a, $this->cell4_1c2a->getNextCellForAxis($this->axis1));
        $this->assertSame($this->cell4_1e2b, $this->cell4_1c2b->getNextCellForAxis($this->axis1));
        $this->assertSame($this->cell4_1f2a, $this->cell4_1d2a->getNextCellForAxis($this->axis1));
        $this->assertSame($this->cell4_1f2b, $this->cell4_1d2b->getNextCellForAxis($this->axis1));
        $this->assertSame($this->cell4_1k2a, $this->cell4_1e2a->getNextCellForAxis($this->axis1));
        $this->assertSame($this->cell4_1k2b, $this->cell4_1e2b->getNextCellForAxis($this->axis1));
        $this->assertSame($this->cell4_1j2a, $this->cell4_1f2a->getNextCellForAxis($this->axis1));
        $this->assertSame($this->cell4_1j2b, $this->cell4_1f2b->getNextCellForAxis($this->axis1));
        $this->assertSame($this->cell4_1i2a, $this->cell4_1g2a->getNextCellForAxis($this->axis1));
        $this->assertSame($this->cell4_1i2b, $this->cell4_1g2b->getNextCellForAxis($this->axis1));
        $this->assertNull($this->cell4_1h2a->getNextCellForAxis($this->axis1));
        $this->assertNull($this->cell4_1h2b->getNextCellForAxis($this->axis1));
        $this->assertNull($this->cell4_1i2a->getNextCellForAxis($this->axis1));
        $this->assertNull($this->cell4_1i2b->getNextCellForAxis($this->axis1));
        $this->assertSame($this->cell4_1l2a, $this->cell4_1j2a->getNextCellForAxis($this->axis1));
        $this->assertSame($this->cell4_1l2b, $this->cell4_1j2b->getNextCellForAxis($this->axis1));
        $this->assertNull($this->cell4_1k2a->getNextCellForAxis($this->axis1));
        $this->assertNull($this->cell4_1k2b->getNextCellForAxis($this->axis1));
        $this->assertNull($this->cell4_1l2a->getNextCellForAxis($this->axis1));
        $this->assertNull($this->cell4_1l2b->getNextCellForAxis($this->axis1));
    }

    /**
     * @expectedException Core_Exception_InvalidArgument
     * @expectedexceptionMessage Given axis needs to be used by this cell's granularity and allows member positioning.
     */
    function testGetNextCellForAxisNotPositioning()
    {
        $this->cell4_1a2a->getNextCellForAxis($this->axis2);
    }

}
