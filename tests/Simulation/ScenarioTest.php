<?php

namespace Tests\Simulation;

use AF_Model_InputSet_Primary;
use Core\Test\TestCase;
use Simulation_Model_Scenario;
use Simulation_Model_Set;

class ScenarioTest extends TestCase
{
    /**
     * Génere un objet pret à l'emploi pour les tests.
     * @param string $label
     * @param AF_Model_InputSet_Primary $aFInputSetPrimary
     * @param Simulation_Model_Set $set
     * @return Simulation_Model_Scenario
     */
    public static function generateObject($label, $aFInputSetPrimary = null, $set = null)
    {
        if ($set === null) {
            $set = SetTest::generateObject();
        }
        if ($aFInputSetPrimary === null) {
            $aFInputSetPrimary = new AF_Model_InputSet_Primary($set->getAF());
            $aFInputSetPrimary->save();
        }

        // Création d'un nouvel objet.
        $scenario = new Simulation_Model_Scenario();
        $scenario->setLabel($label);
        $scenario->setSet($set);
        $scenario->setAFInputSetPrimary($aFInputSetPrimary);
        $scenario->save();
        self::getEntityManager()->flush();

        return $scenario;
    }

    /**
     * Supprime un objet de test généré avec generateObject().
     * @param Simulation_Model_Scenario $scenario
     * @param bool $deleteAFInputSetPrimary
     * @param bool $deleteSet
     */
    public static function deleteObject(
        Simulation_Model_Scenario $scenario,
        $deleteAFInputSetPrimary = true,
        $deleteSet = true
    ) {
        if ($deleteAFInputSetPrimary) {
            $aFInputSetPrimary = $scenario->getAFInputSetPrimary();
        }
        if ($deleteSet) {
            $set = $scenario->getSet();
        }

        // Suppression de l'objet.
        $scenario->delete();
        self::getEntityManager()->flush();

        if ($deleteAFInputSetPrimary) {
            $aFInputSetPrimary->delete();
            self::getEntityManager()->flush();
        }
        if ($deleteSet) {
            SetTest::deleteObject($set);
        }
    }

    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun Simulation_Model_Scenario en base, sinon suppression !
        if (Simulation_Model_Scenario::countTotal() > 0) {
            echo PHP_EOL . 'Des Simulation_Scenario restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Simulation_Model_Scenario::loadList() as $scenario) {
                $scenario->delete();
            }
            self::getEntityManager()->flush();
        }
        // Vérification qu'il ne reste aucun Simulation_Model_Set en base, sinon suppression !
        if (Simulation_Model_Set::countTotal() > 0) {
            echo PHP_EOL . 'Des Simulation_Set restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Simulation_Model_Set::loadList() as $set) {
                $set->delete();
            }
            self::getEntityManager()->flush();
        }
    }

    public function testConstruct()
    {
        $set = SetTest::generateObject();
        $aFInputSetPrimary = new AF_Model_InputSet_Primary($set->getAF());
        $aFInputSetPrimary->save();
        self::getEntityManager()->flush();

        $o = new Simulation_Model_Scenario();
        $o->setSet($set);
        $o->setAFInputSetPrimary($aFInputSetPrimary);
        $o->save();
        $this->assertInstanceOf(Simulation_Model_Scenario::class, $o);
        $this->assertEquals($o->getKey(), array());
        self::getEntityManager()->flush();
        $this->assertNotEquals(array(), $o->getKey());

        return $o;
    }

    /**
     * Test le chargement.
     * @depends testConstruct
     * @param Simulation_Model_Scenario $o
     * @return Simulation_Model_Scenario
     */
    public function testLoad($o)
    {
        $oLoaded = Simulation_Model_Scenario::load($o->getKey());
        $this->assertInstanceOf(Simulation_Model_Scenario::class, $o);
        $this->assertEquals($oLoaded->getKey(), $o->getKey());
        $this->assertSame($oLoaded->getLabel(), $o->getLabel());
        $this->assertSame($oLoaded->getSet(), $o->getSet());
        $this->assertSame($oLoaded->getAFInputSetPrimary(), $o->getAFInputSetPrimary());
        return $oLoaded;
    }

    /**
     * Test la suppression.
     * @depends testLoad
     * @param Simulation_Model_Scenario $o
     */
    public function testDelete($o)
    {
        $o->delete();
        self::getEntityManager()->flush();
        $this->assertEquals(array(), $o->getKey());
        $o->getAFInputSetPrimary()->delete();
        self::getEntityManager()->flush();
        SetTest::deleteObject($o->getSet());
    }

    public static function tearDownAfterClass()
    {
        // Vérification qu'il ne reste aucun Simulation_Model_Scenario en base, sinon suppression !
        if (Simulation_Model_Scenario::countTotal() > 0) {
            echo PHP_EOL . 'Des Simulation_Scenario restants ont été trouvé après les tests, suppression en cours !';
            foreach (Simulation_Model_Scenario::loadList() as $scenario) {
                $scenario->delete();
            }
            self::getEntityManager()->flush();
        }
        // Vérification qu'il ne reste aucun Simulation_Model_Set en base, sinon suppression !
        if (Simulation_Model_Set::countTotal() > 0) {
            echo PHP_EOL . 'Des Simulation_Set restants ont été trouvé après les tests, suppression en cours !';
            foreach (Simulation_Model_Set::loadList() as $set) {
                $set->delete();
            }
            self::getEntityManager()->flush();
        }
    }
}
