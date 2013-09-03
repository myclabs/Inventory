<?php
/**
 * @author matthieu.napoli
 * @author valentin.claras
 */

namespace Core\Domain;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\Common\Collections\Selectable;

/**
 *  Entity repository interface
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
     * @return object[]
     */
    public function getAll();

    /**
     * @param array|mixed $id
     * @throws \Core_Exception_NotFound
     * @return object
     */
    public function getOne($id);

    /**
     * @param array $criteria
     * @throws \Core_Exception_NotFound
     * @throws \Core_Exception_TooMany
     * @return object
     */
    function getOneBy(array $criteria);

}
