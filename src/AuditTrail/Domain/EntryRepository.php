<?php
/**
 * @author matthieu.napoli
 */

namespace AuditTrail\Domain;

use Core\Model\EntityRepositoryInterface;

/**
 * Audit trail entry repository
 */
interface EntryRepository extends EntityRepositoryInterface
{
    /**
     * @param int $count
     * @return Entry[]
     */
    public function findLatest($count);
}
