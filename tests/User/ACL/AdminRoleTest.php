<?php

namespace Tests\User\ACL;

use Core\Test\TestCase;
use MyCLabs\ACL\ACL;
use User\Domain\ACL\Actions;
use User\Domain\ACL\AdminRole;
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
     * @var ACL
     */
    private $acl;

    public static function setUpBeforeClass()
    {
        foreach (User::loadList() as $o) {
            $o->delete();
        }
        self::getEntityManager()->flush();
    }

    public function testIsAllowed()
    {
        $targetUser = $this->userService->createUser('target@example.com', 'target');
        $admin = $this->userService->createUser('admin@example.com', 'admin');

        $this->entityManager->flush();

        $this->acl->grant($admin, new AdminRole($admin));

        $this->assertTrue($this->acl->isAllowed($admin, Actions::VIEW, $targetUser));
        $this->assertTrue($this->acl->isAllowed($admin, Actions::EDIT, $targetUser));
        $this->assertTrue($this->acl->isAllowed($admin, Actions::DELETE, $targetUser));
        $this->assertTrue($this->acl->isAllowed($admin, Actions::UNDELETE, $targetUser));
        $this->assertTrue($this->acl->isAllowed($admin, Actions::ALLOW, $targetUser));

        $this->userService->deleteUser($targetUser);
        $this->userService->deleteUser($admin);

        $this->entityManager->flush();
        $this->entityManager->clear();
    }
}
