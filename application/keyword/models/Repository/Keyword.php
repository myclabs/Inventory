<?php
/**
 * Classe Keyword_Model_Repository_Keyword
 * @author     valentin.claras
 * @package    Keyword
 * @subpackage Model
 */

/**
 * Gère les Keyword.
 * @package    Keyword
 * @subpackage Model
 */
class Keyword_Model_Repository_Keyword extends Core_Model_Repository
{
    /**
     * Charge la liste des Keyword ne possédant pas d'association en tant qu'objet.
     *
     * @param Core_Model_Query $queryParameters Paramètres de la requête
     *
     * @return Keyword_Model_Keyword[]
     */
    public function loadListRoots(Core_Model_Query $queryParameters)
    {
        $entityName = $this->getEntityName();

        $queryBuilderLoadListRoots = $this->createQueryBuilder($entityName::getAlias());
        $queryBuilderLoadListRoots->distinct();
        $queryParameters->rootAlias = $entityName::getAlias();
        $this->addCustomParametersToQueryBuilder($queryBuilderLoadListRoots);
        $queryParameters->parseToQueryBuilderWithLimit($queryBuilderLoadListRoots);

        $queryBuilderLoadListRoots->leftJoin(
                Keyword_Model_Keyword::getAlias().'.objectAssociation',
                Keyword_Model_Association::getAlias()
            );
        $queryBuilderLoadListRoots->addGroupBy(Keyword_Model_Keyword::getAlias().'.'.Keyword_Model_Keyword::QUERY_ID);
        $queryBuilderLoadListRoots->having(
                $queryBuilderLoadListRoots->expr()->eq(
                        'count('
                            .Keyword_Model_Association::getAlias().'.'.Keyword_Model_Association::QUERY_PREDICATE.
                        ')',
                        0
                    )
            );

        return $queryBuilderLoadListRoots->getQuery()->getResult();
    }

    /**
     * Charge une liste des Keyword répondant à l'expression donnée.
     *
     * @param string $expressionQuery
     *
     * @return Keyword_Model_Keyword[]
     */
    public function loadListMatchingQuery($expressionQuery)
    {
        $queryBuilderLoadListMAtchingQuery = $this->createQueryBuilder(Keyword_Model_Keyword::getAlias());
        $queryBuilderLoadListMAtchingQuery->distinct();

        $queryBuilderLoadListMAtchingQuery->where(
                    $this->executeExpressionQuery($queryBuilderLoadListMAtchingQuery, $expressionQuery)
                );
        $queryBuilderLoadListMAtchingQuery->orderBy(
                Keyword_Model_Keyword::getAlias().'.'.Keyword_Model_Keyword::QUERY_LABEL,
                'ASC'
            );

        return $queryBuilderLoadListMAtchingQuery->getQuery()->getResult();
    }

    /**
     * Créer une sous requête.
     *
     * @param Doctrine\ORM\QueryBuilder $queryBuilder
     * @param string                    $expressionQuery
     * @param int                       &$level
     *
     * @return Doctrine\ORM\QueryBuilder
     */
    protected function executeExpressionQuery($queryBuilder, $expressionQuery, &$level=0)
    {
        if (strpos($expressionQuery, '|')) {
            $orExpression = $queryBuilder->expr()->orx();
            $orSubQueries = explode('|', $expressionQuery);
            foreach ($orSubQueries as $orSubQuery) {
                $orExpression->add($this->executeExpressionQuery($queryBuilder, $orSubQuery, $level));
                $level ++;
            }
            return $orExpression;
        } else if (strpos($expressionQuery, '&')) {
            $andExpression = $queryBuilder->expr()->andx();
            $andSubQueries = explode('&', $expressionQuery);
            foreach ($andSubQueries as $andSubQuery) {
                $andExpression->add($this->executeExpressionQuery($queryBuilder, $andSubQuery, $level));
                $level ++;
            }
            return $andExpression;
        } else {
            return $queryBuilder->expr()->in(
                    Keyword_Model_Keyword::getAlias().'.'.Keyword_Model_Keyword::QUERY_ID,
                    $this->createSubExpressionQuery($queryBuilder, $expressionQuery, $level)->getDQL()
                );
        }
    }

    /**
     * Créer une sous requête correspondant à l'expressionQuery.
     *
     * @param Doctrine\ORM\QueryBuilder $queryBuilder
     * @param string                    $expressionQuery
     * @param int                       $level
     *
     * @throws Core_Exception_InvalidArgument
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function createSubExpressionQuery($queryBuilder, $expressionQuery, $level)
    {
        list($subject, $predicate, $object) = explode(',', $expressionQuery);
        $subject = trim($subject);
        $predicate = trim($predicate);
        $object = trim($object);

        $queryBuilderExpression = $this->createQueryBuilder(Keyword_Model_Keyword::getAlias().'_'.$level);
        $queryBuilderExpression->distinct();
        $queryBuilderExpression->select(Keyword_Model_Keyword::getAlias().'_'.$level.'.'.Keyword_Model_Keyword::QUERY_ID);

        $this->checkPredicateRefExists($predicate);
        if ($subject == 'this') {
            $this->checkKeywordRefExists($object);
            $queryBuilderExpression->leftJoin(
                    Keyword_Model_Keyword::getAlias().'_'.$level.'.'.'subjectAssociation',
                    Keyword_Model_Association::getAlias().'_'.$level
                );
            $queryBuilderExpression->leftJoin(
                    Keyword_Model_Association::getAlias().'_'.$level.'.'.Keyword_Model_Association::QUERY_OBJECT,
                    Keyword_Model_Keyword::getAliasAsObject().'_'.$level
                );
            $queryBuilder->setParameter('object'.$level, $object);
        } else if ($object == 'this') {
            $this->checkKeywordRefExists($subject);
            $queryBuilderExpression->leftJoin(
                    Keyword_Model_Keyword::getAlias().'_'.$level.'.'.'objectAssociation',
                    Keyword_Model_Association::getAlias().'_'.$level
                );
            $queryBuilderExpression->leftJoin(
                    Keyword_Model_Association::getAlias().'_'.$level.'.'.Keyword_Model_Association::QUERY_SUBJECT,
                    Keyword_Model_Keyword::getAliasAsObject().'_'.$level
                );
            $queryBuilder->setParameter('object'.$level, $subject);
        } else {
            throw new Core_Exception_InvalidArgument('Invalid expression query.');
        }

        $queryBuilderExpression->leftJoin(
                Keyword_Model_Association::getAlias().'_'.$level.'.'.Keyword_Model_Association::QUERY_PREDICATE,
                Keyword_Model_Predicate::getAlias().'_'.$level
            );

        $queryBuilderExpression->where(
                $queryBuilderExpression->expr()->andX(
                        $queryBuilderExpression->expr()->orX(
                                $queryBuilderExpression->expr()->eq(
                                        Keyword_Model_Predicate::getAlias().'_'.$level.'.'.
                                            Keyword_Model_Predicate::QUERY_REF,
                                        ':directPredicate'.$level
                                    ),
                                $queryBuilderExpression->expr()->eq(
                                        Keyword_Model_Predicate::getAlias().'_'.$level.'.'.
                                            Keyword_Model_Predicate::QUERY_REVERSE_REF,
                                        ':reversePredicate'.$level
                                    )
                            ),
                        $queryBuilderExpression->expr()->eq(
                                Keyword_Model_Keyword::getAliasAsObject().'_'.$level.'.'.
                                    Keyword_Model_Keyword::QUERY_REF,
                                ':object'.$level
                            )
                    )
            );

        $queryBuilder->setParameter('directPredicate'.$level, $predicate);
        $queryBuilder->setParameter('reversePredicate'.$level, $predicate);

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
        $queryRefExists->filter->addCondition(Keyword_Model_Keyword::QUERY_REF, $ref);
        if (Keyword_Model_Keyword::countTotal($queryRefExists) <= 0) {
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
        $queryRefExists->filter->addCondition(Keyword_Model_Predicate::QUERY_REF, $ref);
        $queryRefExists->filter->addCondition(Keyword_Model_Predicate::QUERY_REVERSE_REF, $ref);
        if (Keyword_Model_Predicate::countTotal($queryRefExists) <= 0) {
            throw new Core_Exception_NotFound(__('Keyword', 'query', 'predicateNotFound', array('REF' => $ref)));
        }
    }

}