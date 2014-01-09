<?php
/**
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage ORM
 */

use Doctrine\DBAL\DBALException;

/**
 * @package    Core
 * @subpackage ORM
 */
class Core_ORM_DBALExceptionParser
{

    /**
     * Foreign key constraint violation
     */
    const MYSQL_FOREIGN_KEY_VIOLATION = 1451;

    /**
     * Duplicate entry for a unique key
     */
    const MYSQL_DUPLICATE_ENTRY = 1062;

    /**
     * A column cannot be null
     */
    const MYSQL_NOT_NULL_VIOLATION = 1048;

    /**
     * @var DBALException
     */
    protected $dbException;

    /**
     * @var int SQL error number
     */
    protected $sqlError;

    /**
     * @param Doctrine\DBAL\DBALException $dbException
     */
    public function __construct(Doctrine\DBAL\DBALException $dbException)
    {
        $this->dbException = $dbException;
        // Sql error
        $matches = [];
        $result = preg_match('/SQLSTATE\[[0-9]+\]: [A-z0-9\s]+: ([0-9]+) /',
                             $this->dbException->getMessage(),
                             $matches);
        if ($result === 1) {
            $this->sqlError = $matches[1];
        }
    }

    /**
     * @return bool True if the exception is because of a foreign key constraint violation
     */
    public function isForeignKeyViolation()
    {
        return ($this->sqlError == self::MYSQL_FOREIGN_KEY_VIOLATION);
    }

    /**
     * Returns the Foreign Key Violation exception
     * @return Core_ORM_ForeignKeyViolationException
     */
    public function getForeignKeyViolationException()
    {
        // Extract the table and column names from the exception message
        $matches = [];
        $result = preg_match('/\(`[A-z0-9_]+`\.`([A-z0-9_]+)`, CONSTRAINT `[A-z0-9_]+`'
                                 . ' FOREIGN KEY \(`([A-z0-9_]+)`\)'
                                 . ' REFERENCES `([A-z0-9_]+)` \(`([A-z0-9_]+)`\)\)/',
                             $this->dbException->getMessage(),
                             $matches);
        if (($result === 1) && (count($matches) >= 5)) {
            $sourceEntity = $this->getClassNameFromTableName($matches[1]) ?: $matches[1];
            $sourceField = $this->getFieldNameFromColumnName($sourceEntity, $matches[2]) ?: $matches[2];
            $referencedEntity = $this->getClassNameFromTableName($matches[3]) ?: $matches[3];
            $referencedField = $this->getFieldNameFromColumnName($referencedEntity, $matches[4]) ?: $matches[4];
        } else {
            $sourceEntity = null;
            $sourceField = null;
            $referencedEntity = null;
            $referencedField = null;
        }
        return new Core_ORM_ForeignKeyViolationException($sourceEntity, $sourceField, $referencedEntity,
                                                         $referencedField, $this->dbException);
    }

    /**
     * @return bool True if the exception is because of a duplicate entry for a unique key
     */
    public function isDuplicateEntry()
    {
        return ($this->sqlError == self::MYSQL_DUPLICATE_ENTRY);
    }

    /**
     * Returns the Duplicate Entry exception
     * @return Core_ORM_DuplicateEntryException
     */
    public function getDuplicateEntryException()
    {
        $matches = [];
        $result = preg_match("/Duplicate entry '([^']*)' for key '([^']+)'/",
                             $this->dbException->getMessage(),
                             $matches);
        if (($result === 1) && (count($matches) >= 3)) {
            $entry = $matches[1];
            $key = $matches[2];
        } else {
            $entry = null;
            $key = null;
        }
        return new Core_ORM_DuplicateEntryException($entry, $key, $this->dbException);
    }

    /**
     * @return bool True if the exception is because of a null inserted in a not null column
     */
    public function isNotNullViolation()
    {
        return ($this->sqlError == self::MYSQL_NOT_NULL_VIOLATION);
    }

    /**
     * Returns the Not Null Violation exception
     * @return Core_ORM_NotNullViolationException
     */
    public function getNotNullViolationException()
    {
        $matches = [];
        $result = preg_match("/Column '([^']+)' cannot be null/",
                             $this->dbException->getMessage(),
                             $matches);
        if (($result === 1) && (count($matches) >= 2)) {
            $column = $matches[1];
        } else {
            $column = null;
        }
        return new Core_ORM_NotNullViolationException($column, $this->dbException);
    }

    /**
     * @param string $table Table name
     * @return string Entity class name, null if not found
     */
    protected function getClassNameFromTableName($table)
    {
        $em = \Core\ContainerSingleton::getEntityManager();
        // Go through all the classes
        $classNames = $em->getConfiguration()->getMetadataDriverImpl()->getAllClassNames();
        foreach ($classNames as $className) {
            $classMetaData = $em->getClassMetadata($className);
            if (strtolower($table) == strtolower($classMetaData->getTableName())) {
                return $classMetaData->getName();
            }
        }
        return null;
    }

    /**
     * @param string $className
     * @param string $column
     * @return string Field name, null if not found
     */
    protected function getFieldNameFromColumnName($className, $column)
    {
        if (!$className || !class_exists($className)) {
            return null;
        }
        $em = \Core\ContainerSingleton::getEntityManager();
        $classMetaData = $em->getClassMetadata($className);
        if ($classMetaData) {
            return $classMetaData->getFieldForColumn($column);
        }
        return null;
    }

}
