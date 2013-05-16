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
     * Trouve les ressources parent d'une ressource
     *
     * @param User_Model_Resource_Entity $resource
     *
     * @return User_Model_Resource_Entity[] Tableau indexé par l'ID de chaque ressource pour éviter les doublons
     */
    function getParentResources(User_Model_Resource_Entity $resource);

    /**
     * Trouve les ressources filles d'une ressource
     *
     * @param User_Model_Resource_Entity $resource
     *
     * @return User_Model_Resource_Entity[] Tableau indexé par l'ID de chaque ressource pour éviter les doublons
     */
    function getChildResources(User_Model_Resource_Entity $resource);

}
