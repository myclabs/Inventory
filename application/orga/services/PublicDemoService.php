<?php

use Doctrine\ORM\EntityManager;
use User\Domain\UserService;

/**
 * Service gérant la démo publique et gratuite.
 */
class Orga_Service_PublicDemoService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var UserService
     */
    private $userService;

    public function __construct(
        EntityManager $entityManager,
        UserService $userService
    ) {
        $this->entityManager = $entityManager;
        $this->userService = $userService;
    }

    /**
     * @param string $email
     * @param string $password
     */
    public function createDemoAccount($email, $password)
    {
        $this->entityManager->beginTransaction();

        try {
            $user = $this->userService->createUser($email, $password);
            $user->initTutorials();

            // TODO : structure orga et ajout en tant que coordinateur

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
        }
    }
}
