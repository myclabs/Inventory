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
        return $this->findOneBy(['subject' => $subjectKeyword, 'predicate' => $predicate, 'object' => $objectKeyword]);
    }

}
