<?php

namespace User\Domain\ACL;

use Core_Model_Entity;
use Core_Model_Query;
use User\Domain\ACL\Action\Action;
use User\Domain\ACL\Resource;
use Zend_Registry;

/**
 * Autorisation d'accès à une ressource.
 *
 * @author matthieu.napoli
 */
class Authorization extends Core_Model_Entity
{
    /**#@+
     * Constante de tri et filtre
     */
    const QUERY_IDENTITY = 'identity';
    const QUERY_ACTION = 'action';
    const QUERY_RESOURCE = 'resource';
    /**#@-*/

    /**
     * @var int
     */
    protected $id;

    /**
     * @var SecurityIdentity
     */
    protected $identity;

    /**
     * Action sur la ressource
     * @var Action
     */
    protected $action;

    /**
     * @var Resource
     */
    protected $resource;

    /**
     * Retourne l'autorisation correspondant aux paramètres donnés, ou null si non trouvé.
     * @param SecurityIdentity $identity
     * @param Action           $action
     * @param Resource         $resource
     * @return Authorization|null
     */
    public static function search(
        SecurityIdentity $identity,
        Action $action,
        Resource $resource
    ) {
        $query = new Core_Model_Query();
        $query->filter->addCondition(self::QUERY_IDENTITY, $identity);
        // TODO Bug dans Doctrine (http://www.doctrine-project.org/jira/browse/DDC-2290)
        $type = \Doctrine\DBAL\Types\Type::getType('user_action');
        /** @var $entityManager \Doctrine\ORM\EntityManager */
        $entityManager = Zend_Registry::get('EntityManagers')['default'];
        $platform = $entityManager->getConnection()->getDatabasePlatform();
        $query->filter->addCondition(self::QUERY_ACTION, $type->convertToDatabaseValue($action, $platform));
        $query->filter->addCondition(self::QUERY_RESOURCE, $resource);
        $list = self::loadList($query);
        if (count($list) == 0) {
            return null;
        }
        return current($list);
    }

    /**
     * @param SecurityIdentity $identity
     * @param Action           $action
     * @param Resource         $resource
     */
    public function __construct(
        SecurityIdentity $identity,
        Action $action,
        Resource $resource
    ) {
        if ($identity) {
            $this->identity = $identity;
            $identity->addAuthorization($this);
        }
        if ($action) {
            $this->action = $action;
        }
        if ($resource) {
            $this->resource = $resource;
            $resource->addAuthorization($this);
        }
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Action
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Renvoie la ressource associée à l'autorisation
     * @return Resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Renvoie le role associée à l'autorisation
     * @return SecurityIdentity
     */
    public function getIdentity()
    {
        return $this->identity;
    }
}
