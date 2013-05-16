<?php
/**
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage ORM
 */

use Doctrine\DBAL\DBALException;

/**
 * Class representing a DB Foreign Key Violation
 * @package    Core
 * @subpackage ORM
 */
class Core_ORM_ForeignKeyViolationException extends DBALException
{

    /**
     * @var string
     */
    protected $sourceEntity;

    /**
     * @var string
     */
    protected $sourceField;

    /**
     * @var string
     */
    protected $referencedEntity;

    /**
     * @var string
     */
    protected $referencedField;


    /**
     * @param string         $sourceEntity
     * @param string         $sourceField
     * @param string         $referencedEntity
     * @param string         $referencedField
     * @param Exception|null $previous Previous exception
     */
    public function __construct($sourceEntity, $sourceField, $referencedEntity, $referencedField,
                                Exception $previous = null
    ) {
        $previousMessage = $previous ? $previous->getMessage() : '';
        $message = "Foreign key constraint violation on $sourceEntity.$sourceField referencing"
            . " $referencedEntity.$referencedField."
            . PHP_EOL . PHP_EOL . $previousMessage;
        parent::__construct($message, 0, $previous);
        $this->sourceEntity = $sourceEntity;
        $this->sourceField = $sourceField;
        $this->referencedEntity = $referencedEntity;
        $this->referencedField = $referencedField;
    }

    /**
     * @return string Name of the entity source of the foreign key
     */
    public function getSourceEntity()
    {
        return $this->sourceEntity;
    }

    /**
     * Checks if the source entity is an instance of the class name given
     * @param string $className
     * @return bool True if the source entity of the violation is a subclass of $className
     */
    public function isSourceEntityInstanceOf($className)
    {
        return $this->isInstanceOf($this->sourceEntity, $className);
    }

    /**
     * @return string
     */
    public function getSourceField()
    {
        return $this->sourceField;
    }

    /**
     * @return string Name of the entity referenced by the foreign key
     */
    public function getReferencedEntity()
    {
        return $this->referencedEntity;
    }

    /**
     * Checks if the referenced entity is an instance of the class name given
     * @param string $className
     * @return bool True if the referenced entity of the foreign key is a subclass of $className
     */
    public function isReferencedEntityInstanceOf($className)
    {
        return $this->isInstanceOf($this->referencedEntity, $className);
    }

    /**
     * @return string
     */
    public function getReferencedField()
    {
        return $this->referencedField;
    }

    /**
     * @param string $className1
     * @param string $className2
     * @return bool True if $className1 is a subclass of $className2
     */
    protected function isInstanceOf($className1, $className2)
    {
        // Same class
        if ($className1 == $className2) {
            return true;
        }
        if (! class_exists($className1)) {
            return false;
        }
        // Sub class
        $sourceEntityReflection = new ReflectionClass($className1);
        return $sourceEntityReflection->isSubclassOf($className2);
    }

}
