<?php

namespace Unit\Architecture\Repository;

use Core\Model\DoctrineEntityRepository;
use Unit\Domain\PhysicalQuantity;
use Unit\Domain\Unit\Unit;
use Unit\Domain\Unit\UnitRepository;

/**
 * Doctrine implementation
 *
 * @author  matthieu.napoli
 */
class DoctrineUnitRepository extends DoctrineEntityRepository implements UnitRepository
{
    /**
     * {@inheritdoc}
     */
    public function findByRef($ref)
    {
        return $this->findBy(['ref' => $ref]);
    }

    /**
     * {@inheritdoc}
     */
    public function findByPhysicalQuantity(PhysicalQuantity $physicalQuantity)
    {
        $dql = 'SELECT u FROM Unit\Domain\Unit\StandardUnit u WHERE u.physicalQuantity = ?';
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter(1, $physicalQuantity);
        return $query->getResult();
    }
}
