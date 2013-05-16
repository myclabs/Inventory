<?php
/**
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage ORM
 */

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Proxy\ProxyFactory;
use Doctrine\ORM\Query\FilterCollection;

/**
 * Doctrine EntityManager extension
 * @package    Core
 * @subpackage ORM
 */
class Core_ORM_EntityManager extends EntityManager
{

    /**
     * {@inheritdoc}
     *
     * Copy-paste of Doctrine's parent method, waiting for DDC-2196
     * @link http://www.doctrine-project.org/jira/browse/DDC-2196
     *
     * DO NOT EDIT
     */
    public static function create($conn, Configuration $config, EventManager $eventManager = null)
    {
        if ( ! $config->getMetadataDriverImpl()) {
            throw ORMException::missingMappingDriverImpl();
        }

        switch (true) {
            case (is_array($conn)):
                $conn = \Doctrine\DBAL\DriverManager::getConnection(
                    $conn, $config, ($eventManager ?: new EventManager())
                );
                break;

            case ($conn instanceof Connection):
                if ($eventManager !== null && $conn->getEventManager() !== $eventManager) {
                    throw ORMException::mismatchedEventManager();
                }
                break;

            default:
                throw new \InvalidArgumentException("Invalid argument: " . $conn);
        }

        return new static($conn, $config, $conn->getEventManager());
    }

    /**
     * {@inheritdoc}
     *
     * @throws Core_ORM_ForeignKeyViolationException A foreign key constraint was violated
     */
    public function flush($entity = null)
    {
        try {
            parent::flush($entity);
        } catch (DBALException $e) {
            $exceptionParser = new Core_ORM_DBALExceptionParser($e);
            if ($exceptionParser->isForeignKeyViolation()) {
                throw $exceptionParser->getForeignKeyViolationException();
            }
            if ($exceptionParser->isDuplicateEntry()) {
                throw $exceptionParser->getDuplicateEntryException();
            }
            if ($exceptionParser->isNotNullViolation()) {
                throw $exceptionParser->getNotNullViolationException();
            }
            throw $e;
        }
    }

}
