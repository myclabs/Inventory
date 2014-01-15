<?php

namespace Tests\Simulation;

use AF_Model_AF;
use Core\Test\TestCase;
use Simulation_Model_Set;
use User\Domain\User;

class SetTest extends TestCase
{
    /**
     * Génere un objet pret à l'emploi pour les tests.
     * @param int $i
     * @param AF_Model_AF $aF
     * @param User $user
     * @return Simulation_Model_Set
     */
    public static function generateObject($i = 0, $aF = null, $user = null)
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
        self::getEntityManager()->flush();

        // Création d'un nouvel objet.
        $set = new Simulation_Model_Set();
        $set->setLabel('Set '.$i);
        $set->setAF($aF);
        $set->setUser($user);
        $set->save();
        self::getEntityManager()->flush();

        return $set;
    }

    /**
     * Supprime un objet de test généré avec generateObject().
     * @param Simulation_Model_Set &$set
     * @param bool $deleteAF
     * @param bool $deleteUser
     */
    public static function deleteObject(Simulation_Model_Set $set, $deleteAF = true, $deleteUser = true)
    {
        if ($deleteAF) {
            $af = $set->getAF();
        }
        if ($deleteUser) {
            $user = $set->getUser();
        }

        // Suppression de l'objet.
        $set->delete();
        self::getEntityManager()->flush();

        if ($deleteAF) {
            $af->delete();
        }
        if ($deleteUser) {
            $user->delete();
        }
        self::getEntityManager()->flush();
    }

    /**
     * @var AF_Model_AF
     */
    protected $af;

    /**
     * @var User
     */
    protected $user;


    public static function setUpBeforeClass()
    {
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
        $aF = new AF_Model_AF('test');
        $aF->save();

        $user = new User();
        $user->setEmail('courriel@simulation.set');
        $user->setPassword('test');
        $user->save();

        $this->entityManager->flush();

        $o = new Simulation_Model_Set();
        $o->setUser($user);
        $o->setAF($aF);
        $o->save();
        $this->assertInstanceOf('Simulation_Model_Set', $o);
        $this->assertEquals($o->getKey(), array());
        $this->entityManager->flush();
        $this->assertNotEquals(array(), $o->getKey());

        return $o;
    }

    /**
     * Test le chargement.
     * @depends testConstruct
     * @param Simulation_Model_Set $o
     * @return Simulation_Model_Set
     */
    public function testLoad($o)
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
    public function testDelete($o)
    {
        $o->delete();
        $this->entityManager->flush();
        $this->assertEquals(array(), $o->getKey());
        $o->getAF()->delete();
        $o->getUser()->delete();
        $this->entityManager->flush();
    }

    public static function tearDownAfterClass()
    {
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
