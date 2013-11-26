<?php
use User\Domain\User;

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
        return $suite;
    }

    /**
     * Génere un objet pret à l'emploi pour les tests.
     * @param int $i
     * @param AF_Model_AF $aF
     * @param User $user
     * @return Simulation_Model_Set
     */
    public static function generateObject($i=0, $aF=null, $user=null)
    {
        if ($aF === null) {
            $aF = new AF_Model_AF('af_set'.$i);
            $aF->save();
        }

        if ($user === null) {
            $user = new User();
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
     * @var User
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

        $user = new User();
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
