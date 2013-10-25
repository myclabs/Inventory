<?php

namespace User\Domain\ACL;

use User\Domain\ACL\Resource\EntityResource;

/**
 * Service qui résoud les ressources parentes et filles
 *
 * Implémenter pour définir des règles d'héritage de ressources, et ajouter au service ACLService
 *
 * @author matthieu.napoli
 *
 * @see    User_Service_ACL
 */
interface ResourceTreeTraverser
{

    /**
     * Trouve toutes les ressources parent d'une ressource (récursivement)
     *
     * @param EntityResource $resource
     *
     * @return EntityResource[]
     */
    public function getAllParentResources(EntityResource $resource);

    /**
     * Trouve toutes les ressources filles d'une ressource (récursivement)
     *
     * @param EntityResource $resource
     *
     * @return EntityResource[]
     */
    public function getAllChildResources(EntityResource $resource);
}
