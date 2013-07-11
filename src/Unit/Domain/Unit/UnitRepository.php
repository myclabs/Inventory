<?php
/**
 * @author  matthieu.napoli
 */

namespace Unit\Domain\Unit;

use Doctrine\ORM\EntityRepository;

/**
 * Repository pour Unit
 */
class UnitRepository extends EntityRepository
{
    /**
     * Adds an element to the repository.
     *
     * @param mixed $element The element to add.
     */
    public function add($element)
    {
        $this->getEntityManager()->persist($element);
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
     * Checks whether the repository is empty (contains no elements).
     *
     * @return boolean TRUE if empty, FALSE otherwise.
     */
    public function isEmpty()
    {
        return $this->count() === 0;
    }

    /**
     * Removes the specified element from the repository, if it is found.
     *
     * @param mixed $element The element to remove.
     */
    public function remove($element)
    {
        $this->getEntityManager()->remove($element);
    }

    /**
     * Gets an element by its ID.
     *
     * @param string|integer $id The ID of the element to retrieve.
     *
     * @return mixed
     */
    public function get($id)
    {
        return $this->find($id);
    }

    /**
     * Gets a native PHP array representation of the elements of the repository.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->findAll();
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
