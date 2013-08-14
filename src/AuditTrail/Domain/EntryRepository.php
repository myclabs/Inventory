<?php

namespace AuditTrail\Domain;

use AuditTrail\Domain\Context\OrganizationContext;
use Core\Model\EntityRepositoryInterface;

/**
 * Audit trail entry repository
 *
 * @author matthieu.napoli
 */
interface EntryRepository extends EntityRepositoryInterface
{
    /**
     * @param int $count
     * @return Entry[]
     */
    public function findLatest($count);

    /**
     * @param OrganizationContext $context
     * @param int                 $count
     * @return Entry[]
     */
    public function findLatestForOrganizationContext(OrganizationContext $context, $count);
}
