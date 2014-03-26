<?php

namespace Core\Domain;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Entity repository interface
 *
 * @author matthieu.napoli
 * @author valentin.claras
 */
interface EntityRepository extends ObjectRepository, Selectable
{
    /**
     * @param object $entity
     */
    public function add($entity);

    /**
     * @param object $entity
     */
    public function remove($entity);

    /**
     * Returns the number of elements in the repository.
     *
     * @return int
     */
    public function count();

    /**
     * Returns all elements of the repository.
     *
     * @return object[]
     */
    public function getAll();

    /**
     * Returns one element from the repository.
     *
     * @param array|mixed $id
     *
     * @throws \Core_Exception_NotFound The entity was not found.
     * @return object
     */
    public function get($id);

    /**
     * Returns one element from the repository based on given criterias.
     *
     * @param array $criteria
     *
     * @throws \Core_Exception_NotFound The entity was not found.
     * @throws \Core_Exception_TooMany Too many results were found for the given criterias.
     * @return object
     */
    public function getBy(array $criteria);

    /**
     * Selects all elements from a selectable that match the expression and
     * returns a new collection containing these elements.
     *
     * @param Criteria $criteria
     *
     * @return Paginator
     */
    public function matching(Criteria $criteria);
}
