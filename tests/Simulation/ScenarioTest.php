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
//        $suite->addTestSuite('Simulation_Test_ScenarioOthers');
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

/**
 * Test des méthodes avancées de l'objet Simulation_Model_Simulation.
 * @package Simulation
 * @subpackage Test
 */
class SimulationMetierTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Simulation_Model_Simulation
     */
    protected $_simulation = null;

    /**
     * Méthode appelée avant l'exécution des tests
     */
    public static function setUpBeforeClass()
    {
        // Vide les tables
        Simulation_Model_DAO_Simulation::getInstance()->unitTestsClearTable();
        Simulation_Model_DAO_Set::getInstance()->unitTestsClearTable();

        // Activation du multiton.
        Zend_Registry::set('desactiverMultiton', false);
    }

    /**
     * Méthode appelée avant l'exécution des tests
     */
    protected function setUp()
    {
        $this->_simulation = new Simulation_Model_Simulation();
        $this->_simulation->save();
    }

    /**
     * Function testSetSetWithoutSaving
     *  Verifie que l'exception est générée si on attribut une set sans sauvegarder
     * @expectedException Core_Exception_UndefinedAttribute
     */
    function testSetSetWithoutSaving()
    {
        $simulation = new Simulation_Model_Simulation();
        $set = new Simulation_Model_Set();

        try {
            $simulation->setSet($set);
            $this->fail();

        } catch (Core_Exception_UndefinedAttribute $e) {
            SetTest::deleteObject($set);
            $message = 'Impossible de définir le Set, la Simulation doit avoir été sauvegardé en premier.';
            if ($e->getMessage() === $message) {
                throw $e;
            } else {
                 $this->fail();
            }
        }
    }

//     /**
//      * Function testSetGetSet
//      *  Test les méthodes set et get Set.
//      */
//     function testSetGetSet()
//     {
//         $set = SetTest::generateObject();
//         $simulation = new Simulation_Model_Simulation();
//         $simulation->setLabel('LabelSimulation');
//         $simulation->save();

//         $simulation->setSet($set);
//         $simulation->setLabel('LabelSimulation');
//         $this->assertSame($simulation->getSet(), $set);

//         $set->deleteSimulation($simulation);
//         $simulation->delete();

//         SetTest::deleteObject($set);
//     }

//     /**
//      * Function testSetSetAgain
//      *  Test la méthode set Set.
//      * @expectedException Core_Exception_Duplicate
//      */
//     function testSetSetAgain()
//     {
//         $set = SetTest::generateObject(1);
//         $this->_simulation->setSet($set);
//         try {
//             $setBis = SetTest::generateObject(3);
//             $this->_simulation->setSet($setBis);
//             $this->fail();
//         } catch (Core_Exception_Duplicate $e) {
//             SetTest::deleteObject($setBis);
//             $message = 'Impossible de redéfinir le Set, il a déjà été défini.';
//             if ($e->getMessage() === $message) {
//                 throw $e;
//             } else {
//                 $this->fail();
//             }
//         }
//     }

//     /**
//      * Function testdeleteWithSet
//      *  Test la fonction delete.
//      */
//     function testDeleteWithSet()
//     {
//         $simulation = SimulationTest::generateObject();
//         $set = SetTest::generateObject();

//         $simulation->setSet($set);
//         $this->assertTrue($set->hasSimulation($simulation));
//         $simulation->delete();
//         $set->delete();
//         $this->assertNull($simulation->getKey());
//         $this->assertFalse($set->hasSimulation($simulation));
//         SetTest::deleteObject($set);
//     }

//     /**
//      * Function testSetGetPrimarySet
//      *  Test les méthodes set et get PrimarySet.
//      */
//     function testSetGetPrimarySet()
//     {
//         $root = new AF_Model_Form_Group();
//         $root->setRef('refRootSim');
//         $root->setLabel('labelRootSim');
//         $root->setKey('14');

//         $value = new Classif_Model_Version();
//         $value->setRef('ClassifRef');
//         $value->setLabel('LabelValue');
//         $value->setCreationDate('DateValue');
//         $value->setKey('69');

//         $context = new Classif_Model_Context();
//         $context->setRef('refContextSim');
//         $context->setLabel('labelcontextSim');
//         $context->setVersion($value);
//         $context->setKey('56');

//         $branch = new AF_Model_Branch();
//         $branch->setRef('RefBranch');
//         $branch->setLabel('LabelBranch');
//         $branch->setClassifVersion($value);
//         $branch->setCreationDate('DateBranch');
//         $branch->setKey('58');

//         $version = new AF_Model_Version();
//         $version->setRefTechnoDB('RefVersion');
//         $version->setLabel('LabelVersion');
//         $version->updateRef('RefVersion');
//         $version->setBranch($branch);
//         $version->setCreationDate();
//         $version->setKey('51');

//         $af = new AF_Model_AF();
//         $af->setRef('RefAfSim');
//         $af->setIdContext($context);
//         $af->setIdRoot($root);
//         $af->setIdVersion($version);
//         $af->setKey('685');

//         $set = SetTest::generateObject();
//         $this->_simulation->setSet($set);
//         $this->_simulation->save();
//         // Le Set est fait automatisquement par l'ETLData qui observe le PrimarySet.
//         $aFInputSetPrimary = new AF_Model_Input_PrimarySet();
//         $aFInputSetPrimary->setObservationParameters(array('providerClassName' => 'Simulation_Model_Simulation',
//             'providerClassKey' => $this->_simulation->getKey()));
//         $aFInputSetPrimary->setAf($af);
//         $aFInputSetPrimary->save();
//         $this->assertSame($this->_simulation->getAFInputSetPrimary(), $aFInputSetPrimary);

//         $aFInputSetPrimary->delete();
//         $af->delete();
//         $context->delete();
//         $root->delete();
//         $value->delete();
//         $version->delete();
//     }

//     /**
//      * Function testSetPrimarySetAgain
//      *  Test la méthode set PrimarySet.
//      * @expectedException Core_Exception_Duplicate
//      */
//     function testSetPrimarySetAgain()
//     {
//         $root = new AF_Model_Form_Group();
//         $root->setRef('refRootSim');
//         $root->setLabel('labelRootSim');
//         $root->setKey('14');

//         $value = new Classif_Model_Version();
//         $value->setRef('ClassifRef');
//         $value->setLabel('LabelValue');
//         $value->setCreationDate('DateValue');
//         $value->setKey('69');

//         $context = new Classif_Model_Context();
//         $context->setRef('refContextSim');
//         $context->setLabel('labelcontextSim');
//         $context->setVersion($value);
//         $context->setKey('56');

//         $branch = new AF_Model_Branch();
//         $branch->setRef('RefBranch');
//         $branch->setLabel('LabelBranch');
//         $branch->setClassifVersion($value);
//         $branch->setCreationDate('DateBranch');
//         $branch->setKey('58');

//         $version = new AF_Model_Version();
//         $version->setRefTechnoDB('RefVersion');
//         $version->setLabel('LabelVersion');
//         $version->updateRef('RefVersion');
//         $version->setBranch($branch);
//         $version->setCreationDate();
//         $version->setKey('51');

//         $af = new AF_Model_AF();
//         $af->setRef('RefAfSim');
//         $af->setIdContext($context);
//         $af->setIdRoot($root);
//         $af->setIdVersion($version);
//         $af->setKey('685');

//         $set = SetTest::generateObject();
//         $this->_simulation->setSet($set);
//         $aFInputSetPrimary = new AF_Model_Input_PrimarySet();
//         $aFInputSetPrimary->setObservationParameters(array('providerClassName' => 'Simulation_Model_Simulation',
//             'providerClassKey' => $this->_simulation->getKey()));
//         $aFInputSetPrimary->setAf($af);
//         $aFInputSetPrimary->save();
//         $this->_simulation->setAFInputSetPrimary($aFInputSetPrimary);
//         $this->_simulation->save();

//         try {
//             $root = new AF_Model_Form_Group();
//             $root->setRef('refRootSim');
//             $root->setLabel('labelRootSim');
//             $root->setKey('14');

//             $value = new Classif_Model_Version();
//             $value->setRef('ClassifRef');
//             $value->setLabel('LabelValue');
//             $value->setCreationDate('DateValue');
//             $value->setKey('69');

//             $context = new Classif_Model_Context();
//             $context->setRef('refContextSim');
//             $context->setLabel('labelcontextSim');
//             $context->setVersion($value);
//             $context->setKey('56');

//             $branch = new AF_Model_Branch();
//             $branch->setRef('RefBranch');
//             $branch->setLabel('LabelBranch');
//             $branch->setClassifVersion($value);
//             $branch->setCreationDate('DateBranch');
//             $branch->setKey('58');

//             $version = new AF_Model_Version();
//             $version->setRefTechnoDB('RefVersion');
//             $version->setLabel('LabelVersion');
//             $version->updateRef('RefVersion');
//             $version->setBranch($branch);
//             $version->setCreationDate();
//             $version->setKey('51');

//             $afbis = new AF_Model_AF();
//             $afbis->setRef('RefAfSim');
//             $afbis->setIdContext($context);
//             $afbis->setIdRoot($root);
//             $afbis->setIdVersion($version);
//             $afbis->setKey('685');

//             $aFInputSetPrimaryBis = new AF_Model_Input_PrimarySet();
//             $aFInputSetPrimaryBis->setObservationParameters(array('providerClassName' => 'Simulation_Model_Simulation',
//                 'providerClassKey' => $this->_simulation->getKey()));
//             $aFInputSetPrimaryBis->setAf($afbis);
//             $aFInputSetPrimaryBis->save();
//             $this->_simulation->setAFInputSetPrimary($aFInputSetPrimaryBis);
//             $this->fail();
//         } catch (Core_Exception_Duplicate $e) {
//             $aFInputSetPrimaryBis->delete();
//             $aFInputSetPrimary->delete();
//             $message = 'Impossible de redéfinir le PrimarySet, il a déjà été défini.';
//             if ($e->getMessage() === $message) {
//                 throw $e;
//             } else {
//                 $this->fail();
//             }
//         }
//     }

    /**
     * Fct testAddAFGranularities
     *  test l'ajout d'une granularité
     */
    function testAddAFGranularities()
    {
        $structure = new Orga_Model_Structure();
        $structure->setLabel('labelstructure');
        $structure->save();

        $orgaGranularity = new Orga_Model_Granularity();
        $orgaGranularity->setStructure($structure);
        $orgaGranularity->save();

        $aFGranularities = new Simulation_Model_AFGranularities();
        $aFGranularities->setAFConfigGranularity($orgaGranularity);
        $aFGranularities->setAFInputGranularity($orgaGranularity);
        $aFGranularities->save();

        $aFGranularities->delete();
        $structure->delete();
    }

    /**
     * Test la méthode set PrimarySet.
     * @expectedException Core_Exception_UndefinedAttribute
     */
    function testGetPrimarySetUndefined()
    {
        try {
            $aFInputSetPrimary = $this->_simulation->getAFInputSetPrimary();
            $this->fail();
        } catch (Core_Exception_UndefinedAttribute $e) {
            $message = 'Le PrimarySet n\'a pas été défini.';
            if ($e->getMessage() === $message) {
                throw $e;
            } else {
                $this->fail();
            }
        }
    }

//     /**
//      * Test le chargement en fonction du PrimarySet.
//      * @return Simulation_Model_Simulation
//      */
//     function testLoadByIdAFInputSetPrimary()
//     {
//         $root = new AF_Model_Form_Group();
//         $root->setRef('refRootSim');
//         $root->setLabel('LabelRootSim');
//         $root->setKey('14');

//         $value = new Classif_Model_Version();
//         $value->setRef('ClassifRef');
//         $value->setLabel('LabelValue');
//         $value->setCreationDate('DateValue');
//         $value->setKey('69');

//         $context = new Classif_Model_Context();
//         $context->setRef('refContextSim');
//         $context->setLabel('labelcontextSim');
//         $context->setVersion($value);
//         $context->setKey('56');

//         $branch = new AF_Model_Branch();
//         $branch->setRef('RefBranch');
//         $branch->setLabel('LabelBranch');
//         $branch->setClassifVersion($value);
//         $branch->setCreationDate('DateBranch');
//         $branch->setKey('58');

//         $version = new AF_Model_Version();
//         $version->setRefTechnoDB('RefVersion');
//         $version->setLabel('LabelVersion');
//         $version->updateRef('RefVersion');
//         $version->setBranch($branch);
//         $version->setCreationDate();
//         $version->setKey('51');

//         $af = new AF_Model_AF();
//         $af->setRef('RefAfSim');
//         $af->setIdContext($context);
//         $af->setIdRoot($root);
//         $af->setIdVersion($version);
//         $af->setKey('685');

//         $set = SetTest::generateObject();
//         $this->_simulation->setSet($set);
//         $aFInputSetPrimary = new AF_Model_Input_PrimarySet();
//         $aFInputSetPrimary->setObservationParameters(array('providerClassName' => 'Simulation_Model_Simulation',
//             'providerClassKey' => $this->_simulation->getKey()));
//         $aFInputSetPrimary->setAf($af);
//         $aFInputSetPrimary->save();
//         $this->_simulation->save();
//         $simulationLoaded = Simulation_Model_Simulation::loadByIdAFInputSetPrimary($aFInputSetPrimary->getKey());
//         $this->assertEquals($this->_simulation, $simulationLoaded);
//         $aFInputSetPrimary->delete();
//     }

    /**
     * Function testGetTechnoDBVersionUndefined
     *  Test la méthode set Version de TechnoDB.
     * @expectedException Core_Exception_UndefinedAttribute
     */
    function testGetTechnoDBVersionUndefined()
    {
        try {
            $technoDBVersion = $this->_simulation->getTechnoDBVersion();
            $this->fail();
        } catch (Core_Exception_UndefinedAttribute $e) {
            $message = 'La Version de TechnoDB n\'a pas été défini.';
            if ($e->getMessage() === $message) {
                throw $e;
            } else {
                $this->fail();
            }
        }
    }

//     /**
//      * Function testSetgetTechnoDBVersion
//      *  Test les méthodes set et get Version de TechnoDB.
//      */
//     function testSetGetTechnoDBVersion()
//     {
//         $set = SetTest::generateObject();
//         $composedRefTechnoDB = explode('#', $set->getAF()->getVersion()->getRefTechnoDB());
//         $technoDBBranch = TechnoDB_Model_Branch::loadByRef($composedRefTechnoDB[0]);

//         $technoDBVersion = new TechnoDB_Model_Version('test');
//         $technoDBVersion->save();

//         $technoDBVersion->setBranch($technoDBBranch);
//         $technoDBVersion->save();

//         $this->_simulation->setSet($set);
//         $this->_simulation->setTechnoDBVersion($technoDBVersion);
//         $this->_simulation->save();

//         $this->assertEquals($this->_simulation->getTechnoDBVersion(), $technoDBVersion);
//     }

    /**
     * testSetGetLabel
     *  Test les méthodes set et get Ref.
     */
    function testSetGetLabel()
    {
        $this->_simulation->setLabel('testSimulation');
        $this->_simulation->save();
        $this->assertEquals($this->_simulation->getLabel(), 'testSimulation');
    }

    /**
     * Test la méthode set Ref.
     * @expectedException Core_Exception_InvalidArgument
     */
    function testSetNotAStringLabel()
    {
        try {
            $this->_simulation->setLabel(0);
        } catch (Core_Exception_InvalidArgument $e) {
            $message = 'Le label d\'une Simulation doit être une chaîne. #captainObvious';
            if ($e->getMessage() === $message) {
                throw $e;
            } else {
                $this->fail();
            }
        }
    }

    /**
     * Méthode appelée à la fin des test
     */
    protected function tearDown()
    {
        try {
//             AF_Model_Form_Group::loadByRef('RefRootSim')->delete();
//             Classif_Model_Context::loadByRef('RefContext')->delete();
//             AF_Model_AF::loadByRef('RefAf')->delete();
//             AF_Model_Branch::loadByRef('RefBranch')->delete();
            $set = $this->_simulation->getSet();
            SetTest::deleteObject($set);
            SimulationTest::deleteObject($this->_simulation);

        } catch (Core_Exception_UndefinedAttribute $e) {
            SimulationTest::deleteObject($this->_simulation);
        }
    }

    /**
     * Méthode appelée à la fin des test
     */
    public static function tearDownAfterClass()
    {
        // (Re)Désactivation du multiton.
        Zend_Registry::set('desactiverMultiton', false);

        // Vérifie que les tables sont vides
        if (!Simulation_Model_DAO_Simulation::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Simulation n'est pas vide après les tests\n";
        }
        if (!Simulation_Model_DAO_Set::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Set n'est pas vide après les tests\n";
        }
    }
}