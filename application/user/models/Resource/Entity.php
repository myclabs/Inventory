<?php
/**
 * @author     matthieu.napoli
 * @package    User
 * @subpackage Model
 */

/**
 * Ressource de type Entité
 * @package    User
 * @subpackage Model
 */
class User_Model_Resource_Entity extends User_Model_Resource
{

    const QUERY_ENTITY_NAME = 'entityName';
    const QUERY_ENTITY_IDENTIFIER = 'entityIdentifier';

    /**
     * @var string
     */
    protected $entityName;

    /**
     * @var mixed
     */
    protected $entityIdentifier;


    /**
     * Retourne une instance de la classe ressource lié à une entité spécifique donnée.
     *
     * @param Core_Model_Entity $entity
     *
     * @return User_Model_Resource_Entity|null
     */
    public static function loadByEntity($entity)
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(self::QUERY_ENTITY_NAME, self::getEntityNameFromEntity($entity));
        $query->filter->addCondition(self::QUERY_ENTITY_IDENTIFIER, self::getEntityIdentifierFromEntity($entity));
        $list = self::loadList($query);
        if (count($list) == 0) {
            return null;
        }
        return current($list);
    }

    /**
     * Retourne une instance de la classe ressource lié à un nom d'entité seulement
     *
     * @param string $entityName
     *
     * @return User_Model_Resource_Entity|null
     */
    public static function loadByEntityName($entityName)
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(self::QUERY_ENTITY_NAME, $entityName);
        $query->filter->addCondition(self::QUERY_ENTITY_IDENTIFIER, null, Core_Model_Filter::OPERATOR_NULL);
        $list = self::loadList($query);
        if (count($list) == 0) {
            return null;
        }
        return current($list);
    }

    /**
     * @param Core_Model_Entity $entity
     * @throws Core_Exception
     */
    public function setEntity(Core_Model_Entity $entity)
    {
        // Modification interdite
        if ($this->entityName) {
            throw new Core_Exception("The resource cannot be modified");
        }
        $this->entityName = self::getEntityNameFromEntity($entity);
        $this->entityIdentifier = self::getEntityIdentifierFromEntity($entity);
    }

    /**
     * @throws Core_Exception_UndefinedAttribute No entity was set for this resource
     * @return Core_Model_Entity|null
     */
    public function getEntity()
    {
        $entityName = $this->getEntityName();
        if (empty($entityName)) {
            return null;
        }
        $identifier = $this->getEntityIdentifier();
        if ($identifier != null) {
            return $entityName::load($identifier);
        }
        return null;
    }

    /**
     * @return string
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * @param string $entityName Name of the entity
     * @throws Core_Exception
     */
    public function setEntityName($entityName)
    {
        // Modification interdite
        if ($this->entityName) {
            throw new Core_Exception("The resource cannot be modified");
        }
        $this->entityName = $entityName;
    }

    /**
     * @return mixed
     */
    public function getEntityIdentifier()
    {
        return $this->entityIdentifier;
    }

    /**
     * @param Core_Model_Entity $entity
     * @return string Entity name
     */
    private static function getEntityNameFromEntity(Core_Model_Entity $entity)
    {
        if ($entity instanceof \Doctrine\ORM\Proxy\Proxy) {
            return get_parent_class($entity);
        }
        return get_class($entity);
    }

    /**
     * @param Core_Model_Entity $entity
     * @throws Core_Exception
     * @throws Core_ORM_NotPersistedEntityException
     * @return int Entity identifier
     */
    private static function getEntityIdentifierFromEntity(Core_Model_Entity $entity)
    {
        $identifier = $entity->getKey();
        if (count($identifier) == 0) {
            throw new Core_ORM_NotPersistedEntityException("The entity has no identity (it is not persisted)");
        }
        // ID composite : cas pas supporté pour le moment
        if (count($identifier) > 1) {
            throw new Core_Exception("Unsupported ACL case: composite ID for " . get_class($entity));
        }
        // ID nommé autrement que "id" : cas pas supporté pour le moment
        if (!isset($identifier['id'])) {
            throw new Core_Exception("Unsupported ACL case: ID of " . get_class($entity) . " is not named 'id'");
        }
        // Cas normal : "id"
        return (int) $identifier['id'];
    }

}
