<?php

namespace Tests\User\ACL\Role;

use Core_Test_TestCase;
use User\Domain\ACL\Action;
use User\Domain\ACL\Authorization\Authorization;
use User\Domain\ACL\ACLService;
use User\Domain\ACL\Resource\NamedResource;
use User\Domain\ACL\Role\AdminRole;
use User\Domain\User;
use User\Domain\UserService;

class AdminRoleTest extends Core_Test_TestCase
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

        $this->targetUser = $this->userService->createUser('target', 'target');
        $this->entityManager->flush();
        $this->admin = $this->userService->createUser('admin', 'admin');
        $this->admin->addRole(new AdminRole($this->admin));

        $this->entityManager->flush();
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->userService->deleteUser($this->targetUser);
        $this->userService->deleteUser($this->admin);

        $this->entityManager->flush();
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
        $repository = NamedResource::loadByName('repository');

        return [
            [Action::CREATE(), NamedResource::loadByName(User::class)],
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
        ];
    }
}