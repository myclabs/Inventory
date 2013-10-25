<?php

namespace User\Domain\ACL;

use User\Domain\ACL\Resource\EntityResource;
use User\Domain\User;

/**
 * Parcourt l'héritage des ressources de type User.
 *
 * @author matthieu.napoli
 */
class UsersResourceTreeTraverser implements ResourceTreeTraverser
{
    /**
     * {@inheritdoc}
     */
    public function getAllParentResources(EntityResource $resource)
    {
        $parentResources = [];

        $entity = $resource->getEntity();

        // Si la ressource représente un utilisateur
        if ($entity instanceof User) {

            // Pour chaque role, ressource : "Tous les utilisateurs du role X"
            foreach ($entity->getRoles() as $role) {
                $roleResource = EntityResource::loadByEntity($role);
                if ($roleResource) {
                    $parentResources[] = $roleResource;
                }
            }

        }

        // Si la ressource représente un utilisateur ou un role
        if ($entity instanceof User || $entity instanceof Role) {

            // La ressource "Tous les utilisateurs"
            $allUsersResource = EntityResource::loadByEntityName(User::class);
            if ($allUsersResource) {
                $parentResources[] = $allUsersResource;
            }

        }

        return $parentResources;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllChildResources(EntityResource $resource)
    {
        $children = [];

        $entity = $resource->getEntity();

        // Si la ressource représente "Tous les utilisateurs", ajoute tous les roles et tous les utilisateurs
        if ($entity === null) {

            // Tous les roles
            $roles = Role::loadList();
            foreach ($roles as $role) {
                $resource = EntityResource::loadByEntity($role);
                if ($resource) {
                    $children[] = $resource;
                }
            }

            // Tous les utilisateurs
            $users = User::loadList();
            foreach ($users as $user) {
                $resource = EntityResource::loadByEntity($user);
                if ($resource) {
                    $children[] = $resource;
                }
            }

        }

        // Si la ressource représente un role, ajoute tous les utilisateurs du role
        if ($entity instanceof Role) {

            // Tous les utilisateurs du role
            $users = $entity->getUsers();
            foreach ($users as $user) {
                $resource = EntityResource::loadByEntity($user);
                if ($resource) {
                    $children[] = $resource;
                }
            }

        }

        return $children;
    }
}
