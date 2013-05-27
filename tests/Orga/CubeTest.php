<?php
/**
 * Class Orga_Test_CubeTest
 * @author valentin.claras
 * @author maxime.fourt
 * @package    Orga
 * @subpackage Test
 */

/**
 * Test Cube Class.
 * @package    Orga
 * @subpackage Test
 */
class Orga_Test_CubeTest
{
    /**
     * Creation of the test suite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Orga_Test_CubeSetUp');
        $suite->addTestSuite('Orga_Test_CubeOthers');
        return $suite;
    }

    /**
     * Generation of a test object
     *
     * @return Orga_Model_Cube
     */
    public static function generateObject()
    {
        $o = new Orga_Model_Cube();
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        return $o;
    }

    /**
     * Deletion of an object created with generateObject
     *
     * @param Orga_Cube $o
     */
    public static function deleteObject($o)
    {
        if ($o) $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }
}

/**
 * Test des méthodes de base de l'objet métier Orga_Model_Cube
 * @package Orga
 */
class Orga_Test_CubeSetUp extends PHPUnit_Framework_TestCase
{
    /**
     * Methode exécuter avant chaque test
     */
     public static  function setUpBeforeClass()
     {
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
     * @return Orga_Model_Cube
     */
    function testConstruct()
    {
        $o = new Orga_Model_Cube();
        $this->assertInstanceOf('Orga_Model_Cube', $o);
        $this->assertEquals($o->getKey(), array());
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertNotEquals(array(), $o->getKey());
        return $o;
    }

    /**
     * @depends testConstruct
     * @param Orga_Model_Cube $o
     */
    function testLoad(Orga_Model_Cube $o)
    {
         $oLoaded = Orga_Model_Cube::load($o->getKey());
         $this->assertInstanceOf('Orga_Model_Cube', $o);
         $this->assertEquals($oLoaded->getKey(), $o->getKey());
         return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param Orga_Model_Cube $o
     */
    function testDelete(Orga_Model_Cube $o)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertEquals(array(), $o->getKey());
    }

    /**
     * Function called once, after all the tests
     */
    public static function tearDownAfterClass()
    {
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
 * Test des fonctionnalités de l'objet métier Orga_Model_Cube
 * @package Orga
 */
class Orga_Test_CubeOthers extends PHPUnit_Framework_TestCase
{
    /**
     * @var Orga_Model_Cube
     */
    protected $cube;

    /**
     * Méthode appelée avant les tests
     */
    public static  function setUpBeforeClass()
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
     * Function called before each test.
     */
    protected function setUp()
    {
        // Create a test object
        $this->cube = Orga_Test_CubeTest::generateObject();
    }

    /**
     * Tests all functions relative to Axis.
     */
    public function testManageAxes()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');

        $axis1 = new Orga_Model_Axis();
        $axis1->setRef('RefManageAxis1');
        $axis1->setLabel('LabelManageAxis1');

        $axis2 = new Orga_Model_Axis();
        $axis2->setRef('RefManageAxis2');
        $axis2->setLabel('LabelManageAxis2');

        $this->assertFalse($this->cube->hasAxes());
        $this->assertEmpty($this->cube->getAxes());
        $this->assertEmpty($this->cube->getRootAxes());
        $this->assertNull($axis1->getCube());
        $this->assertEquals(null, $axis1->getGlobalPosition());
        $this->assertEquals(null, $axis2->getGlobalPosition());

        $this->cube->addAxis($axis1);

        $this->assertTrue($this->cube->hasAxes());
        $this->assertEquals(array(0 => $axis1), $this->cube->getAxes());
        $this->assertEquals(array(0 => $axis1), $this->cube->getRootAxes());
        $this->assertSame($this->cube, $axis1->getCube());
        $this->assertNull($axis2->getCube());
        $this->assertEquals(1, $axis1->getGlobalPosition());
        $this->assertEquals(null, $axis2->getGlobalPosition());

        $axis1->save();
        $entityManagers['default']->flush();

        $axis11 = new Orga_Model_Axis();
        $axis11->setRef('RefManageAxis11');
        $axis11->setLabel('LabelManageAxis11');

        $this->assertNull($axis11->getCube());

        $this->cube->addAxis($axis2);
        $this->cube->addAxis($axis11);
        $axis11->setDirectNarrower($axis1);

        $axis11->save();
        $axis2->save();
        $entityManagers['default']->flush();

        $this->assertTrue($this->cube->hasAxes());
        $this->assertEquals(array(0 => $axis1, 1 => $axis2, 2 => $axis11), $this->cube->getAxes());
        $this->assertEquals(array(0 => $axis1, 1 => $axis2), $this->cube->getRootAxes());
        $this->assertEquals(array(0 => $axis1, 1 => $axis11, 2 => $axis2), $this->cube->getFirstOrderedAxes());
        $this->assertEquals(array(0 => $axis11, 1 => $axis1, 2 => $axis2), $this->cube->getLastOrderedAxes());
        $this->assertSame($this->cube, $axis1->getCube());
        $this->assertSame($this->cube, $axis2->getCube());
        $this->assertSame($this->cube, $axis11->getCube());
        $this->assertEquals(1, $axis1->getGlobalPosition());
        $this->assertEquals(3, $axis2->getGlobalPosition());
        $this->assertEquals(2, $axis11->getGlobalPosition());

        $axis1->delete();
        $entityManagers['default']->flush();

        $this->assertTrue($this->cube->hasAxes());
        $this->assertEquals(array(1 => $axis2), $this->cube->getAxes());
        $this->assertEquals(array(1 => $axis2), $this->cube->getRootAxes());
        $this->assertSame($this->cube, $axis1->getCube());
        $this->assertSame($this->cube, $axis11->getCube());
        $this->assertSame($this->cube, $axis2->getCube());
    }

    /**
     * Tests all functions relative to Granularity.
     */
    public function testManageGranularities()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');

        $axis1 = new Orga_Model_Axis();
        $axis1->setRef('RefManageGranularities1');
        $axis1->setLabel('LabelManageGranularities1');
        $axis1->setCube($this->cube);
        $axis1->save();
        $axis2 = new Orga_Model_Axis();
        $axis2->setRef('RefManageGranularities2');
        $axis2->setLabel('LabelManageGranularities2');
        $axis2->setCube($this->cube);
        $axis2->save();

        $granularity1 = new Orga_Model_Granularity();

        $granularity2 = new Orga_Model_Granularity();

        $this->assertFalse($this->cube->hasGranularities());
        $this->assertEmpty($this->cube->getGranularities());
        $this->assertNull($granularity1->getCube());
        $this->assertNull($granularity2->getCube());

        $this->cube->addGranularity($granularity1);

        $granularity1->save();
        $granularity1->addAxis($axis1);
        $entityManagers['default']->flush();

        $this->assertTrue($this->cube->hasGranularities());
        $this->assertEquals(array(0 => $granularity1), $this->cube->getGranularities());
        $this->assertSame($this->cube, $granularity1->getCube());
        $this->assertNull($granularity2->getCube());

        $this->cube->addGranularity($granularity2);

        $granularity2->save();
        $granularity2->addAxis($axis2);
        $entityManagers['default']->flush();

        $this->assertTrue($this->cube->hasGranularities());
        $this->assertEquals(array(0 => $granularity1, 1 => $granularity2), $this->cube->getGranularities());
        $this->assertSame($this->cube, $granularity1->getCube());
        $this->assertSame($this->cube, $granularity2->getCube());

        $granularity1->delete();
        $entityManagers['default']->flush();

        $this->assertTrue($this->cube->hasGranularities());
        $this->assertEquals(array(1 => $granularity2), $this->cube->getGranularities());
        $this->assertSame($this->cube, $granularity2->getCube());
    }

    /**
     * Test the cube function to order granularities.
     */
    public function testOrderGranularities()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');

        $axis1 = new Orga_Model_Axis();
        $axis1->setRef('RefOrderGranularities1');
        $axis1->setLabel('LabelOrderGranularities1');
        $axis1->setCube($this->cube);
        $axis1->save();
        $entityManagers['default']->flush();

        $axis11 = new Orga_Model_Axis();
        $axis11->setRef('RefOrderGranularities11');
        $axis11->setLabel('LabelOrderGranularities11');
        $axis11->setCube($this->cube);
        $axis11->setDirectNarrower($axis1);
        $axis11->save();
        $entityManagers['default']->flush();

        $axis111 = new Orga_Model_Axis();
        $axis111->setRef('RefOrderGranularities111');
        $axis111->setLabel('LabelOrderGranularities111');
        $axis111->setCube($this->cube);
        $axis111->setDirectNarrower($axis11);
        $axis111->save();
        $entityManagers['default']->flush();

        $axis12 = new Orga_Model_Axis();
        $axis12->setRef('RefOrderGranularities12');
        $axis12->setLabel('LabelOrderGranularities12');
        $axis12->setCube($this->cube);
        $axis12->setDirectNarrower($axis1);
        $axis12->save();
        $entityManagers['default']->flush();

        $axis121 = new Orga_Model_Axis();
        $axis121->setRef('RefOrderGranularities121');
        $axis121->setLabel('LabelOrderGranularities121');
        $axis121->setCube($this->cube);
        $axis121->setDirectNarrower($axis12);
        $axis121->save();
        $entityManagers['default']->flush();

        $axis122 = new Orga_Model_Axis();
        $axis122->setRef('RefOrderGranularities122');
        $axis122->setLabel('LabelOrderGranularities122');
        $axis122->setCube($this->cube);
        $axis122->setDirectNarrower($axis12);
        $axis122->save();
        $entityManagers['default']->flush();

        $axis123 = new Orga_Model_Axis();
        $axis123->setRef('RefOrderGranularities123');
        $axis123->setLabel('LabelOrderGranularities123');
        $axis123->setCube($this->cube);
        $axis123->setDirectNarrower($axis12);
        $axis123->save();
        $entityManagers['default']->flush();

        $axis2 = new Orga_Model_Axis();
        $axis2->setRef('RefOrderGranularities2');
        $axis2->setLabel('LabelOrderGranularities2');
        $axis2->setCube($this->cube);
        $axis2->save();
        $entityManagers['default']->flush();

        $axis21 = new Orga_Model_Axis();
        $axis21->setRef('RefOrderGranularities21');
        $axis21->setLabel('LabelOrderGranularities21');
        $axis21->setCube($this->cube);
        $axis21->setDirectNarrower($axis2);
        $axis21->save();
        $entityManagers['default']->flush();

        $axis3 = new Orga_Model_Axis();
        $axis3->setRef('RefOrderGranularities3');
        $axis3->setLabel('LabelOrderGranularities3');
        $axis3->setCube($this->cube);
        $axis3->save();
        $entityManagers['default']->flush();

        $axis31 = new Orga_Model_Axis();
        $axis31->setRef('RefOrderGranularities31');
        $axis31->setLabel('LabelOrderGranularities31');
        $axis31->setCube($this->cube);
        $axis31->setDirectNarrower($axis3);
        $axis31->save();
        $entityManagers['default']->flush();

        $axis311 = new Orga_Model_Axis();
        $axis311->setRef('RefOrderGranularities311');
        $axis311->setLabel('LabelOrderGranularities311');
        $axis311->setCube($this->cube);
        $axis311->setDirectNarrower($axis31);
        $axis311->save();
        $entityManagers['default']->flush();

        $axis312 = new Orga_Model_Axis();
        $axis312->setRef('RefOrderGranularities312');
        $axis312->setLabel('LabelOrderGranularities312');
        $axis312->setCube($this->cube);
        $axis312->setDirectNarrower($axis31);
        $axis312->save();
        $entityManagers['default']->flush();

        $axis32 = new Orga_Model_Axis();
        $axis32->setRef('RefOrderGranularities32');
        $axis32->setLabel('LabelOrderGranularities32');
        $axis32->setCube($this->cube);
        $axis32->setDirectNarrower($axis3);
        $axis32->save();
        $entityManagers['default']->flush();

        $axis33 = new Orga_Model_Axis();
        $axis33->setRef('RefOrderGranularities33');
        $axis33->setLabel('LabelOrderGranularities33');
        $axis33->setCube($this->cube);
        $axis33->setDirectNarrower($axis3);
        $axis33->save();
        $entityManagers['default']->flush();

        $axis331 = new Orga_Model_Axis();
        $axis331->setRef('RefOrderGranularities331');
        $axis331->setLabel('LabelOrderGranularities331');
        $axis331->setCube($this->cube);
        $axis331->setDirectNarrower($axis33);
        $axis331->save();
        $entityManagers['default']->flush();

        $axis332 = new Orga_Model_Axis();
        $axis332->setRef('RefOrderGranularities332');
        $axis332->setLabel('LabelOrderGranularities332');
        $axis332->setCube($this->cube);
        $axis332->setDirectNarrower($axis33);
        $axis332->save();
        $entityManagers['default']->flush();

        $granularity0 = new Orga_Model_Granularity();
        $granularity0->setCube($this->cube);
        $granularity0->save();

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

        $this->assertEquals($granularity0->getPosition(), 1);
        $this->assertEquals($granularity1->getPosition(), 4);
        $this->assertEquals($granularity2->getPosition(), 5);
        $this->assertEquals($granularity3->getPosition(), 2);
        $this->assertEquals($granularity4->getPosition(), 6);
        $this->assertEquals($granularity5->getPosition(), 3);

        $this->assertEquals($this->cube->getGranularities(), array($granularity0, $granularity1, $granularity2, $granularity3, $granularity4, $granularity5));
    }

    /**
     * Function tearDown
     *  Fonction appelee apres chaque test
     */
    protected function tearDown()
    {
        Orga_Test_CubeTest::deleteObject($this->cube);
    }

    /**
     * Véfirifie si la table est vide et supprime les mots clés créés dans keyword
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
