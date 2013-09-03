<?php

namespace Keyword\Domain;

use Core\Domain\EntityRepository;
use Core_Exception_NotFound;

/**
 * Gère les Predicate.
 * @author valentin.claras
 */
interface PredicateRepository extends EntityRepository
{
    /**
     * Retourne un Predicate grâce à son ref.
     *
     * @param string $predicateRef
     * @throws \Core_Exception_NotFound
     * @return Predicate
     */
    function getOneByRef($predicateRef);

    /**
     * Retourne un Predicate grâce à son ref inverse.
     *
     * @param string $predicateReverseRef
     * @throws \Core_Exception_NotFound
     * @return Predicate
     */
    function getOneByReverseRef($predicateReverseRef);

}
