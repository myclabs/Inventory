<?php
use User\Domain\ACL\Resource;
use User\Domain\ACL\Resource\EntityResource;
use User\Domain\ACL\ACLFilterService;

/**
 * @package    User
 * @subpackage Test
 */

/**
 * @package    User
 * @subpackage Test
 */
class ResourceEntityTest
{

    /**
     * @return PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('ResourceEntitySetUpTest');
        $suite->addTestSuite('ResourceEntityMetierTest');
        return $suite;
    }

    /**
     * Génère un objet pret à l'emploi pour les tests
     * @return EntityResource Objet généré
     */
    public static function generateObject()
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entity = new Inventory_Model_SimpleExample();
        $entity->save();
        $entityManagers['default']->flush();

        $resource = new EntityResource();
        $resource->setEntity($entity);
        $resource->save();
        $entityManagers['default']->flush();
        return $resource;
    }

    /**
     * Supprime un objet de test généré avec generateObject()
     * @param EntityResource $resource l'objet de test a supprimer
     */
    public static function deleteObject($resource)
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        $resource->delete();
        $resource->getEntity()->delete();
        $entityManagers['default']->flush();
    }

}

/**
 * Test des méthodes base de l'objet Resource
 * @package    User
 * @subpackage Test
 */
class ResourceEntitySetUpTest extends Core_Test_TestCase
{

    /**
     * Méthode appelée avant l'exécution des tests
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        foreach (Inventory_Model_SimpleExample::loadList() as $o) {
            $o->delete();
        }
        foreach (EntityResource::loadList() as $o) {
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
        $entity = new Inventory_Model_SimpleExample();
        $entity->save();
        $this->entityManager->flush();

        $o = new EntityResource();
        $o->setEntity($entity);
        $o->save();
        $this->entityManager->flush();

        $this->assertNotNull($o->getId());

        return $o;
    }

    /**
     * @depends testConstruct
     * @param Resource $o
     * @return Resource
     */
    function testLoad(Resource $o)
    {
        /** @var $oLoaded Resource */
        $oLoaded = Resource::load($o->getId());
        $this->assertInstanceOf(Resource::class, $oLoaded);
        // Vérification des attributs
        $this->assertEquals($o->getId(), $oLoaded->getId());
        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param Resource $o
     */
    function testDelete(Resource $o)
    {
        $o->delete();
        $this->entityManager->flush();
        $this->assertNull($o->getId());
    }

}

/**
 * Test des méthodes métier de l'objet Resource
 * @package    User
 * @subpackage Test
 */
class ResourceEntityMetierTest extends Core_Test_TestCase
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
        foreach (Inventory_Model_SimpleExample::loadList() as $o) {
            $o->delete();
        }
        foreach (EntityResource::loadList() as $o) {
            $o->delete();
        }
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

    /**
     * Test setEntity
     */
    public function testGetSetEntity()
    {
        $entity = new Inventory_Model_SimpleExample();
        $entity->save();
        $this->entityManager->flush();

        $resource = new EntityResource();
        $resource->setEntity($entity);

        $this->assertEquals(get_class($entity), $resource->getEntityName());
        $this->assertSame($entity, $resource->getEntity());

        $entity->delete();
        $this->entityManager->flush();
    }

    /**
     * @expectedException Core_ORM_NotPersistedEntityException
     */
    public function testSetEntityNotPersisted()
    {
        $entity = new Inventory_Model_SimpleExample();
        $resource = new EntityResource();
        $resource->setEntity($entity);
    }

    /**
     * Pas d'entité définie
     */
    public function testGetEntityNotSet()
    {
        $resource = new EntityResource();
        $this->assertNull($resource->getEntity());
    }

    /**
     * Test loadByEntity
     */
    public function testLoadByEntity()
    {
        $entity = new Inventory_Model_SimpleExample();
        $entity->save();
        $this->entityManager->flush();

        $resource = new EntityResource();
        $resource->setEntity($entity);
        $resource->save();
        $this->entityManager->flush();

        $loadedResource = EntityResource::loadByEntity($entity);
        $this->assertSame($resource, $loadedResource);
        $this->assertEquals($resource->getEntityName(), $loadedResource->getEntityName());
        $this->assertEquals($resource->getEntityIdentifier(), $loadedResource->getEntityIdentifier());

        $resource->delete();
        $entity->delete();
        $this->entityManager->flush();
    }

    /**
     * Test loadByEntity
     */
    public function testLoadByEntityNotFound()
    {
        $entity = new Inventory_Model_SimpleExample();
        $entity->save();
        $this->entityManager->flush();

        $loadedResource = EntityResource::loadByEntity($entity);
        $this->assertNull($loadedResource);

        $entity->delete();
        $this->entityManager->flush();
    }

    /**
     * Test loadByEntityName
     */
    public function testLoadByEntityNameNotFound()
    {
        $loadedResource = EntityResource::loadByEntityName("foo");
        $this->assertNull($loadedResource);
    }
}
