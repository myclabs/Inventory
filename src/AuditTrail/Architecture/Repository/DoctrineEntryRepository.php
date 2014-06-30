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
use Orga_Model_Repository_Cell;
use DateTime;

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
            // Obligé d'utiliser IN et une sous-requête à cause de limitation Doctrine avec CTI
            // @see http://stackoverflow.com/questions/14851602/where-ing-in-discriminated-tables/14854067#14854067
            $subQuery = $this->getEntityManager()->createQueryBuilder();
            $subQuery->select('context')
                ->from('AuditTrail\Domain\Context\OrganizationContext', 'context')
                ->innerJoin('context.cell', 'cell');
            /** @var Orga_Model_Repository_Cell $cellRepository */
            $cellRepository = $this->getEntityManager()->getRepository(Orga_Model_Cell::class);
            $cellRepository->addCellAndChildrenConditionsToQueryBuilder($subQuery, $context->getCell(), 'cell');

            $qb->andWhere('e.context IN (' . $subQuery->getDQL() . ')');

            // Merge les paramètres
            $qb->setParameters($subQuery->getParameters());
        } else {
            $qb->andWhere('e.context IN (SELECT c FROM AuditTrail\Domain\Context\OrganizationContext c WHERE c.organization = :organization)')
                ->setParameter('organization', $context->getOrganization());
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param OrganizationContext $context
     * @param DateTime            $upTo
     * @param DateTime            $from
     * @return Entry[]
     */
    public function findUpToForOrganizationContext(OrganizationContext $context, DateTime $upTo, DateTime $from=null)
    {
        $qb = $this->createQueryBuilder('e');
        $qb->addOrderBy('e.date', 'DESC');

        if ($context->getCell()) {
            // Obligé d'utiliser IN et une sous-requête à cause de limitation Doctrine avec CTI
            // @see http://stackoverflow.com/questions/14851602/where-ing-in-discriminated-tables/14854067#14854067
            $subQuery = $this->getEntityManager()->createQueryBuilder();
            $subQuery->select('context')
                ->from('AuditTrail\Domain\Context\OrganizationContext', 'context')
                ->innerJoin('context.cell', 'cell');
            /** @var Orga_Model_Repository_Cell $cellRepository */
            $cellRepository = $this->getEntityManager()->getRepository(Orga_Model_Cell::class);
            $cellRepository->addCellAndChildrenConditionsToQueryBuilder($subQuery, $context->getCell(), 'cell');

            $qb->andWhere('e.context IN (' . $subQuery->getDQL() . ')');

            // Merge les paramètres
            $qb->setParameters($subQuery->getParameters());
        } else {
            $qb->andWhere('e.context IN (SELECT c FROM AuditTrail\Domain\Context\OrganizationContext c WHERE c.organization = :organization)')
                ->setParameter('organization', $context->getOrganization());
        }

        $qb->andWhere($qb->expr()->gte('e.date', ':upTo'));
        $qb->setParameter('upTo', $upTo);
        if (($from !== null) && ($upTo < $from)) {
            $qb->andWhere($qb->expr()->lte('e.date', ':from'));
            $qb->setParameter('from', $from);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param OrganizationContext $context
     * @param DateTime            $upTo
     * @param DateTime            $from
     * @return Entry[]
     */
    public function hasFromForOrganizationContext(OrganizationContext $context, DateTime $from=null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select($qb->expr()->count('e'))
            ->from($this->_entityName, 'e')
            ->addOrderBy('e.date', 'DESC');

        if ($context->getCell()) {
            // Obligé d'utiliser IN et une sous-requête à cause de limitation Doctrine avec CTI
            // @see http://stackoverflow.com/questions/14851602/where-ing-in-discriminated-tables/14854067#14854067
            $subQuery = $this->getEntityManager()->createQueryBuilder();
            $subQuery->select('context')
                ->from('AuditTrail\Domain\Context\OrganizationContext', 'context')
                ->innerJoin('context.cell', 'cell');
            /** @var Orga_Model_Repository_Cell $cellRepository */
            $cellRepository = $this->getEntityManager()->getRepository(Orga_Model_Cell::class);
            $cellRepository->addCellAndChildrenConditionsToQueryBuilder($subQuery, $context->getCell(), 'cell');

            $qb->andWhere('e.context IN (' . $subQuery->getDQL() . ')');

            // Merge les paramètres
            $qb->setParameters($subQuery->getParameters());
        } else {
            $qb->andWhere('e.context IN (SELECT c FROM AuditTrail\Domain\Context\OrganizationContext c WHERE c.organization = :organization)')
                ->setParameter('organization', $context->getOrganization());
        }

        $qb->andWhere($qb->expr()->lte('e.date', ':from'));
        $qb->setParameter('from', $from);

        return ($qb->getQuery()->getSingleScalarResult() > 0);
    }
}
