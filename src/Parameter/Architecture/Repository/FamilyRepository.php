<?php

namespace Parameter\Architecture\Repository;

use Core_Model_Repository_Ordered;
use Doctrine\ORM\EntityManager;
use Doctrine;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * @author matthieu.napoli
 */
class FamilyRepository extends Core_Model_Repository_Ordered
{
    /**
     * @param EntityManager $em    The EntityManager to use.
     * @param ClassMetadata $class The class descriptor.
     */
    public function __construct($em, ClassMetadata $class)
    {
        $this->_entityName = 'Parameter\Domain\Family\Family';
        $this->_em = $em;
        $this->_class = $class;
    }
}
