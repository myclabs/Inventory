<?php

namespace Tests\User;

use Core\Test\TestCase;
use Core_Exception_InvalidArgument;
use Core_Exception_NotFound;
use Core_Tools;
use User\Domain\User;
use User\Domain\UserService;

class UserServiceTest extends TestCase
{
    /**
     * @Inject
     * @var UserService
     */
    private $userService;

    /**
     * Test de la méthode de login
     */
    public function testLogin()
    {
        $user = $this->userService->createUser(Core_Tools::generateString(20), 'test');
        $this->entityManager->flush();

        $o = $this->userService->login($user->getEmail(), 'test');
        $this->assertTrue($o instanceof User);
        $this->assertEquals($o->getId(), $user->getId());

        $this->userService->deleteUser($user);
        $this->entityManager->flush();
    }

    /**
     * Test de la méthode de login
     * @expectedException Core_Exception_InvalidArgument
     */
    public function testWrongPassword()
    {
        $user = $this->userService->createUser(Core_Tools::generateString(20), 'test');
        $this->entityManager->flush();

        try {
            $this->userService->login($user->getEmail(), 'mauvais-password');
        } catch (Core_Exception_InvalidArgument $e) {
            $this->userService->deleteUser($user);
            $this->entityManager->flush();
            throw $e;
        }
    }

    /**
     * Test de la méthode de login
     * @expectedException Core_Exception_NotFound
     */
    public function testWrongLogin()
    {
        $user = $this->userService->createUser(Core_Tools::generateString(20), 'test');
        $this->entityManager->flush();

        try {
            $this->userService->login('foo', 'test');
        } catch (Core_Exception_NotFound $e) {
            $this->userService->deleteUser($user);
            $this->entityManager->flush();
            throw $e;
        }
    }
}
