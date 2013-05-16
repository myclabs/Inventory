<?php
/**
 * @author  matthieu.napoli
 * @package Techno
 */

use Doctrine\ORM\EntityManager;

/**
 * @package Techno
 */
class Techno_Model_Repository_Family extends Core_Model_Repository_Ordered
{

    /**
     * Initializes a new EntityRepository
     *
     * @param EntityManager $em The EntityManager to use.
     * @param Doctrine\ORM\Mapping\ClassMetadata $class The class descriptor.
     */
    public function __construct($em, Doctrine\ORM\Mapping\ClassMetadata $class)
    {
        $this->_entityName = 'Techno_Model_Family';
        $this->_em         = $em;
        $this->_class      = $class;
    }

}
