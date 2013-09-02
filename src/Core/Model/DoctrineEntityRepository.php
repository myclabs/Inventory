<?php

namespace Core\Model;

use Doctrine\ORM as Doctrine;

/**
 * Entity repository implementation using Doctrine
 *
 * @author matthieu.napoli
 */
class DoctrineEntityRepository extends Doctrine\EntityRepository implements EntityRepositoryInterface
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

    /**
     * Checks whether an element is contained in the repository.
     *
     * @param mixed $element The element to search for.
     *
     * @return boolean TRUE if the repository contains the element, FALSE otherwise.
     */
    public function contains($element)
    {
        return $this->getEntityManager()->contains($element);
    }

    /**
     * Returns the number of elements in the repository.
     *
     * @return int
     */
    public function count()
    {
        $query = $this->createQueryBuilder('e')->select('count(e)')->getQuery();
        return $query->getSingleScalarResult();
    }
}
