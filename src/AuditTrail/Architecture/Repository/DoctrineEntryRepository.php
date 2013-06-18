<?php
/**
 * @author matthieu.napoli
 */

namespace AuditTrail\Architecture\Repository;

use AuditTrail\Domain\EntryRepository;
use Core\Model\EntityRepository;

/**
 * Audit trail entry repository
 */
class DoctrineEntryRepository extends EntityRepository implements EntryRepository
{
}
