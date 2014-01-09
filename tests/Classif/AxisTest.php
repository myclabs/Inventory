<?php
/**
 * Classe Classif_Test_AxisTest
 * @author     valentin.claras
 * @author     cyril.perraud
 * @package    Classif
 * @subpackage Test
 */

/**
 * Creation of the Test Suite
 * @package    Classif
 */
class Classif_Test_AxisTest
{
    /**
     * Creation of the test suite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Classif_Test_AxisSetUp');
        $suite->addTestSuite('Classif_Test_AxisOther');
        return $suite;
    }

    /**
     * Generation de l'objet de test.
     *
     * @param string $ref
     * @param string $label
     *
     * @return Classif_Model_Axis
     */
    public static function generateObject($ref=null, $label=null)
    {
        $o = new Classif_Model_Axis();
        $o->setRef(($ref ===null) ? 'ref' : $ref);
        $o->setLabel(($label ===null) ? 'label' : $label);
        $o->save();
        \Core\ContainerSingleton::getEntityManager()->flush();
        return $o;
    }

    /**
     * Suppression d'un objet cree avec generateObject
     * @param Classif_Model_Axis $o
     */
    public static function deleteObject($o)
    {
        $o->delete();
        \Core\ContainerSingleton::getEntityManager()->flush();
    }

}

/**
 * Test of the creation/modification/deletion of the entity
 * @package    Classif
 */
class Classif_Test_AxisSetUp extends PHPUnit_Framework_TestCase
{

    /**
     * Function called once, before all the tests
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun Classif_Model_Axis en base, sinon suppression !
        if (Classif_Model_Axis::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Axis restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Classif_Model_Axis::loadList() as $axis) {
                $axis->delete();
            }
            \Core\ContainerSingleton::getEntityManager()->flush();
        }
    }

    /**
     * Test le constructeur
     * @return Classif_Model_Axis
     */
    function testConstruct()
    {
        $o = new Classif_Model_Axis();
        $this->assertInstanceOf('Classif_Model_Axis', $o);
        $o->setRef('RefAxisTest');
        $o->setLabel('LabelAxisTest');
        $this->assertEquals(array(), $o->getKey());
        $o->save();
        \Core\ContainerSingleton::getEntityManager()->flush();
        $this->assertNotEquals(array(), $o->getKey());
        return $o;
    }

    /**
     * @depends testConstruct
     * @param Classif_Model_Axis $o
     */
    function testLoad(Classif_Model_Axis $o)
    {
         $oLoaded = Classif_Model_Axis::load($o->getKey());
         $this->assertInstanceOf('Classif_Model_Axis', $o);
         $this->assertEquals($oLoaded->getKey(), $o->getKey());
         $this->assertEquals($oLoaded->getRef(), $o->getRef());
         $this->assertEquals($oLoaded->getLabel(), $o->getLabel());
         return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param Classif_Model_Axis $o
     */
    function testDelete(Classif_Model_Axis $o)
    {
        $o->delete();
        \Core\ContainerSingleton::getEntityManager()->flush();
        $this->assertEquals(array(), $o->getKey());
    }

    /**
     * Function called once, after all the tests
     */
    public static function tearDownAfterClass()
    {
        // Vérification qu'il ne reste aucun Classif_Model_Axis en base, sinon suppression !
        if (Classif_Model_Axis::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Axis restants ont été trouvé après les tests, suppression en cours !';
            foreach (Classif_Model_Axis::loadList() as $axis) {
                $axis->delete();
            }
            \Core\ContainerSingleton::getEntityManager()->flush();
        }
    }

}


/**
 * Tests of User class
 * @package    Classif
 */
class Classif_Test_AxisOther extends PHPUnit_Framework_TestCase
{
    // Test objects
    protected $axis;


    /**
     * Function called once, before all the tests
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun Classif_Model_Axis en base, sinon suppression !
        if (Classif_Model_Axis::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Axis restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Classif_Model_Axis::loadList() as $axis) {
                $axis->delete();
            }
            \Core\ContainerSingleton::getEntityManager()->flush();
        }
    }

    /**
     * Function called before each test
     */
    protected function setUp()
    {
        $this->axis = Classif_Test_AxisTest::generateObject();
    }

    /**
     * Test setNarrower
     */
    function testSetGetNarrower()
    {
        $narrower = Classif_Test_AxisTest::generateObject('narrower');
        $this->axis->setDirectNarrower($narrower);
        $this->assertSame($this->axis->getDirectNarrower(), $narrower);
        $this->axis->setDirectNarrower();
        $this->assertNull($this->axis->getDirectNarrower());
        Classif_Test_AxisTest::deleteObject($narrower);
    }

    /**
     * Test d'ajout d'un broader
     */
    public function testManageBroaders()
    {
        $broader1 = Classif_Test_AxisTest::generateObject('broader1');
        $broader11 = Classif_Test_AxisTest::generateObject('broader11');
        $broader2 = Classif_Test_AxisTest::generateObject('broader2');
        $broader3 = Classif_Test_AxisTest::generateObject('broader3');

        $this->assertFalse($this->axis->hasDirectBroaders());
        $this->assertFalse($this->axis->hasDirectBroader($broader1));
        $this->assertFalse($this->axis->hasDirectBroader($broader11));
        $this->assertFalse($this->axis->hasDirectBroader($broader2));
        $this->assertFalse($this->axis->hasDirectBroader($broader3));
        $this->assertEmpty($this->axis->getDirectBroaders());
        $this->assertEmpty($this->axis->getAllBroaders());

        $this->axis->addDirectBroader($broader1);
        $this->axis->addDirectBroader($broader2);

        $this->assertTrue($this->axis->hasDirectBroaders());
        $this->assertTrue($this->axis->hasDirectBroader($broader1));
        $this->assertFalse($this->axis->hasDirectBroader($broader11));
        $this->assertTrue($this->axis->hasDirectBroader($broader2));
        $this->assertFalse($this->axis->hasDirectBroader($broader3));
        $this->assertEquals(array($broader1, $broader2), $this->axis->getDirectBroaders());
        $this->assertEquals(array($broader1, $broader2), $this->axis->getAllBroaders());

        $broader1->addDirectBroader($broader11);

        $this->assertTrue($this->axis->hasDirectBroaders());
        $this->assertTrue($this->axis->hasDirectBroader($broader1));
        $this->assertFalse($this->axis->hasDirectBroader($broader11));
        $this->assertTrue($this->axis->hasDirectBroader($broader2));
        $this->assertFalse($this->axis->hasDirectBroader($broader3));
        $this->assertEquals(array($broader1, $broader2), $this->axis->getDirectBroaders());
        $this->assertEquals(array($broader11, $broader1, $broader2), $this->axis->getAllBroaders());

        $this->axis->removeDirectBroader($broader2);

        $this->assertTrue($this->axis->hasDirectBroaders());
        $this->assertTrue($this->axis->hasDirectBroader($broader1));
        $this->assertFalse($this->axis->hasDirectBroader($broader11));
        $this->assertFalse($this->axis->hasDirectBroader($broader2));
        $this->assertFalse($this->axis->hasDirectBroader($broader3));
        $this->assertEquals(array($broader1), $this->axis->getDirectBroaders());
        $this->assertEquals(array($broader11, $broader1), $this->axis->getAllBroaders());

        Classif_Test_AxisTest::deleteObject($broader3);
        Classif_Test_AxisTest::deleteObject($broader2);
        Classif_Test_AxisTest::deleteObject($broader11);
        Classif_Test_AxisTest::deleteObject($broader1);

        $this->assertFalse($this->axis->hasDirectBroaders());
        $this->assertFalse($this->axis->hasDirectBroader($broader1));
        $this->assertFalse($this->axis->hasDirectBroader($broader11));
        $this->assertFalse($this->axis->hasDirectBroader($broader2));
        $this->assertFalse($this->axis->hasDirectBroader($broader3));
        $this->assertEmpty($this->axis->getDirectBroaders());
        $this->assertEmpty($this->axis->getAllBroaders());
    }

    /**
     * Function called after each test
     */
    protected function tearDown()
    {
        if ($this->axis) {
            Classif_Test_AxisTest::deleteObject($this->axis);
        }
    }

    /**
     * Function called once, after all the tests
     */
    public static function tearDownAfterClass()
    {
        // Vérification qu'il ne reste aucun Classif_Model_Axis en base, sinon suppression !
        if (Classif_Model_Axis::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Axis restants ont été trouvé après les tests, suppression en cours !';
            foreach (Classif_Model_Axis::loadList() as $axis) {
                $axis->delete();
            }
            \Core\ContainerSingleton::getEntityManager()->flush();
        }
    }

}
