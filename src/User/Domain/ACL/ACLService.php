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
            $entityClass = $resource;
            $resource = null;
        } else {
            $entityClass = $this->entityManager->getClassMetadata(get_class($resource))->getName();
        }

        if (! isset($this->authorizationRepositories[$entityClass])) {
            throw new RuntimeException("No authorization repository registered for entity of type $entityClass");
        }

        $authorizationRepository = $this->authorizationRepositories[$entityClass];

        return $authorizationRepository->exists($user, $action, $resource);
    }

    /**
     * Enregistre un repository d'autorisations qui gère les autorisation d'une classe d'entité
     * @param string                           $entityClass
     * @param AuthorizationRepositoryInterface $authorizationRepository
     */
    public function setAuthorizationRepository($entityClass, AuthorizationRepositoryInterface $authorizationRepository)
    {
        $this->authorizationRepositories[$entityClass] = $authorizationRepository;
    }

    /**
     * Regénère la liste des autorisations.
     */
    public function rebuildAuthorizations()
    {
        $this->emptyAuthorizations();

        /** @var User[] $users */
        $users = User::loadList();

        foreach ($users as $user) {
            foreach ($user->getRoles() as $role) {
                $user->replaceAuthorizations($role->getAuthorizations());
            }
        }
    }

    /**
     * Vide le filtre
     */
    private function emptyAuthorizations()
    {
        $query = $this->entityManager->createQuery('DELETE FROM ' . Authorization::class);
        $query->execute();
    }
}
