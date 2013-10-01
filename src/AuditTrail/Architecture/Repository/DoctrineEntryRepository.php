<?php
/**
 * @author matthieu.napoli
 */

namespace AuditTrail\Architecture\Repository;

use AuditTrail\Domain\Context\OrganizationContext;
use AuditTrail\Domain\Entry;
use AuditTrail\Domain\EntryRepository;
use Core\Domain\DoctrineEntityRepository;
use Orga_Model_Cell;

/**
 * Audit trail entry repository
 */
class DoctrineEntryRepository extends DoctrineEntityRepository implements EntryRepository
{
    /**
     * @param int $count
     * @return Entry[]
     */
    public function findLatest($count)
    {
        $qb = $this->createQueryBuilder('e');
        $qb->addOrderBy('e.date', 'DESC')
            ->setMaxResults($count);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param OrganizationContext $context
     * @param int                 $count
     * @return Entry[]
     */
    public function findLatestForOrganizationContext(OrganizationContext $context, $count)
    {
        $qb = $this->createQueryBuilder('e');
        $qb->addOrderBy('e.date', 'DESC')
            ->setMaxResults($count);

        if ($context->getCell()) {
            $cells = $context->getCell()->getChildCells();
            $cells[] = $context->getCell();

            // Requete moche à cause de limitation Doctrine avec CTI
            // @see http://stackoverflow.com/questions/14851602/where-ing-in-discriminated-tables/14854067#14854067
            $qb->andWhere('e.context IN (SELECT c FROM AuditTrail\Domain\Context\OrganizationContext c WHERE c.cell IN (:cells))')
                ->setParameter('cells', $cells);
        } else {
            $qb->andWhere('e.context IN (SELECT c FROM AuditTrail\Domain\Context\OrganizationContext c WHERE c.organization = :organization)')
                ->setParameter('organization', $context->getOrganization());
        }

        return $qb->getQuery()->getResult();
    }
}
