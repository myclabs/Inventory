<?php

use User\Domain\ACL\Action\DefaultAction;
use User\Domain\ACL\Authorization;
use User\Domain\ACL\Resource;
use User\Domain\ACL\Role;
use User\Domain\ACL\ACLFilterService;

class AuthorizationTest
{

    /**
     * Déclaration de la suite de test à effectuer
     * @return PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('AuthorizationSetUpTest');
        $suite->addTestSuite('AuthorizationMetierTest');
        return $suite;
    }

    /**
     * Génere un objet pret à l'emploi pour les tests
     * @return Authorization Objet généré
     */
    public static function generateObject()
    {
        $resource = ResourceNamedTest::generateObject();
        $role = RoleTest::generateObject();

        $o = new Authorization($role, DefaultAction::VIEW(), $resource);
        $o->save();

        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        return $o;
    }

    /**
     * Supprime un objet de test généré avec generateObject()
     * @param Authorization $o l'objet de test a supprimer
     */
    public static function deleteObject(Authorization $o)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        ResourceNamedTest::deleteObject($o->getResource());
        RoleTest::deleteObject($o->getIdentity());
    }

}

/**
 * Test des méthodes de base de l'objet Authorization.
 *
 * @package    User
 * @subpackage Test
 */
class AuthorizationSetUpTest extends Core_Test_TestCase
{

    /**
     * Méthode appelée avant l'exécution des tests
     */
    public static function setUpBeforeClass()
    {
        /** @var \DI\Container $container */
        $container = Zend_Registry::get('container');
        /** @var ACLFilterService $aclFilterService */
        $aclFilterService = $container->get(ACLFilterService::class);

        $aclFilterService->enabled = false;
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        foreach (Authorization::loadList() as $o) {
            $o->delete();
        }
        foreach (Resource::loadList() as $o) {
            $o->delete();
        }
        foreach (Role::loadList() as $o) {
            $o->delete();
        }
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @return Authorization
     */
    function testConstruct()
    {
        // Fixture
        $resource = ResourceNamedTest::generateObject();
        $role = RoleTest::generateObject();

        $o = new Authorization($role, DefaultAction::VIEW(), $resource);

        $this->assertEquals(DefaultAction::VIEW(), $o->getAction());
        $this->assertSame($resource, $o->getResource());
        $this->assertSame($role, $o->getIdentity());

        $o->save();
        $this->entityManager->flush();

        $this->assertNotNull($o->getId());

        return $o;
    }

    /**
     * @depends testConstruct
     * @param Authorization $o
     * @return Authorization
     */
    function testLoad(Authorization $o)
    {
        /** @var $oLoaded Authorization */
        $oLoaded = Authorization::load($o->getId());

        $this->assertSame($o, $oLoaded);

        // Vérification des attributs
        $this->assertEquals($oLoaded->getAction(), $o->getAction());
        $this->assertInstanceOf(Resource::class, $oLoaded->getResource());
        $this->assertEquals($o->getResource()->getId(), $oLoaded->getResource()->getId());
        $this->assertInstanceOf(Role::class, $oLoaded->getIdentity());
        $this->assertEquals($o->getIdentity()->getId(), $oLoaded->getIdentity()->getId());

        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param Authorization $o
     */
    function testDelete(Authorization $o)
    {
        $resource = $o->getResource();
        $role = $o->getIdentity();

        $o->delete();
        $this->entityManager->flush();

        $this->assertNull($o->getId());

        ResourceNamedTest::deleteObject($resource);
        RoleTest::deleteObject($role);
    }

}

/**
 * Test des méthodes métier de l'objet Authorization.
 *
 * @package    User
 * @subpackage Test
 */
class AuthorizationMetierTest extends Core_Test_TestCase
{

    /**
     * Méthode appelée avant l'exécution des tests
     */
    public static function setUpBeforeClass()
    {
        /** @var \DI\Container $container */
        $container = Zend_Registry::get('container');
        /** @var ACLFilterService $aclFilterService */
        $aclFilterService = $container->get(ACLFilterService::class);

        $aclFilterService->enabled = false;
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        foreach (Role::loadList() as $o) {
            $o->delete();
        }
        foreach (Authorization::loadList() as $o) {
            $o->delete();
        }
        foreach (Resource::loadList() as $o) {
            $o->delete();
        }
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
        /** @var ACLFilterService $aclFilterService */
        $aclFilterService = $this->get(ACLFilterService::class);
        $aclFilterService->enabled = false;
    }

    /**
     * Rechercher
     */
    function testSearch()
    {
        $authorization = AuthorizationTest::generateObject();

        $o = Authorization::search(
            $authorization->getIdentity(),
            $authorization->getAction(),
            $authorization->getResource()
        );
        $this->assertSame($authorization, $o);

        AuthorizationTest::deleteObject($authorization);
    }

    /**
     * Rechercher
     */
    function testSearchNotFound()
    {
        $authorization = AuthorizationTest::generateObject();

        $o = Authorization::search(
            $authorization->getIdentity(),
            DefaultAction::EDIT(),
            $authorization->getResource()
        );
        $this->assertNull($o);

        AuthorizationTest::deleteObject($authorization);
    }

}
