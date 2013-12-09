<?php

namespace Tests\User\ACL\Role;

use Core\Test\TestCase;
use Orga_Model_Organization;
use User\Domain\ACL\Action;
use User\Domain\ACL\ACLService;
use User\Domain\ACL\Resource\NamedResource;
use User\Domain\ACL\Role\AdminRole;
use User\Domain\User;
use User\Domain\UserService;

class AdminRoleTest extends TestCase
{
    /**
     * @Inject
     * @var UserService
     */
    private $userService;

    /**
     * @Inject
     * @var ACLService
     */
    private $aclService;

    /**
     * @var User
     */
    private $admin;

    /**
     * @var User
     */
    private $targetUser;

    public static function setUpBeforeClass()
    {
        foreach (User::loadList() as $o) {
            $o->delete();
        }
        self::getEntityManager()->flush();
    }

    public function setUp()
    {
        parent::setUp();

        $this->targetUser = $this->userService->createUser('target@example.com', 'target');
        $this->entityManager->flush();
        $this->admin = $this->userService->createUser('admin@example.com', 'admin');
        $this->aclService->addRole($this->admin, new AdminRole($this->admin));

        $this->entityManager->flush();
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->userService->deleteUser($this->targetUser);
        $this->userService->deleteUser($this->admin);

        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    /**
     * @dataProvider dataProvider
     */
    public function testIsAllowed(Action $action, $resource = null, $value = true)
    {
        $resource = $resource ?: $this->targetUser;

        if ($value) {
            $this->assertTrue($this->aclService->isAllowed($this->admin, $action, $resource));
        } else {
            $this->assertFalse($this->aclService->isAllowed($this->admin, $action, $resource));
        }
    }

    public function dataProvider()
    {
        $allUsers = NamedResource::loadByName(User::class);
        $repository = NamedResource::loadByName('repository');
        $allOrganizations = NamedResource::loadByName(Orga_Model_Organization::class);

        return [
            // Teste sur la ressource abstraite "tous les utilisateurs"
            [Action::CREATE(), $allUsers],
            [Action::VIEW(), $allUsers],
            [Action::EDIT(), $allUsers],
            [Action::DELETE(), $allUsers],
            [Action::UNDELETE(), $allUsers],
            [Action::ALLOW(), $allUsers],

            // Teste sur un autre utilisateur
            [Action::VIEW()],
            [Action::EDIT()],
            [Action::DELETE()],
            [Action::UNDELETE()],
            [Action::ALLOW()],

            // Sur les référentiels de données
            [Action::VIEW(), $repository],
            [Action::EDIT(), $repository],
            [Action::DELETE(), $repository, false],
            [Action::UNDELETE(), $repository, false],
            [Action::ALLOW(), $repository, false],

            // Teste sur la ressource abstraite "toutes les organisations"
            [Action::CREATE(), $allOrganizations],
        ];
    }
}
