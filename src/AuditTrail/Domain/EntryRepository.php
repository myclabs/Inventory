<?php
/**
 * @author matthieu.napoli
 */

namespace AuditTrail\Domain;

use AuditTrail\Domain\Context\WorkspaceContext;
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
     * @param WorkspaceContext $context
     * @param int                 $count
     * @return Entry[]
     */
    public function findLatestForWorkspaceContext(WorkspaceContext $context, $count);

    /**
     * @param WorkspaceContext $context
     * @param DateTime            $upTo
     * @param DateTime            $from
     * @return Entry[]
     */
    public function findUpToForWorkspaceContext(WorkspaceContext $context, DateTime $upTo, DateTime $from=null);

    /**
     * @param WorkspaceContext $context
     * @param DateTime            $from
     * @return Entry[]
     */
    public function hasFromForWorkspaceContext(WorkspaceContext $context, DateTime $from=null);
}
