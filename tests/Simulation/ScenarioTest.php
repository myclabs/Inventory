<?php
/**
 * @package Simulation
 * @subpackage Tests
 */

/**
 * Classe de test de la classe Simulation du modèle.
 * @author valentin.claras
 * @package Simulation
 * @subpackage Test
 */
class Simulation_Test_ScenarioTest
{
    /**
     * Déclaration de la suite de test à éffectuer.
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Simulation_Test_ScenarioSetUp');
        return $suite;
    }

    /**
     * Génere un objet pret à l'emploi pour les tests.
     * @param string label
     * @param AF_Model_InputSet_Primary $aFInputSetPrimary
     * @param Simulation_Model_Set $set
     * @return Simulation_Model_Simulation
     */
    public static function generateObject($label, $aFInputSetPrimary=null, $set=null)
    {
        if ($set === null) {
            $set = Simulation_Test_SetTest::generateObject();
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
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();

        return $scenario;
    }

    /**
     * Supprime un objet de test généré avec generateObject().
     * @param Simulation_Model_Scenario $scenario
     * @param bool $deleteAFInputSetPrimary
     * @param bool $deleteSet
     */
    public static function deleteObject(Simulation_Model_Scenario $scenario, $deleteAFInputSetPrimary=true, $deleteSet=true)
    {
        if ($deleteAFInputSetPrimary) {
            $aFInputSetPrimary = $scenario->getAFInputSetPrimary();
        }
        if ($deleteSet) {
            $set = $scenario->getSet();
        }

        // Suppression de l'objet.
        $scenario->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();

        if ($deleteAFInputSetPrimary) {
            $aFInputSetPrimary->delete();
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        if ($deleteSet) {
            Simulation_Test_SetTest::deleteObject($set);
        }
    }
}

/**
 * Test des méthodes de base de l'objet Simulation_Model_Simulation.
 * @package Simulation
 * @subpackage Test
 */
class Simulation_Test_ScenarioSetUp extends PHPUnit_Framework_TestCase
{
    /**
     * Méthode appelée avant l'exécution des tests
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun Simulation_Model_Scenario en base, sinon suppression !
        if (Simulation_Model_Scenario::countTotal() > 0) {
            echo PHP_EOL . 'Des Simulation_Scenario restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Simulation_Model_Scenario::loadList() as $scenario) {
                $scenario->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun Simulation_Model_Set en base, sinon suppression !
        if (Simulation_Model_Set::countTotal() > 0) {
            echo PHP_EOL . 'Des Simulation_Set restants ont été trouvé avant les tests, suppression en cours !';
            foreach (Simulation_Model_Set::loadList() as $set) {
                $set->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }

    /**
     * Méthode appelée avant l'exécution des tests
     */
    protected function setUp()
    {
    }

    /**
     * Test le constructeur.
     * @return Simulation_Model_Simulation
     */
    function testConstruct()
    {
        $set = Simulation_Test_SetTest::generateObject();
        $aFInputSetPrimary = new AF_Model_InputSet_Primary($set->getAF());
        $aFInputSetPrimary->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();

        $o = new Simulation_Model_Scenario();
        $o->setSet($set);
        $o->setAFInputSetPrimary($aFInputSetPrimary);
        $o->save();
        $this->assertInstanceOf('Simulation_Model_Scenario', $o);
        $this->assertEquals($o->getKey(), array());
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertNotEquals(array(), $o->getKey());

        return $o;
    }

    /**
     * Test le chargement.
     * @depends testConstruct
     * @param Simulation_Model_Scenario $o
     * @return Simulation_Model_Scenario
     */
    function testLoad($o)
    {
        $oLoaded = Simulation_Model_Scenario::load($o->getKey());
        $this->assertInstanceOf('Simulation_Model_Scenario', $o);
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
    function testDelete($o)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertEquals(array(), $o->getKey());
        $o->getAFInputSetPrimary()->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        Simulation_Test_SetTest::deleteObject($o->getSet());
    }

    /**
     * Méthode appelée à la fin des test
     */
    protected function tearDown()
    {
    }

    /**
     * Méthode appelée à la fin des test
     */
    public static function tearDownAfterClass()
    {
        // Vérification qu'il ne reste aucun Simulation_Model_Scenario en base, sinon suppression !
        if (Simulation_Model_Scenario::countTotal() > 0) {
            echo PHP_EOL . 'Des Simulation_Scenario restants ont été trouvé après les tests, suppression en cours !';
            foreach (Simulation_Model_Scenario::loadList() as $scenario) {
                $scenario->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
        // Vérification qu'il ne reste aucun Simulation_Model_Set en base, sinon suppression !
        if (Simulation_Model_Set::countTotal() > 0) {
            echo PHP_EOL . 'Des Simulation_Set restants ont été trouvé après les tests, suppression en cours !';
            foreach (Simulation_Model_Set::loadList() as $set) {
                $set->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }
}
