<?php
/**
 * @author     matthieu.napoli
 * @package    User
 * @subpackage Service
 */

/**
 * Service qui résoud les ressources parentes et filles
 *
 * Implémenter pour définir des règles d'héritage de ressources, et ajouter au service User_Service_ACL
 *
 * @package    User
 * @subpackage Service
 *
 * @see User_Service_ACL
 */
interface User_Service_ACL_ResourceTreeTraverser
{

    /**
     * Trouve toutes les ressources parent d'une ressource (récursivement)
     *
     * @param User_Model_Resource_Entity $resource
     *
     * @return User_Model_Resource_Entity[]
     */
    function getAllParentResources(User_Model_Resource_Entity $resource);

    /**
     * Trouve toutes les ressources filles d'une ressource (récursivement)
     *
     * @param User_Model_Resource_Entity $resource
     *
     * @return User_Model_Resource_Entity[]
     */
    function getAllChildResources(User_Model_Resource_Entity $resource);

}
