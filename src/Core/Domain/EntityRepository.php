<?php

namespace Core\Domain;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\Common\Collections\Selectable;

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
    function add($entity);

    /**
     * @param object $entity
     */
    function remove($entity);

    /**
     * Returns the number of elements in the repository.
     *
     * @return int
     */
    function count();

    /**
     * Returns all elements of the repository.
     *
     * @return object[]
     */
    function getAll();

    /**
     * Returns one element from the repository.
     *
     * @param array|mixed $id
     *
     * @throws \Core_Exception_NotFound The entity was not found.
     * @return object
     */
    function get($id);

    /**
     * Returns one element from the repository based on given criterias.
     *
     * @param array $criteria
     *
     * @throws \Core_Exception_NotFound The entity was not found.
     * @throws \Core_Exception_TooMany Too many results were found for the given criterias.
     * @return object
     */
    function getBy(array $criteria);
}
