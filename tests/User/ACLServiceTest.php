<?php
use User\Domain\ACL\Action\DefaultAction;
use User\Domain\ACL\Authorization;
use User\Domain\ACL\Resource;
use User\Domain\ACL\Role;
use User\Domain\ACL\ACLService;
use User\Domain\ACL\ACLFilterService;
use User\Domain\User;
use User\Domain\UserService;

/**
 * @package User
 * @subpackage Test
 */

/**
 * Test de ACLService
 * @package    User
 * @subpackage Test
 */
class ACLServiceTest extends Core_Test_TestCase
{

    /**
     * @var UserService
     */
    protected $userService;

    /**
     * @var ACLService
     */
    protected $aclService;


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
        foreach (User::loadList() as $o) {
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

        $this->userService = $this->get(UserService::class);
        $this->aclService = $this->get(ACLService::class);
        /** @var ACLFilterService $aclFilterService */
        $aclFilterService = $this->get(ACLFilterService::class);

        $aclFilterService->enabled = false;
    }

    /**
     * User -> Entity
     */
    public function testIsAllowedUserToEntity()
    {
        // Fixtures
        $targetUser = $this->userService->createUser('target', 'target');
        $admin = $this->userService->createUser('admin', 'admin');
        $this->aclService->allow($admin, DefaultAction::VIEW(), $targetUser);
        $this->entityManager->flush();

        $result = $this->aclService->isAllowed($admin, DefaultAction::VIEW(), $targetUser);

        // Fixtures
        $this->aclService->disallow($admin, DefaultAction::VIEW(), $targetUser);
        $this->entityManager->flush();
        $this->userService->deleteUser($targetUser);
        $this->userService->deleteUser($admin);

        $this->assertTrue($result);
    }

    /**
     * User -> Resource
     */
    public function testIsAllowedUserToResource()
    {
        // Fixtures
        $targetUser = $this->userService->createUser('target', 'target');
        $admin = $this->userService->createUser('admin', 'admin');
        $this->aclService->allow($admin, DefaultAction::VIEW(), $targetUser);
        $this->entityManager->flush();

        $targetResource = $this->aclService->getResourceForEntity($targetUser);
        $result = $this->aclService->isAllowed($admin, DefaultAction::VIEW(), $targetResource);

        // Fixtures
        $this->aclService->disallow($admin, DefaultAction::VIEW(), $targetUser);
        $this->entityManager->flush();
        $this->userService->deleteUser($targetUser);
        $this->userService->deleteUser($admin);

        $this->assertTrue($result);
    }

    /**
     * Role -> Entity
     */
    public function testIsAllowedRoleToEntity()
    {
        // Fixtures
        $targetUser = $this->userService->createUser('target', 'target');
        $admin = $this->userService->createUser('admin', 'admin');
        $roleAdmin = new Role('admin', 'Admin');
        $roleAdmin->save();
        $admin->addRole($roleAdmin);
        $this->entityManager->flush();
        $this->aclService->allow($roleAdmin, DefaultAction::VIEW(), $targetUser);
        $this->entityManager->flush();

        $result = $this->aclService->isAllowed($roleAdmin, DefaultAction::VIEW(), $targetUser);

        // Fixtures
        $this->aclService->disallow($roleAdmin, DefaultAction::VIEW(), $targetUser);
        $this->entityManager->flush();
        $this->userService->deleteUser($targetUser);
        $this->userService->deleteUser($admin);
        $roleAdmin->delete();
        $this->entityManager->flush();

        $this->assertTrue($result);
    }

    /**
     * Role -> Resource
     */
    public function testIsAllowedRoleToResource()
    {
        // Fixtures
        $targetUser = $this->userService->createUser('target', 'target');
        $admin = $this->userService->createUser('admin', 'admin');
        $roleAdmin = new Role('admin', 'Admin');
        $roleAdmin->save();
        $admin->addRole($roleAdmin);
        $this->entityManager->flush();
        $this->aclService->allow($roleAdmin, DefaultAction::VIEW(), $targetUser);
        $this->entityManager->flush();

        $targetResource = $this->aclService->getResourceForEntity($targetUser);
        $result = $this->aclService->isAllowed($roleAdmin, DefaultAction::VIEW(), $targetResource);

        // Fixtures
        $this->aclService->disallow($roleAdmin, DefaultAction::VIEW(), $targetUser);
        $this->entityManager->flush();
        $this->userService->deleteUser($targetUser);
        $this->userService->deleteUser($admin);
        $roleAdmin->delete();
        $this->entityManager->flush();

        $this->assertTrue($result);
    }
}
