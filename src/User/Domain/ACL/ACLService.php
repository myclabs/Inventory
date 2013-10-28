<?php

namespace User\Domain\ACL;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use RuntimeException;
use User\Domain\User;

/**
 * Droits d'accès.
 *
 * @author matthieu.napoli
 */
class ACLService
{
    /**
     * @var AuthorizationRepositoryInterface[]
     */
    private $authorizationRepositories = [];

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(EntityManager $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * Vérifie une autorisation d'accès à une ressource.
     *
     * Retourne un résultat sous forme de booléen (accès autorisé ou non).
     *
     * @param User          $user     Demandeur de l'accès
     * @param Action        $action   Action demandée
     * @param object|string $resource Entité ou nom de classe
     *
     * @throws RuntimeException
     * @return boolean
     */
    public function isAllowed(User $user, Action $action, $resource)
    {
        if (is_string($resource)) {
            // Si la ressource est un nom de classe
            $resourceName = $resource;
            $resource = null;
        } else {
            $resourceName = $this->entityManager->getClassMetadata(get_class($resource))->getName();
        }

        if (! isset($this->authorizationRepositories[$resourceName])) {
            throw new RuntimeException("No authorization repository registered for resource $resourceName");
        }

        $authorizationRepository = $this->authorizationRepositories[$resourceName];

        return $authorizationRepository->exists($user, $action, $resource);
    }

    /**
     * Enregistre un repository d'autorisations qui gère les autorisation d'une classe d'entité
     * @param string                           $resourceName Nom de la classe, ou nom abstrait de ressource
     * @param AuthorizationRepositoryInterface $authorizationRepository
     */
    public function setAuthorizationRepository($resourceName, AuthorizationRepositoryInterface $authorizationRepository)
    {
        $this->authorizationRepositories[$resourceName] = $authorizationRepository;
    }

    /**
     * Regénère la liste des autorisations.
     */
    public function rebuildAuthorizations()
    {
        // Vide les autorisations
        $query = $this->entityManager->createQuery('DELETE FROM ' . Authorization::class);
        $query->execute();
        $this->entityManager->clear(Authorization::class);

        /** @var User[] $users */
        $users = User::loadList();

        foreach ($users as $user) {
            $user->updateAuthorizations();
        }
    }
}
