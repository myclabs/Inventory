<?php
/**
 * Class Orga_Test_AxisTest
 * @author valentin.claras
 * @author sidoine.Tardieu
 * @package    Orga
 * @subpackage Test
 */

//require_once dirname(__FILE__).'/OrganizationTest.php';

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
     * @param Orga_Model_Organization $organization
     * @return Orga_Model_Axis
     */
    public static function generateObject($refAxis='RefTestAxis', $labelAxis='LabelTestAxis', $organization=null)
    {
        if ($organization === null) {
            $organization = Orga_Test_OrganizationTest::generateObject();
        }
        $o = new Orga_Model_Axis($organization);
        $o->setRef($refAxis);
        $o->setLabel($labelAxis);
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        return $o;
    }

    /**
     * Suppression d'un objet cree avec generateObject
     * @param Orga_Model_Axis $o
     * @param bool $deleteOrganization
     * @depends generateObject
     */
    public static function deleteObject($o, $deleteOrganization=true)
    {
        if ($deleteOrganization === true) {
            $o->getOrganization()->delete();
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
     * @return Orga_Model_Axis
     */
    function testConstruct()
    {
        $organization = Orga_Test_OrganizationTest::generateObject();
        $o = new Orga_Model_Axis($organization);
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
         $this->assertSame($oLoaded->getOrganization(), $o->getOrganization());
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
        Orga_Test_OrganizationTest::deleteObject($o->getOrganization());
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
 * @package Orga
 * @subpackage Test
 */
class Orga_Test_AxisOthers extends PHPUnit_Framework_TestCase
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
        $this->axis = Orga_Test_AxisTest::generateObject();
        $this->organization = $this->axis->getOrganization();
    }

    /**
     * Test de loadbyref
     */
    public function testLoadByRefAndOrganization()
    {
        $o = Orga_Model_Axis::loadByRefAndOrganization($this->axis->getRef(), $this->organization);
        $this->assertSame($this->axis, $o);
    }

    /**
     * Tests all functions relative to Broaders and Narrower Axis.
     */
    public function testManagerBroaders()
    {
        $axis1 = Orga_Test_AxisTest::generateObject('RefAxisManageBroaders1', 'LabelAxisManageBroaders1', $this->organization);
        $axis11 = Orga_Test_AxisTest::generateObject('RefAxisManageBroaders11', 'LabelAxisManageBroaders11', $this->organization);
        $axis2 = Orga_Test_AxisTest::generateObject('RefAxisManageBroaders2', 'LabelAxisManageBroaders2', $this->organization);
        $axis21 = Orga_Test_AxisTest::generateObject('RefAxisManageBroaders21', 'LabelAxisManageBroaders21', $this->organization);
        $axis22 = Orga_Test_AxisTest::generateObject('RefAxisManageBroaders22', 'LabelAxisManageBroaders22', $this->organization);
        $axis3 = Orga_Test_AxisTest::generateObject('RefAxisManageBroaders3', 'LabelAxisManageBroaders3', $this->organization);

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
     * Fonction appelee apres chaques tests
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