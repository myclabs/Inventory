<?php

namespace Tests\Classif;

use Classif_Model_Indicator;
use Core\Test\TestCase;
use Unit\UnitAPI;

class IndicatorTest extends TestCase
{
    /**
     * Generation de l'objet de test.
     * @param string $ref
     * @param string $label
     * @param UnitAPI $unit
     * @param UnitAPI $ratioUnit
     * @return Classif_Model_Indicator
     */
    public static function generateObject($ref = null, $label = null, UnitAPI $unit = null, UnitAPI $ratioUnit = null)
    {
        $o = new Classif_Model_Indicator();
        $o->setRef(($ref === null) ? 'ref' : $ref);
        $o->setLabel(($label === null) ? 'label' : $label);
        $o->setUnit(($unit === null) ? new UnitAPI('m') : $unit);
        $o->setRatioUnit(($ratioUnit === null) ? new UnitAPI('m') : $ratioUnit);
        $o->save();
        self::getEntityManager()->flush();
        return $o;
    }

    /**
     * Suppression d'un objet cree avec generateObject
     * @param Classif_Model_Indicator $o
     */
    public static function deleteObject($o)
    {
        $o->delete();
        self::getEntityManager()->flush();
    }

    public static function setUpBeforeClass()
    {
        if (Classif_Model_Indicator::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Indicator restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Classif_Model_Indicator::loadList() as $indicator) {
                $indicator->delete();
            }
            self::getEntityManager()->flush();
        }
    }

    public function testConstruct()
    {
        $unit = new UnitAPI('m');
        $ratioUnit = new UnitAPI('km');
        $o = new Classif_Model_Indicator();
        $o->setRef('RefContextTest');
        $o->setLabel('LabelIndicatorTest');
        $o->setUnit($unit);
        $o->setRatioUnit($ratioUnit);
        $this->assertEquals(array(), $o->getKey());
        $o->save();
        $this->entityManager->flush();
        $this->assertNotEquals(array(), $o->getKey());
        return $o;
    }

    /**
     * @depends testConstruct
     * @param Classif_Model_Indicator $o
     * @return static
     */
    public function testLoad(Classif_Model_Indicator $o)
    {
         $oLoaded = Classif_Model_Indicator::load($o->getKey());
         $this->assertInstanceOf(Classif_Model_Indicator::class, $o);
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
    public function testDelete(Classif_Model_Indicator $o)
    {
        $o->delete();
        $this->entityManager->flush();
        $this->assertEquals([], $o->getKey());
    }

    /**
     * Function called once, after all the tests
     */
    public static function tearDownAfterClass()
    {
        if (Classif_Model_Indicator::countTotal() > 0) {
            echo PHP_EOL . 'Des Classif_Indicator restants ont été trouvé après les tests, suppression en cours !';
            foreach (Classif_Model_Indicator::loadList() as $indicator) {
                $indicator->delete();
            }
            self::getEntityManager()->flush();
        }
    }
}
