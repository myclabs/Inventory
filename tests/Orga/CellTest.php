<?php
use Account\Domain\Account;
use Core\Test\TestCase;

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
        return $suite;
    }

}


/**
 * Tests de la classe Organization
 * @package Organization
 * @subpackage Test
 */
class Orga_Test_CellAttributes extends TestCase
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
    protected $axis2;
    /**
     * @var Orga_Model_Member
     */
    protected $member1a;
    /**
     * @var Orga_Model_Member
     */
    protected $member1b;
    /**
     * @var Orga_Model_Member
     */
    protected $member1c;
    /**
     * @var Orga_Model_Member
     */
    protected $member1d;
    /**
     * @var Orga_Model_Member
     */
    protected $member1e;
    /**
     * @var Orga_Model_Member
     */
    protected $member1f;
    /**
     * @var Orga_Model_Member
     */
    protected $member11a;
    /**
     * @var Orga_Model_Member
     */
    protected $member11b;
    /**
     * @var Orga_Model_Member
     */
    protected $member11c;
    /**
     * @var Orga_Model_Member
     */
    protected $member111a;
    /**
     * @var Orga_Model_Member
     */
    protected $member111b;
    /**
     * @var Orga_Model_Member
     */
    protected $member12a;
    /**
     * @var Orga_Model_Member
     */
    protected $member12b;
    /**
     * @var Orga_Model_Member
     */
    protected $member2a;
    /**
     * @var Orga_Model_Member
     */
    protected $member2b;
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
     * @var Orga_Model_Cell
     */
    protected $cell0_0;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell1_111a;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell1_111b;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell2_11a12a;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell2_11b12a;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell2_11c12a;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell2_11a12b;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell2_11b12b;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell2_11c12b;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell3_11a12a2a;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell3_11b12a2a;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell3_11c12a2a;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell3_11a12b2a;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell3_11b12b2a;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell3_11c12b2a;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell3_11a12a2b;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell3_11b12a2b;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell3_11c12a2b;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell3_11a12b2b;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell3_11b12b2b;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell3_11c12b2b;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell4_1a2a;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell4_1b2a;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell4_1c2a;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell4_1d2a;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell4_1e2a;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell4_1f2a;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell4_1a2b;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell4_1b2b;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell4_1c2b;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell4_1d2b;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell4_1e2b;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell4_1f2b;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell5_2a;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell5_2b;

    public function setUp()
    {
        parent::setUp();

        $this->organization = new Orga_Model_Organization(
            $this->getMockBuilder(Account::class)->disableOriginalConstructor()->getMock()
        );

        $this->axis1 = new Orga_Model_Axis($this->organization, 'ref_1');
        $this->axis1->getLabel()->set('Label 1', 'fr');

        $this->axis11 = new Orga_Model_Axis($this->organization, 'ref_11', $this->axis1);
        $this->axis11->getLabel()->set('Label 11', 'fr');

        $this->axis111 = new Orga_Model_Axis($this->organization, 'ref_111', $this->axis11);
        $this->axis111->getLabel()->set('Label 111', 'fr');

        $this->axis12 = new Orga_Model_Axis($this->organization, 'ref_12', $this->axis1);
        $this->axis12->getLabel()->set('Label 12', 'fr');

        $this->axis2 = new Orga_Model_Axis($this->organization, 'ref_2');
        $this->axis2->getLabel()->set('Label 2', 'fr');

        $this->member111a = new Orga_Model_Member($this->axis111, 'ref111_a');
        $this->member111a->getLabel()->set('Label 111 A', 'fr');
        $this->member111b = new Orga_Model_Member($this->axis111, 'ref111_b');
        $this->member111b->getLabel()->set('Label 111 B', 'fr');

        $this->member11a = new Orga_Model_Member($this->axis11, 'ref11_a', [$this->member111a]);
        $this->member11a->getLabel()->set('Label 11 A', 'fr');
        $this->member11b = new Orga_Model_Member($this->axis11, 'ref11_b', [$this->member111b]);
        $this->member11b->getLabel()->set('Label 11 B', 'fr');
        $this->member11c = new Orga_Model_Member($this->axis11, 'ref11_c', [$this->member111b]);
        $this->member11c->getLabel()->set('Label 11 C', 'fr');

        $this->member12a = new Orga_Model_Member($this->axis12, 'ref12_a');
        $this->member12a->getLabel()->set('Label 12 A', 'fr');
        $this->member12b = new Orga_Model_Member($this->axis12, 'ref12_b');
        $this->member12b->getLabel()->set('Label 12 B', 'fr');

        $this->member1a = new Orga_Model_Member($this->axis1, 'ref1_a', [$this->member11a, $this->member12a]);
        $this->member1a->getLabel()->set('Label 1 A', 'fr');
        $this->member1b = new Orga_Model_Member($this->axis1, 'ref1_b', [$this->member11a, $this->member12b]);
        $this->member1b->getLabel()->set('Label 1 B', 'fr');
        $this->member1c = new Orga_Model_Member($this->axis1, 'ref1_c', [$this->member11b, $this->member12a]);
        $this->member1c->getLabel()->set('Label 1 C', 'fr');
        $this->member1d = new Orga_Model_Member($this->axis1, 'ref1_d', [$this->member11b, $this->member12b]);
        $this->member1d->getLabel()->set('Label 1 D', 'fr');
        $this->member1e = new Orga_Model_Member($this->axis1, 'ref1_e', [$this->member11c, $this->member12a]);
        $this->member1e->getLabel()->set('Label 1 E', 'fr');
        $this->member1f = new Orga_Model_Member($this->axis1, 'ref1_f', [$this->member11c, $this->member12b]);
        $this->member1f->getLabel()->set('Label 1 F', 'fr');

        $this->member2a = new Orga_Model_Member($this->axis2, 'ref2_a');
        $this->member2a->getLabel()->set('Label 2 A', 'fr');
        $this->member2b = new Orga_Model_Member($this->axis2, 'ref2_b');
        $this->member2b->getLabel()->set('Label 2 B', 'fr');

        $this->granularity0 = new Orga_Model_Granularity($this->organization, []);
        $this->granularity0->setCellsControlRelevance(true);
        $this->granularity1 = new Orga_Model_Granularity($this->organization, [$this->axis111]);
        $this->granularity1->setCellsControlRelevance(true);
        $this->granularity2 = new Orga_Model_Granularity($this->organization, [$this->axis11, $this->axis12]);
        $this->granularity2->setCellsControlRelevance(true);
        $this->granularity3 = new Orga_Model_Granularity($this->organization, [$this->axis11, $this->axis12, $this->axis2]);
        $this->granularity3->setCellsControlRelevance(true);
        $this->granularity4 = new Orga_Model_Granularity($this->organization, [$this->axis1, $this->axis2]);
        $this->granularity4->setCellsControlRelevance(true);
        $this->granularity5 = new Orga_Model_Granularity($this->organization, [$this->axis2]);
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
        $granularity6 = new Orga_Model_Granularity($this->organization, [$this->axis12]);
        $granularity6->getCellByMembers([$this->member12b])->setRelevant(false);
    }

    function testEnableDisable()
    {
        // Désactivation de la partie member12b pour vérifier les mises à jour vers Relevant et NotRelevant.
        $granularity6 = new Orga_Model_Granularity($this->organization, [$this->axis12]);
        $granularity6->setCellsControlRelevance(true);
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
        $this->organization->removeAxis($this->axis11);

        $this->assertTrue($this->cell0_0->isRelevant());

        $this->assertTrue($this->cell1_111a->isRelevant());
        $this->assertTrue($this->cell1_111b->isRelevant());

        $this->assertFalse($this->cell4_1a2a->isRelevant());
        $this->assertFalse($this->cell4_1b2a->isRelevant());
        $this->assertFalse($this->cell4_1c2a->isRelevant());
        $this->assertFalse($this->cell4_1d2a->isRelevant());
        $this->assertFalse($this->cell4_1e2a->isRelevant());
        $this->assertFalse($this->cell4_1f2a->isRelevant());
        $this->assertFalse($this->cell4_1a2b->isRelevant());
        $this->assertFalse($this->cell4_1b2b->isRelevant());
        $this->assertFalse($this->cell4_1c2b->isRelevant());
        $this->assertFalse($this->cell4_1d2b->isRelevant());
        $this->assertFalse($this->cell4_1e2b->isRelevant());
        $this->assertFalse($this->cell4_1f2b->isRelevant());

        $this->assertTrue($this->cell5_2a->isRelevant());
        $this->assertTrue($this->cell5_2b->isRelevant());

        // Utile pour vérifier la mise à jour futur des cellules dont cell1_111a deviendra la nouvelle parente.
        $this->cell1_111a->setRelevant(false);

        $this->assertTrue($this->cell0_0->isRelevant());

        $this->assertFalse($this->cell1_111a->isRelevant());
        $this->assertTrue($this->cell1_111b->isRelevant());

        $this->assertFalse($this->cell4_1a2a->isRelevant());
        $this->assertFalse($this->cell4_1b2a->isRelevant());
        $this->assertFalse($this->cell4_1c2a->isRelevant());
        $this->assertFalse($this->cell4_1d2a->isRelevant());
        $this->assertFalse($this->cell4_1e2a->isRelevant());
        $this->assertFalse($this->cell4_1f2a->isRelevant());
        $this->assertFalse($this->cell4_1a2b->isRelevant());
        $this->assertFalse($this->cell4_1b2b->isRelevant());
        $this->assertFalse($this->cell4_1c2b->isRelevant());
        $this->assertFalse($this->cell4_1d2b->isRelevant());
        $this->assertFalse($this->cell4_1e2b->isRelevant());
        $this->assertFalse($this->cell4_1f2b->isRelevant());

        $this->assertTrue($this->cell5_2a->isRelevant());
        $this->assertTrue($this->cell5_2b->isRelevant());

        // Définition des parents des membres de axis1 pour axis111, son nouveau parent remplaçant axis11.
        $this->member1a->setDirectParentForAxis($this->member111a);
        $this->member1b->setDirectParentForAxis($this->member111a);
        $this->member1c->setDirectParentForAxis($this->member111b);
        $this->member1d->setDirectParentForAxis($this->member111b);
        $this->member1e->setDirectParentForAxis($this->member111a);
        $this->member1f->setDirectParentForAxis($this->member111b);

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
 * Tests de la classe Organization
 * @package Organization
 * @subpackage Test
 */
class Orga_Test_CellHierarchy extends TestCase
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
    protected $axis2;
    /**
     * @var Orga_Model_Member
     */
    protected $member1a;
    /**
     * @var Orga_Model_Member
     */
    protected $member1b;
    /**
     * @var Orga_Model_Member
     */
    protected $member1c;
    /**
     * @var Orga_Model_Member
     */
    protected $member1d;
    /**
     * @var Orga_Model_Member
     */
    protected $member1e;
    /**
     * @var Orga_Model_Member
     */
    protected $member1f;
    /**
     * @var Orga_Model_Member
     */
    protected $member11a;
    /**
     * @var Orga_Model_Member
     */
    protected $member11b;
    /**
     * @var Orga_Model_Member
     */
    protected $member11c;
    /**
     * @var Orga_Model_Member
     */
    protected $member111a;
    /**
     * @var Orga_Model_Member
     */
    protected $member111b;
    /**
     * @var Orga_Model_Member
     */
    protected $member12a;
    /**
     * @var Orga_Model_Member
     */
    protected $member12b;
    /**
     * @var Orga_Model_Member
     */
    protected $member2a;
    /**
     * @var Orga_Model_Member
     */
    protected $member2b;
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
     * @var Orga_Model_Cell
     */
    protected $cell0_0;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell1_111a;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell1_111b;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell2_11a12a;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell2_11b12a;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell2_11c12a;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell2_11a12b;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell2_11b12b;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell2_11c12b;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell3_11a12a2a;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell3_11b12a2a;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell3_11c12a2a;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell3_11a12b2a;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell3_11b12b2a;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell3_11c12b2a;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell3_11a12a2b;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell3_11b12a2b;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell3_11c12a2b;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell3_11a12b2b;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell3_11b12b2b;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell3_11c12b2b;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell4_1a2a;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell4_1b2a;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell4_1c2a;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell4_1d2a;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell4_1e2a;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell4_1f2a;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell4_1a2b;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell4_1b2b;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell4_1c2b;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell4_1d2b;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell4_1e2b;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell4_1f2b;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell5_2a;
    /**
     * @var Orga_Model_Cell
     */
    protected $cell5_2b;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->organization = new Orga_Model_Organization($this->getMockBuilder(Account::class)->disableOriginalConstructor()->getMock());

        $this->axis1 = new Orga_Model_Axis($this->organization, 'ref_1');
        $this->axis1->getLabel()->set('Label 1', 'fr');

        $this->axis11 = new Orga_Model_Axis($this->organization, 'ref_11', $this->axis1);
        $this->axis11->getLabel()->set('Label 11', 'fr');

        $this->axis111 = new Orga_Model_Axis($this->organization, 'ref_111', $this->axis11);
        $this->axis111->getLabel()->set('Label 111', 'fr');

        $this->axis12 = new Orga_Model_Axis($this->organization, 'ref_12', $this->axis1);
        $this->axis12->getLabel()->set('Label 12', 'fr');

        $this->axis2 = new Orga_Model_Axis($this->organization, 'ref_2');
        $this->axis2->setRef('ref_2');
        $this->axis2->getLabel()->set('Label 2', 'fr');

        $this->member111a = new Orga_Model_Member($this->axis111, 'ref111_a');
        $this->member111b = new Orga_Model_Member($this->axis111, 'ref111_b');

        $this->member11a = new Orga_Model_Member($this->axis11, 'ref11_a', [$this->member111a]);
        $this->member11b = new Orga_Model_Member($this->axis11, 'ref11_b', [$this->member111b]);
        $this->member11c = new Orga_Model_Member($this->axis11, 'ref11_c', [$this->member111b]);

        $this->member12a = new Orga_Model_Member($this->axis12, 'ref12_a');
        $this->member12b = new Orga_Model_Member($this->axis12, 'ref12_b');

        $this->member1a = new Orga_Model_Member($this->axis1, 'ref1_a', [$this->member11a, $this->member12a]);
        $this->member1b = new Orga_Model_Member($this->axis1, 'ref1_b', [$this->member11a, $this->member12b]);
        $this->member1c = new Orga_Model_Member($this->axis1, 'ref1_c', [$this->member11b, $this->member12a]);
        $this->member1d = new Orga_Model_Member($this->axis1, 'ref1_d', [$this->member11b, $this->member12b]);
        $this->member1e = new Orga_Model_Member($this->axis1, 'ref1_e', [$this->member11c, $this->member12a]);
        $this->member1f = new Orga_Model_Member($this->axis1, 'ref1_f', [$this->member11c, $this->member12b]);

        $this->member2a = new Orga_Model_Member($this->axis2, 'ref2_a');
        $this->member2b = new Orga_Model_Member($this->axis2, 'ref2_b');

        $this->granularity0 = new Orga_Model_Granularity($this->organization, []);
        $this->granularity1 = new Orga_Model_Granularity($this->organization, [$this->axis111]);
        $this->granularity2 = new Orga_Model_Granularity($this->organization, [$this->axis11, $this->axis12]);
        $this->granularity3 = new Orga_Model_Granularity($this->organization, [$this->axis11, $this->axis12, $this->axis2]);
        $this->granularity4 = new Orga_Model_Granularity($this->organization, [$this->axis1, $this->axis2]);
        $this->granularity5 = new Orga_Model_Granularity($this->organization, [$this->axis2]);

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
