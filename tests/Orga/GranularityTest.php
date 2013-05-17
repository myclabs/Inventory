<?php
/**
 * Class Orga_Test_GranularityTest
 * @author valentin.claras
 * @author sidoine.Tardieu
 * @package    Orga
 * @subpackage Test
 */

// require_once dirname(__FILE__).'/CubeTest.php';

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
     * @param Orga_Model_Cube $cube
     * @return Orga_Model_Granularity
     */
    public static function generateObject($cube=null)
    {
        if ($cube === null) {
            $cube = Orga_Test_CubeTest::generateObject();
        }
        $o = new Orga_Model_Granularity();
        $o->setCube($cube);
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        return $o;
    }

    /**
     * Suppression d'un objet cree avec generateObject
     * @param Orga_Model_Granularity $o
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
     * @return Orga_Model_Granularity
     */
    function testConstruct()
    {
        $cube = Orga_Test_CubeTest::generateObject();
        $o = new Orga_Model_Granularity();
        $o->setCube($cube);
        $o->setNavigability(false);
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
         $this->assertEquals($oLoaded->isNavigable(), $o->isNavigable());
         $this->assertSame($oLoaded->getCube(), $o->getCube());
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
        Orga_Test_CubeTest::deleteObject($o->getCube());
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
 * @package Cube
 * @subpackage Test
 */
class Orga_Test_GranularityOthers extends PHPUnit_Framework_TestCase
{
    /**
     * @var Orga_Model_Cube
     */
    protected $cube;

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
        $this->granularity = Orga_Test_GranularityTest::generateObject();
        $this->cube = $this->granularity->getCube();
    }

    /**
     * Test de loadbyref
     */
    public function testRefAndLabel()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');

        $axis1 = new Orga_Model_Axis();
        $axis1->setRef('RefAxisAxis1');
        $axis1->setLabel('LabelAxisAxis1');
        $axis1->setCube($this->cube);
        $axis1->save();

        $axis2 = new Orga_Model_Axis();
        $axis2->setRef('RefAxisAxis2');
        $axis2->setLabel('LabelAxisAxis2');
        $axis2->setCube($this->cube);
        $axis2->save();
        
        $granularity1 = new Orga_Model_Granularity();
        $granularity1->setCube($this->cube);
        $granularity1->save();
        $granularity1->addAxis($axis1);

        $granularity2 = new Orga_Model_Granularity();
        $granularity2->setCube($this->cube);
        $granularity2->save();
        $granularity2->addAxis($axis1);
        $granularity2->addAxis($axis2);

        $entityManagers['default']->flush();

        $this->assertEquals('global', $this->granularity->getRef());
        $this->assertEquals('RefAxisAxis1', $granularity1->getRef());
        $this->assertEquals('RefAxisAxis1|RefAxisAxis2', $granularity2->getRef());
        $this->assertEquals('global', $this->granularity->getRef());
        $this->assertEquals('LabelAxisAxis1', $granularity1->getLabel());
        $this->assertEquals('LabelAxisAxis1 | LabelAxisAxis2', $granularity2->getLabel());

        $o = Orga_Model_Granularity::loadByRefAndCube('global', $this->cube);
        $this->assertSame($this->granularity, $o);
        $o = Orga_Model_Granularity::loadByRefAndCube('RefAxisAxis1', $this->cube);
        $this->assertSame($granularity1, $o);
        $o = Orga_Model_Granularity::loadByRefAndCube('RefAxisAxis1|RefAxisAxis2', $this->cube);
        $this->assertSame($granularity2, $o);
    }

    /**
     * Tests all functions relative to Granularity in Axis.
     */
    public function testManageAxes()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');

        $axis1 = new Orga_Model_Axis();
        $axis1->setRef('RefAxisAxis1');
        $axis1->setLabel('LabelAxisAxis1');
        $axis1->setCube($this->cube);
        $axis1->save();

        $axis2 = new Orga_Model_Axis();
        $axis2->setRef('RefAxisAxis2');
        $axis2->setLabel('LabelAxisAxis2');
        $axis2->setCube($this->cube);
        $axis2->save();

        $axis3 = new Orga_Model_Axis();
        $axis3->setRef('RefAxisAxis3');
        $axis3->setLabel('LabelAxisAxis3');
        $axis3->setCube($this->cube);
        $axis3->save();

        $entityManagers['default']->flush();

        $this->assertFalse($this->granularity->hasAxes());
        $this->assertEmpty($this->granularity->getAxes());
        $this->assertFalse($this->granularity->hasAxis($axis1));
        $this->assertFalse($this->granularity->hasAxis($axis2));
        $this->assertFalse($this->granularity->hasAxis($axis3));
        $this->assertFalse($axis1->hasGranularity($this->granularity));
        $this->assertFalse($axis2->hasGranularity($this->granularity));
        $this->assertFalse($axis3->hasGranularity($this->granularity));

        $this->granularity->addAxis($axis1);

        $this->assertTrue($this->granularity->hasAxes());
        $this->assertEquals(array(0 => $axis1), $this->granularity->getAxes());
        $this->assertTrue($this->granularity->hasAxis($axis1));
        $this->assertFalse($this->granularity->hasAxis($axis2));
        $this->assertFalse($this->granularity->hasAxis($axis3));
        $this->assertTrue($axis1->hasGranularity($this->granularity));
        $this->assertFalse($axis2->hasGranularity($this->granularity));
        $this->assertFalse($axis3->hasGranularity($this->granularity));

        $this->granularity->addAxis($axis2);
        $this->granularity->addAxis($axis3);

        $this->assertTrue($this->granularity->hasAxes());
        $this->assertEquals(array(0 => $axis1, 1 => $axis2, 2 => $axis3), $this->granularity->getAxes());
        $this->assertTrue($this->granularity->hasAxis($axis1));
        $this->assertTrue($this->granularity->hasAxis($axis2));
        $this->assertTrue($this->granularity->hasAxis($axis3));
        $this->assertTrue($axis1->hasGranularity($this->granularity));
        $this->assertTrue($axis2->hasGranularity($this->granularity));
        $this->assertTrue($axis3->hasGranularity($this->granularity));

        $this->granularity->removeAxis($axis2);

        $this->assertTrue($this->granularity->hasAxes());
        $this->assertEquals(array(0 => $axis1, 2 => $axis3), $this->granularity->getAxes());
        $this->assertTrue($this->granularity->hasAxis($axis1));
        $this->assertFalse($this->granularity->hasAxis($axis2));
        $this->assertTrue($this->granularity->hasAxis($axis3));
        $this->assertTrue($axis1->hasGranularity($this->granularity));
        $this->assertFalse($axis2->hasGranularity($this->granularity));
        $this->assertTrue($axis3->hasGranularity($this->granularity));
    }

    /**
     * Test the granularity function to know her relative narrower broader.
     */
    public function testGetNarrowerBroaderGranularities()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');

        $axis1 = new Orga_Model_Axis();
        $axis1->setRef('RefNarrowerBroaderGranularities1');
        $axis1->setLabel('LabelNarrowerBroaderGranularities1');
        $axis1->setCube($this->cube);
        $axis1->save();
        $entityManagers['default']->flush();

        $axis11 = new Orga_Model_Axis();
        $axis11->setRef('RefNarrowerBroaderGranularities11');
        $axis11->setLabel('LabelNarrowerBroaderGranularities11');
        $axis11->setCube($this->cube);
        $axis11->setDirectNarrower($axis1);
        $axis11->save();
        $entityManagers['default']->flush();

        $axis111 = new Orga_Model_Axis();
        $axis111->setRef('RefNarrowerBroaderGranularities111');
        $axis111->setLabel('LabelNarrowerBroaderGranularities111');
        $axis111->setCube($this->cube);
        $axis111->setDirectNarrower($axis11);
        $axis111->save();
        $entityManagers['default']->flush();

        $axis12 = new Orga_Model_Axis();
        $axis12->setRef('RefNarrowerBroaderGranularities12');
        $axis12->setLabel('LabelNarrowerBroaderGranularities12');
        $axis12->setCube($this->cube);
        $axis12->setDirectNarrower($axis1);
        $axis12->save();
        $entityManagers['default']->flush();

        $axis121 = new Orga_Model_Axis();
        $axis121->setRef('RefNarrowerBroaderGranularities121');
        $axis121->setLabel('LabelNarrowerBroaderGranularities121');
        $axis121->setCube($this->cube);
        $axis121->setDirectNarrower($axis12);
        $axis121->save();
        $entityManagers['default']->flush();

        $axis122 = new Orga_Model_Axis();
        $axis122->setRef('RefNarrowerBroaderGranularities122');
        $axis122->setLabel('LabelNarrowerBroaderGranularities122');
        $axis122->setCube($this->cube);
        $axis122->setDirectNarrower($axis12);
        $axis122->save();
        $entityManagers['default']->flush();

        $axis123 = new Orga_Model_Axis();
        $axis123->setRef('RefNarrowerBroaderGranularities123');
        $axis123->setLabel('LabelNarrowerBroaderGranularities123');
        $axis123->setCube($this->cube);
        $axis123->setDirectNarrower($axis12);
        $axis123->save();
        $entityManagers['default']->flush();

        $axis2 = new Orga_Model_Axis();
        $axis2->setRef('RefNarrowerBroaderGranularities2');
        $axis2->setLabel('LabelNarrowerBroaderGranularities2');
        $axis2->setCube($this->cube);
        $axis2->save();
        $entityManagers['default']->flush();

        $axis21 = new Orga_Model_Axis();
        $axis21->setRef('RefNarrowerBroaderGranularities21');
        $axis21->setLabel('LabelNarrowerBroaderGranularities21');
        $axis21->setCube($this->cube);
        $axis21->setDirectNarrower($axis2);
        $axis21->save();
        $entityManagers['default']->flush();

        $axis3 = new Orga_Model_Axis();
        $axis3->setRef('RefNarrowerBroaderGranularities3');
        $axis3->setLabel('LabelNarrowerBroaderGranularities3');
        $axis3->setCube($this->cube);
        $axis3->save();
        $entityManagers['default']->flush();

        $axis31 = new Orga_Model_Axis();
        $axis31->setRef('RefNarrowerBroaderGranularities31');
        $axis31->setLabel('LabelNarrowerBroaderGranularities31');
        $axis31->setCube($this->cube);
        $axis31->setDirectNarrower($axis3);
        $axis31->save();
        $entityManagers['default']->flush();

        $axis311 = new Orga_Model_Axis();
        $axis311->setRef('RefNarrowerBroaderGranularities311');
        $axis311->setLabel('LabelNarrowerBroaderGranularities311');
        $axis311->setCube($this->cube);
        $axis311->setDirectNarrower($axis31);
        $axis311->save();
        $entityManagers['default']->flush();

        $axis312 = new Orga_Model_Axis();
        $axis312->setRef('RefNarrowerBroaderGranularities312');
        $axis312->setLabel('LabelNarrowerBroaderGranularities312');
        $axis312->setCube($this->cube);
        $axis312->setDirectNarrower($axis31);
        $axis312->save();
        $entityManagers['default']->flush();

        $axis32 = new Orga_Model_Axis();
        $axis32->setRef('RefNarrowerBroaderGranularities32');
        $axis32->setLabel('LabelNarrowerBroaderGranularities32');
        $axis32->setCube($this->cube);
        $axis32->setDirectNarrower($axis3);
        $axis32->save();
        $entityManagers['default']->flush();

        $axis33 = new Orga_Model_Axis();
        $axis33->setRef('RefNarrowerBroaderGranularities33');
        $axis33->setLabel('LabelNarrowerBroaderGranularities33');
        $axis33->setCube($this->cube);
        $axis33->setDirectNarrower($axis3);
        $axis33->save();
        $entityManagers['default']->flush();

        $axis331 = new Orga_Model_Axis();
        $axis331->setRef('RefNarrowerBroaderGranularities331');
        $axis331->setLabel('LabelNarrowerBroaderGranularities331');
        $axis331->setCube($this->cube);
        $axis331->setDirectNarrower($axis33);
        $axis331->save();
        $entityManagers['default']->flush();

        $axis332 = new Orga_Model_Axis();
        $axis332->setRef('RefNarrowerBroaderGranularities332');
        $axis332->setLabel('LabelNarrowerBroaderGranularities332');
        $axis332->setCube($this->cube);
        $axis332->setDirectNarrower($axis33);
        $axis332->save();
        $entityManagers['default']->flush();

        $granularity0 = $this->granularity;

        $granularity1 = new Orga_Model_Granularity();
        $granularity1->setCube($this->cube);
        $granularity1->save();
        $granularity1->addAxis($axis11);
        $granularity1->addAxis($axis122);
        $granularity1->addAxis($axis311);

        $granularity2 = new Orga_Model_Granularity();
        $granularity2->setCube($this->cube);
        $granularity2->save();
        $granularity2->addAxis($axis1);
        $granularity2->addAxis($axis31);

        $granularity3 = new Orga_Model_Granularity();
        $granularity3->setCube($this->cube);
        $granularity3->save();
        $granularity3->addAxis($axis2);

        $granularity4 = new Orga_Model_Granularity();
        $granularity4->setCube($this->cube);
        $granularity4->save();
        $granularity4->addAxis($axis1);
        $granularity4->addAxis($axis3);

        $granularity5 = new Orga_Model_Granularity();
        $granularity5->setCube($this->cube);
        $granularity5->save();
        $granularity5->addAxis($axis12);
        $granularity5->addAxis($axis21);
        $granularity5->addAxis($axis33);

        $entityManagers['default']->flush();

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

        $axis1 = new Orga_Model_Axis();
        $axis1->setRef('RefCrossedEncompassingGranularities1');
        $axis1->setLabel('LabelCrossedEncompassingGranularities1');
        $axis1->setCube($this->cube);
        $axis1->save();
        $entityManagers['default']->flush();

        $axis11 = new Orga_Model_Axis();
        $axis11->setRef('RefCrossedEncompassingGranularities11');
        $axis11->setLabel('LabelCrossedEncompassingGranularities11');
        $axis11->setCube($this->cube);
        $axis11->setDirectNarrower($axis1);
        $axis11->save();
        $entityManagers['default']->flush();

        $axis12 = new Orga_Model_Axis();
        $axis12->setRef('RefCrossedEncompassingGranularities12');
        $axis12->setLabel('LabelCrossedEncompassingGranularities12');
        $axis12->setCube($this->cube);
        $axis12->setDirectNarrower($axis1);
        $axis12->save();
        $entityManagers['default']->flush();

        $axis2 = new Orga_Model_Axis();
        $axis2->setRef('RefCrossedEncompassingGranularities2');
        $axis2->setLabel('LabelCrossedEncompassingGranularities2');
        $axis2->setCube($this->cube);
        $axis2->save();
        $entityManagers['default']->flush();

        $granularity0 = $this->granularity;

        $granularity1 = new Orga_Model_Granularity();
        $granularity1->setCube($this->cube);
        $granularity1->save();
        $granularity1->addAxis($axis1);

        $granularity2 = new Orga_Model_Granularity();
        $granularity2->setCube($this->cube);
        $granularity2->save();
        $granularity2->addAxis($axis2);

        $granularity12 = new Orga_Model_Granularity();
        $granularity12->setCube($this->cube);
        $granularity12->save();
        $granularity12->addAxis($axis1);
        $granularity12->addAxis($axis2);

        $granularity112 = new Orga_Model_Granularity();
        $granularity112->setCube($this->cube);
        $granularity112->save();
        $granularity112->addAxis($axis11);
        $granularity112->addAxis($axis2);

        $granularity122 = new Orga_Model_Granularity();
        $granularity122->setCube($this->cube);
        $granularity122->save();
        $granularity122->addAxis($axis12);
        $granularity122->addAxis($axis2);

        $granularity3 = new Orga_Model_Granularity();
        $granularity3->setCube($this->cube);
        $granularity3->save();
        $granularity3->addAxis($axis11);
        $granularity3->addAxis($axis12);
        $granularity3->addAxis($axis2);

        $entityManagers['default']->flush();

        $this->assertEquals($axis1->getGlobalPosition(), 1);
        $this->assertEquals($axis11->getGlobalPosition(), 2);
        $this->assertEquals($axis12->getGlobalPosition(), 3);
        $this->assertEquals($axis2->getGlobalPosition(), 4);
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

        $axis1 = new Orga_Model_Axis();
        $axis1->setRef('RefGenerationCells1');
        $axis1->setLabel('LabelGenerationCells1');
        $axis1->setCube($this->cube);
        $axis1->save();

        $axis2 = new Orga_Model_Axis();
        $axis2->setRef('RefGenerationCells2');
        $axis2->setLabel('LabelGenerationCells2');
        $axis2->setCube($this->cube);
        $axis2->save();

        $axis3 = new Orga_Model_Axis();
        $axis3->setRef('RefGenerationCells3');
        $axis3->setLabel('LabelGenerationCells3');
        $axis3->setCube($this->cube);
        $axis3->save();

        $entityManagers['default']->flush();

        $member1 = new Orga_Model_Member();
        $member1->setRef('RefGenerationCells1');
        $member1->setLabel('LabelGenerationCells1');
        $member1->setAxis($axis1);
        $member1->save();

        $member21 = new Orga_Model_Member();
        $member21->setRef('RefGenerationCells21');
        $member21->setLabel('LabelGenerationCells21');
        $member21->setAxis($axis2);
        $member21->save();

        $member22 = new Orga_Model_Member();
        $member22->setRef('RefGenerationCells22');
        $member22->setLabel('LabelGenerationCells22');
        $member22->setAxis($axis2);
        $member22->save();

        $member31 = new Orga_Model_Member();
        $member31->setRef('RefGenerationCells31');
        $member31->setLabel('LabelGenerationCells31');
        $member31->setAxis($axis3);
        $member31->save();

        $member32 = new Orga_Model_Member();
        $member32->setRef('RefGenerationCells32');
        $member32->setLabel('LabelGenerationCells32');
        $member32->setAxis($axis3);
        $member32->save();

        $entityManagers['default']->flush();

        $granularity1 = $this->granularity;

        $granularity2 = new Orga_Model_Granularity();
        $granularity2->setCube($this->cube);
        $granularity2->addAxis($axis2);
        $granularity2->save();

        $granularity3 = new Orga_Model_Granularity();
        $granularity3->setCube($this->cube);
        $granularity3->addAxis($axis1);
        $granularity3->addAxis($axis2);
        $granularity3->addAxis($axis3);
        $granularity3->save();

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

        $granularity2->addAxis($axis3);
        $entityManagers['default']->flush();

        $cellsGranularity2 = $granularity2->getCells();
        $this->assertEquals(count($cellsGranularity2), 2);
        $this->assertEquals($cellsGranularity2[2]->getMembers(), array($member21, $member32));
        $this->assertEquals($cellsGranularity2[3]->getMembers(), array($member22, $member32));

        $member23 = new Orga_Model_Member();
        $member23->setRef('RefGenerationCells23');
        $member23->setLabel('LabelGenerationCells23');
        $member23->setAxis($axis2);
        $member23->save();
        $entityManagers['default']->flush();

        $cellsGranularity2 = $granularity2->getCells();
        $this->assertEquals(count($cellsGranularity2), 3);
        $this->assertEquals($cellsGranularity2[2]->getMembers(), array($member21, $member32));
        $this->assertEquals($cellsGranularity2[3]->getMembers(), array($member22, $member32));
        $this->assertEquals($cellsGranularity2[4]->getMembers(), array($member23, $member32));

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
        Orga_Test_GranularityTest::deleteObject($this->granularity);
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

