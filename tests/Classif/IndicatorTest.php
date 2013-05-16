<?php
/**
 * Classe Classif_Test_IndicatorTest
 * @author     valentin.claras
 * @author     cyril.perraud
 * @package    Classif
 * @subpackage Test
 */

/**
 * Creation of the Test Suite
 * @package    Classif
 */
class Classif_Test_IndicatorTest
{
    /**
     * Creation of the test suite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Classif_Test_IndicatorSetUp');
        return $suite;
    }

    /**
     * Generation de l'objet de test.
     *
     * @param string $ref
     * @param string $label
     * @param Unit_API $unit
     * @param Unit_API $ratioUnit
     *
     * @return Classif_Model_Indicator
     */
    public static function generateObject($ref=null, $label=null, $unit=null, $ratioUnit=null)
    {
        $o = new Classif_Model_Indicator();
        $o->setRef(($ref === null) ? 'ref' : $ref);
        $o->setLabel(($label === null) ? 'label' : $label);
        $o->setUnit(($unit === null) ? $o->getRef().'unit' : $unit);
        $o->setRatioUnit(($ratioUnit === null) ? $o->getUnit() : $ratioUnit);
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        return $o;
    }

    /**
     * Suppression d'un objet cree avec generateObject
     * @param Classif_Model_Indicator $o
     */
    public static function deleteObject($o)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

}

/**
 * Test of the creation/modification/deletion of the entity
 * @package    Classif
 */
class Classif_Test_IndicatorSetUp extends PHPUnit_Framework_TestCase
{

    /**
     * Function called once, before all the tests
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun Classif_Model_Indicator en base, sinon suppression !
        if (Classif_Model_Indicator::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Indicator restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Classif_Model_Indicator::loadList() as $indicator) {
                $indicator->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }

    /**
     * Test le constructeur
     * @return Classif_Model_Indicator
     */
    function testConstruct()
    {
        $unit = new Unit_API('IndicatorSetUpTest');
        $ratioUnit = new Unit_API('RatioIndicatorSetUpTest');
        $o = new Classif_Model_Indicator();
        $this->assertInstanceOf('Classif_Model_Indicator', $o);
        $o->setRef('RefContextTest');
        $o->setLabel('LabelIndicatorTest');
        $o->setUnit($unit);
        $o->setRatioUnit($ratioUnit);
        $this->assertEquals(array(), $o->getKey());
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertNotEquals(array(), $o->getKey());
        return $o;
    }

    /**
     * @depends testConstruct
     * @param Classif_Model_Indicator $o
     */
    function testLoad(Classif_Model_Indicator $o)
    {
         $oLoaded = Classif_Model_Indicator::load($o->getKey());
         $this->assertInstanceOf('Classif_Model_Indicator', $o);
         $this->assertEquals($oLoaded->getKey(), $o->getKey());
         $this->assertEquals($oLoaded->getRef(), $o->getRef());
         $this->assertEquals($oLoaded->getLabel(), $o->getLabel());
         $this->assertEquals($oLoaded->getUnit(), $o->getUnit());
         $this->assertEquals($oLoaded->getRatioUnit(), $o->getRatioUnit());
         return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param Classif_Model_Indicator $o
     */
    function testDelete(Classif_Model_Indicator $o)
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
        // Vérification qu'il ne reste aucun Classif_Model_Indicator en base, sinon suppression !
        if (Classif_Model_Indicator::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Indicator restants ont été trouvé après les tests, suppression en cours !';
            foreach (Classif_Model_Indicator::loadList() as $indicator) {
                $indicator->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }

}