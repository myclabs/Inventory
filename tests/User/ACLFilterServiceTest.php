<?php
use User\Domain\ACL\Action\DefaultAction;
use User\Domain\ACL\Authorization;
use User\Domain\ACL\Resource;
use User\Domain\ACL\Resource\EntityResource;
use User\Domain\ACL\SecurityIdentity;
use User\Domain\ACL\ACLService;
use User\Domain\ACL\ACLFilterService;
use User\Domain\User;
use User\Domain\UserService;

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
     * @var UserService
     */
    protected $userService;

    /**
     * @var ACLService
     */
    protected $aclService;

    /**
     * @var ACLFilterService
     */
    protected $aclFilterService;

    /**
     * Méthode appelée avant l'exécution des tests
     */
    public static function setUpBeforeClass()
    {
        /** @var \DI\Container $container */
        $container = Zend_Registry::get('container');
        /** @var ACLFilterService $aclFilterService */
        $aclFilterService = $container->get(ACLFilterService::class);

        $aclFilterService->clean();
        $aclFilterService->enabled = false;
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        foreach (Authorization::loadList() as $o) {
            $o->delete();
        }
        foreach (Resource::loadList() as $o) {
            $o->delete();
        }
        foreach (SecurityIdentity::loadList() as $o) {
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
        $this->aclFilterService = $this->get(ACLFilterService::class);
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
        $this->aclService->allow($admin, DefaultAction::VIEW(), $targetUser);
        $this->entityManager->flush();

        // 2 droits par utilisateur (VIEW et EDIT sur eux-même) + le droit qu'on a rajouté manuellement
        $this->assertEquals(2*2 + 1, $this->aclFilterService->getEntriesCount());

        // Fixtures
        $this->aclService->disallow($admin, DefaultAction::VIEW(), $targetUser);
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
        $allUsersResource = new EntityResource();
        $allUsersResource->setEntityName(User::class);
        $allUsersResource->save();
        $this->entityManager->flush();
        $this->aclService->allow($admin, DefaultAction::VIEW(), $allUsersResource);
        $this->entityManager->flush();

        // 2 droits par utilisateur (VIEW et EDIT sur eux-même)
        $this->assertEquals(2, $this->aclFilterService->getEntriesCount());

        // Ajoute un autre utilisateur
        $admin = $this->userService->createUser('user', 'user');

        // 2 droits par utilisateur (VIEW et EDIT sur eux-même) + (admin voit user)
        $this->assertEquals(2*2+1, $this->aclFilterService->getEntriesCount());

        // Fixtures
        $this->aclService->disallow($admin, DefaultAction::VIEW(), $allUsersResource);
        $this->entityManager->flush();
        $allUsersResource->delete();
        $this->userService->deleteUser($admin);
        $this->entityManager->flush();
    }

}
