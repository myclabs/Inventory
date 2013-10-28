<?php

namespace Tests\User\ACL\Role;

use Core_Test_TestCase;
use User\Domain\ACL\Action;
use User\Domain\ACL\Authorization;
use User\Domain\ACL\ACLService;
use User\Domain\User;
use User\Domain\UserService;

class UserRoleTest extends Core_Test_TestCase
{
    /**
     * @Inject
     * @var UserService
     */
    protected $userService;

    /**
     * @Inject
     * @var ACLService
     */
    protected $aclService;

    /**
     * @var User
     */
    private $user;

    /**
     * @var User
     */
    private $otherUser;

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

        $this->user = $this->userService->createUser('user', 'user');
        $this->otherUser = $this->userService->createUser('otherUser', 'otherUser');

        $this->entityManager->flush();

        $this->aclService->rebuildAuthorizations();
        $this->entityManager->flush();
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->userService->deleteUser($this->otherUser);
        $this->userService->deleteUser($this->user);

        $this->entityManager->flush();
    }

    /**
     * @dataProvider dataProvider
     */
    public function testIsAllowed(Action $action, $himself = true, $value = true)
    {
        $resource = $himself ? $this->user : $this->otherUser;

        if ($value) {
            $this->assertTrue($this->aclService->isAllowed($this->user, $action, $resource));
        } else {
            $this->assertFalse($this->aclService->isAllowed($this->user, $action, $resource));
        }
    }

    public function dataProvider()
    {
        return [
            [Action::CREATE(), User::class, false],

            // Sur lui-même
            [Action::VIEW(), true],
            [Action::EDIT(), true],
            [Action::DELETE(), true],
            [Action::UNDELETE(), true, false],
            [Action::ALLOW(), true, false],

            // Sur les autres
            [Action::VIEW(), false, false],
            [Action::EDIT(), false, false],
            [Action::DELETE(), false, false],
            [Action::UNDELETE(), false, false],
            [Action::ALLOW(), false, false],
        ];
    }
}
