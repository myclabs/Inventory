<?php

namespace Keyword\Architecture\Repository;

use Core_Exception_NotFound;
use Core_Exception_TooMany;
use Core_Model_Query;
use Core_Model_Repository;
use Doctrine\ORM\QueryBuilder;
use Keyword\Domain\Association;
use Keyword\Domain\Keyword;
use Keyword\Domain\Predicate;

/**
 * Gère les Association.
 * @author valentin.claras
 */
class AssociationRepository extends Core_Model_Repository
{

    /**
     * Ajoute des paramètres personnalisés au QueryBuilder utilisé par le loadList et le countTotal.
     *
     * @param QueryBuilder          $queryBuilder
     * @param Core_Model_Query|null $queryParameters
     */
    protected function addCustomParametersToQueryBuilder($queryBuilder, Core_Model_Query $queryParameters = null)
    {
        $this->leftJoinSubject($queryBuilder);
        $this->leftJoinObject($queryBuilder);
        $this->leftJoinPredicate($queryBuilder);
    }

    /**
     * Effectue un leftJoin sur l'association avec Keyword en tant que sujet.
     *
     * @param QueryBuilder $queryBuilder
     */
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
     */
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
     */
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
     * @param string $subjectKeywordRef
     * @param string $objectKeywordRef
     * @param string $predicateRef
     *
     * @throws Core_Exception_NotFound
     * @throws Core_Exception_TooMany
     *
     * @return Association
     */
    public function loadByRefs($subjectKeywordRef, $objectKeywordRef, $predicateRef)
    {
        $entityName = $this->getEntityName();

        $queryBuilderLoadByRefs = $this->createQueryBuilder($entityName::getAlias());
        $queryBuilderLoadByRefs->distinct();
        $this->addCustomParametersToQueryBuilder($queryBuilderLoadByRefs);

        $subjectAlias = Keyword::getAliasAsSubject() . '.' . Keyword::QUERY_REF;
        $subjectBindKey = 'subjectRef';
        $subjectCondition = $queryBuilderLoadByRefs->expr()->eq($subjectAlias, ':' . $subjectBindKey);
        $objectAlias = Keyword::getAliasAsObject() . '.' . Keyword::QUERY_REF;
        $objectBindKey = 'objectRef';
        $objectCondition = $queryBuilderLoadByRefs->expr()->eq($objectAlias, ':' . $objectBindKey);
        $predicateAlias = Predicate::getAlias() . '.' . Predicate::QUERY_REF;
        $predicateBindKey = 'predicateRef';
        $predicateCondition = $queryBuilderLoadByRefs->expr()->eq($predicateAlias, ':' . $predicateBindKey);

        $queryBuilderLoadByRefs->andWhere($subjectCondition);
        $queryBuilderLoadByRefs->andWhere($objectCondition);
        $queryBuilderLoadByRefs->andWhere($predicateCondition);
        $queryBuilderLoadByRefs->setParameter($subjectBindKey, $subjectKeywordRef);
        $queryBuilderLoadByRefs->setParameter($objectBindKey, $objectKeywordRef);
        $queryBuilderLoadByRefs->setParameter($predicateBindKey, $predicateRef);

        $entities = $this->getQueryFromQueryBuilder($queryBuilderLoadByRefs)->getResult();
        if (empty($entities)) {
            $criteria = array($subjectKeywordRef, $objectKeywordRef, $predicateRef);
            throw new Core_Exception_NotFound('No "' . $entityName . '" matching ' . var_export($criteria, true));
        } else {
            if (count($entities) > 1) {
                $criteria = array($subjectKeywordRef, $objectKeywordRef, $predicateRef);
                throw new Core_Exception_TooMany('Too many "' . $entityName . '" matching ' . var_export($criteria, true));
            }
        }

        return $entities[0];
    }

}
