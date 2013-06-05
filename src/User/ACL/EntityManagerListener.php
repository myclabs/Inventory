<?php
/**
 * @author  matthieu.napoli
 * @package User
 */

namespace User\ACL;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use User_Model_Authorization;
use User_Model_Resource_Entity;
use User_Model_Role;
use User_Model_User;
use User_Service_ACLFilter;

/**
 * Listener de l'EntityManager pour maintenir à jour le filtre des ACL
 *
 * @package User
 */
class EntityManagerListener
{

    /**
     * @var User_Service_ACLFilter
     */
    private $aclFilterService;

    /**
     * @var User_Model_Resource_Entity[]|Collection
     */
    private $resourcesToGenerate;

    /**
     * @var User_Model_User[]|Collection
     */
    private $usersToGenerate;


    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->aclFilterService = User_Service_ACLFilter::getInstance();
    }

    /**
     * @param OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        if (!$this->aclFilterService->enabled) {
            return;
        }

        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        $em->beginTransaction();

        $this->resourcesToGenerate = new ArrayCollection();
        $this->usersToGenerate = new ArrayCollection();

        // Tableau indexé par l'ID pour filtrer les doublons
        $resourcesToClean = [];
        // Tableau indexé par l'ID pour filtrer les doublons
        $usersToClean = [];

        // Créations
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            // Ressource
            if ($entity instanceof User_Model_Resource_Entity) {
                // Clean ressource (pour la sous-hérarchie)
                $this->aclFilterService->cleanForResource($entity);
                // À générer
                if (!$this->resourcesToGenerate->contains($entity)) {
                    $this->resourcesToGenerate->add($entity);
                }
            }
            // Autorisation
            if ($entity instanceof User_Model_Authorization) {
                $resource = $entity->getResource();
                if ($resource instanceof User_Model_Resource_Entity) {
                    // Clean ressource
                    $resourcesToClean[$resource->getId()] = $resource;
                    // À regénérer
                    if (!$this->resourcesToGenerate->contains($resource)) {
                        $this->resourcesToGenerate->add($resource);
                    }
                }
            }
            // Utilisateur
            if ($entity instanceof User_Model_User) {
                if (!$this->usersToGenerate->contains($entity)) {
                    $this->usersToGenerate->add($entity);
                }
            }
        }

        // Suppressions dans des collections
        foreach ($uow->getScheduledCollectionDeletions() as $col) {
            /** @var $col PersistentCollection */
            $owner = $col->getOwner();
            if ($owner instanceof User_Model_User && $col->getTypeClass()->getName() === 'User_Model_Role') {
                $usersToClean[$owner->getId()] = $owner;
                if (!$this->usersToGenerate->contains($owner)) {
                    $this->usersToGenerate->add($owner);
                }
            }
        }

        // MAJ dans des collections
        foreach ($uow->getScheduledCollectionUpdates() as $col) {
            /** @var $col PersistentCollection */
            $owner = $col->getOwner();
            if ($owner instanceof User_Model_User && $col->getTypeClass()->getName() === 'User_Model_Role') {
                $usersToClean[$owner->getId()] = $owner;
                if (!$this->usersToGenerate->contains($owner)) {
                    $this->usersToGenerate->add($owner);
                }
            }
        }

        // Suppressions
        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            // Ressource
            if ($entity instanceof User_Model_Resource_Entity) {
                // Clean ressource
                $resourcesToClean[$entity->getId()] = $entity;
                // À regénérer
                if (!$this->resourcesToGenerate->contains($entity)) {
                    $this->resourcesToGenerate->add($entity);
                }
            }
            // Autorisation
            if ($entity instanceof User_Model_Authorization) {
                $resource = $entity->getResource();
                if ($resource instanceof User_Model_Resource_Entity) {
                    // Clean ressource
                    $resourcesToClean[$resource->getId()] = $resource;
                    // À regénérer
                    if (!$this->resourcesToGenerate->contains($resource)) {
                        $this->resourcesToGenerate->add($resource);
                    }
                }
            }
            // Role
            if ($entity instanceof User_Model_Role) {
                // Clean et regénère tous les utilisateurs du rôle
                foreach ($entity->getUsers() as $user) {
                    $usersToClean[$user->getId()] = $user;
                    if (!$this->usersToGenerate->contains($user)) {
                        $this->usersToGenerate->add($user);
                    }
                }
            }
            // Utilisateur
            if ($entity instanceof User_Model_User) {
                $usersToClean[$entity->getId()] = $entity;
                // Assure que l'utilisateur n'est pas à regénérer
                if ($this->usersToGenerate->contains($entity)) {
                    $this->usersToGenerate->removeElement($entity);
                }
            }
        }

        // Clean des entrées ACL
        foreach ($resourcesToClean as $resource) {
            $this->aclFilterService->cleanForResource($resource);
        }
        foreach ($usersToClean as $user) {
            $this->aclFilterService->cleanForUser($user);
        }
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        if (!$this->aclFilterService->enabled) {
            return;
        }

        $objects = array_merge($this->resourcesToGenerate->toArray(), $this->usersToGenerate->toArray());

        $this->aclFilterService->generateFor($objects);

        $args->getEntityManager()->commit();

        unset($objects);
        $this->resourcesToGenerate->clear();
        $this->usersToGenerate->clear();
    }

}
