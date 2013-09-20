<?php

namespace Keyword\Architecture\Repository;

use Core\Domain\Translatable\TranslatableRepository;
use Keyword\Domain\Predicate;
use Keyword\Domain\PredicateRepository;
use Core\Domain\DoctrineEntityRepository;
use Core_Exception_NotFound;

/**
 * Gère les prédicats.
 *
 * @author valentin.claras
 */
class DoctrinePredicateRepository extends DoctrineEntityRepository implements PredicateRepository
{
    use TranslatableRepository;

    /**
     * {@inheritdoc}
     */
    public function getErrorMessageForRef($ref)
    {
        try {
            \Core_Tools::checkRef($ref);
        } catch (\Core_Exception_User $e) {
            return $e->getMessage();
        }
        try {
            $this->getByRef($ref);
            $this->getByReverseRef($ref);
            return __('UI', 'formValidation', 'alreadyUsedIdentifier', array('REF' => $ref));
        } catch (\Core_Exception_NotFound $e) {
            // Pas de Keyword trouvé.
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function checkRef($ref)
    {
        \Core_Tools::checkRef($ref);
        try {
            $this->getByRef($ref);
            $this->getByReverseRef($ref);
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
     * {@inheritdoc}
     */
    public function getByRef($predicateRef)
    {
        return $this->getBy(['ref' => $predicateRef]);
    }

    /**
     * {@inheritdoc}
     */
    public function getByReverseRef($predicateReverseRef)
    {
        return $this->getBy(['reverseRef' => $predicateReverseRef]);
    }

}
