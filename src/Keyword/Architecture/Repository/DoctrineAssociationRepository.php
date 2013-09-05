<?php

namespace Keyword\Architecture\Repository;

use Core\Domain\DoctrineEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Keyword\Domain\Association;
use Keyword\Domain\AssociationRepository;
use Keyword\Domain\Keyword;
use Keyword\Domain\Predicate;

/**
 * Gère les Association.
 * @author valentin.claras
 */
class DoctrineAssociationRepository extends DoctrineEntityRepository implements AssociationRepository
{
    /**
     * Effectue un leftJoin sur l'association avec Keyword en tant que sujet.
     *
     * @param QueryBuilder $queryBuilder
     *
    protected function leftJoinSubject(QueryBuilder $queryBuilder)
    {
        $queryBuilder->leftJoin(
            Association::getAlias() . '.' . Association::QUERY_SUBJECT,
            Keyword::getAliasAsSubject()
        );
    }

    /**
     * Effectue un leftJoin sur l'association avec Keyword en tant qu'objet.
     *
     * @param QueryBuilder $queryBuilder
     *
    protected function leftJoinObject(QueryBuilder $queryBuilder)
    {
        $queryBuilder->leftJoin(
            Association::getAlias() . '.' . Association::QUERY_OBJECT,
            Keyword::getAliasAsObject()
        );
    }

    /**
     * Effectue un leftJoin sur l'association avec Predicate.
     *
     * @param QueryBuilder $queryBuilder
     *
    protected function leftJoinPredicate(QueryBuilder $queryBuilder)
    {
        $queryBuilder->leftJoin(
            Association::getAlias() . '.' . Association::QUERY_PREDICATE,
            Predicate::getAlias()
        );
    }*/

    /**
     * Renoie les messages d'erreur concernant la validation d'une Association.
     *
     * @param Association $association
     *
     * @return mixed string null
     */
    public function getErrorMessageForAssociation(Association $association)
    {
        if ($association->getSubject() === $association->getObject()) {
            return __('Keyword', 'relation', 'subjectSameAsObject', array('REF' => $association->getSubject()->getRef()));
        }
        try {
            $this->getOneBySubjectPredicateObject($association->getSubject(), $association->getPredicate(), $association->getObject());
            return __('Keyword', 'relation', 'associationAlreadyExists');
        } catch (\Core_Exception_NotFound $e) {
            // Valide.
        }
        try {
            $this->getOneBySubjectPredicateObject($association->getObject(), $association->getPredicate(), $association->getSubject());
            return __('Keyword', 'relation', 'associationAlreadyExists');
        } catch (\Core_Exception_NotFound $e) {
            // Valide.
        }

        return null;
    }

    /**
     * Vérifie la disponibilité d'une Association.
     *
     * @param Association $association
     *
     * @throws \Core_Exception_User
     */
    public function checkAssociation(Association $association)
    {
        if ($association->getSubject() === $association->getObject()) {
            throw new \Core_Exception_User(
                'Keyword', 'relation', 'subjectSameAsObject', array('REF' => $association->getSubject()->getRef())
            );
        }
        try {
            $this->getOneBySubjectPredicateObject($association->getSubject(), $association->getPredicate(), $association->getObject());
            throw new \Core_Exception_User('Keyword', 'relation', 'associationAlreadyExists');
        } catch (\Core_Exception_NotFound $e) {
            // Valide.
        }
        try {
            $this->getOneBySubjectPredicateObject($association->getObject(), $association->getPredicate(), $association->getSubject());
            throw new \Core_Exception_User('Keyword', 'relation', 'associationAlreadyExists');
        } catch (\Core_Exception_NotFound $e) {
            // Valide.
        }
    }

    /**
     * @param Association $entity
     */
    public function add($entity)
    {
        $this->checkAssociation($entity);
        parent::add($entity);
    }

    /**
     * Charge une Association en fonction des refs de ses composants.
     *
     * @param Keyword $subjectKeyword
     * @param Predicate $predicate
     * @param Keyword $objectKeyword
     * @throws \Core_Exception_NotFound
     * @throws \Core_Exception_TooMany
     * @return Association
     */
    public function getOneBySubjectPredicateObject(Keyword $subjectKeyword, Predicate $predicate, Keyword $objectKeyword)
    {
        return $this->getOneBy(
            ['subject' => $subjectKeyword->getId(), 'predicate' => $predicate->getId(), 'object' => $objectKeyword->getId()]
        );
    }

}
