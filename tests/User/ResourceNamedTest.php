<?php
use User\Domain\ACL\Resource;
use User\Domain\ACL\Resource\NamedResource;
use User\Domain\ACL\ACLFilterService;

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
     * @return NamedResource Objet généré
     */
    public static function generateObject()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        $resource = new NamedResource();
        $resource->setName(Core_Tools::generateString());
        $resource->save();
        $entityManagers['default']->flush();
        return $resource;
    }

    /**
     * Supprime un objet de test généré avec generateObject()
     * @param NamedResource $resource l'objet de test a supprimer
     */
    public static function deleteObject(NamedResource $resource)
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
        foreach (NamedResource::loadList() as $o) {
            $o->delete();
        }
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }


    /**
     * @return Resource
     */
    function testConstruct()
    {
        $o = new NamedResource();
        $o->setName(Core_Tools::generateString());
        $o->save();
        $this->entityManager->flush();

        $this->assertNotNull($o->getId());

        return $o;
    }

    /**
     * @depends testConstruct
     * @param NamedResource $o
     * @return NamedResource
     */
    function testLoad(NamedResource $o)
    {
        /** @var $oLoaded NamedResource */
        $oLoaded = NamedResource::load($o->getId());
        $this->assertInstanceOf(NamedResource::class, $oLoaded);
        // Vérification des attributs
        $this->assertEquals($o->getId(), $oLoaded->getId());
        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param NamedResource $o
     */
    function testDelete(NamedResource $o)
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
        /** @var ACLFilterService $aclFilterService */
        $aclFilterService = $container->get(ACLFilterService::class);

        $aclFilterService->enabled = false;
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        foreach (NamedResource::loadList() as $o) {
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
        $resource = new NamedResource();
        $resource->setName($name);

        $this->assertSame($name, $resource->getName());
    }

    /**
     * Pas d'entité définie
     */
    public function testGetNameNotSet()
    {
        $resource = new NamedResource();
        $this->assertNull($resource->getName());
    }

    /**
     * Pas d'entité définie
     */
    public function testSaveNameNotSet()
    {
        $resource = new NamedResource();
        $resource->save();
        $this->entityManager->flush();
    }

    /**
     * Test loadByName
     */
    public function testLoadByName()
    {
        $resource = ResourceNamedTest::generateObject();

        $loadedResource = NamedResource::loadByName($resource->getName());
        $this->assertSame($resource, $loadedResource);
        $this->assertEquals($resource->getName(), $loadedResource->getName());

        ResourceNamedTest::deleteObject($resource);
    }

    /**
     * Test loadByName
     */
    public function testLoadByEntityNameNotFound()
    {
        $loadedResource = NamedResource::loadByName("foo");
        $this->assertNull($loadedResource);
    }

}
