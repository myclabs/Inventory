<?php

namespace Keyword\Architecture\Repository;

use Core\Domain\DoctrineEntityRepository;
use Core\Domain\Translatable\TranslatableRepository;
use Core_Exception_NotFound;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Keyword\Domain\Keyword;
use Keyword\Domain\Predicate;
use Keyword\Domain\Association;

/**
 * Gère les Keyword.
 * @author valentin.claras
 */
class DoctrineAssociationRepository extends DoctrineEntityRepository
{
    /**
     * Renoie les messages d'erreur concernant la validation d'une Association.
     *
     * @param \Keyword\Domain\Keyword $subjectKeyword
     * @param \Keyword\Domain\Predicate $predicate
     * @param \Keyword\Domain\Keyword $objectKeyword
     *
     * @return mixed string null
     */
    public function getErrorMessageForAssociation(Keyword $subjectKeyword, Predicate $predicate, Keyword $objectKeyword)
    {
        if ($subjectKeyword === $objectKeyword) {
            return __('Keyword', 'relation', 'subjectSameAsObject', array('REF' => $subjectKeyword->getRef()));
        }
        try {
            $this->getBySubjectPredicateObject($subjectKeyword, $predicate, $objectKeyword);
            return __('Keyword', 'relation', 'associationAlreadyExists');
        } catch (\Core_Exception_NotFound $e) {
            // Valide.
        }
        try {
            $this->getBySubjectPredicateObject($objectKeyword, $predicate, $subjectKeyword);
            return __('Keyword', 'relation', 'associationAlreadyExists');
        } catch (\Core_Exception_NotFound $e) {
            // Valide.
        }

        return null;
    }

    /**
     * Vérifie la disponibilité d'une Association.
     *
     * @param Keyword $subjectKeyword
     * @param Predicate $predicate
     * @param Keyword $objectKeyword
     *
     * @throws \Core_Exception_User
     */
    public function checkAssociation(Keyword $subjectKeyword, Predicate $predicate, Keyword $objectKeyword)
    {
        if ($subjectKeyword === $objectKeyword) {
            throw new \Core_Exception_User(
                'Keyword', 'relation', 'subjectSameAsObject', array('REF' => $subjectKeyword->getRef())
            );
        }
        try {
            $this->getBySubjectPredicateObject($subjectKeyword, $predicate, $objectKeyword);
            throw new \Core_Exception_User('Keyword', 'relation', 'associationAlreadyExists');
        } catch (\Core_Exception_NotFound $e) {
            // Valide.
        }
        try {
            $this->getBySubjectPredicateObject($objectKeyword, $predicate, $subjectKeyword);
            throw new \Core_Exception_User('Keyword', 'relation', 'associationAlreadyExists');
        } catch (\Core_Exception_NotFound $e) {
            // Valide.
        }
    }

    /**
     * Charge une Association en fonction des refs de ses composants.
     *
     * @param Keyword $subjectKeyword
     * @param Predicate $predicate
     * @param Keyword $objectKeyword
     * @return Association
     */
    public function getBySubjectPredicateObject(Keyword $subjectKeyword, Predicate $predicate, Keyword $objectKeyword)
    {
        return $this->getBy(
            ['subject' => $subjectKeyword->getId(), 'predicate' => $predicate->getId(), 'object' => $objectKeyword->getId()]
        );
    }

    /**
     * Selects all elements from a selectable that match the expression and
     * returns a new collection containing these elements.
     *
     * @param Criteria $criteria
     *
     * @return Paginator
     */
    public function matching(Criteria $criteria)
    {
        $queryBuilder = $this->createQueryBuilder('this')
            ->join('this.subject', 'subject')
            ->join('this.predicate', 'predicate')
            ->join('this.object', 'object');
        $queryBuilder->addCriteria($criteria);

        return new Paginator($queryBuilder, false);
    }

}
