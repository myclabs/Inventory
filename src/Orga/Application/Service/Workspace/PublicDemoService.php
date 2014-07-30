<?php

namespace Orga\Application\Service\Workspace;

use Doctrine\ORM\EntityManager;
use Exception;
use User\Domain\UserService;

/**
 * Service gérant la démo publique et gratuite.
 */
class PublicDemoService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var WorkspaceService
     */
    private $workspaceService;

    /**
     * @param EntityManager $entityManager
     * @param UserService $userService
     * @param WorkspaceService $workspaceService
     */
    public function __construct(
        EntityManager $entityManager,
        UserService $userService,
        WorkspaceService $workspaceService
    ) {
        $this->entityManager = $entityManager;
        $this->userService = $userService;
        $this->workspaceService = $workspaceService;
    }

    /**
     * @param $email
     * @param $password
     * @param $projectName
     */
    public function createDemoAccount($email, $password, $projectName)
    {
        $this->entityManager->beginTransaction();

        try {
            $user = $this->userService->createUser($email, $password);
            $user->initTutorials();

            //@todo Créer un Account avec un Workspace et donner des droits.

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
        }
    }
}
