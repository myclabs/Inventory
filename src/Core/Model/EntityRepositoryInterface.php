<?php
/**
 * @author matthieu.napoli
 */

namespace Core\Model;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\Common\Collections\Selectable;

/**
 *  Entity repository interface
 */
interface EntityRepositoryInterface extends ObjectRepository, Selectable
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
     * Checks whether an element is contained in the repository.
     *
     * @param mixed $element The element to search for.
     *
     * @return boolean TRUE if the repository contains the element, FALSE otherwise.
     */
    public function contains($element);

    /**
     * Returns the number of elements in the repository.
     *
     * @return int
     */
    public function count();
}
