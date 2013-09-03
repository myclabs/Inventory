<?php

namespace Predicate\Architecture\Repository;

use Keyword\Domain\Predicate;
use Keyword\Domain\PredicateRepository;
use Core\Domain\DoctrineEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Core_Exception_NotFound;

/**
 * Gère les Predicate.
 * @author valentin.claras
 */
class DoctrinePredicateRepository extends DoctrineEntityRepository implements PredicateRepository
{
    /**
     * Retourne un Predicate grâce à son ref.
     *
     * @param string $predicateRef
     * @throws \Core_Exception_NotFound
     * @throws \Core_Exception_TooMany
     * @return Predicate
     */
    public function getOneByRef($predicateRef)
    {
        return $this->findOneBy(['ref' => $predicateRef]);
    }

    /**
     * Retourne un Predicate grâce à son ref.
     *
     * @param string $predicateReverseRef
     * @throws \Core_Exception_NotFound
     * @throws \Core_Exception_TooMany
     * @return Predicate
     */
    public function getOneByReverseRef($predicateReverseRef)
    {
        return $this->findOneBy(['reverseRef' => $predicateReverseRef]);
    }

}
