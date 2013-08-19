<?php
/**
 * @package    User
 * @subpackage Test
 */

/**
 * @package    User
 * @subpackage Test
 */
class ResourceNamedTest
{

    /**
     * @return PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('ResourceNamedSetUpTest');
        $suite->addTestSuite('ResourceNamedMetierTest');
        return $suite;
    }

    /**
     * Génère un objet pret à l'emploi pour les tests
     * @return User_Model_Resource_Named Objet généré
     */
    public static function generateObject()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        $resource = new User_Model_Resource_Named();
        $resource->setName(Core_Tools::generateString());
        $resource->save();
        $entityManagers['default']->flush();
        return $resource;
    }

    /**
     * Supprime un objet de test généré avec generateObject()
     * @param User_Model_Resource_Named $resource l'objet de test a supprimer
     */
    public static function deleteObject(User_Model_Resource_Named $resource)
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        $resource->delete();
        $entityManagers['default']->flush();
    }

}

/**
 * @package    User
 * @subpackage Test
 */
class ResourceNamedSetUpTest extends Core_Test_TestCase
{

    /**
     * Méthode appelée avant l'exécution des tests
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        foreach (User_Model_Resource_Named::loadList() as $o) {
            $o->delete();
        }
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }


    /**
     * @return User_Model_Resource
     */
    function testConstruct()
    {
        $o = new User_Model_Resource_Named();
        $o->setName(Core_Tools::generateString());
        $o->save();
        $this->entityManager->flush();

        $this->assertNotNull($o->getId());

        return $o;
    }

    /**
     * @depends testConstruct
     * @param User_Model_Resource_Named $o
     * @return User_Model_Resource_Named
     */
    function testLoad(User_Model_Resource_Named $o)
    {
        /** @var $oLoaded User_Model_Resource_Named */
        $oLoaded = User_Model_Resource_Named::load($o->getId());
        $this->assertInstanceOf('User_Model_Resource_Named', $oLoaded);
        // Vérification des attributs
        $this->assertEquals($o->getId(), $oLoaded->getId());
        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param User_Model_Resource_Named $o
     */
    function testDelete(User_Model_Resource_Named $o)
    {
        $o->delete();
        $this->entityManager->flush();
        $this->assertNull($o->getId());
    }

}

/**
 * @package    User
 * @subpackage Test
 */
class ResourceNamedMetierTest extends Core_Test_TestCase
{

    /**
     * Méthode appelée avant l'exécution des tests.
     */
    public static function setUpBeforeClass()
    {
        /** @var \DI\Container $container */
        $container = Zend_Registry::get('container');
        /** @var User_Service_ACLFilter $aclFilterService */
        $aclFilterService = $container->get('User_Service_ACLFilter');

        $aclFilterService->enabled = false;
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        foreach (User_Model_Resource_Named::loadList() as $o) {
            $o->delete();
        }
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

    /**
     * Test setEntity
     */
    public function testGetSetName()
    {
        $name = Core_Tools::generateString();
        $resource = new User_Model_Resource_Named();
        $resource->setName($name);

        $this->assertSame($name, $resource->getName());
    }

    /**
     * Pas d'entité définie
     */
    public function testGetNameNotSet()
    {
        $resource = new User_Model_Resource_Named();
        $this->assertNull($resource->getName());
    }

    /**
     * Pas d'entité définie
     */
    public function testSaveNameNotSet()
    {
        $resource = new User_Model_Resource_Named();
        $resource->save();
        $this->entityManager->flush();
    }

    /**
     * Test loadByName
     */
    public function testLoadByName()
    {
        $resource = ResourceNamedTest::generateObject();

        $loadedResource = User_Model_Resource_Named::loadByName($resource->getName());
        $this->assertSame($resource, $loadedResource);
        $this->assertEquals($resource->getName(), $loadedResource->getName());

        ResourceNamedTest::deleteObject($resource);
    }

    /**
     * Test loadByName
     */
    public function testLoadByEntityNameNotFound()
    {
        $loadedResource = User_Model_Resource_Named::loadByName("foo");
        $this->assertNull($loadedResource);
    }

}
