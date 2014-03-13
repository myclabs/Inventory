<?php

namespace User\Domain\ACL;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use MyCLabs\ACL\Model\Actions;
use MyCLabs\ACL\Model\Role;
use User\Domain\User;

class ACLUserListener
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var UnitOfWork
     */
    private $uow;

    /**
     * Stores the users that are scheduled for insertion.
     *
     * @var User[]
     */
    private $newUsers = [];

    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $this->em = $eventArgs->getEntityManager();
        $this->uow = $this->em->getUnitOfWork();

        // Remember new users
        $this->newUsers = [];
        foreach ($this->uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof User) {
                $this->newUsers[] = $entity;
            }
        }

        // Process new roles and resources
        foreach ($this->uow->getScheduledEntityInsertions() as $entity) {
            if ($this->supportsResource($entity)) {
                $this->processNewResource($entity);
            } elseif ($this->supportsRole($entity)) {
                $this->processNewRole($entity);
            }
        }

        $this->newUsers = [];
    }

    private function supportsResource($entity)
    {
        return $entity instanceof User;
    }

    private function supportsRole($entity)
    {
        return $entity instanceof AdminRole;
    }

    private function processNewRole(Role $role)
    {
        $authorizations = [];

        if ($role instanceof AdminRole) {
            $authorizations = [UserAuthorization::create($role, new Actions([
                Actions::CREATE,
                Actions::VIEW,
                Actions::EDIT,
                Actions::DELETE,
                Actions::UNDELETE,
                Actions::ALLOW,
            ]))];
            $authorizations = array_merge($authorizations, $this->inherit($authorizations));
        }

        foreach ($authorizations as $authorization) {
            $this->persistNewEntity($authorization);
        }

        // TODO accounts, organizations, cells…
    }

    private function processNewResource(User $user)
    {
        $authorizations = [];

        // Inherits from the authorizations on "all articles"
        $repository = $this->em->getRepository(AdminRole::class);
        foreach ($repository->findAll() as $role) {
            /** @var AdminRole $role */
            foreach ($role->getRootAuthorizations() as $parentAuthorization) {
                $authorizations[] = UserAuthorization::createChildAuthorization($parentAuthorization, $user);
            }
        }

        foreach ($authorizations as $authorization) {
            $this->persistNewEntity($authorization);
        }
    }

    /**
     * @param UserAuthorization[] $parentAuthorizations
     * @return UserAuthorization[]
     */
    private function inherit(array $parentAuthorizations)
    {
        $authorizations = [];

        $usersRepository = $this->em->getRepository(User::class);
        $allUsers = array_merge($usersRepository->findAll(), $this->newUsers);

        foreach ($parentAuthorizations as $parentAuthorization) {
            foreach ($allUsers as $article) {
                $authorizations[] = UserAuthorization::createChildAuthorization($parentAuthorization, $article);
            }
        }

        return $authorizations;
    }

    private function persistNewEntity($entity)
    {
        $this->em->persist($entity);
        $this->uow->computeChangeSet($this->em->getClassMetadata(get_class($entity)), $entity);
    }
}
