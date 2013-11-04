<?php

namespace Tests\User\ACL\Role;

use Core_Test_TestCase;
use User\Domain\ACL\Action;
use User\Domain\ACL\ACLService;
use User\Domain\ACL\Resource\NamedResource;
use User\Domain\User;
use User\Domain\UserService;

class UserRoleTest extends Core_Test_TestCase
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
        self::getEntityManager()->clear();
    }

    public function setUp()
    {
        parent::setUp();

        $this->user = $this->userService->createUser('user', 'user');
        $this->otherUser = $this->userService->createUser('otherUser', 'otherUser');

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
    public function testIsAllowed(Action $action, $resource, $value = true)
    {
        if ($resource === 'himself') {
            $resource = $this->user;
        }
        if ($resource === 'other') {
            $resource = $this->otherUser;
        }

        if ($value) {
            $this->assertTrue($resource->isAllowed($this->user, $action));
            $this->assertTrue($this->aclService->isAllowed($this->user, $action, $resource));
        } else {
            $this->assertFalse($resource->isAllowed($this->user, $action));
            $this->assertFalse($this->aclService->isAllowed($this->user, $action, $resource));
        }
    }

    public function dataProvider()
    {
        $repository = NamedResource::loadByName('repository');

        return [
            [Action::CREATE(), NamedResource::loadByName(User::class), false],

            // Sur lui-même
            [Action::VIEW(), 'himself'],
            [Action::EDIT(), 'himself'],
            [Action::DELETE(), 'himself'],
            [Action::UNDELETE(), 'himself', false],
            [Action::ALLOW(), 'himself', false],

            // Sur les autres
            [Action::VIEW(), 'other', false],
            [Action::EDIT(), 'other', false],
            [Action::DELETE(), 'other', false],
            [Action::UNDELETE(), 'other', false],
            [Action::ALLOW(), 'other', false],

            // Sur les référentiels de données
            [Action::VIEW(), $repository, true],
            [Action::EDIT(), $repository, false],
            [Action::DELETE(), $repository, false],
            [Action::UNDELETE(), $repository, false],
            [Action::ALLOW(), $repository, false],
        ];
    }
}
