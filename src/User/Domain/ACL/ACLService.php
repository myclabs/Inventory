<?php

namespace User\Domain\ACL;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use User\Domain\ACL\Authorization\Authorization;
use User\Domain\ACL\Resource\Resource;
use User\Domain\User;

/**
 * Droits d'accès.
 *
 * @author matthieu.napoli
 */
class ACLService
{
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
     * @param User     $user     Demandeur de l'accès
     * @param Action   $action   Action demandée
     * @param Resource $resource Resource
     *
     * @return boolean
     */
    public function isAllowed(User $user, Action $action, Resource $resource)
    {
        return $resource->isAllowed($user, $action);
    }

    /**
     * Regénère la liste des autorisations.
     */
    public function rebuildAuthorizations()
    {
        // Vide les autorisations
//        $query = $this->entityManager->createQuery('DELETE FROM ' . Authorization::class);
//        $query->execute();
//        $this->entityManager->clear(Authorization::class);

        /** @var User[] $users */
        $users = User::loadList();

        foreach ($users as $user) {
            $user->updateAuthorizations();
        }
    }
}
