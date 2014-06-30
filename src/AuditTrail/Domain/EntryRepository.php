<?php
/**
 * @author matthieu.napoli
 */

namespace AuditTrail\Domain;

use AuditTrail\Domain\Context\OrganizationContext;
use Core\Domain\EntityRepository;
use DateTime;

/**
 * Audit trail entry repository
 */
interface EntryRepository extends EntityRepository
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

    /**
     * @param OrganizationContext $context
     * @param DateTime            $upTo
     * @param DateTime            $from
     * @return Entry[]
     */
    public function findUpToForOrganizationContext(OrganizationContext $context, DateTime $upTo, DateTime $from=null);

    /**
     * @param OrganizationContext $context
     * @param DateTime            $from
     * @return Entry[]
     */
    public function hasFromForOrganizationContext(OrganizationContext $context, DateTime $from=null);
}
