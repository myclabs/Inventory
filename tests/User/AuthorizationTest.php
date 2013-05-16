<?php
/**
 * @package User
 */

/**
 * Creation of the Test Suite.
 * @package    User
 * @subpackage Test
 */
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
     * @return User_Model_Authorization Objet généré
     */
    public static function generateObject()
    {
        $resource = ResourceNamedTest::generateObject();
        $role = RoleTest::generateObject();

        $o = new User_Model_Authorization($role, User_Model_Action_Default::VIEW(), $resource);
        $o->save();

        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        return $o;
    }

    /**
     * Supprime un objet de test généré avec generateObject()
     * @param User_Model_Authorization $o l'objet de test a supprimer
     */
    public static function deleteObject(User_Model_Authorization $o)
    {
        $o->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        ResourceNamedTest::deleteObject($o->getResource());
        RoleTest::deleteObject($o->getIdentity());
    }

}

/**
 * Test des méthodes de base de l'objet User_Model_Authorization.
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
        User_Service_ACLFilter::getInstance()->enabled = false;
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        foreach (User_Model_Authorization::loadList() as $o) {
            $o->delete();
        }
        foreach (User_Model_Resource::loadList() as $o) {
            $o->delete();
        }
        foreach (User_Model_Role::loadList() as $o) {
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
     * @return User_Model_Authorization
     */
    function testConstruct()
    {
        // Fixture
        $resource = ResourceNamedTest::generateObject();
        $role = RoleTest::generateObject();

        $o = new User_Model_Authorization($role, User_Model_Action_Default::VIEW(), $resource);

        $this->assertEquals(User_Model_Action_Default::VIEW(), $o->getAction());
        $this->assertSame($resource, $o->getResource());
        $this->assertSame($role, $o->getIdentity());

        $o->save();
        $this->entityManager->flush();

        $this->assertNotNull($o->getId());

        return $o;
    }

    /**
     * @depends testConstruct
     * @param User_Model_Authorization $o
     * @return User_Model_Authorization
     */
    function testLoad(User_Model_Authorization $o)
    {
        /** @var $oLoaded User_Model_Authorization */
        $oLoaded = User_Model_Authorization::load($o->getId());

        $this->assertSame($o, $oLoaded);

        // Vérification des attributs
        $this->assertEquals($oLoaded->getAction(), $o->getAction());
        $this->assertInstanceOf('User_Model_Resource', $oLoaded->getResource());
        $this->assertEquals($o->getResource()->getId(), $oLoaded->getResource()->getId());
        $this->assertInstanceOf('User_Model_Role', $oLoaded->getIdentity());
        $this->assertEquals($o->getIdentity()->getId(), $oLoaded->getIdentity()->getId());

        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param User_Model_Authorization $o
     */
    function testDelete(User_Model_Authorization $o)
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
 * Test des méthodes métier de l'objet User_Model_Authorization.
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
        User_Service_ACLFilter::getInstance()->enabled = false;
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        foreach (User_Model_Role::loadList() as $o) {
            $o->delete();
        }
        foreach (User_Model_Authorization::loadList() as $o) {
            $o->delete();
        }
        foreach (User_Model_Resource::loadList() as $o) {
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
        User_Service_ACLFilter::getInstance()->enabled = false;
    }

    /**
     * Rechercher
     */
    function testSearch()
    {
        $authorization = AuthorizationTest::generateObject();

        $o = User_Model_Authorization::search($authorization->getIdentity(),
                                              $authorization->getAction(),
                                              $authorization->getResource());
        $this->assertSame($authorization, $o);

        AuthorizationTest::deleteObject($authorization);
    }

    /**
     * Rechercher
     */
    function testSearchNotFound()
    {
        $authorization = AuthorizationTest::generateObject();

        $o = User_Model_Authorization::search($authorization->getIdentity(),
                                              User_Model_Action_Default::EDIT(),
                                              $authorization->getResource());
        $this->assertNull($o);

        AuthorizationTest::deleteObject($authorization);
    }

}
