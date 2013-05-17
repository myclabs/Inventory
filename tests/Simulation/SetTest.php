<?php
/**
 * @package Simulation
 * @subpackage Tests
 */

/**
 * Classe de test de la classe Set du modèle.
 * @author valentin.claras
 * @package Simulation
 * @subpackage Test
 */
class Simulation_Test_SetTest
{
    /**
     * Déclaration de la suite de test à éffectuer.
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Simulation_Test_SetSetUp');
//        $suite->addTestSuite('Simulation_Test_SetOthers');
        return $suite;
    }

    /**
     * Génere un objet pret à l'emploi pour les tests.
     * @param int $i
     * @param AF_Model_AF $aF
     * @param User_Model_User $user
     * @return Simulation_Model_Set
     */
    public static function generateObject($i=0, $aF=null, $user=null)
    {
        if ($aF === null) {
            $aF = new AF_Model_AF('af_set'.$i);
            $aF->save();
        }

        if ($user === null) {
            $user = new User_Model_User();
            $user->setEmail('courriel@simulation.set'.$i);
            $user->setPassword('test');
            $user->save();
        }
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();

        // Création d'un nouvel objet.
        $set = new Simulation_Model_Set();
        $set->setLabel('Set '.$i);
        $set->setAF($aF);
        $set->setUser($user);
        $set->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();

        return $set;
    }

    /**
     * Supprime un objet de test généré avec generateObject().
     * @param Simulation_Model_Set &$set
     * @param bool $deleteAF
     * @param bool $deleteUser
     */
    public static function deleteObject(Simulation_Model_Set $set, $deleteAF=true, $deleteUser=true)
    {
        if ($deleteAF) {
            $aF = $set->getAF();
        }
        if ($deleteUser) {
            $user = $set->getUser();
        }

        // Suppression de l'objet.
        $set->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();

        if ($deleteAF) {
            $aF->delete();
        }
        if ($deleteUser) {
            $user->delete();
        }
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }
}

/**
 * Test des méthodes de base de l'objet Simulation_Model_Set.
 * @package Simulation
 * @subpackage Test
 */
class Simulation_Test_SetSetUp extends PHPUnit_Framework_TestCase
{

    /**
     * @var AF_Model_AF
     */
    protected $_aF = null;

    /**
     * @var User_Model_User
     */
    protected $_user = null;


    /**
     * Méthode appelée avant l'exécution des tests
     */
    public static function setUpBeforeClass()
    {
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
     * Test le constructeur.
     * @return Simulation_Model_Set
     */
    function testConstruct()
    {
        $aF = new AF_Model_AF('test');
        $aF->save();

        $user = new User_Model_User();
        $user->setEmail('courriel@simulation.set');
        $user->setPassword('test');
        $user->save();

        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();

        $o = new Simulation_Model_Set();
        $o->setUser($user);
        $o->setAF($aF);
        $o->save();
        $this->assertInstanceOf('Simulation_Model_Set', $o);
        $this->assertEquals($o->getKey(), array());
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertNotEquals(array(), $o->getKey());

        return $o;
    }

    /**
     * Test le chargement.
     * @depends testConstruct
     * @param Simulation_Model_Set $o
     * @return Simulation_Model_Set
     */
    function testLoad($o)
    {
        $oLoaded = Simulation_Model_Set::load($o->getKey());
        $this->assertInstanceOf('Simulation_Model_Set', $o);
        $this->assertEquals($oLoaded->getKey(), $o->getKey());
        $this->assertSame($oLoaded->getUser(), $o->getUser());
        $this->assertSame($oLoaded->getAF(), $o->getAF());
        $this->assertSame($oLoaded->getDWAxis(), $o->getDWAxis());
        return $oLoaded;
    }

    /**
     * Test la suppression.
     * @depends testLoad
     * @param Simulation_Model_Set $o
     */
    function testDelete($o)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->assertEquals(array(), $o->getKey());
        $o->getAF()->delete();
        $o->getUser()->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

    /**
     * Méthode appelée à la fin des test
     */
    public static function tearDownAfterClass()
    {
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
 * Test des méthodes avancées de l'objet Simulation_Model_Set.
 * @package Simulation
 * @subpackage Test
 */
class SetMetierTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Simulation_Model_Set
     */
    protected $_set = null;

    /**
     * Méthode appelée avant l'exécution des tests
     */
    public static function setUpBeforeClass()
    {
        // Vide les tables
        Simulation_Model_DAO_Simulation::getInstance()->unitTestsClearTable();
        Simulation_Model_DAO_Set::getInstance()->unitTestsClearTable();
    }

    /**
     * Méthode appelée avant l'exécution des tests
     */
    protected function setUp()
    {
        $this->_set = SetTest::generateObject();
    }


    /**
     * Test la méthode getAF.
     * @expectedException Core_Exception_UndefinedAttribute
     */
    function testGetAFWithoutSetting()
    {
        try {
            $set = new Simulation_Model_Set();
            $set->getAF();
            $this->fail();
        } catch (Core_Exception_Duplicate $e) {
            $message = 'L\'AF n\'a pas été défini.';
            if ($e->getMessage() === $message) {
                throw $e;
            } else {
                $this->fail();
            }
        }
    }

    /**
     * Function testgetsetAF
     */
    function testgetsetAF()
    {
        $set = new Simulation_Model_Set();
        $set->setAF($this->_set->getAF());
        $this->assertSame($this->_set->getAF(), $set->getAF());
    }

    /**
     * Test la méthode set AF.
     * @expectedException Core_Exception_Duplicate
     */
    function testSetAFAgain()
    {
        try {
            $aF = new AF_Model_AF();
            $aF->setKey(10);
            $this->_set->setAF($aF);
            $this->fail();
        } catch (Core_Exception_Duplicate $e) {
            $message = 'Impossible de redéfinir l\'AF, il a déjà été défini.';
            if ($e->getMessage() === $message) {
                throw $e;
            } else {
                $this->fail();
            }
        }
    }

    /**
     * Test la méthode getDWCube.
     * @expectedException Core_Exception_UndefinedAttribute
     */
    function testGetUserWithoutSetting()
    {
        try {
            $set = new Simulation_Model_Set();
            $set->getUser();
            $this->fail();
        } catch (Core_Exception_Duplicate $e) {
            $message = 'Le User n\'a pas été défini.';
            if ($e->getMessage() === $message) {
                throw $e;
            } else {
                $this->fail();
            }
        }
    }

    /**
     * Test la méthode set User.
     * @expectedException Core_Exception_Duplicate
     */
    function testSetUserAgain()
    {
        try {
            $user = new User_Model_User();
            $user->setKey(10);
            $this->_set->setUser($user);
            $this->fail();
        } catch (Core_Exception_Duplicate $e) {
            $message = 'Impossible de redéfinir le User, il a déjà été défini.';
            if ($e->getMessage() === $message) {
                throw $e;
            } else {
                $this->fail();
            }
        }
    }

    /**
     * Test la méthode getDWCube.
     * @expectedException Core_Exception_UndefinedAttribute
     */
    function testGetCubeWithoutSaving()
    {
        try {
            $set = new Simulation_Model_Set();
            $set->getDWCube();
            $this->fail();
        } catch (Core_Exception_Duplicate $e) {
            $message = 'Le Cube de DW n\'a pas encore été créé, il faut sauvegarder le Set avant.';
            if ($e->getMessage() === $message) {
                throw $e;
            } else {
                $this->fail();
            }
        }
    }

    /**
     * Test la méthode getDWCube.
     */
    function testGetCube()
    {
        $this->assertTrue($this->_set->getDWCube() instanceof DW_Model_Cube);
    }

    /**
     * Test les méthodes set et get Label.
     *
     */
    function testSetGetLabel()
    {
        $this->_set->setLabel('testSet');
        $this->_set->save();
        $this->assertEquals($this->_set->getLabel(), 'testSet');
    }

    /**
     * Test la méthode setLabel.
     * @expectedException Core_Exception_InvalidArgument
     */
    function testSetNotAStringLabel()
    {
        try {
            $this->_set->setLabel(0);
            $this->fail();
        } catch (Core_Exception_InvalidArgument $e) {
            $message = 'Le label d\'un Simulation Set doit être une chaîne. #captainObvious';
            if ($e->getMessage() === $message) {
                throw $e;
            } else {
                $this->fail();
            }
        }
    }

    /**
     * Fonction testgetSimulations
     *  en fonctionnement normal
     */
    function testgetSimulations()
    {
        $this->assertNotNull($this->_set->getSimulations());
    }

    /**
     * Test la méthode addSimulation.
     * @expectedException Core_Exception_UndefinedAttribute
     */
    function testAddSimulationNotSaved()
    {
        try {
            $set = new Simulation_Model_Set();
            $set->addSimulation(new Simulation_Model_Simulation());
            $this->fail();
        } catch (Core_Exception_UndefinedAttribute $e) {
            $message = 'Impossible d\'ajouter une simulation à un jeu de simulations non sauvegardé.';
            if ($e->getMessage() === $message) {
                throw $e;
            } else {
                $this->fail();
            }
        }
    }

    /**
     * Test l'ajout et la suppression d'une simulation.
     */
    function testAddDeleteSimulationWithoutSaving()
    {
        $simulation = SimulationTest::generateObject();
        // Passage à l'état ADDED.
        $this->_set->addSimulation($simulation);
        $this->assertTrue($this->_set->hasSimulation($simulation));
        // Effacement de la simulation du tableau car jamais sauvegardée.
        $this->_set->deleteSimulation($simulation);
        $this->assertFalse($this->_set->hasSimulation($simulation));
        SimulationTest::deleteObject($simulation);
    }

    /**
     * Test l'ajout et la suppression d'une simulation.
     */
    function testAddDeleteSimulationWithSaving()
    {
        // Activation du multiton pour profiter de l'ajout dans les deux objets.
        Zend_Registry::set('desactiverMultiton', false);
        $simulation = SimulationTest::generateObject();
        $set = SetTest::generateObject(1);
        // Passage à l'état ADDED.
        $set->addSimulation($simulation);
        // Passage à l'état LOADED.
        $set->save();
        $this->assertTrue($set->hasSimulation($simulation));
        // Passage à l'état DELETED.
        $set->deleteSimulation($simulation);
        $this->assertFalse($set->hasSimulation($simulation));
        // Passage à l'état LOADED.
        $set->addSimulation($simulation);
        $this->assertTrue($set->hasSimulation($simulation));
        // Désactivation du multiton pour effectuer un chargment complet et indépendant.
        Zend_Registry::set('desactiverMultiton', true);
        $setLoaded = Simulation_Model_Set::load($set->getKey());

        // Réactivation du multiton pour la fin du test.
        Zend_Registry::set('desactiverMultiton', false);
        $this->assertTrue($setLoaded->hasSimulation($simulation));
        $setLoaded->deleteSimulation($simulation);
        $this->assertFalse($setLoaded->hasSimulation($simulation));
        $setLoaded->save();
        $this->assertFalse($setLoaded->hasSimulation($simulation));

        // Ajout d'une nouvelle simulation, et vérification de sa suppression lors de la suppression du Set.
        $newSimulation = new Simulation_Model_Simulation();
        $newSimulation->save();
        $setLoaded->addSimulation($newSimulation);
        $setLoaded->save();
        SetTest::deleteObject($setLoaded);
        $this->assertNull($newSimulation->getKey());
        // (Re)Désactivation du multiton.
        Zend_Registry::set('desactiverMultiton', true);
    }

    /**
     * Méthode appelée à la fin des test
     */
    protected function tearDown()
    {
        SetTest::deleteObject($this->_set);
    }

    /**
     * Méthode appelée à la fin des test
     */
    public static function tearDownAfterClass()
    {
        // Vérifie que les tables sont vides
        if (!Simulation_Model_DAO_Simulation::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Simulation n'est pas vide après les tests\n";
        }
        if (!Simulation_Model_DAO_Set::getInstance()->unitTestsIsTableEmpty()) {
            echo "\nLa table Set n'est pas vide après les tests\n";
        }
    }
}