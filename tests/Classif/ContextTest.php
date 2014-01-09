<?php
/**
 * Classe Classif_Test_ContextTest
 * @author     valentin.claras
 * @author     cyril.perraud
 * @package    Classif
 * @subpackage Test
 */

/**
 * Creation of the Test Suite
 * @package    Classif
 */
class Classif_Test_ContextTest
{
    /**
     * Creation of the test suite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Classif_Test_ContextSetUp');
        return $suite;
    }

    /**
     * Generation de l'objet de test.
     *
     * @param string $ref
     * @param string $label
     *
     * @return Classif_Model_Context
     */
    public static function generateObject($ref=null, $label=null)
    {
        $o = new Classif_Model_Context();
        $o->setRef(($ref ===null) ? 'ref' : $ref);
        $o->setLabel(($label ===null) ? 'label' : $label);
        $o->save();
        \Core\ContainerSingleton::getEntityManager()->flush();
        return $o;
    }

    /**
     * Suppression d'un objet cree avec generateObject
     * @param Classif_Model_Context $o
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
class Classif_Test_ContextSetUp extends PHPUnit_Framework_TestCase
{

    /**
     * Function called once, before all the tests
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun Classif_Model_Context en base, sinon suppression !
        if (Classif_Model_Context::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Context restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Classif_Model_Context::loadList() as $context) {
                $context->delete();
            }
            \Core\ContainerSingleton::getEntityManager()->flush();
        }
    }

    /**
     * Test le constructeur
     * @return Classif_Model_Context
     */
    function testConstruct()
    {
        $o = new Classif_Model_Context();
        $this->assertInstanceOf('Classif_Model_Context', $o);
        $o->setRef('RefContextTest');
        $o->setLabel('LabelContextTest');
        $this->assertEquals(array(), $o->getKey());
        $o->save();
        \Core\ContainerSingleton::getEntityManager()->flush();
        $this->assertNotEquals(array(), $o->getKey());
        return $o;
    }

    /**
     * @depends testConstruct
     * @param Classif_Model_Context $o
     */
    function testLoad(Classif_Model_Context $o)
    {
         $oLoaded = Classif_Model_Context::load($o->getKey());
         $this->assertInstanceOf('Classif_Model_Context', $o);
         $this->assertEquals($oLoaded->getKey(), $o->getKey());
         $this->assertEquals($oLoaded->getRef(), $o->getRef());
         $this->assertEquals($oLoaded->getLabel(), $o->getLabel());
         return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param Classif_Model_Context $o
     */
    function testDelete(Classif_Model_Context $o)
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
        // Vérification qu'il ne reste aucun Classif_Model_Context en base, sinon suppression !
        if (Classif_Model_Context::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Context restants ont été trouvé après les tests, suppression en cours !';
            foreach (Classif_Model_Context::loadList() as $context) {
                $context->delete();
            }
            \Core\ContainerSingleton::getEntityManager()->flush();
        }
    }

}
