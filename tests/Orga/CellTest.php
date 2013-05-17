<?php
/**
 * Class Orga_Test_CellTest
 * @author valentin.claras
 * @author sidoine.Tardieu
 * @package    Orga
 * @subpackage Test
 */

//require_once dirname(__FILE__).'/CubeTest.php';

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
        $suite->addTestSuite('Orga_Test_CellOthers');
        return $suite;
    }

}


/**
 * Tests de la classe Cube
 * @package Orga
 * @subpackage Test
 */
class Orga_Test_CellOthers extends PHPUnit_Framework_TestCase
{
    /**
     * @var Orga_Model_Cube
     */
    protected $cube;

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
     * @var Orga_Model_Axis
     */
    protected $axis21;

    /**
     * @var Orga_Model_Member
     */
    protected $member1A111;

    /**
     * @var Orga_Model_Member
     */
    protected $member2A111;

    /**
     * @var Orga_Model_Member
     */
    protected $member1A11;

    /**
     * @var Orga_Model_Member
     */
    protected $member2A11;

    /**
     * @var Orga_Model_Member
     */
    protected $member3A11;

    /**
     * @var Orga_Model_Member
     */
    protected $member1A12;

    /**
     * @var Orga_Model_Member
     */
    protected $member1A1;

    /**
     * @var Orga_Model_Member
     */
    protected $member2A1;

    /**
     * @var Orga_Model_Member
     */
    protected $member1A21;

    /**
     * @var Orga_Model_Member
     */
    protected $member2A21;

    /**
     * @var Orga_Model_Member
     */
    protected $member1A2;

    /**
     * @var Orga_Model_Member
     */
    protected $member2A2;

    /**
     * @var Orga_Model_Member
     */
    protected $member3A2;

    /**
     * @var Orga_Model_Member
     */
    protected $member4A2;

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
     * Fonction appelee une fois, avant tous les tests
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun Orga_Model_Cell en base, sinon suppression !
        if (Orga_Model_Cell::countTotal() > 0) {
            echo PHP_EOL . 'Des Orga_Cell restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Orga_Model_Cell::loadList() as $cell) {
                $cell->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun Orga_Model_Granularity en base, sinon suppression !
        if (Orga_Model_Granularity::countTotal() > 0) {
            echo PHP_EOL . 'Des Orga_Granularity restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Orga_Model_Granularity::loadList() as $granularity) {
                $granularity->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun Orga_Model_Member en base, sinon suppression !
        if (Orga_Model_Member::countTotal() > 0) {
            echo PHP_EOL . 'Des Orga_Member restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Orga_Model_Member::loadList() as $member) {
                $member->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun Orga_Model_Axis en base, sinon suppression !
        if (Orga_Model_Axis::countTotal() > 0) {
            echo PHP_EOL . 'Des Orga_Axis restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Orga_Model_Axis::loadList() as $axis) {
                $axis->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun Orga_Model_Cube en base, sinon suppression !
        if (Orga_Model_Cube::countTotal() > 0) {
            echo PHP_EOL . 'Des Orga_Cube restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Orga_Model_Cube::loadList() as $cube) {
                $cube->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }

    /**
     * Fonction appelee avant chaque test
     */
    protected function setUp()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');

        $this->cube = Orga_Test_CubeTest::generateObject();

        $this->axis1 = new Orga_Model_Axis();
        $this->axis1->setRef('RefCell1');
        $this->axis1->setLabel('LabelCell1');
        $this->axis1->setCube($this->cube);
        $this->axis1->save();

        $this->axis11 = new Orga_Model_Axis();
        $this->axis11->setRef('RefCell11');
        $this->axis11->setLabel('LabelCell11');
        $this->axis11->setCube($this->cube);
        $this->axis11->setDirectNarrower($this->axis1);
        $this->axis11->save();

        $this->axis111 = new Orga_Model_Axis();
        $this->axis111->setRef('RefCell111');
        $this->axis111->setLabel('LabelCell111');
        $this->axis111->setCube($this->cube);
        $this->axis111->setDirectNarrower($this->axis11);
        $this->axis111->save();

        $this->axis12 = new Orga_Model_Axis();
        $this->axis12->setRef('RefCell12');
        $this->axis12->setLabel('LabelCell12');
        $this->axis12->setCube($this->cube);
        $this->axis12->setDirectNarrower($this->axis1);
        $this->axis12->save();

        $this->axis2 = new Orga_Model_Axis();
        $this->axis2->setRef('RefCell2');
        $this->axis2->setLabel('LabelCell2');
        $this->axis2->setCube($this->cube);
        $this->axis2->save();

        $this->axis21 = new Orga_Model_Axis();
        $this->axis21->setRef('RefCell21');
        $this->axis21->setLabel('LabelCell21');
        $this->axis21->setCube($this->cube);
        $this->axis21->setDirectNarrower($this->axis2);
        $this->axis21->save();
        $entityManagers['default']->flush();

        $this->member1A111 = new Orga_Model_Member();
        $this->member1A111->setRef('RefCell1A111');
        $this->member1A111->setLabel('LabelCell1A111');
        $this->member1A111->setAxis($this->axis111);
        $this->member1A111->save();

        $this->member2A111 = new Orga_Model_Member();
        $this->member2A111->setRef('RefCell2A111');
        $this->member2A111->setLabel('LabelCell2A111');
        $this->member2A111->setAxis($this->axis111);
        $this->member2A111->save();

        $this->member1A11 = new Orga_Model_Member();
        $this->member1A11->setRef('RefCell1A11');
        $this->member1A11->setLabel('LabelCel1Al11');
        $this->member1A11->setAxis($this->axis11);
        $this->member1A11->save();
        $this->member1A11->addDirectParent($this->member1A111);

        $this->member2A11 = new Orga_Model_Member();
        $this->member2A11->setRef('RefCell2A11');
        $this->member2A11->setLabel('LabelCell2A11');
        $this->member2A11->setAxis($this->axis11);
        $this->member2A11->save();
        $this->member2A11->addDirectParent($this->member2A111);

        $this->member3A11 = new Orga_Model_Member();
        $this->member3A11->setRef('RefCell3A11');
        $this->member3A11->setLabel('LabelCell3A11');
        $this->member3A11->setAxis($this->axis11);
        $this->member3A11->save();
        $this->member3A11->addDirectParent($this->member2A111);

        $this->member1A12 = new Orga_Model_Member();
        $this->member1A12->setRef('RefCell1A12');
        $this->member1A12->setLabel('LabelCell1A12');
        $this->member1A12->setAxis($this->axis12);
        $this->member1A12->save();

        $this->member1A1 = new Orga_Model_Member();
        $this->member1A1->setRef('RefCell1A1');
        $this->member1A1->setLabel('LabelCell1A1');
        $this->member1A1->setAxis($this->axis1);
        $this->member1A1->save();
        $this->member1A1->addDirectParent($this->member1A11);
        $this->member1A1->addDirectParent($this->member1A12);

        $this->member2A1 = new Orga_Model_Member();
        $this->member2A1->setRef('RefCell2A1');
        $this->member2A1->setLabel('LabelCell2A1');
        $this->member2A1->setAxis($this->axis1);
        $this->member2A1->save();
        $this->member2A1->addDirectParent($this->member2A11);
        $this->member2A1->addDirectParent($this->member1A12);

        $this->member1A21 = new Orga_Model_Member();
        $this->member1A21->setRef('RefCell1A21');
        $this->member1A21->setLabel('LabelCell1A21');
        $this->member1A21->setAxis($this->axis21);
        $this->member1A21->save();

        $this->member2A21 = new Orga_Model_Member();
        $this->member2A21->setRef('RefCell2A21');
        $this->member2A21->setLabel('LabelCell2A21');
        $this->member2A21->setAxis($this->axis21);
        $this->member2A21->save();

        $this->member1A2 = new Orga_Model_Member();
        $this->member1A2->setRef('RefCell1A2');
        $this->member1A2->setLabel('LabelCell1A2');
        $this->member1A2->setAxis($this->axis2);
        $this->member1A2->save();
        $this->member1A2->addDirectParent($this->member1A21);

        $this->member2A2 = new Orga_Model_Member();
        $this->member2A2->setRef('RefCell2A2');
        $this->member2A2->setLabel('LabelCell2A2');
        $this->member2A2->setAxis($this->axis2);
        $this->member2A2->save();
        $this->member2A2->addDirectParent($this->member1A21);

        $this->member3A2 = new Orga_Model_Member();
        $this->member3A2->setRef('RefCell3A2');
        $this->member3A2->setLabel('LabelCell3A2');
        $this->member3A2->setAxis($this->axis2);
        $this->member3A2->save();
        $this->member3A2->addDirectParent($this->member1A21);

        $this->member4A2 = new Orga_Model_Member();
        $this->member4A2->setRef('RefCell4A2');
        $this->member4A2->setLabel('LabelCell4A2');
        $this->member4A2->setAxis($this->axis2);
        $this->member4A2->save();
        $this->member4A2->addDirectParent($this->member2A21);

        $this->granularity0 = new Orga_Model_Granularity();
        $this->granularity0->setCube($this->cube);
        $this->granularity0->save();

        $this->granularity1 = new Orga_Model_Granularity();
        $this->granularity1->setCube($this->cube);
        $this->granularity1->addAxis($this->axis111);
        $this->granularity1->addAxis($this->axis21);
        $this->granularity1->save();

        $this->granularity2 = new Orga_Model_Granularity();
        $this->granularity2->setCube($this->cube);
        $this->granularity2->addAxis($this->axis11);
        $this->granularity2->addAxis($this->axis21);
        $this->granularity2->save();

        $this->granularity3 = new Orga_Model_Granularity();
        $this->granularity3->setCube($this->cube);
        $this->granularity3->addAxis($this->axis12);
        $this->granularity3->addAxis($this->axis21);
        $this->granularity3->save();

        $this->granularity4 = new Orga_Model_Granularity();
        $this->granularity4->setCube($this->cube);
        $this->granularity4->addAxis($this->axis11);
        $this->granularity4->addAxis($this->axis2);
        $this->granularity4->save();

        $this->granularity5 = new Orga_Model_Granularity();
        $this->granularity5->setCube($this->cube);
        $this->granularity5->addAxis($this->axis1);
        $this->granularity5->addAxis($this->axis2);
        $this->granularity5->save();

        $entityManagers['default']->flush();
    }

    /**
     * Test les méthodes de récupération des cellules enfants et parentes.
     */
    public function testLoadByGranularityAndListMembers()
    {
        $cellG0 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity0, array());
        $cell1G1 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity1, array($this->member1A111, $this->member1A21));
        $cell2G1 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity1, array($this->member2A111, $this->member2A21));
        $cell1G2 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity2, array($this->member1A11, $this->member1A21));
        $cell2G2 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity2, array($this->member3A11, $this->member2A21));
        $cell1G3 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity3, array($this->member1A12, $this->member1A21));
        $cell2G3 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity3, array($this->member1A12, $this->member2A21));
        $cell1G4 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity4, array($this->member1A11, $this->member2A2));
        $cell2G4 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity4, array($this->member3A11, $this->member4A2));
        $cell1G5 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity5, array($this->member1A1, $this->member2A2));
        $cell2G5 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity5, array($this->member2A1, $this->member4A2));

        $this->assertEquals(array(), $cellG0->getMembers());
        $this->assertEquals('', $cellG0->getMembersHashKey());

        $this->assertSame($this->granularity1, $cell1G1->getGranularity());
        $this->assertEquals(array($this->member1A111, $this->member1A21), $cell1G1->getMembers());
        $this->assertEquals('RefCell1A111#|RefCell1A21#', $cell1G1->getMembersHashKey());

        $this->assertSame($this->granularity1, $cell2G1->getGranularity());
        $this->assertEquals(array($this->member2A111, $this->member2A21), $cell2G1->getMembers());
        $this->assertEquals('RefCell2A111#|RefCell2A21#', $cell2G1->getMembersHashKey());

        $this->assertSame($this->granularity2, $cell1G2->getGranularity());
        $this->assertEquals(array($this->member1A11, $this->member1A21), $cell1G2->getMembers());
        $this->assertEquals('RefCell1A11#|RefCell1A21#', $cell1G2->getMembersHashKey());

        $this->assertSame($this->granularity2, $cell2G2->getGranularity());
        $this->assertEquals(array($this->member3A11, $this->member2A21), $cell2G2->getMembers());
        $this->assertEquals('RefCell3A11#|RefCell2A21#', $cell2G2->getMembersHashKey());

        $this->assertSame($this->granularity3, $cell1G3->getGranularity());
        $this->assertEquals(array($this->member1A12, $this->member1A21), $cell1G3->getMembers());
        $this->assertEquals('RefCell1A12#|RefCell1A21#', $cell1G3->getMembersHashKey());

        $this->assertSame($this->granularity3, $cell2G3->getGranularity());
        $this->assertEquals(array($this->member1A12, $this->member2A21), $cell2G3->getMembers());
        $this->assertEquals('RefCell1A12#|RefCell2A21#', $cell2G3->getMembersHashKey());

        $this->assertSame($this->granularity4, $cell1G4->getGranularity());
        $this->assertEquals(array($this->member1A11, $this->member2A2), $cell1G4->getMembers());
        $this->assertEquals('RefCell1A11#|RefCell2A2#', $cell1G4->getMembersHashKey());

        $this->assertSame($this->granularity4, $cell2G4->getGranularity());
        $this->assertEquals(array($this->member3A11, $this->member4A2), $cell2G4->getMembers());
        $this->assertEquals('RefCell3A11#|RefCell4A2#', $cell2G4->getMembersHashKey());

        $this->assertSame($this->granularity5, $cell1G5->getGranularity());
        $this->assertEquals(array($this->member1A1, $this->member2A2), $cell1G5->getMembers());
        $this->assertEquals('RefCell1A1#|RefCell2A2#', $cell1G5->getMembersHashKey());

        $this->assertSame($this->granularity5, $cell2G5->getGranularity());
        $this->assertEquals(array($this->member2A1, $this->member4A2), $cell2G5->getMembers());
        $this->assertEquals('RefCell2A1#|RefCell4A2#', $cell2G5->getMembersHashKey());
    }

    /**
     * Test les méthodes de récupération des cellules enfants et parentes.
     */
    public function testGetChildAndParentCells()
    {
        $cellG0 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity0, array());

        $cell1G1 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity1, array($this->member1A111, $this->member1A21));
        $cell2G1 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity1, array($this->member1A111, $this->member2A21));
        $cell3G1 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity1, array($this->member2A111, $this->member1A21));
        $cell4G1 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity1, array($this->member2A111, $this->member2A21));

        $cell1G2 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity2, array($this->member1A11, $this->member1A21));
        $cell2G2 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity2, array($this->member1A11, $this->member2A21));
        $cell3G2 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity2, array($this->member2A11, $this->member1A21));
        $cell4G2 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity2, array($this->member2A11, $this->member2A21));
        $cell5G2 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity2, array($this->member3A11, $this->member1A21));
        $cell6G2 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity2, array($this->member3A11, $this->member2A21));

        $cell1G3 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity3, array($this->member1A12, $this->member1A21));
        $cell2G3 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity3, array($this->member1A12, $this->member2A21));

        $cell1G4 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity4, array($this->member1A11, $this->member1A2));
        $cell2G4 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity4, array($this->member1A11, $this->member2A2));
        $cell3G4 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity4, array($this->member1A11, $this->member3A2));
        $cell4G4 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity4, array($this->member1A11, $this->member4A2));
        $cell5G4 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity4, array($this->member2A11, $this->member1A2));
        $cell6G4 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity4, array($this->member2A11, $this->member2A2));
        $cell7G4 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity4, array($this->member2A11, $this->member3A2));
        $cell8G4 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity4, array($this->member2A11, $this->member4A2));
        $cell9G4 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity4, array($this->member3A11, $this->member1A2));
        $cell10G4 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity4, array($this->member3A11, $this->member2A2));
        $cell11G4 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity4, array($this->member3A11, $this->member3A2));
        $cell12G4 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity4, array($this->member3A11, $this->member4A2));

        $cell1G5 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity5, array($this->member1A1, $this->member1A2));
        $cell2G5 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity5, array($this->member1A1, $this->member2A2));
        $cell3G5 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity5, array($this->member1A1, $this->member3A2));
        $cell4G5 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity5, array($this->member1A1, $this->member4A2));
        $cell5G5 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity5, array($this->member2A1, $this->member1A2));
        $cell6G5 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity5, array($this->member2A1, $this->member2A2));
        $cell7G5 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity5, array($this->member2A1, $this->member3A2));
        $cell8G5 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity5, array($this->member2A1, $this->member4A2));

        $this->assertEquals(array(), $cellG0->getParentCells());
        $childCells = array_merge($this->granularity1->getCells(), $this->granularity2->getCells(), $this->granularity3->getCells(), $this->granularity4->getCells(), $this->granularity5->getCells());
        $this->assertEquals(count($childCells), $cellG0->countTotalChildCells());
        foreach ($cellG0->getChildCells() as $childCell) {
            $this->assertTrue(in_array($childCell, $childCells));
        }

        $this->assertEquals(array($cellG0), $cell2G1->getParentCells());
        $childCells = array($cell2G2, $cell4G4, $cell4G5);
        $this->assertEquals(count($childCells), $cell2G1->countTotalChildCells());
        foreach ($cell2G1->getChildCells() as $childCell) {
            $this->assertTrue(in_array($childCell, $childCells));
        }

        $this->assertEquals(array($cellG0), $cell3G1->getParentCells());
        $childCells = array($cell3G2, $cell5G2, $cell5G4, $cell6G4,$cell7G4, $cell9G4, $cell10G4, $cell11G4, $cell5G5, $cell6G5, $cell7G5);
        $this->assertEquals(count($childCells), $cell3G1->countTotalChildCells());
        foreach ($cell3G1->getChildCells() as $childCell) {
            $this->assertTrue(in_array($childCell, $childCells));
        }

        $this->assertEquals(array($cell4G1, $cellG0), $cell4G2->getParentCells());
        $childCells = array($cell8G4, $cell8G5);
        $this->assertEquals(count($childCells), $cell4G2->countTotalChildCells());
        foreach ($cell4G2->getChildCells() as $childCell) {
            $this->assertTrue(in_array($childCell, $childCells));
        }

        $this->assertEquals(array($cell4G1, $cellG0), $cell6G2->getParentCells());
        $childCells = array($cell12G4);
        $this->assertEquals(count($childCells), $cell6G2->countTotalChildCells());
        foreach ($cell6G2->getChildCells() as $childCell) {
            $this->assertTrue(in_array($childCell, $childCells));
        }

        $this->assertEquals(array($cellG0), $cell2G3->getParentCells());
        $childCells = array($cell4G5, $cell8G5);
        $this->assertEquals(count($childCells), $cell2G3->countTotalChildCells());
        foreach ($cell2G3->getChildCells() as $childCell) {
            $this->assertTrue(in_array($childCell, $childCells));
        }

        $this->assertEquals(array($cell1G2, $cell1G1, $cellG0), $cell2G4->getParentCells());
        $childCells = array($cell2G5);
        $this->assertEquals(count($childCells), $cell2G4->countTotalChildCells());
        foreach ($cell2G4->getChildCells() as $childCell) {
            $this->assertTrue(in_array($childCell, $childCells));
        }

        $this->assertEquals(array($cell1G2, $cell1G1, $cellG0), $cell3G4->getParentCells());
        $childCells = array($cell3G5);
        $this->assertEquals(count($childCells), $cell3G4->countTotalChildCells());
        foreach ($cell3G4->getChildCells() as $childCell) {
            $this->assertTrue(in_array($childCell, $childCells));
        }

        $this->assertEquals(array($cell1G4, $cell1G3, $cell1G2, $cell1G1, $cellG0), $cell1G5->getParentCells());
        $childCells = array();
        $this->assertEquals(count($childCells), $cell1G5->countTotalChildCells());
        foreach ($cell1G5->getChildCells() as $childCell) {
            $this->assertTrue(in_array($childCell, $childCells));
        }

        $this->assertEquals($cell1G3, $cell3G5->getParentCellForGranularity($this->granularity3));

        $this->assertEquals($cell5G2, $cell11G4->getParentCellForGranularity($this->granularity2));

        $childCells = array($cell5G5, $cell6G5, $cell7G5);
        $this->assertEquals(count($childCells), $cell3G2->countTotalChildCellsForGranularity($this->granularity5));
        foreach ($cell3G2->getChildCellsForGranularity($this->granularity5) as $childCell) {
            $this->assertTrue(in_array($childCell, $childCells));
        }

        $childCells = array($cell8G4, $cell12G4);
        $this->assertEquals(count($childCells), $cell4G1->countTotalChildCellsForGranularity($this->granularity4));
        foreach ($cell4G1->getChildCellsForGranularity($this->granularity4) as $childCell) {
            $this->assertTrue(in_array($childCell, $childCells));
        }
    }

    /**
     * Test les méthodes de modification de la pertinence des cellules.
     */
    public function testRelevant()
    {
        $cellG0 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity0, array());

        $cell1G1 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity1, array($this->member1A111, $this->member1A21));
        $cell2G1 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity1, array($this->member1A111, $this->member2A21));
        $cell3G1 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity1, array($this->member2A111, $this->member1A21));
        $cell4G1 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity1, array($this->member2A111, $this->member2A21));

        $cell1G2 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity2, array($this->member1A11, $this->member1A21));
        $cell2G2 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity2, array($this->member1A11, $this->member2A21));
        $cell3G2 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity2, array($this->member2A11, $this->member1A21));
        $cell4G2 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity2, array($this->member2A11, $this->member2A21));
        $cell5G2 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity2, array($this->member3A11, $this->member1A21));
        $cell6G2 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity2, array($this->member3A11, $this->member2A21));

        $cell1G3 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity3, array($this->member1A12, $this->member1A21));
        $cell2G3 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity3, array($this->member1A12, $this->member2A21));

        $cell1G4 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity4, array($this->member1A11, $this->member1A2));
        $cell2G4 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity4, array($this->member1A11, $this->member2A2));
        $cell3G4 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity4, array($this->member1A11, $this->member3A2));
        $cell4G4 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity4, array($this->member1A11, $this->member4A2));
        $cell5G4 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity4, array($this->member2A11, $this->member1A2));
        $cell6G4 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity4, array($this->member2A11, $this->member2A2));
        $cell7G4 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity4, array($this->member2A11, $this->member3A2));
        $cell8G4 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity4, array($this->member2A11, $this->member4A2));
        $cell9G4 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity4, array($this->member3A11, $this->member1A2));
        $cell10G4 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity4, array($this->member3A11, $this->member2A2));
        $cell11G4 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity4, array($this->member3A11, $this->member3A2));
        $cell12G4 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity4, array($this->member3A11, $this->member4A2));

        $cell1G5 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity5, array($this->member1A1, $this->member1A2));
        $cell2G5 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity5, array($this->member1A1, $this->member2A2));
        $cell3G5 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity5, array($this->member1A1, $this->member3A2));
        $cell4G5 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity5, array($this->member1A1, $this->member4A2));
        $cell5G5 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity5, array($this->member2A1, $this->member1A2));
        $cell6G5 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity5, array($this->member2A1, $this->member2A2));
        $cell7G5 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity5, array($this->member2A1, $this->member3A2));
        $cell8G5 = Orga_Model_Cell::loadByGranularityAndListMembers($this->granularity5, array($this->member2A1, $this->member4A2));

        $cell1G1->setRelevant(false);

        $this->assertFalse($cell1G1->isRelevant());
        $this->assertFalse($cell1G2->isRelevant());
        $this->assertFalse($cell1G4->isRelevant());
        $this->assertFalse($cell2G4->isRelevant());
        $this->assertFalse($cell3G4->isRelevant());
        $this->assertFalse($cell1G5->isRelevant());
        $this->assertFalse($cell2G5->isRelevant());
        $this->assertFalse($cell3G5->isRelevant());

        $this->assertTrue($cellG0->isRelevant());
        $this->assertTrue($cell2G1->isRelevant());
        $this->assertTrue($cell3G1->isRelevant());
        $this->assertTrue($cell4G1->isRelevant());
        $this->assertTrue($cell2G2->isRelevant());
        $this->assertTrue($cell3G2->isRelevant());
        $this->assertTrue($cell4G2->isRelevant());
        $this->assertTrue($cell5G2->isRelevant());
        $this->assertTrue($cell6G2->isRelevant());
        $this->assertTrue($cell1G3->isRelevant());
        $this->assertTrue($cell2G3->isRelevant());
        $this->assertTrue($cell4G4->isRelevant());
        $this->assertTrue($cell5G4->isRelevant());
        $this->assertTrue($cell6G4->isRelevant());
        $this->assertTrue($cell7G4->isRelevant());
        $this->assertTrue($cell8G4->isRelevant());
        $this->assertTrue($cell9G4->isRelevant());
        $this->assertTrue($cell10G4->isRelevant());
        $this->assertTrue($cell11G4->isRelevant());
        $this->assertTrue($cell12G4->isRelevant());
        $this->assertTrue($cell4G5->isRelevant());
        $this->assertTrue($cell5G5->isRelevant());
        $this->assertTrue($cell6G5->isRelevant());
        $this->assertTrue($cell7G5->isRelevant());
        $this->assertTrue($cell8G5->isRelevant());

        $cell1G3->setRelevant(false);

        $this->assertFalse($cell1G1->isRelevant());
        $this->assertFalse($cell1G2->isRelevant());
        $this->assertFalse($cell1G3->isRelevant());
        $this->assertFalse($cell1G4->isRelevant());
        $this->assertFalse($cell2G4->isRelevant());
        $this->assertFalse($cell3G4->isRelevant());
        $this->assertFalse($cell1G5->isRelevant());
        $this->assertFalse($cell2G5->isRelevant());
        $this->assertFalse($cell3G5->isRelevant());
        $this->assertFalse($cell5G5->isRelevant());
        $this->assertFalse($cell6G5->isRelevant());
        $this->assertFalse($cell7G5->isRelevant());

        $this->assertTrue($cellG0->isRelevant());
        $this->assertTrue($cell2G1->isRelevant());
        $this->assertTrue($cell3G1->isRelevant());
        $this->assertTrue($cell4G1->isRelevant());
        $this->assertTrue($cell2G2->isRelevant());
        $this->assertTrue($cell3G2->isRelevant());
        $this->assertTrue($cell4G2->isRelevant());
        $this->assertTrue($cell5G2->isRelevant());
        $this->assertTrue($cell6G2->isRelevant());
        $this->assertTrue($cell2G3->isRelevant());
        $this->assertTrue($cell4G4->isRelevant());
        $this->assertTrue($cell5G4->isRelevant());
        $this->assertTrue($cell6G4->isRelevant());
        $this->assertTrue($cell7G4->isRelevant());
        $this->assertTrue($cell8G4->isRelevant());
        $this->assertTrue($cell9G4->isRelevant());
        $this->assertTrue($cell10G4->isRelevant());
        $this->assertTrue($cell11G4->isRelevant());
        $this->assertTrue($cell12G4->isRelevant());
        $this->assertTrue($cell4G5->isRelevant());
        $this->assertTrue($cell8G5->isRelevant());

        $cell1G1->setRelevant(true);

        $this->assertFalse($cell1G3->isRelevant());
        $this->assertFalse($cell1G5->isRelevant());
        $this->assertFalse($cell2G5->isRelevant());
        $this->assertFalse($cell3G5->isRelevant());
        $this->assertFalse($cell5G5->isRelevant());
        $this->assertFalse($cell6G5->isRelevant());
        $this->assertFalse($cell7G5->isRelevant());

        $this->assertTrue($cellG0->isRelevant());
        $this->assertTrue($cell1G1->isRelevant());
        $this->assertTrue($cell2G1->isRelevant());
        $this->assertTrue($cell3G1->isRelevant());
        $this->assertTrue($cell4G1->isRelevant());
        $this->assertTrue($cell1G2->isRelevant());
        $this->assertTrue($cell2G2->isRelevant());
        $this->assertTrue($cell3G2->isRelevant());
        $this->assertTrue($cell4G2->isRelevant());
        $this->assertTrue($cell5G2->isRelevant());
        $this->assertTrue($cell6G2->isRelevant());
        $this->assertTrue($cell2G3->isRelevant());
        $this->assertTrue($cell1G4->isRelevant());
        $this->assertTrue($cell2G4->isRelevant());
        $this->assertTrue($cell3G4->isRelevant());
        $this->assertTrue($cell4G4->isRelevant());
        $this->assertTrue($cell5G4->isRelevant());
        $this->assertTrue($cell6G4->isRelevant());
        $this->assertTrue($cell7G4->isRelevant());
        $this->assertTrue($cell8G4->isRelevant());
        $this->assertTrue($cell9G4->isRelevant());
        $this->assertTrue($cell10G4->isRelevant());
        $this->assertTrue($cell11G4->isRelevant());
        $this->assertTrue($cell12G4->isRelevant());
        $this->assertTrue($cell4G5->isRelevant());
        $this->assertTrue($cell8G5->isRelevant());
    }

    /**
     * Fonction appelee apres tous chaques test
     */
    protected function tearDown()
    {
        $this->cube->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

    /**
     * Fonction appelee une fois, apres tous les tests
     */
    public static function tearDownAfterClass()
    {
        // Vérification qu'il ne reste aucun Orga_Model_Cell en base, sinon suppression !
        if (Orga_Model_Cell::countTotal() > 0) {
            echo PHP_EOL . 'Des Orga_Cell restants ont été trouvé après les tests, suppression en cours !';
            foreach (Orga_Model_Cell::loadList() as $cell) {
                $cell->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun Orga_Model_Granularity en base, sinon suppression !
        if (Orga_Model_Granularity::countTotal() > 0) {
            echo PHP_EOL . 'Des Orga_Granularity restants ont été trouvé après les tests, suppression en cours !';
            foreach (Orga_Model_Granularity::loadList() as $granularity) {
                $granularity->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun Orga_Model_Member en base, sinon suppression !
        if (Orga_Model_Member::countTotal() > 0) {
            echo PHP_EOL . 'Des Orga_Member restants ont été trouvé après les tests, suppression en cours !';
            foreach (Orga_Model_Member::loadList() as $member) {
                $member->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun Orga_Model_Axis en base, sinon suppression !
        if (Orga_Model_Axis::countTotal() > 0) {
            echo PHP_EOL . 'Des Orga_Axis restants ont été trouvé après les tests, suppression en cours !';
            foreach (Orga_Model_Axis::loadList() as $axis) {
                $axis->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun Orga_Model_Cube en base, sinon suppression !
        if (Orga_Model_Cube::countTotal() > 0) {
            echo PHP_EOL . 'Des Orga_Cube restants ont été trouvé après les tests, suppression en cours !';
            foreach (Orga_Model_Cube::loadList() as $cube) {
                $cube->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }


}
