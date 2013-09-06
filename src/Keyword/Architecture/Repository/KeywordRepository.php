<?php

namespace Keyword\Architecture\Repository;

use Core_Exception_InvalidArgument;
use Core_Exception_NotFound;
use Core_Model_Filter;
use Core_Model_Query;
use Core_Model_Repository;
use Doctrine\ORM\QueryBuilder;
use Keyword\Domain\Association;
use Keyword\Domain\Keyword;
use Keyword\Domain\Predicate;

/**
 * Gère les Keyword.
 * @author valentin.claras
 */
class KeywordRepository extends Core_Model_Repository
{
    /**
     * Charge la liste des Keyword ne possédant pas d'association en tant qu'objet.
     *
     * @param Core_Model_Query $queryParameters Paramètres de la requête
     *
     * @return Keyword[]
     */
    public function loadListRoots(Core_Model_Query $queryParameters)
    {
        $entityName = $this->getEntityName();

        $queryBuilderLoadListRoots = $this->createQueryBuilder($entityName::getAlias());
        $queryBuilderLoadListRoots->distinct();
        $queryParameters->rootAlias = $entityName::getAlias();
        $this->addCustomParametersToQueryBuilder($queryBuilderLoadListRoots);
        $queryParameters->parseToQueryBuilderWithLimit($queryBuilderLoadListRoots);

        $queryBuilderLoadListRoots->leftJoin(Keyword::getAlias() . '.objectAssociation', Association::getAlias());
        $queryBuilderLoadListRoots->addGroupBy(Keyword::getAlias() . '.' . Keyword::QUERY_ID);
        $queryBuilderLoadListRoots->having(
            $queryBuilderLoadListRoots->expr()->eq(
                'count(' . Association::getAlias() . '.' . Association::QUERY_PREDICATE . ')',
                0
            )
        );

        return $this->getQueryFromQueryBuilder($queryBuilderLoadListRoots)->getResult();
    }

    /**
     * Charge une liste des Keyword répondant à l'expression donnée.
     *
     * @param string $expressionQuery
     *
     * @return Keyword[]
     */
    public function loadListMatchingQuery($expressionQuery)
    {
        $queryBuilderLoadListMatchingQuery = $this->createQueryBuilder(Keyword::getAlias());
        $queryBuilderLoadListMatchingQuery->distinct();

        $queryBuilderLoadListMatchingQuery->where(
            $this->executeExpressionQuery($queryBuilderLoadListMatchingQuery, $expressionQuery)
        );
        $queryBuilderLoadListMatchingQuery->orderBy(Keyword::getAlias() . '.' . Keyword::QUERY_LABEL, 'ASC');

        return $this->getQueryFromQueryBuilder($queryBuilderLoadListMatchingQuery)->getResult();
    }

    /**
     * Créer une sous requête.
     *
     * @param QueryBuilder $queryBuilder
     * @param string       $expressionQuery
     * @param int          &$level
     *
     * @return QueryBuilder
     */
    protected function executeExpressionQuery($queryBuilder, $expressionQuery, &$level = 0)
    {
        if (strpos($expressionQuery, '|')) {
            $orExpression = $queryBuilder->expr()->orx();
            $orSubQueries = explode('|', $expressionQuery);
            foreach ($orSubQueries as $orSubQuery) {
                $orExpression->add($this->executeExpressionQuery($queryBuilder, $orSubQuery, $level));
                $level++;
            }
            return $orExpression;
        } else {
            if (strpos($expressionQuery, '&')) {
                $andExpression = $queryBuilder->expr()->andx();
                $andSubQueries = explode('&', $expressionQuery);
                foreach ($andSubQueries as $andSubQuery) {
                    $andExpression->add($this->executeExpressionQuery($queryBuilder, $andSubQuery, $level));
                    $level++;
                }
                return $andExpression;
            } else {
                return $queryBuilder->expr()->in(
                    Keyword::getAlias() . '.' . Keyword::QUERY_ID,
                    $this->createSubExpressionQuery($queryBuilder, $expressionQuery, $level)->getDQL()
                );
            }
        }
    }

    /**
     * Créer une sous requête correspondant à l'expressionQuery.
     *
     * @param QueryBuilder $queryBuilder
     * @param string       $expressionQuery
     * @param int          $level
     *
     * @throws Core_Exception_InvalidArgument
     *
     * @return QueryBuilder
     */
    protected function createSubExpressionQuery($queryBuilder, $expressionQuery, $level)
    {
        list($subject, $predicate, $object) = explode(',', $expressionQuery);
        $subject = trim($subject);
        $predicate = trim($predicate);
        $object = trim($object);

        $queryBuilderExpression = $this->createQueryBuilder(Keyword::getAlias() . '_' . $level);
        $queryBuilderExpression->distinct();
        $queryBuilderExpression->select(Keyword::getAlias() . '_' . $level . '.' . Keyword::QUERY_ID);

        $this->checkPredicateRefExists($predicate);
        if ($subject == 'this') {
            $this->checkKeywordRefExists($object);
            $queryBuilderExpression->leftJoin(
                Keyword::getAlias() . '_' . $level . '.' . 'subjectAssociation',
                Association::getAlias() . '_' . $level
            );
            $queryBuilderExpression->leftJoin(
                Association::getAlias() . '_' . $level . '.' . Association::QUERY_OBJECT,
                Keyword::getAliasAsObject() . '_' . $level
            );
            $queryBuilder->setParameter('object' . $level, $object);
        } else {
            if ($object == 'this') {
                $this->checkKeywordRefExists($subject);
                $queryBuilderExpression->leftJoin(
                    Keyword::getAlias() . '_' . $level . '.' . 'objectAssociation',
                    Association::getAlias() . '_' . $level
                );
                $queryBuilderExpression->leftJoin(
                    Association::getAlias() . '_' . $level . '.' . Association::QUERY_SUBJECT,
                    Keyword::getAliasAsObject() . '_' . $level
                );
                $queryBuilder->setParameter('object' . $level, $subject);
            } else {
                throw new Core_Exception_InvalidArgument('Invalid expression query.');
            }
        }

        $queryBuilderExpression->leftJoin(
            Association::getAlias() . '_' . $level . '.' . Association::QUERY_PREDICATE,
            Predicate::getAlias() . '_' . $level
        );

        $queryBuilderExpression->where(
            $queryBuilderExpression->expr()->andX(
                $queryBuilderExpression->expr()->orX(
                    $queryBuilderExpression->expr()->eq(
                        Predicate::getAlias() . '_' . $level . '.' . Predicate::QUERY_REF,
                        ':directPredicate' . $level
                    ),
                    $queryBuilderExpression->expr()->eq(
                        Predicate::getAlias() . '_' . $level . '.' . Predicate::QUERY_REVERSE_REF,
                        ':reversePredicate' . $level
                    )
                ),
                $queryBuilderExpression->expr()->eq(
                    Keyword::getAliasAsObject() . '_' . $level . '.' . Keyword::QUERY_REF,
                    ':object' . $level
                )
            )
        );

        $queryBuilder->setParameter('directPredicate' . $level, $predicate);
        $queryBuilder->setParameter('reversePredicate' . $level, $predicate);

        return $queryBuilderExpression;
    }

    /**
     * Vérifie que la ref du Predicate sur lequel se base la requête existe bel et bien.
     *
     * @param string $ref
     *
     * @throws Core_Exception_NotFound
     */
    protected function checkKeywordRefExists($ref)
    {
        $queryRefExists = new Core_Model_Query();
        $queryRefExists->filter->addCondition(Keyword::QUERY_REF, $ref);
        if (Keyword::countTotal($queryRefExists) <= 0) {
            throw new Core_Exception_NotFound(__('Keyword', 'query', 'keywordNotFound', array('REF' => $ref)));
        }
    }

    /**
     * Vérifie que la ref du Keyword sur lequel se base la requête existe bel et bien.
     *
     * @param string $ref
     *
     * @throws Core_Exception_NotFound
     */
    protected function checkPredicateRefExists($ref)
    {
        $queryRefExists = new Core_Model_Query();
        $queryRefExists->filter->condition = Core_Model_Filter::CONDITION_OR;
        $queryRefExists->filter->addCondition(Predicate::QUERY_REF, $ref);
        $queryRefExists->filter->addCondition(Predicate::QUERY_REVERSE_REF, $ref);
        if (Predicate::countTotal($queryRefExists) <= 0) {
            throw new Core_Exception_NotFound(__('Keyword', 'query', 'predicateNotFound', array('REF' => $ref)));
        }
    }

}
