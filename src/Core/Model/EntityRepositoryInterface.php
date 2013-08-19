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
}
