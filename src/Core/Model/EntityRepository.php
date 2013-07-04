<?php
/**
 * @author matthieu.napoli
 */

namespace Core\Model;

use Doctrine\ORM as Doctrine;

/**
 *  Entity repository interface
 */
class EntityRepository extends Doctrine\EntityRepository implements EntityRepositoryInterface
{
    /**
     * @param object $entity
     */
    public function add($entity)
    {
        $this->getEntityManager()->persist($entity);
    }

    /**
     * @param object $entity
     */
    public function remove($entity)
    {
        $this->getEntityManager()->remove($entity);
    }
}
