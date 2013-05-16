<?php
/**
 * @author     matthieu.napoli
 * @package    User
 * @subpackage Service
 */

/**
 * Droits d'accès
 * @package    User
 * @subpackage Service
 */
class User_Service_ACL_UsersResourceTreeTraverser extends Core_Singleton
    implements User_Service_ACL_ResourceTreeTraverser
{

    /**
     * {@inheritdoc}
     */
    public function getParentResources(User_Model_Resource_Entity $resource)
    {
        // Tableau indexé par l'ID pour éviter les doublons
        $parentResources = [];

        $entity = $resource->getEntity();

        // Si la ressource représente un utilisateur
        if ($entity instanceof User_Model_User) {

            // Pour chaque role, ressource : "Tous les utilisateurs du role X"
            foreach ($entity->getRoles() as $role) {
                $roleResource = User_Model_Resource_Entity::loadByEntity($role);
                if ($roleResource) {
                    // Tableau indexé par l'ID pour éviter les doublons
                    $parentResources[$roleResource->getId()] = $roleResource;
                }
            }

        }

        // Si la ressource représente un utilisateur ou un role
        if ($entity instanceof User_Model_User || $entity instanceof User_Model_Role) {

            // La ressource "Tous les utilisateurs"
            $allUsersResource = User_Model_Resource_Entity::loadByEntityName("User_Model_User");
            if ($allUsersResource) {
                $parentResources[0] = $allUsersResource;
            }

        }

        return $parentResources;
    }

    /**
     * {@inheritdoc}
     */
    function getChildResources(User_Model_Resource_Entity $resource)
    {
        // Tableau indexé par l'ID pour éviter les doublons
        $children = [];

        $entity = $resource->getEntity();

        // Si la ressource représente "Tous les utilisateurs", ajoute tous les roles et tous les utilisateurs
        if ($entity === null) {
            // Tous les utilisateurs
            $users = User_Model_User::loadList();
            foreach ($users as $user) {
                $resource = User_Model_Resource_Entity::loadByEntity($user);
                if ($resource) {
                    // Tableau indexé par l'ID pour éviter les doublons
                    $children[$resource->getId()] = $resource;
                }
            }
        }

        // Si la ressource représente un role, ajoute tous les utilisateurs du role
        if ($entity instanceof User_Model_Role) {
            // Tous les utilisateurs du role
            $users = $entity->getUsers();
            foreach ($users as $user) {
                $resource = User_Model_Resource_Entity::loadByEntity($user);
                if ($resource) {
                    // Tableau indexé par l'ID pour éviter les doublons
                    $children[$resource->getId()] = $resource;
                }
            }
        }

        return $children;
    }

}
