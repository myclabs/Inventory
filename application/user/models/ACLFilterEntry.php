<?php
/**
 * @author     matthieu.napoli
 * @package    User
 * @subpackage Model
 */

/**
 * Entrée du filtre d'autorisation d'accès à une ressource
 *
 * Cette "autorisation cachée" n'autorise pas d'héritage de ressource car elle est
 * déjà dénormalisée (i.e. elle représente l'héritage de manière dénormalisée)
 *
 * @package    User
 * @subpackage Model
 */
class User_Model_ACLFilterEntry extends Core_Model_Entity
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
     * @var User_Model_Action
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


    /**
     * @param User_Model_User            $user
     * @param User_Model_Action          $action
     * @param User_Model_Resource_Entity $resource
     */
    public function __construct(User_Model_User $user, User_Model_Action $action, User_Model_Resource_Entity $resource)
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
     * @return User_Model_Action
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
        return sprintf("%s-%s-%s-%s", $this->idUser, $this->action->getValue(),
                       $this->entityName, $this->entityIdentifier);
    }

}
