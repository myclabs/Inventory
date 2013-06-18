<?php
/**
 * @author matthieu.napoli
 */

namespace AuditTrail\Domain;

use Doctrine\ORM\EntityRepository;

/**
 * Audit trail entry repository
 */
class EntryRepository extends EntityRepository
{
    /**
     * @param Entry $entry
     */
    public function add(Entry $entry)
    {
        $this->getEntityManager()->persist($entry);
    }
}
