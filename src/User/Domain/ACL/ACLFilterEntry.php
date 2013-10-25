<?php

namespace User\Domain\ACL;

use Core_Model_Entity;
use User\Domain\ACL\Action\Action;
use User\Domain\ACL\Resource\EntityResource;
use User\Domain\User;

/**
 * Entrée du filtre d'autorisation d'accès à une ressource.
 *
 * Cette "autorisation cachée" n'autorise pas d'héritage de ressource car elle est
 * déjà dénormalisée (i.e. elle représente l'héritage de manière dénormalisée).
 *
 * @author matthieu.napoli
 */
class ACLFilterEntry extends Core_Model_Entity
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $idUser;

    /**
     * @var Action
     */
    protected $action;

    /**
     * @var int
     */
    protected $entityIdentifier;

    /**
     * @var string
     */
    protected $entityName;


    public function __construct(User $user, Action $action, EntityResource $resource)
    {
        $this->idUser = $user->getId();
        $this->action = $action;
        $this->entityName = $resource->getEntityName();
        $this->entityIdentifier = $resource->getEntityIdentifier();
    }

    /**
     * @return int
     */
    public function getIdUser()
    {
        return $this->idUser;
    }

    /**
     * @return Action
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return int
     */
    public function getEntityIdentifier()
    {
        return $this->entityIdentifier;
    }

    /**
     * @return string
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * @return string
     */
    public function getUniqueKey()
    {
        return sprintf(
            "%s-%s-%s-%s",
            $this->idUser,
            $this->action->getValue(),
            $this->entityName,
            $this->entityIdentifier
        );
    }
}
