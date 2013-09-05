<?php

namespace Keyword\Architecture\Repository;

use Core\Domain\Translatable\TranslatableRepository;
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
    use TranslatableRepository;

    /**
     * Renoie les messages d'erreur concernant la validation d'une ref.
     *
     * @param string $ref
     *
     * @return mixed string null
     */
    public function getErrorMessageForRef($ref)
    {
        try {
            \Core_Tools::checkRef($ref);
        } catch (\Core_Exception_User $e) {
            return $e->getMessage();
        }
        try {
            $existingPredicateWithRef = $this->getOneByRef($ref);
            $existingPredicateWithReverseRef = $this->getOneByReverseRef($ref);
            return __('UI', 'formValidation', 'alreadyUsedIdentifier', array('REF' => $ref));
        } catch (\Core_Exception_NotFound $e) {
            // Pas de Keyword trouvé.
        }
        return null;
    }

    /**
     * Vérifie la disponibilité d'une référence pour un prédicat.
     *
     * @param string $ref
     *
     * @throws \Core_Exception_User
     */
    public function checkRef($ref)
    {
        \Core_Tools::checkRef($ref);
        try {
            $existingPredicateWithRef = $this->getOneByRef($ref);
            $existingPredicateWithReverseRef = $this->getOneByReverseRef($ref);
            throw new \Core_Exception_User('UI', 'formValidation', 'alreadyUsedIdentifier', array('REF' => $ref));
        } catch (\Core_Exception_NotFound $e) {
            // Pas de Keyword trouvé.
        }
    }

    /**
     * @param Predicate $entity
     */
    public function add($entity)
    {
        $this->checkRef($entity->getRef());
        $this->checkRef($entity->getReverseRef());
        parent::add($entity);
    }

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
        return $this->getOneBy(['ref' => $predicateRef]);
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
        return $this->getOneBy(['reverseRef' => $predicateReverseRef]);
    }

}
