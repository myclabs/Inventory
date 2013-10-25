<?php
use User\Domain\ACL\Role;

/**
 * @package    User
 * @subpackage Test
 */

/**
 * Creation of the Test Suite.
 *
 * @package    User
 * @subpackage Test
 */
class RoleTest
{

    /**
     * Déclaration de la suite de test à éffectuer.
     * @return PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('RoleSetUpTest');
        $suite->addTestSuite('RoleMetierTest');
        return $suite;
    }

    /**
     * Génere un objet pret à l'emploi pour les tests.
     * @return Role Objet généré
     */
    public static function generateObject()
    {
        $o = new Role(Core_Tools::generateString(10), 'Role de test');
        $o->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        return $o;
    }

    /**
     * Supprime un objet de test généré avec generateObject().
     * @param Role $o
     */
    public static function deleteObject($o)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

}

/**
 * Test des méthodes de base de l'objet Role.
 * @package    User
 * @subpackage Test
 */
class RoleSetUpTest extends Core_Test_TestCase
{

    /**
     * Méthode appelée avant l'exécution des tests
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        foreach (Role::loadList() as $o) {
            $o->delete();
        }
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

    /**
     * Test du Constructeur
     * @return Role
     */
    function testConstruct()
    {
        $o = new Role();
        $o->setRef('test');
        $o->setName('Role de test');

        $this->assertEquals('Role de test', $o->getName());
        $this->assertEquals('test', $o->getRef());

        $o->save();
        $this->entityManager->flush();

        $this->assertNotNull($o->getId());
        return $o;
    }

    /**
     * @depends testConstruct
     * @param Role $o
     * @return Role
     */
    function testLoad(Role $o)
    {
        $this->entityManager->clear();
        /** @var $oLoaded Role */
        $oLoaded = Role::load($o->getId());

        $this->assertInstanceOf(Role::class, $oLoaded);
        $this->assertEquals($o->getId(), $oLoaded->getId());
        $this->assertEquals($o->getName(), $oLoaded->getName());
        $this->assertEquals($o->getRef(), $oLoaded->getRef());
        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param Role $o
     */
    function testDelete(Role $o)
    {
        $o->delete();
        $this->entityManager->flush();
        $this->assertNull($o->getId());
    }

}

/**
 * Test des méthodes métier de l'objet Role.
 * @package    User
 * @subpackage Test
 */
class RoleMetierTest extends Core_Test_TestCase
{

    /**
     * Méthode appelée avant l'exécution des tests
     */
    public static function setUpBeforeClass()
    {
        foreach (Role::loadList() as $o) {
            $o->delete();
        }
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

    /**
     * Test de la fonction de chargement par le code
     */
    function testLoadByRef()
    {
        $role = RoleTest::generateObject();

        $o = Role::loadByRef($role->getRef());
        $this->assertSame($role, $o);

        RoleTest::deleteObject($role);
    }

    /**
     * Test de getUsers
     */
    function testRoleUsers()
    {
        $role = RoleTest::generateObject();
        $user = UserTest::generateObject();

        $this->assertEquals(0, count($role->getUsers()));

        $user->addRole($role);

        $user->save();

        $this->assertEquals(1, count($role->getUsers()));

        UserTest::deleteObject($user);
        RoleTest::deleteObject($role);
    }

}
