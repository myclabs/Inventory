<?php

namespace Unit\Domain\Unit;

use Core\Model\EntityRepositoryInterface;
use Unit\Domain\PhysicalQuantity;

/**
 * Unit repository
 *
 * @author  matthieu.napoli
 */
interface UnitRepository extends EntityRepositoryInterface
{
    /**
     * @param string $ref
     * @return Unit
     */
    public function findByRef($ref);

    /**
     * @param PhysicalQuantity $physicalQuantity
     * @return StandardUnit[]
     */
    public function findByPhysicalQuantity(PhysicalQuantity $physicalQuantity);
}
