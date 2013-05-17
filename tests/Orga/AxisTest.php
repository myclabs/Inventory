<?php
/**
 * Class Orga_Test_AxisTest
 * @author valentin.claras
 * @author sidoine.Tardieu
 * @package    Orga
 * @subpackage Test
 */

//require_once dirname(__FILE__).'/CubeTest.php';

/**
 * Creation de la suite de test sur les Axis.
 * @package    Orga
 * @subpackage Test
 */
class Orga_Test_AxisTest
{
    /**
     * Creation de la suite de test
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Orga_Test_AxisSetUp');
        $suite->addTestSuite('Orga_Test_AxisOthers');
        return $suite;
    }

    /**
     * Generation de l'objet de test.
     * @param string $refAxis
     * @param string $labelAxis
     * @param Orga_Model_Cube $cube
     * @return Orga_Model_Axis
     */
    public static function generateObject($refAxis='RefTestAxis', $labelAxis='LabelTestAxis', $cube=null)
    {
        if ($cube === null) {
            $cube = Orga_Test_CubeTest::generateObject();
        }
        $o = new Orga_Model_Axis();
        $o->setRef($refAxis);
        $o->setLabel($labelAxis);
        $o->setCube($cube);
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        return $o;
    }

    /**
     * Suppression d'un objet cree avec generateObject
     * @param Orga_Model_Axis $o
     * @param bool $deleteCube
     * @depends generateObject
     */
    public static function deleteObject($o, $deleteCube=true)
    {
        if ($deleteCube === true) {
            $o->getCube()->delete();
        } else {
            $o->delete();
        }
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }
}

/**
 * Test de la creation/modification/suppression de l'entite
 * @package Orga
 * @subpackage Test
 */
class Orga_Test_AxisSetUp extends PHPUnit_Framework_TestCase
{
    /**
     * Fonction appelee une fois, avant tous les tests
     */
    public static function setUpBeforeClass()
    {
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
     * Test le constructeur.
     *
     * @return Orga_Model_Axis
     */
    function testConstruct()
    {
        $cube = Orga_Test_CubeTest::generateObject();
        $o = new Orga_Model_Axis();
        $o->setCube($cube);
        $o->setRef('RefTestAxis');
        $o->setLabel('LabalTestAxis');
        $o->setContextualize(true);
        $this->assertInstanceOf('Orga_Model_Axis', $o);
        $this->assertEquals($o->getKey(), array());
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertNotEquals(array(), $o->getKey());
        return $o;
    }

    /**
     * @depends testConstruct
     * @param Orga_Model_Axis $o
     */
    function testLoad(Orga_Model_Axis $o)
    {
         $oLoaded = Orga_Model_Axis::load($o->getKey());
         $this->assertInstanceOf('Orga_Model_Axis', $o);
         $this->assertEquals($oLoaded->getKey(), $o->getKey());
         $this->assertEquals($oLoaded->getRef(), $o->getRef());
         $this->assertEquals($oLoaded->getLabel(), $o->getLabel());
         $this->assertEquals($oLoaded->isContextualizing(), $o->isContextualizing());
         $this->assertSame($oLoaded->getCube(), $o->getCube());
         return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param Orga_Model_Axis $o
     */
    function testDelete(Orga_Model_Axis $o)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertEquals(array(), $o->getKey());
        Orga_Test_CubeTest::deleteObject($o->getCube());
    }

    /**
     * Fonction appelee une fois, apres tous les tests
     */
    public static function tearDownAfterClass()
    {
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

/**
 * Tests de la classe Cube
 * @package Orga
 * @subpackage Test
 */
class Orga_Test_AxisOthers extends PHPUnit_Framework_TestCase
{
    /**
     * @var Orga_Model_Cube
     */
    protected $cube;

    /**
     * @var Orga_Model_Axis
     */
    protected $axis;

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
        // Crée un objet de test
        $this->axis = Orga_Test_AxisTest::generateObject();
        $this->cube = $this->axis->getCube();
    }

    /**
     * Test de loadbyref
     */
    public function testLoadByRefAndCube()
    {
        $o = Orga_Model_Axis::loadByRefAndCube($this->axis->getRef(), $this->cube);
        $this->assertSame($this->axis, $o);
    }

    /**
     * Tests all functions relative to Broaders and Narrower Axis.
     */
    public function testManagerBroaders()
    {
        $axis1 = Orga_Test_AxisTest::generateObject('RefAxisManageBroaders1', 'LabelAxisManageBroaders1', $this->cube);
        $axis11 = Orga_Test_AxisTest::generateObject('RefAxisManageBroaders11', 'LabelAxisManageBroaders11', $this->cube);
        $axis2 = Orga_Test_AxisTest::generateObject('RefAxisManageBroaders2', 'LabelAxisManageBroaders2', $this->cube);
        $axis21 = Orga_Test_AxisTest::generateObject('RefAxisManageBroaders21', 'LabelAxisManageBroaders21', $this->cube);
        $axis22 = Orga_Test_AxisTest::generateObject('RefAxisManageBroaders22', 'LabelAxisManageBroaders22', $this->cube);
        $axis3 = Orga_Test_AxisTest::generateObject('RefAxisManageBroaders3', 'LabelAxisManageBroaders3', $this->cube);

        $this->assertFalse($this->axis->hasDirectBroaders());
        $this->assertEmpty($this->axis->getDirectBroaders());
        $this->assertEmpty($this->axis->getAllBroadersFirstOrdered());
        $this->assertEmpty($this->axis->getAllBroadersLastOrdered());
        $this->assertFalse($this->axis->hasDirectBroaders($axis1));
        $this->assertFalse($this->axis->hasDirectBroaders($axis11));
        $this->assertFalse($this->axis->hasDirectBroaders($axis2));
        $this->assertFalse($this->axis->hasDirectBroaders($axis21));
        $this->assertFalse($this->axis->hasDirectBroaders($axis22));
        $this->assertFalse($this->axis->hasDirectBroaders($axis3));
        // DirectNarrower.
        $this->assertNull($axis1->getDirectNarrower());
        $this->assertNull($axis11->getDirectNarrower());
        $this->assertNull($axis2->getDirectNarrower());
        $this->assertNull($axis21->getDirectNarrower());
        $this->assertNull($axis22->getDirectNarrower());
        $this->assertNull($axis3->getDirectNarrower());
        // HasDirectBroaders.
        $this->assertFalse($axis1->hasDirectBroaders());
        $this->assertFalse($axis11->hasDirectBroaders());
        $this->assertFalse($axis2->hasDirectBroaders());
        $this->assertFalse($axis21->hasDirectBroaders());
        $this->assertFalse($axis22->hasDirectBroaders());
        $this->assertFalse($axis3->hasDirectBroaders());
        // GetDirectBroaders.
        $this->assertEmpty($axis1->getDirectBroaders());
        $this->assertEmpty($axis11->getDirectBroaders());
        $this->assertEmpty($axis2->getDirectBroaders());
        $this->assertEmpty($axis21->getDirectBroaders());
        $this->assertEmpty($axis22->getDirectBroaders());
        $this->assertEmpty($axis3->getDirectBroaders());
        // GetAllBroaders.
        $this->assertEmpty($axis1->getAllBroadersFirstOrdered());
        $this->assertEmpty($axis1->getAllBroadersLastOrdered());
        $this->assertEmpty($axis11->getAllBroadersFirstOrdered());
        $this->assertEmpty($axis11->getAllBroadersLastOrdered());
        $this->assertEmpty($axis2->getAllBroadersFirstOrdered());
        $this->assertEmpty($axis2->getAllBroadersLastOrdered());
        $this->assertEmpty($axis21->getAllBroadersFirstOrdered());
        $this->assertEmpty($axis21->getAllBroadersLastOrdered());
        $this->assertEmpty($axis22->getAllBroadersFirstOrdered());
        $this->assertEmpty($axis22->getAllBroadersLastOrdered());
        $this->assertEmpty($axis3->getAllBroadersFirstOrdered());
        $this->assertEmpty($axis3->getAllBroadersLastOrdered());

        $axis1->setDirectNarrower($this->axis);
        $axis11->setDirectNarrower($axis1);

        $this->assertTrue($this->axis->hasDirectBroaders());
        $this->assertEquals(array($axis1), $this->axis->getDirectBroaders());
        $this->assertEquals(array($axis1, $axis11), $this->axis->getAllBroadersFirstOrdered());
        $this->assertEquals(array($axis11, $axis1), $this->axis->getAllBroadersLastOrdered());
        // DirectNarrower.
        $this->assertSame($this->axis, $axis1->getDirectNarrower());
        $this->assertSame($axis1, $axis11->getDirectNarrower());
        $this->assertNull($axis2->getDirectNarrower());
        $this->assertNull($axis21->getDirectNarrower());
        $this->assertNull($axis22->getDirectNarrower());
        $this->assertNull($axis3->getDirectNarrower());
        // HasDirectBroaders.
        $this->assertTrue($axis1->hasDirectBroaders());
        $this->assertFalse($axis11->hasDirectBroaders());
        $this->assertFalse($axis2->hasDirectBroaders());
        $this->assertFalse($axis21->hasDirectBroaders());
        $this->assertFalse($axis22->hasDirectBroaders());
        $this->assertFalse($axis3->hasDirectBroaders());
        // GetDirectBroaders.
        $this->assertEquals(array($axis11), $axis1->getDirectBroaders());
        $this->assertEmpty($axis11->getDirectBroaders());
        $this->assertEmpty($axis2->getDirectBroaders());
        $this->assertEmpty($axis21->getDirectBroaders());
        $this->assertEmpty($axis22->getDirectBroaders());
        $this->assertEmpty($axis3->getDirectBroaders());
        // GetAllBroaders.
        $this->assertEquals(array($axis11), $axis1->getAllBroadersFirstOrdered());
        $this->assertEquals(array($axis11), $axis1->getAllBroadersLastOrdered());
        $this->assertEmpty($axis11->getAllBroadersFirstOrdered());
        $this->assertEmpty($axis11->getAllBroadersLastOrdered());
        $this->assertEmpty($axis2->getAllBroadersFirstOrdered());
        $this->assertEmpty($axis2->getAllBroadersLastOrdered());
        $this->assertEmpty($axis21->getAllBroadersFirstOrdered());
        $this->assertEmpty($axis21->getAllBroadersLastOrdered());
        $this->assertEmpty($axis22->getAllBroadersFirstOrdered());
        $this->assertEmpty($axis22->getAllBroadersLastOrdered());
        $this->assertEmpty($axis3->getAllBroadersFirstOrdered());
        $this->assertEmpty($axis3->getAllBroadersLastOrdered());

        $axis2->setDirectNarrower($this->axis);
        $axis21->setDirectNarrower($axis2);
        $axis22->setDirectNarrower($axis2);

        $this->assertTrue($this->axis->hasDirectBroaders());
        $this->assertEquals(array($axis1, $axis2), $this->axis->getDirectBroaders());
        $this->assertEquals(array($axis1, $axis11, $axis2, $axis21, $axis22), $this->axis->getAllBroadersFirstOrdered());
        $this->assertEquals(array($axis11, $axis1, $axis21, $axis22, $axis2), $this->axis->getAllBroadersLastOrdered());
        // DirectNarrower.
        $this->assertSame($this->axis, $axis1->getDirectNarrower());
        $this->assertSame($axis1, $axis11->getDirectNarrower());
        $this->assertSame($this->axis, $axis2->getDirectNarrower());
        $this->assertSame($axis2, $axis21->getDirectNarrower());
        $this->assertSame($axis2, $axis22->getDirectNarrower());
        $this->assertNull($axis3->getDirectNarrower());
        // HasDirectBroaders.
        $this->assertTrue($axis1->hasDirectBroaders());
        $this->assertFalse($axis11->hasDirectBroaders());
        $this->assertTrue($axis2->hasDirectBroaders());
        $this->assertFalse($axis21->hasDirectBroaders());
        $this->assertFalse($axis22->hasDirectBroaders());
        $this->assertFalse($axis3->hasDirectBroaders());
        // GetDirectBroaders.
        $this->assertEquals(array($axis11), $axis1->getDirectBroaders());
        $this->assertEmpty($axis11->getDirectBroaders());
        $this->assertEquals(array($axis21, $axis22), $axis2->getDirectBroaders());
        $this->assertEmpty($axis21->getDirectBroaders());
        $this->assertEmpty($axis22->getDirectBroaders());
        $this->assertEmpty($axis3->getDirectBroaders());
        // GetAllBroaders.
        $this->assertEquals(array($axis11), $axis1->getAllBroadersFirstOrdered());
        $this->assertEquals(array($axis11), $axis1->getAllBroadersLastOrdered());
        $this->assertEmpty($axis11->getAllBroadersFirstOrdered());
        $this->assertEmpty($axis11->getAllBroadersLastOrdered());
        $this->assertEquals(array($axis21, $axis22), $axis2->getAllBroadersFirstOrdered());
        $this->assertEquals(array($axis21, $axis22), $axis2->getAllBroadersLastOrdered());
        $this->assertEmpty($axis21->getAllBroadersFirstOrdered());
        $this->assertEmpty($axis21->getAllBroadersLastOrdered());
        $this->assertEmpty($axis22->getAllBroadersFirstOrdered());
        $this->assertEmpty($axis22->getAllBroadersLastOrdered());
        $this->assertEmpty($axis3->getAllBroadersFirstOrdered());
        $this->assertEmpty($axis3->getAllBroadersLastOrdered());
    }

    /**
     * Tests all functions relative to Member in Axis.
     */
    public function testManagerMembers()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');

        $member1 = new Orga_Model_Member();
        $member1->setRef('RefMemberAxis1');
        $member1->setLabel('LabelMemberAxis1');

        $member2 = new Orga_Model_Member();
        $member2->setRef('RefMemberAxis2');
        $member2->setLabel('LabelMemberAxis2');

        $member3 = new Orga_Model_Member();
        $member3->setRef('RefMemberAxis3');
        $member3->setLabel('LabelMemberAxis3');

        $this->assertFalse($this->axis->hasMembers());
        $this->assertEmpty($this->axis->getMembers());
        $this->assertFalse($this->axis->hasMember($member1));
        $this->assertFalse($this->axis->hasMember($member2));
        $this->assertFalse($this->axis->hasMember($member3));
        try {
            $this->assertNull($member1->getAxis());
        } catch (Core_Exception_UndefinedAttribute $e) {
            $this->assertEquals($e->getMessage(), 'The Axis has not been defined yet.');
        }
        try {
            $this->assertNull($member3->getAxis());
        } catch (Core_Exception_UndefinedAttribute $e) {
            $this->assertEquals($e->getMessage(), 'The Axis has not been defined yet.');
        }
        try {
            $this->assertNull($member3->getAxis());
        } catch (Core_Exception_UndefinedAttribute $e) {
            $this->assertEquals($e->getMessage(), 'The Axis has not been defined yet.');
        }

        $this->axis->addMember($member1);
        $member1->save();
        $entityManagers['default']->flush();

        $this->assertTrue($this->axis->hasMembers());
        $this->assertEquals(array(0 => $member1), $this->axis->getMembers());
        $this->assertTrue($this->axis->hasMember($member1));
        $this->assertFalse($this->axis->hasMember($member2));
        $this->assertFalse($this->axis->hasMember($member3));
        $this->assertSame($this->axis, $member1->getAxis());
        try {
            $this->assertNull($member3->getAxis());
        } catch (Core_Exception_UndefinedAttribute $e) {
            $this->assertEquals($e->getMessage(), 'The Axis has not been defined yet.');
        }
        try {
            $this->assertNull($member3->getAxis());
        } catch (Core_Exception_UndefinedAttribute $e) {
            $this->assertEquals($e->getMessage(), 'The Axis has not been defined yet.');
        }

        $this->axis->addMember($member2);
        $member2->save();
        $this->axis->addMember($member3);
        $member3->save();
        $entityManagers['default']->flush();

        $this->assertTrue($this->axis->hasMembers());
        $this->assertEquals(array(0 => $member1, 1 => $member2, 2 => $member3), $this->axis->getMembers());
        $this->assertTrue($this->axis->hasMember($member1));
        $this->assertTrue($this->axis->hasMember($member2));
        $this->assertTrue($this->axis->hasMember($member3));
        $this->assertSame($this->axis, $member1->getAxis());
        $this->assertSame($this->axis, $member2->getAxis());
        $this->assertSame($this->axis, $member3->getAxis());
    }

    /**
     * Tests all functions relative to Granularity in Axis.
     */
    public function testManageGranularities()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');

        $granularity1 = new Orga_Model_Granularity();
        $granularity1->setCube($this->cube);
        $granularity1->save();

        $granularity2 = new Orga_Model_Granularity();
        $granularity2->setCube($this->cube);
        $granularity2->save();

        $granularity3 = new Orga_Model_Granularity();
        $granularity3->setCube($this->cube);
        $granularity3->save();

        $this->assertFalse($this->axis->hasGranularities());
        $this->assertEmpty($this->axis->getGranularities());
        $this->assertFalse($this->axis->hasGranularity($granularity1));
        $this->assertFalse($this->axis->hasGranularity($granularity2));
        $this->assertFalse($this->axis->hasGranularity($granularity3));
        $this->assertFalse($granularity1->hasAxis($this->axis));
        $this->assertFalse($granularity2->hasAxis($this->axis));
        $this->assertFalse($granularity3->hasAxis($this->axis));

        $this->axis->addGranularity($granularity1);

        $this->assertTrue($this->axis->hasGranularities());
        $this->assertEquals(array(0 => $granularity1), $this->axis->getGranularities());
        $this->assertTrue($this->axis->hasGranularity($granularity1));
        $this->assertFalse($this->axis->hasGranularity($granularity2));
        $this->assertFalse($this->axis->hasGranularity($granularity3));
        $this->assertTrue($granularity1->hasAxis($this->axis));
        $this->assertFalse($granularity2->hasAxis($this->axis));
        $this->assertFalse($granularity3->hasAxis($this->axis));

        $this->axis->addGranularity($granularity2);
        $this->axis->addGranularity($granularity3);

        $this->assertTrue($this->axis->hasGranularities());
        $this->assertEquals(array(0 => $granularity1, 1 => $granularity2, 2 => $granularity3), $this->axis->getGranularities());
        $this->assertTrue($this->axis->hasGranularity($granularity1));
        $this->assertTrue($this->axis->hasGranularity($granularity2));
        $this->assertTrue($this->axis->hasGranularity($granularity3));
        $this->assertTrue($granularity1->hasAxis($this->axis));
        $this->assertTrue($granularity2->hasAxis($this->axis));
        $this->assertTrue($granularity3->hasAxis($this->axis));

        $this->axis->removeGranularity($granularity2);

        $this->assertTrue($this->axis->hasGranularities());
        $this->assertEquals(array(0 => $granularity1, 2 => $granularity3), $this->axis->getGranularities());
        $this->assertTrue($this->axis->hasGranularity($granularity1));
        $this->assertFalse($this->axis->hasGranularity($granularity2));
        $this->assertTrue($this->axis->hasGranularity($granularity3));
        $this->assertTrue($granularity1->hasAxis($this->axis));
        $this->assertFalse($granularity2->hasAxis($this->axis));
        $this->assertTrue($granularity3->hasAxis($this->axis));
    }

    /**
     * Fonction appelee apres chaques tests
     */
    protected function tearDown()
    {
       Orga_Test_CubeTest::deleteObject($this->cube);
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