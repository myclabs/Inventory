<?php
/**
 * @author matthieu.napoli
 */

namespace AuditTrail\Architecture\Repository;

use AuditTrail\Domain\Context\OrganizationContext;
use AuditTrail\Domain\Entry;
use AuditTrail\Domain\EntryRepository;
use Core\Model\EntityRepository;
use Orga_Model_Cell;

/**
 * Audit trail entry repository
 */
class DoctrineEntryRepository extends EntityRepository implements EntryRepository
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
        $qb->join('e.context', 'c')
            ->where('c INSTANCE OF AuditTrail\Domain\Context\OrganizationContext')
            ->addOrderBy('e.date', 'DESC')
            ->setMaxResults($count);

        if ($context->getCell()) {
            $cellsId = $this->getAllChildCellsId($context->getCell());
            $cellsId[] = $context->getCell()->getId();

            $qb->where('c.cell.id IN :cellsId');
            $qb->setParameter('cellsId', $cellsId);
        } else {
            $qb->where('c.organization = :organization');
            $qb->setParameter('organization', $context->getOrganization());
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Orga_Model_Cell $cell
     * @return int[]
     */
    private function getAllChildCellsId(Orga_Model_Cell $cell)
    {
        $childCellsId = [];

        foreach ($cell->getChildCells() as $childCell) {
            $childCellsId[] = $childCell->getId();
            $childCellsId = array_merge($childCellsId, $this->getAllChildCellsId($childCell));
        }

        return $childCellsId;
    }
}
