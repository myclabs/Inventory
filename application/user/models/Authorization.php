<?php
/**
 * @author     matthieu.napoli
 * @package    User
 * @subpackage Model
 */

/**
 * Autorisation d'accès à une ressource
 * @package    User
 * @subpackage Model
 */
class User_Model_Authorization extends Core_Model_Entity
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
     * @var User_Model_SecurityIdentity
     */
    protected $identity;

    /**
     * Action sur la ressource
     * @var User_Model_Action
     */
    protected $action;

    /**
     * @var User_Model_Resource
     */
    protected $resource;


    /**
     * Retourne l'autorisation correspondant aux paramètres donnés, ou null si non trouvé.
     * @param User_Model_SecurityIdentity $identity
     * @param User_Model_Action   $action
     * @param User_Model_Resource $resource
     * @return User_Model_Authorization|null
     */
    public static function search(User_Model_SecurityIdentity $identity, User_Model_Action $action,
                                  User_Model_Resource $resource)
    {
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
     * @param User_Model_SecurityIdentity $identity
     * @param User_Model_Action           $action
     * @param User_Model_Resource         $resource
     */
    public function __construct(User_Model_SecurityIdentity $identity, User_Model_Action $action,
                                User_Model_Resource $resource)
    {
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
     * @return User_Model_Action
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Renvoie la ressource associée à l'autorisation
     * @return User_Model_Resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Renvoie le role associée à l'autorisation
     * @return User_Model_SecurityIdentity
     */
    public function getIdentity()
    {
        return $this->identity;
    }

}
