<?php
use User\Domain\ACL\Action;
use User\Domain\ACL\Authorization;
use User\Domain\ACL\Resource;
use User\Domain\ACL\ACLService;
use User\Domain\ACL\ACLFilterService;

/**
 * @package    User
 * @subpackage Test
 */

/**
 * @package    User
 * @subpackage Test
 */
class ResourceTest extends Core_Test_TestCase
{

    /**
     * @var ACLService
     */
    protected $aclService;

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
        foreach (Resource::loadList() as $o) {
            $o->delete();
        }
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

    public function setUp()
    {
        parent::setUp();
        // Service des ACL
        $this->aclService = $this->get(ACLService::class);
    }


    public function testDirectAuthorizations()
    {
        $resource = ResourceNamedTest::generateObject();
        $role = RoleTest::generateObject();

        $authorization = $this->aclService->allow($role, Action::VIEW(), $resource);
        $this->entityManager->flush();

        $authorizations = $resource->getDirectAuthorizations();

        $this->assertCount(1, $authorizations);
        foreach ($authorizations as $a) {
            $this->assertInstanceOf(Authorization::class, $authorization);
            $this->assertEquals($authorization, $a);
        }

        ResourceNamedTest::deleteObject($resource);
        RoleTest::deleteObject($role);
    }

    public function testGetLinkedSecurityIdentities()
    {
        $resource = ResourceNamedTest::generateObject();
        $role = RoleTest::generateObject();

        $this->aclService->allow($role, Action::VIEW(), $resource);
        $this->entityManager->flush();

        $identities = $resource->getLinkedSecurityIdentities();

        $this->assertCount(1, $identities);

        ResourceNamedTest::deleteObject($resource);
        RoleTest::deleteObject($role);
    }

    /**
     * Test qu'il n'y a pas de doublons renvoyés
     */
    public function testGetLinkedSecurityIdentitiesNoDuplicates()
    {
        $resource = ResourceNamedTest::generateObject();
        $role = RoleTest::generateObject();

        $this->aclService->allow($role, Action::VIEW(), $resource);
        $this->aclService->allow($role, Action::EDIT(), $resource);
        $this->entityManager->flush();

        $identities = $resource->getLinkedSecurityIdentities();

        $this->assertCount(1, $identities);

        ResourceNamedTest::deleteObject($resource);
        RoleTest::deleteObject($role);
    }

}
