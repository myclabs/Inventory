<?php
/**
 * Class Orga_Test_GranularityTest
 * @author valentin.claras
 * @author sidoine.Tardieu
 * @package    Orga
 * @subpackage Test
 */

// require_once dirname(__FILE__).'/OrganizationTest.php';

/**
 * Creation de la suite de test concernant les Granularity.
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
        $suite->addTestSuite('Orga_Test_GranularitySetUp');
        $suite->addTestSuite('Orga_Test_GranularityOthers');
        return $suite;
    }

    /**
     * Generation de l'objet de test
     * @param Orga_Model_Organization $organization
     * @param Orga_Model_Axis[] $axes
     * @return Orga_Model_Granularity
     */
    public static function generateObject($organization=null, $axes=array())
    {
        if ($organization === null) {
            $organization = Orga_Test_OrganizationTest::generateObject();
        }
        $o = new Orga_Model_Granularity($organization, $axes);
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        return $o;
    }

    /**
     * Suppression d'un objet cree avec generateObject
     * @param Orga_Model_Granularity $o
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
 * @package Granularity
 * @subpackage Test
 */
class Orga_Test_GranularitySetUp extends PHPUnit_Framework_TestCase
{
    /**
     * Fonction appelee une fois, avant tous les tests
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun Orga_Model_Granularity en base, sinon suppression !
        if (Orga_Model_Granularity::countTotal() > 0) {
            echo PHP_EOL . 'Des Orga_Granularity restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Orga_Model_Granularity::loadList() as $granularity) {
                $granularity->delete();
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
     * @return Orga_Model_Granularity
     */
    function testConstruct()
    {
        $organization = Orga_Test_OrganizationTest::generateObject();
        $o = new Orga_Model_Granularity($organization);
        $this->assertInstanceOf('Orga_Model_Granularity', $o);
        $this->assertEquals($o->getKey(), array());
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertNotEquals(array(), $o->getKey());
        return $o;
    }

    /**
     * @depends testConstruct
     * @param Orga_Model_Granularity $o
     */
    function testLoad(Orga_Model_Granularity $o)
    {
         $oLoaded = Orga_Model_Granularity::load($o->getKey());
         $this->assertInstanceOf('Orga_Model_Granularity', $o);
         $this->assertEquals($oLoaded->getKey(), $o->getKey());
         $this->assertSame($oLoaded->getOrganization(), $o->getOrganization());
         return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param Orga_Model_Granularity $o
     */
    function testDelete(Orga_Model_Granularity $o)
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
        // Vérification qu'il ne reste aucun Orga_Model_Granularity en base, sinon suppression !
        if (Orga_Model_Granularity::countTotal() > 0) {
            echo PHP_EOL . 'Des Orga_Granularity restants ont été trouvé après les tests, suppression en cours !';
            foreach (Orga_Model_Granularity::loadList() as $granularity) {
                $granularity->delete();
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
class Orga_Test_GranularityOthers extends PHPUnit_Framework_TestCase
{
    /**
     * @var Orga_Model_Organization
     */
    protected $organization;

    /**
     * @var Orga_Model_Granularity
     */
    protected $granularity;

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
        $this->granularity = Orga_Test_GranularityTest::generateObject();
        $this->organization = $this->granularity->getOrganization();
    }

    /**
     * Test de loadbyref
     */
    public function testRefAndLabel()
    {
        $axis1 = new Orga_Model_Axis($this->organization);
        $axis1->setRef('RefAxisAxis1');
        $axis1->setLabel('LabelAxisAxis1');

        $axis2 = new Orga_Model_Axis($this->organization);
        $axis2->setRef('RefAxisAxis2');
        $axis2->setLabel('LabelAxisAxis2');

        $granularity1 = new Orga_Model_Granularity($this->organization, [$axis1]);

        $granularity2 = new Orga_Model_Granularity($this->organization, [$axis1, $axis2]);

        $this->assertEquals('global', $this->granularity->getRef());
        $this->assertEquals('RefAxisAxis1', $granularity1->getRef());
        $this->assertEquals('RefAxisAxis1|RefAxisAxis2', $granularity2->getRef());
        $this->assertEquals('global', $this->granularity->getRef());
        $this->assertEquals('LabelAxisAxis1', $granularity1->getLabel());
        $this->assertEquals('LabelAxisAxis1 | LabelAxisAxis2', $granularity2->getLabel());

        $o = Orga_Model_Granularity::loadByRefAndOrganization('global', $this->organization);
        $this->assertSame($this->granularity, $o);
        $o = Orga_Model_Granularity::loadByRefAndOrganization('RefAxisAxis1', $this->organization);
        $this->assertSame($granularity1, $o);
        $o = Orga_Model_Granularity::loadByRefAndOrganization('RefAxisAxis1|RefAxisAxis2', $this->organization);
        $this->assertSame($granularity2, $o);
    }

    /**
     * Test the granularity function to know her relative narrower broader.
     */
    public function testGetNarrowerBroaderGranularities()
    {
        $axis1 = new Orga_Model_Axis($this->organization);
        $axis1->setRef('RefNarrowerBroaderGranularities1');
        $axis1->setLabel('LabelNarrowerBroaderGranularities1');
        $axis1->save();

        $axis11 = new Orga_Model_Axis($this->organization);
        $axis11->setRef('RefNarrowerBroaderGranularities11');
        $axis11->setLabel('LabelNarrowerBroaderGranularities11');
        $axis11->setDirectNarrower($axis1);
        $axis11->save();

        $axis111 = new Orga_Model_Axis($this->organization);
        $axis111->setRef('RefNarrowerBroaderGranularities111');
        $axis111->setLabel('LabelNarrowerBroaderGranularities111');
        $axis111->setDirectNarrower($axis11);
        $axis111->save();

        $axis12 = new Orga_Model_Axis($this->organization);
        $axis12->setRef('RefNarrowerBroaderGranularities12');
        $axis12->setLabel('LabelNarrowerBroaderGranularities12');
        $axis12->setDirectNarrower($axis1);
        $axis12->save();

        $axis121 = new Orga_Model_Axis($this->organization);
        $axis121->setRef('RefNarrowerBroaderGranularities121');
        $axis121->setLabel('LabelNarrowerBroaderGranularities121');
        $axis121->setDirectNarrower($axis12);
        $axis121->save();

        $axis122 = new Orga_Model_Axis($this->organization);
        $axis122->setRef('RefNarrowerBroaderGranularities122');
        $axis122->setLabel('LabelNarrowerBroaderGranularities122');
        $axis122->setDirectNarrower($axis12);
        $axis122->save();

        $axis123 = new Orga_Model_Axis($this->organization);
        $axis123->setRef('RefNarrowerBroaderGranularities123');
        $axis123->setLabel('LabelNarrowerBroaderGranularities123');
        $axis123->setDirectNarrower($axis12);
        $axis123->save();

        $axis2 = new Orga_Model_Axis($this->organization);
        $axis2->setRef('RefNarrowerBroaderGranularities2');
        $axis2->setLabel('LabelNarrowerBroaderGranularities2');
        $axis2->save();

        $axis21 = new Orga_Model_Axis($this->organization);
        $axis21->setRef('RefNarrowerBroaderGranularities21');
        $axis21->setLabel('LabelNarrowerBroaderGranularities21');
        $axis21->setDirectNarrower($axis2);
        $axis21->save();

        $axis3 = new Orga_Model_Axis($this->organization);
        $axis3->setRef('RefNarrowerBroaderGranularities3');
        $axis3->setLabel('LabelNarrowerBroaderGranularities3');
        $axis3->save();

        $axis31 = new Orga_Model_Axis($this->organization);
        $axis31->setRef('RefNarrowerBroaderGranularities31');
        $axis31->setLabel('LabelNarrowerBroaderGranularities31');
        $axis31->setDirectNarrower($axis3);
        $axis31->save();

        $axis311 = new Orga_Model_Axis($this->organization);
        $axis311->setRef('RefNarrowerBroaderGranularities311');
        $axis311->setLabel('LabelNarrowerBroaderGranularities311');
        $axis311->setDirectNarrower($axis31);
        $axis311->save();

        $axis312 = new Orga_Model_Axis($this->organization);
        $axis312->setRef('RefNarrowerBroaderGranularities312');
        $axis312->setLabel('LabelNarrowerBroaderGranularities312');
        $axis312->setDirectNarrower($axis31);
        $axis312->save();

        $axis32 = new Orga_Model_Axis($this->organization);
        $axis32->setRef('RefNarrowerBroaderGranularities32');
        $axis32->setLabel('LabelNarrowerBroaderGranularities32');
        $axis32->setDirectNarrower($axis3);
        $axis32->save();

        $axis33 = new Orga_Model_Axis($this->organization);
        $axis33->setRef('RefNarrowerBroaderGranularities33');
        $axis33->setLabel('LabelNarrowerBroaderGranularities33');
        $axis33->setDirectNarrower($axis3);
        $axis33->save();

        $axis331 = new Orga_Model_Axis($this->organization);
        $axis331->setRef('RefNarrowerBroaderGranularities331');
        $axis331->setLabel('LabelNarrowerBroaderGranularities331');
        $axis331->setDirectNarrower($axis33);
        $axis331->save();

        $axis332 = new Orga_Model_Axis($this->organization);
        $axis332->setRef('RefNarrowerBroaderGranularities332');
        $axis332->setLabel('LabelNarrowerBroaderGranularities332');
        $axis332->setDirectNarrower($axis33);
        $axis332->save();

        $granularity0 = $this->granularity;

        $granularity1 = new Orga_Model_Granularity($this->organization, [$axis11, $axis122, $axis311]);

        $granularity2 = new Orga_Model_Granularity($this->organization, [$axis1, $axis31]);

        $granularity3 = new Orga_Model_Granularity($this->organization, [$axis2]);

        $granularity4 = new Orga_Model_Granularity($this->organization, [$axis1, $axis3]);

        $granularity5 = new Orga_Model_Granularity($this->organization, [$axis12, $axis21, $axis33]);

        $this->assertFalse($granularity0->isNarrowerThan($granularity1));
        $this->assertFalse($granularity0->isNarrowerThan($granularity2));
        $this->assertFalse($granularity0->isNarrowerThan($granularity3));
        $this->assertFalse($granularity0->isNarrowerThan($granularity4));
        $this->assertFalse($granularity0->isNarrowerThan($granularity5));
        $this->assertTrue($granularity0->isBroaderThan($granularity1));
        $this->assertTrue($granularity0->isBroaderThan($granularity2));
        $this->assertTrue($granularity0->isBroaderThan($granularity3));
        $this->assertTrue($granularity0->isBroaderThan($granularity4));
        $this->assertTrue($granularity0->isBroaderThan($granularity5));

        $this->assertTrue($granularity1->isNarrowerThan($granularity0));
        $this->assertFalse($granularity1->isNarrowerThan($granularity2));
        $this->assertFalse($granularity1->isNarrowerThan($granularity3));
        $this->assertFalse($granularity1->isNarrowerThan($granularity4));
        $this->assertFalse($granularity1->isNarrowerThan($granularity5));
        $this->assertFalse($granularity1->isBroaderThan($granularity0));
        $this->assertTrue($granularity1->isBroaderThan($granularity2));
        $this->assertFalse($granularity1->isBroaderThan($granularity3));
        $this->assertTrue($granularity1->isBroaderThan($granularity4));
        $this->assertFalse($granularity1->isBroaderThan($granularity5));

        $this->assertTrue($granularity2->isNarrowerThan($granularity0));
        $this->assertTrue($granularity2->isNarrowerThan($granularity1));
        $this->assertFalse($granularity2->isNarrowerThan($granularity3));
        $this->assertFalse($granularity2->isNarrowerThan($granularity4));
        $this->assertFalse($granularity2->isNarrowerThan($granularity5));
        $this->assertFalse($granularity2->isBroaderThan($granularity0));
        $this->assertFalse($granularity2->isBroaderThan($granularity1));
        $this->assertFalse($granularity2->isBroaderThan($granularity3));
        $this->assertTrue($granularity2->isBroaderThan($granularity4));
        $this->assertFalse($granularity2->isBroaderThan($granularity5));

        $this->assertTrue($granularity3->isNarrowerThan($granularity0));
        $this->assertFalse($granularity3->isNarrowerThan($granularity1));
        $this->assertFalse($granularity3->isNarrowerThan($granularity2));
        $this->assertFalse($granularity3->isNarrowerThan($granularity4));
        $this->assertFalse($granularity3->isNarrowerThan($granularity5));
        $this->assertFalse($granularity3->isBroaderThan($granularity0));
        $this->assertFalse($granularity3->isBroaderThan($granularity1));
        $this->assertFalse($granularity3->isBroaderThan($granularity2));
        $this->assertFalse($granularity3->isBroaderThan($granularity4));
        $this->assertFalse($granularity3->isBroaderThan($granularity5));

        $this->assertTrue($granularity4->isNarrowerThan($granularity0));
        $this->assertTrue($granularity4->isNarrowerThan($granularity1));
        $this->assertTrue($granularity4->isNarrowerThan($granularity2));
        $this->assertFalse($granularity4->isNarrowerThan($granularity3));
        $this->assertFalse($granularity4->isNarrowerThan($granularity5));
        $this->assertFalse($granularity4->isBroaderThan($granularity0));
        $this->assertFalse($granularity4->isBroaderThan($granularity1));
        $this->assertFalse($granularity4->isBroaderThan($granularity2));
        $this->assertFalse($granularity4->isBroaderThan($granularity3));
        $this->assertFalse($granularity4->isBroaderThan($granularity5));

        $this->assertTrue($granularity5->isNarrowerThan($granularity0));
        $this->assertFalse($granularity5->isNarrowerThan($granularity1));
        $this->assertFalse($granularity5->isNarrowerThan($granularity2));
        $this->assertFalse($granularity5->isNarrowerThan($granularity3));
        $this->assertFalse($granularity5->isNarrowerThan($granularity4));
        $this->assertFalse($granularity5->isBroaderThan($granularity0));
        $this->assertFalse($granularity5->isBroaderThan($granularity1));
        $this->assertFalse($granularity5->isBroaderThan($granularity2));
        $this->assertFalse($granularity5->isBroaderThan($granularity3));
        $this->assertFalse($granularity5->isBroaderThan($granularity4));

        $this->assertEquals($granularity0->getNarrowerGranularities(), array($granularity1, $granularity2, $granularity3, $granularity4, $granularity5));
        $this->assertEquals($granularity0->getBroaderGranularities(), array());
        $this->assertEquals($granularity1->getNarrowerGranularities(), array($granularity2, $granularity4));
        $this->assertEquals($granularity1->getBroaderGranularities(), array($granularity0));
        $this->assertEquals($granularity2->getNarrowerGranularities(), array($granularity4));
        $this->assertEquals($granularity2->getBroaderGranularities(), array($granularity1, $granularity0));
        $this->assertEquals($granularity3->getNarrowerGranularities(), array());
        $this->assertEquals($granularity3->getBroaderGranularities(), array($granularity0));
        $this->assertEquals($granularity4->getNarrowerGranularities(), array());
        $this->assertEquals($granularity4->getBroaderGranularities(), array($granularity2, $granularity1, $granularity0));
        $this->assertEquals($granularity5->getNarrowerGranularities(), array());
        $this->assertEquals($granularity5->getBroaderGranularities(), array($granularity0));
    }

    /**
     * Test the granularity function to know her relative crossed encompassing.
     */
    public function testGetCrossedEncompassingGranularities()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');

        $axis1 = new Orga_Model_Axis($this->organization);
        $axis1->setRef('RefCrossedEncompassingGranularities1');
        $axis1->setLabel('LabelCrossedEncompassingGranularities1');

        $axis11 = new Orga_Model_Axis($this->organization);
        $axis11->setRef('RefCrossedEncompassingGranularities11');
        $axis11->setLabel('LabelCrossedEncompassingGranularities11');
        $axis11->setDirectNarrower($axis1);

        $axis12 = new Orga_Model_Axis($this->organization);
        $axis12->setRef('RefCrossedEncompassingGranularities12');
        $axis12->setLabel('LabelCrossedEncompassingGranularities12');
        $axis12->setDirectNarrower($axis1);

        $axis2 = new Orga_Model_Axis($this->organization);
        $axis2->setRef('RefCrossedEncompassingGranularities2');
        $axis2->setLabel('LabelCrossedEncompassingGranularities2');

        $granularity0 = $this->granularity;

        $granularity1 = new Orga_Model_Granularity($this->organization, [$axis1]);

        $granularity2 = new Orga_Model_Granularity($this->organization, [$axis2]);

        $granularity12 = new Orga_Model_Granularity($this->organization, [$axis1, $axis2]);

        $granularity112 = new Orga_Model_Granularity($this->organization, [$axis11, $axis2]);

        $granularity122 = new Orga_Model_Granularity($this->organization, [$axis12, $axis2]);

        $granularity3 = new Orga_Model_Granularity($this->organization, [$axis11, $axis12, $axis2]);

        $this->assertEquals(1, $axis1->getGlobalPosition());
        $this->assertEquals(2, $axis11->getGlobalPosition());
        $this->assertEquals(3, $axis12->getGlobalPosition());
        $this->assertEquals(4, $axis2->getGlobalPosition());
        $this->assertSame($granularity112->getCrossedGranularity($granularity122), $granularity3);
        $this->assertSame($granularity112->getEncompassingGranularity($granularity122), $granularity2);
        $this->assertSame($granularity1->getEncompassingGranularity($granularity2), $granularity0);
    }

    /**
     * Test de la génération des cellules par la granularité.
     */
    public function testGenerationCells()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');

        $axis1 = new Orga_Model_Axis($this->organization);
        $axis1->setRef('RefGenerationCells1');
        $axis1->setLabel('LabelGenerationCells1');

        $axis2 = new Orga_Model_Axis($this->organization);
        $axis2->setRef('RefGenerationCells2');
        $axis2->setLabel('LabelGenerationCells2');

        $axis3 = new Orga_Model_Axis($this->organization);
        $axis3->setRef('RefGenerationCells3');
        $axis3->setLabel('LabelGenerationCells3');

        $member1 = new Orga_Model_Member($axis1);
        $member1->setRef('RefGenerationCells1');
        $member1->setLabel('LabelGenerationCells1');

        $member21 = new Orga_Model_Member($axis2);
        $member21->setRef('RefGenerationCells21');
        $member21->setLabel('LabelGenerationCells21');

        $member22 = new Orga_Model_Member($axis2);
        $member22->setRef('RefGenerationCells22');
        $member22->setLabel('LabelGenerationCells22');

        $member31 = new Orga_Model_Member($axis3);
        $member31->setRef('RefGenerationCells31');
        $member31->setLabel('LabelGenerationCells31');

        $member32 = new Orga_Model_Member($axis3);
        $member32->setRef('RefGenerationCells32');
        $member32->setLabel('LabelGenerationCells32');

        $granularity1 = $this->granularity;

        $granularity2 = new Orga_Model_Granularity($this->organization, [$axis2]);

        $granularity3 = new Orga_Model_Granularity($this->organization, [$axis1, $axis2, $axis3]);

        $this->organization->save();
        $entityManagers['default']->flush();

        $cellsGranularity1 = $granularity1->getCells();
        $this->assertEquals(count($cellsGranularity1), 1);
        $this->assertEquals(count($cellsGranularity1[0]->getMembers()), 0);

        $cellsGranularity2 = $granularity2->getCells();
        $this->assertEquals(count($cellsGranularity2), 2);
        $this->assertEquals($cellsGranularity2[0]->getMembers(), array($member21));
        $this->assertEquals($cellsGranularity2[1]->getMembers(), array($member22));

        $cellsGranularity3 = $granularity3->getCells();
        $this->assertEquals(count($cellsGranularity3), 4);
        $this->assertEquals($cellsGranularity3[0]->getMembers(), array($member1, $member21, $member31));
        $this->assertEquals($cellsGranularity3[1]->getMembers(), array($member1, $member21, $member32));
        $this->assertEquals($cellsGranularity3[2]->getMembers(), array($member1, $member22, $member31));
        $this->assertEquals($cellsGranularity3[3]->getMembers(), array($member1, $member22, $member32));

        $member31->delete();
        $entityManagers['default']->flush();

        $cellsGranularity3 = $granularity3->getCells();
        $this->assertEquals(count($cellsGranularity3), 2);
        $this->assertEquals($cellsGranularity3[1]->getMembers(), array($member1, $member21, $member32));
        $this->assertEquals($cellsGranularity3[3]->getMembers(), array($member1, $member22, $member32));

        $member23 = new Orga_Model_Member($axis2);
        $member23->setRef('RefGenerationCells23');
        $member23->setLabel('LabelGenerationCells23');
        $member23->save();
        $entityManagers['default']->flush();

        $cellsGranularity2 = $granularity2->getCells();
        $this->assertEquals(count($cellsGranularity2), 3);
        $this->assertEquals($cellsGranularity2[0]->getMembers(), array($member21));
        $this->assertEquals($cellsGranularity2[1]->getMembers(), array($member22));
        $this->assertEquals($cellsGranularity2[2]->getMembers(), array($member23));

        $cellsGranularity3 = $granularity3->getCells();
        $this->assertEquals(count($cellsGranularity3), 3);
        $this->assertEquals($cellsGranularity3[1]->getMembers(), array($member1, $member21, $member32));
        $this->assertEquals($cellsGranularity3[3]->getMembers(), array($member1, $member22, $member32));
        $this->assertEquals($cellsGranularity3[4]->getMembers(), array($member23, $member1, $member32));
    }

    /**
     * Fonction appelee apres chaque test.
     */
    protected function tearDown()
    {
        Orga_Test_OrganizationTest::deleteObject($this->organization);
//        Orga_Test_GranularityTest::deleteObject($this->granularity);
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

