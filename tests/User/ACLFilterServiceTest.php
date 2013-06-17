<?php
/**
 * @package    User
 * @subpackage Test
 */

/**
 * @package    User
 * @subpackage Test
 */
class ACLFilterServiceTest extends Core_Test_TestCase
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
     * @var User_Service_ACLFilter
     */
    protected $aclFilterService;

    /**
     * Méthode appelée avant l'exécution des tests
     */
    public static function setUpBeforeClass()
    {
        /** @var \DI\Container $container */
        $container = Zend_Registry::get('container');
        /** @var User_Service_ACLFilter $aclFilterService */
        $aclFilterService = $container->get('User_Service_ACLFilter');

        $aclFilterService->clean();
        $aclFilterService->enabled = false;
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        foreach (User_Model_Authorization::loadList() as $o) {
            $o->delete();
        }
        foreach (User_Model_Resource::loadList() as $o) {
            $o->delete();
        }
        foreach (User_Model_SecurityIdentity::loadList() as $o) {
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
        $this->userService = $this->get('User_Service_User');
        $this->aclService = $this->get('User_Service_ACL');
        $this->aclFilterService = $this->get('User_Service_ACLFilter');
        $this->aclFilterService->enabled = true;
    }

    /**
     * Clean
     */
    public function testClean()
    {
        $this->aclFilterService->clean();
        $this->assertEquals(0, $this->aclFilterService->getEntriesCount());
    }

    /**
     * @depends testClean
     */
    public function testGenerateEmpty()
    {
        $this->aclFilterService->generate();
        $this->assertEquals(0, $this->aclFilterService->getEntriesCount());
    }

    /**
     * @depends testGenerateEmpty
     */
    public function testGenerateNotEmpty()
    {
        // Fixtures
        $targetUser = $this->userService->createUser('target', 'target');
        $admin = $this->userService->createUser('admin', 'admin');
        $this->aclService->allow($admin, User_Model_Action_Default::VIEW(), $targetUser);
        $this->entityManager->flush();

        // 2 droits par utilisateur (VIEW et EDIT sur eux-même) + le droit qu'on a rajouté manuellement
        $this->assertEquals(2*2 + 1, $this->aclFilterService->getEntriesCount());

        // Fixtures
        $this->aclService->disallow($admin, User_Model_Action_Default::VIEW(), $targetUser);
        $this->entityManager->flush();
        $this->userService->deleteUser($targetUser);
        $this->userService->deleteUser($admin);
    }

    /**
     * Génération du cache avec une Resource_Entity qui ne désigne pas une entité précise
     */
    public function testGenerateWithEntityWithNoIdentifier()
    {
        // Fixtures
        $admin = $this->userService->createUser('admin', 'admin');
        $this->entityManager->flush();
        $allUsersResource = new User_Model_Resource_Entity();
        $allUsersResource->setEntityName('User_Model_User');
        $allUsersResource->save();
        $this->entityManager->flush();
        $this->aclService->allow($admin, User_Model_Action_Default::VIEW(), $allUsersResource);
        $this->entityManager->flush();

        // 2 droits par utilisateur (VIEW et EDIT sur eux-même) + (le droit qu'on a rajouté manuellement == 0)
        $this->assertEquals(2, $this->aclFilterService->getEntriesCount());

        // Ajoute un autre utilisateur
        $admin = $this->userService->createUser('user', 'user');

        // 2 droits par utilisateur (VIEW et EDIT sur eux-même) + (le droit qu'on a rajouté manuellement == 0)
        $this->assertEquals(2*2, $this->aclFilterService->getEntriesCount());

        // Fixtures
        $this->aclService->disallow($admin, User_Model_Action_Default::VIEW(), $allUsersResource);
        $this->entityManager->flush();
        $allUsersResource->delete();
        $this->userService->deleteUser($admin);
        $this->entityManager->flush();
    }

}
