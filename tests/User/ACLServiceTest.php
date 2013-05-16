<?php
/**
 * @package User
 * @subpackage Test
 */

/**
 * Test de User_Service_ACL
 * @package    User
 * @subpackage Test
 */
class ACLServiceTest extends Core_Test_TestCase
{

    /**
     * @var User_Service_User
     */
    protected $userService;

    /**
     * @var User_Service_ACL
     */
    protected $aclService;


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
        foreach (User_Model_User::loadList() as $o) {
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

        $this->userService = User_Service_User::getInstance();
        $this->aclService = User_Service_ACL::getInstance();

        User_Service_ACLFilter::getInstance()->enabled = false;
    }

    /**
     * User -> Entity
     */
    public function testIsAllowedUserToEntity()
    {
        // Fixtures
        $targetUser = $this->userService->createUser('target', 'target');
        $admin = $this->userService->createUser('admin', 'admin');
        $this->aclService->allow($admin, User_Model_Action_Default::VIEW(), $targetUser);
        $this->entityManager->flush();

        $result = $this->aclService->isAllowed($admin, User_Model_Action_Default::VIEW(), $targetUser);

        // Fixtures
        $this->aclService->disallow($admin, User_Model_Action_Default::VIEW(), $targetUser);
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
        $this->aclService->allow($admin, User_Model_Action_Default::VIEW(), $targetUser);
        $this->entityManager->flush();

        $targetResource = $this->aclService->getResourceForEntity($targetUser);
        $result = $this->aclService->isAllowed($admin, User_Model_Action_Default::VIEW(), $targetResource);

        // Fixtures
        $this->aclService->disallow($admin, User_Model_Action_Default::VIEW(), $targetUser);
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
        $roleAdmin = new User_Model_Role('admin', 'Admin');
        $roleAdmin->save();
        $admin->addRole($roleAdmin);
        $this->entityManager->flush();
        $this->aclService->allow($roleAdmin, User_Model_Action_Default::VIEW(), $targetUser);
        $this->entityManager->flush();

        $result = $this->aclService->isAllowed($roleAdmin, User_Model_Action_Default::VIEW(), $targetUser);

        // Fixtures
        $this->aclService->disallow($roleAdmin, User_Model_Action_Default::VIEW(), $targetUser);
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
        $roleAdmin = new User_Model_Role('admin', 'Admin');
        $roleAdmin->save();
        $admin->addRole($roleAdmin);
        $this->entityManager->flush();
        $this->aclService->allow($roleAdmin, User_Model_Action_Default::VIEW(), $targetUser);
        $this->entityManager->flush();

        $targetResource = $this->aclService->getResourceForEntity($targetUser);
        $result = $this->aclService->isAllowed($roleAdmin, User_Model_Action_Default::VIEW(), $targetResource);

        // Fixtures
        $this->aclService->disallow($roleAdmin, User_Model_Action_Default::VIEW(), $targetUser);
        $this->entityManager->flush();
        $this->userService->deleteUser($targetUser);
        $this->userService->deleteUser($admin);
        $roleAdmin->delete();
        $this->entityManager->flush();

        $this->assertTrue($result);
    }

}
