<?php
/**
 * Class Orga_Test_MemberTest
 * @author valentin.claras
 * @author sidoine.Tardieu
 * @package    Orga
 * @subpackage Test
 */

// require_once dirname(__FILE__).'/AxisTest.php';

/**
 * Creation de la suite de test concernant les Member.
 * @package    Orga
 * @subpackage Test
 */
class Orga_Test_MemberTest
{
    /**
     * Creation de la suite de test
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Orga_Test_MemberSetUp');
        $suite->addTestSuite('Orga_Test_MemberOthers');
        return $suite;
    }

    /**
     * Generation de l'objet de test
     * @param string $refMember
     * @param string $labelMember
     * @param Orga_Model_Axis $axis
     * @return Orga_Model_Member
     */
    public static function generateObject($refMember='RefTestMember', $labelMember='LabelMember', $axis=null)
    {
        if ($axis === null) {
            $axis = Orga_Test_AxisTest::generateObject();
        }
        $o = new Orga_Model_Member($axis);
        $o->setRef($refMember);
        $o->setLabel($labelMember);
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        return $o;
    }

    /**
     * Suppression d'un objet cree avec generateObject
     * @param Orga_Model_Member $o
     * @param bool $deleteAxis
     */
    public static function deleteObject($o, $deleteAxis=true)
    {
        if ($deleteAxis === true) {
            $o->getAxis()->delete();
        } else {
            $o->delete();
        }
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }
}

/**
 * Test de la creation/modification/suppression de l'entite
 * @package Organization
 * @subpackage Test
 */
class Orga_Test_MemberSetUp extends PHPUnit_Framework_TestCase
{
    /**
     * Fonction appelee une fois, avant tous les tests
     */
    public static function setUpBeforeClass()
    {
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
        // Vérification qu'il ne reste aucun Orga_Model_Organization en base, sinon suppression !
        if (Orga_Model_Organization::countTotal() > 0) {
            echo PHP_EOL . 'Des Orga_Organization restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Orga_Model_Organization::loadList() as $organization) {
                $organization->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }

    /**
     * Test le constructeur.
     *
     * @return Orga_Model_Member
     */
    function testConstruct()
    {
        $axis = Orga_Test_AxisTest::generateObject();
        $o = new Orga_Model_Member($axis);
        $o->setRef('RefTestMember');
        $o->setLabel('LabalTestMember');
        $this->assertInstanceOf('Orga_Model_Member', $o);
        $this->assertEquals($o->getKey(), array());
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertNotEquals(array(), $o->getKey());
        return $o;
    }

    /**
     * @depends testConstruct
     * @param Orga_Model_Member $o
     */
    function testLoad(Orga_Model_Member $o)
    {
         $oLoaded = Orga_Model_Member::load($o->getKey());
         $this->assertInstanceOf('Orga_Model_Member', $o);
         $this->assertEquals($oLoaded->getKey(), $o->getKey());
         $this->assertEquals($oLoaded->getRef(), $o->getRef());
         $this->assertEquals($oLoaded->getLabel(), $o->getLabel());
         $this->assertSame($oLoaded->getAxis(), $o->getAxis());
         return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param Orga_Model_Member $o
     */
    function testDelete(Orga_Model_Member $o)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertEquals(array(), $o->getKey());
        Orga_Test_AxisTest::deleteObject($o->getAxis());
    }

    /**
     * Fonction appelee une fois, apres tous les tests
     */
    public static function tearDownAfterClass()
    {
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
        // Vérification qu'il ne reste aucun Orga_Model_Organization en base, sinon suppression !
        if (Orga_Model_Organization::countTotal() > 0) {
            echo PHP_EOL . 'Des Orga_Organization restants ont été trouvé après les tests, suppression en cours !';
            foreach (Orga_Model_Organization::loadList() as $organization) {
                $organization->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }
}

/**
 * Tests de la classe Organization
 * @package Organization
 * @subpackage Test
 */
class Orga_Test_MemberOthers extends PHPUnit_Framework_TestCase
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
    protected $member;

    /**
     * Fonction appelee une fois, avant tous les tests
     */
    public static function setUpBeforeClass()
    {
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
        // Vérification qu'il ne reste aucun Orga_Model_Organization en base, sinon suppression !
        if (Orga_Model_Organization::countTotal() > 0) {
            echo PHP_EOL . 'Des Orga_Organization restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Orga_Model_Organization::loadList() as $organization) {
                $organization->delete();
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
        // Crée un objet de test
        $this->member = Orga_Test_MemberTest::generateObject();
        $this->axis = $this->member->getAxis();
        $this->organization = $this->axis->getOrganization();
    }

    /**
     * Function testLoadByRef
     *  Test le chargement par reference
     */
    public function testLoadByRef()
    {
        $o = Orga_Model_Member::loadByCompleteRefAndAxis($this->member->getCompleteRef(), $this->member->getAxis());
        $this->assertSame($this->member, $o);
    }

    /**
     * Tests all functions relative to Children and Parents Members.
     */
    public function testManagerChildrenAndParents()
    {
        $member1 = Orga_Test_MemberTest::generateObject('RefMemberManageChildren1', 'LabelMemberManageChildren1', $this->axis);
        $member11 = Orga_Test_MemberTest::generateObject('RefMemberManageChildren11', 'LabelMemberManageChildren11', $this->axis);
        $member2 = Orga_Test_MemberTest::generateObject('RefMemberManageChildren2', 'LabelMemberManageChildren2', $this->axis);
        $member21 = Orga_Test_MemberTest::generateObject('RefMemberManageChildren21', 'LabelMemberManageChildren21', $this->axis);
        $member22 = Orga_Test_MemberTest::generateObject('RefMemberManageChildren22', 'LabelMemberManageChildren22', $this->axis);
        $member3 = Orga_Test_MemberTest::generateObject('RefMemberManageChildren3', 'LabelMemberManageChildren3', $this->axis);

        // HasDirectParents.
        $this->assertFalse($member1->hasDirectParents());
        $this->assertFalse($member11->hasDirectParents());
        $this->assertFalse($member2->hasDirectParents());
        $this->assertFalse($member21->hasDirectParents());
        $this->assertFalse($member22->hasDirectParents());
        $this->assertFalse($member3->hasDirectParents());
        // HasDirectParent.
        $this->assertFalse($member1->hasDirectParent($this->member));
        $this->assertFalse($member11->hasDirectParent($this->member));
        $this->assertFalse($member2->hasDirectParent($this->member));
        $this->assertFalse($member21->hasDirectParent($this->member));
        $this->assertFalse($member22->hasDirectParent($this->member));
        $this->assertFalse($member3->hasDirectParent($this->member));
        // GetDirectParent.
        $this->assertEmpty($member1->getDirectParents());
        $this->assertEmpty($member11->getDirectParents());
        $this->assertEmpty($member2->getDirectParents());
        $this->assertEmpty($member21->getDirectParents());
        $this->assertEmpty($member22->getDirectParents());
        $this->assertEmpty($member3->getDirectParents());
        // GetAllParent.
        $this->assertEmpty($member1->getAllParents());
        $this->assertEmpty($member11->getAllParents());
        $this->assertEmpty($member2->getAllParents());
        $this->assertEmpty($member21->getAllParents());
        $this->assertEmpty($member22->getAllParents());
        $this->assertEmpty($member3->getAllParents());
        // HasDirectChildren.
        $this->assertFalse($member1->hasDirectChildren());
        $this->assertFalse($member11->hasDirectChildren());
        $this->assertFalse($member2->hasDirectChildren());
        $this->assertFalse($member21->hasDirectChildren());
        $this->assertFalse($member22->hasDirectChildren());
        $this->assertFalse($member3->hasDirectChildren());
        // HasDirectChild.
        $this->assertFalse($member1->hasDirectChild($this->member));
        $this->assertFalse($member11->hasDirectChild($this->member));
        $this->assertFalse($member2->hasDirectChild($this->member));
        $this->assertFalse($member21->hasDirectChild($this->member));
        $this->assertFalse($member22->hasDirectChild($this->member));
        $this->assertFalse($member3->hasDirectChild($this->member));
        // GetDirectChildren.
        $this->assertEmpty($member1->getDirectChildren());
        $this->assertEmpty($member11->getDirectChildren());
        $this->assertEmpty($member2->getDirectChildren());
        $this->assertEmpty($member21->getDirectChildren());
        $this->assertEmpty($member22->getDirectChildren());
        $this->assertEmpty($member3->getDirectChildren());
        // GetAllChildren.
        $this->assertEmpty($member1->getAllChildren());
        $this->assertEmpty($member11->getAllChildren());
        $this->assertEmpty($member2->getAllChildren());
        $this->assertEmpty($member21->getAllChildren());
        $this->assertEmpty($member22->getAllChildren());
        $this->assertEmpty($member3->getAllChildren());

        $member1->addDirectParent($this->member);
        $member11->addDirectParent($member1);

        // HasDirectParents.
        $this->assertTrue($member1->hasDirectParents());
        $this->assertTrue($member11->hasDirectParents());
        $this->assertFalse($member2->hasDirectParents());
        $this->assertFalse($member21->hasDirectParents());
        $this->assertFalse($member22->hasDirectParents());
        $this->assertFalse($member3->hasDirectParents());
        // HasDirectParent.
        $this->assertTrue($member1->hasDirectParent($this->member));
        $this->assertFalse($member11->hasDirectParent($this->member));
        $this->assertTrue($member11->hasDirectParent($member1));
        $this->assertFalse($member2->hasDirectParent($this->member));
        $this->assertFalse($member21->hasDirectParent($this->member));
        $this->assertFalse($member22->hasDirectParent($this->member));
        $this->assertFalse($member3->hasDirectParent($this->member));
        // GetDirectParent.
        $this->assertEquals(array($this->member), $member1->getDirectParents());
        $this->assertEquals(array($member1), $member11->getDirectParents());
        $this->assertEmpty($member2->getDirectParents());
        $this->assertEmpty($member21->getDirectParents());
        $this->assertEmpty($member22->getDirectParents());
        $this->assertEmpty($member3->getDirectParents());
        // GetAllParent.
        $this->assertEquals(array($this->member), $member1->getAllParents());
        $this->assertEquals(array($member1, $this->member), $member11->getAllParents());
        $this->assertEmpty($member2->getAllParents());
        $this->assertEmpty($member21->getAllParents());
        $this->assertEmpty($member22->getAllParents());
        $this->assertEmpty($member3->getAllParents());
        // HasDirectChildren.
        $this->assertTrue($member1->hasDirectChildren());
        $this->assertFalse($member11->hasDirectChildren());
        $this->assertFalse($member2->hasDirectChildren());
        $this->assertFalse($member21->hasDirectChildren());
        $this->assertFalse($member22->hasDirectChildren());
        $this->assertFalse($member3->hasDirectChildren());
        // HasDirectChild.
        $this->assertFalse($member1->hasDirectChild($this->member));
        $this->assertTrue($member1->hasDirectChild($member11));
        $this->assertFalse($member11->hasDirectChild($this->member));
        $this->assertFalse($member2->hasDirectChild($this->member));
        $this->assertFalse($member21->hasDirectChild($this->member));
        $this->assertFalse($member22->hasDirectChild($this->member));
        $this->assertFalse($member3->hasDirectChild($this->member));
        // GetDirectChildren.
        $this->assertEquals(array($member11), $member1->getDirectChildren());
        $this->assertEmpty($member11->getDirectChildren());
        $this->assertEmpty($member2->getDirectChildren());
        $this->assertEmpty($member21->getDirectChildren());
        $this->assertEmpty($member22->getDirectChildren());
        $this->assertEmpty($member3->getDirectChildren());
        // GetAllChildren.
        $this->assertEquals(array($member11), $member1->getAllChildren());
        $this->assertEmpty($member11->getAllChildren());
        $this->assertEmpty($member2->getAllChildren());
        $this->assertEmpty($member21->getAllChildren());
        $this->assertEmpty($member22->getAllChildren());
        $this->assertEmpty($member3->getAllChildren());

        $member2->addDirectParent($this->member);
        $member21->addDirectParent($member2);
        $member22->addDirectParent($member2);
        $member3->addDirectChild($this->member);

        // HasDirectParent.
        $this->assertTrue($member1->hasDirectParent($this->member));
        $this->assertFalse($member11->hasDirectParent($this->member));
        $this->assertTrue($member11->hasDirectParent($member1));
        $this->assertTrue($member2->hasDirectParent($this->member));
        $this->assertFalse($member21->hasDirectParent($this->member));
        $this->assertTrue($member21->hasDirectParent($member2));
        $this->assertFalse($member22->hasDirectParent($this->member));
        $this->assertTrue($member22->hasDirectParent($member2));
        $this->assertFalse($member3->hasDirectParent($this->member));
        // GetDirectParent.
        $this->assertEquals(array($this->member), $member1->getDirectParents());
        $this->assertEquals(array($member1), $member11->getDirectParents());
        $this->assertEquals(array($this->member), $member2->getDirectParents());
        $this->assertEquals(array($member2), $member21->getDirectParents());
        $this->assertEquals(array($member2), $member22->getDirectParents());
        $this->assertEmpty($member3->getDirectParents());
        // GetAllParent.
        $this->assertEquals(array($this->member, $member3), $member1->getAllParents());
        $this->assertEquals(array($member1, $this->member, $member3), $member11->getAllParents());
        $this->assertEquals(array($this->member, $member3), $member2->getAllParents());
        $this->assertEquals(array($member2, $this->member, $member3), $member21->getAllParents());
        $this->assertEquals(array($member2, $this->member, $member3), $member22->getAllParents());
        $this->assertEmpty($member3->getAllParents());
        // HasDirectChildren.
        $this->assertTrue($member1->hasDirectChildren());
        $this->assertFalse($member11->hasDirectChildren());
        $this->assertTrue($member2->hasDirectChildren());
        $this->assertFalse($member21->hasDirectChildren());
        $this->assertFalse($member22->hasDirectChildren());
        $this->assertTrue($member3->hasDirectChildren());
        // HasDirectChild.
        $this->assertFalse($member1->hasDirectChild($this->member));
        $this->assertTrue($member1->hasDirectChild($member11));
        $this->assertFalse($member11->hasDirectChild($this->member));
        $this->assertFalse($member2->hasDirectChild($this->member));
        $this->assertTrue($member2->hasDirectChild($member21));
        $this->assertTrue($member2->hasDirectChild($member22));
        $this->assertFalse($member21->hasDirectChild($this->member));
        $this->assertFalse($member22->hasDirectChild($this->member));
        $this->assertTrue($member3->hasDirectChild($this->member));
        // GetDirectChildren.
        $this->assertEquals(array($member11), $member1->getDirectChildren());
        $this->assertEmpty($member11->getDirectChildren());
        $this->assertEquals(array($member21, $member22), $member2->getDirectChildren());
        $this->assertEmpty($member21->getDirectChildren());
        $this->assertEmpty($member22->getDirectChildren());
        $this->assertEquals(array($this->member), $member3->getDirectChildren());
        // GetAllChildren.
        $this->assertEquals(array($member11), $member1->getAllChildren());
        $this->assertEmpty($member11->getAllChildren());
        $this->assertEquals(array($member21, $member22), $member2->getAllChildren());
        $this->assertEmpty($member21->getAllChildren());
        $this->assertEmpty($member22->getAllChildren());
        $this->assertEquals(array($this->member, $member1, $member11, $member2, $member21, $member22), $member3->getAllChildren());
    }

    /**
     * Test the member function to get parent or children for a given Axis.
     */
    public function testGetParentAndChildrenForAxis()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');

        $axisA = Orga_Test_AxisTest::generateObject('a', 'a', $this->organization);
        $axisB = Orga_Test_AxisTest::generateObject('b', 'b', $this->organization);
        $axisC = Orga_Test_AxisTest::generateObject('c', 'c', $this->organization);
        $axisD = Orga_Test_AxisTest::generateObject('d', 'd', $this->organization);
        $axisE = Orga_Test_AxisTest::generateObject('e', 'e', $this->organization);
        $axisF = Orga_Test_AxisTest::generateObject('f', 'f', $this->organization);
        $axisG = Orga_Test_AxisTest::generateObject('g', 'g', $this->organization);
        $axisH = Orga_Test_AxisTest::generateObject('h', 'h', $this->organization);

        $axisB->setDirectNarrower($axisA);
        $axisC->setDirectNarrower($axisA);
        $axisD->setDirectNarrower($axisC);
        $axisE->setDirectNarrower($axisC);
        $axisF->setDirectNarrower($axisD);
        $axisG->setDirectNarrower($axisE);
        $axisH->setDirectNarrower($axisE);
        $entityManagers['default']->flush();

        $memberA1 = Orga_Test_MemberTest::generateObject('a1', 'a1', $axisA);
        $memberA2 = Orga_Test_MemberTest::generateObject('a2', 'a2', $axisA);
        $memberA3 = Orga_Test_MemberTest::generateObject('a3', 'a3', $axisA);
        $memberB1 = Orga_Test_MemberTest::generateObject('b1', 'b1', $axisB);
        $memberB2 = Orga_Test_MemberTest::generateObject('b2', 'b2', $axisB);
        $memberC1 = Orga_Test_MemberTest::generateObject('c1', 'c1', $axisC);
        $memberC2 = Orga_Test_MemberTest::generateObject('c2', 'c2', $axisC);
        $memberC3 = Orga_Test_MemberTest::generateObject('c3', 'c3', $axisC);
        $memberD1 = Orga_Test_MemberTest::generateObject('d1', 'd1', $axisD);
        $memberD2 = Orga_Test_MemberTest::generateObject('d2', 'd2', $axisD);
        $memberE1 = Orga_Test_MemberTest::generateObject('e1', 'e1', $axisE);
        $memberE2 = Orga_Test_MemberTest::generateObject('e2', 'e2', $axisE);
        $memberF1 = Orga_Test_MemberTest::generateObject('f1', 'f1', $axisF);
        $memberF2 = Orga_Test_MemberTest::generateObject('f2', 'f2', $axisF);
        $memberG1 = Orga_Test_MemberTest::generateObject('g1', 'g1', $axisG);
        $memberH1 = Orga_Test_MemberTest::generateObject('h1', 'h1', $axisH);
        $memberH2 = Orga_Test_MemberTest::generateObject('h2', 'h2', $axisH);


        $memberA1->addDirectParent($memberB1);
        $memberA2->addDirectParent($memberB2);
        $memberA3->addDirectParent($memberB1);
        $memberA1->addDirectParent($memberC1);
        $memberA2->addDirectParent($memberC2);
        $memberA3->addDirectParent($memberC3);
        $memberC1->addDirectParent($memberD1);
        $memberC2->addDirectParent($memberD1);
        $memberC3->addDirectParent($memberD2);
        $memberC1->addDirectParent($memberE1);
        $memberC2->addDirectParent($memberE2);
        $memberC3->addDirectParent($memberE2);
        $memberD1->addDirectParent($memberF1);
        $memberD2->addDirectParent($memberF2);
        $memberE1->addDirectParent($memberG1);
        $memberE2->addDirectParent($memberG1);
        $memberE1->addDirectParent($memberH1);
        $memberE2->addDirectParent($memberH2);
        $entityManagers['default']->flush();

        $this->assertEquals($memberA1->getParentForAxis($axisF), $memberF1);
        $this->assertEquals($memberA2->getParentForAxis($axisF), $memberF1);
        $this->assertEquals($memberA3->getParentForAxis($axisF), $memberF2);

        $this->assertEquals($memberA1->getParentForAxis($axisG), $memberG1);
        $this->assertEquals($memberA2->getParentForAxis($axisG), $memberG1);
        $this->assertEquals($memberA3->getParentForAxis($axisG), $memberG1);

        $this->assertEquals($memberA1->getParentForAxis($axisH), $memberH1);
        $this->assertEquals($memberA2->getParentForAxis($axisH), $memberH2);
        $this->assertEquals($memberA3->getParentForAxis($axisH), $memberH2);


        $this->assertEquals($memberF1->getChildrenForAxis($axisA), array($memberA1, $memberA2));
        $this->assertEquals($memberF2->getChildrenForAxis($axisA), array($memberA3));

        $this->assertEquals($memberG1->getChildrenForAxis($axisA), array($memberA1, $memberA2, $memberA3));

        $this->assertEquals($memberH1->getChildrenForAxis($axisA), array($memberA1));
        $this->assertEquals($memberH2->getChildrenForAxis($axisA), array($memberA2, $memberA3));
    }

    /**
     * Fonction appelee apres tous chaques test
     */
    protected function tearDown()
    {
        Orga_Test_OrganizationTest::deleteObject($this->organization);
    }

    /**
     * Fonction appelee une fois, apres tous les tests
     */
    public static function tearDownAfterClass()
    {
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
        // Vérification qu'il ne reste aucun Orga_Model_Organization en base, sinon suppression !
        if (Orga_Model_Organization::countTotal() > 0) {
            echo PHP_EOL . 'Des Orga_Organization restants ont été trouvé après les tests, suppression en cours !';
            foreach (Orga_Model_Organization::loadList() as $organization) {
                $organization->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }


}