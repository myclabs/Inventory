<?php
/**
 * Classe Keyword_Model_Repository_Association
 * @author     valentin.claras
 * @package    Keyword
 * @subpackage Model
 */

/**
 * Gère les Association.
 * @package    Keyword
 * @subpackage Model
 */
class Keyword_Model_Repository_Association extends Core_Model_Repository
{

    /**
     * Ajoute des paramètres personnalisés au QueryBuilder utilisé par le loadList et le countTotal.
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     */
    protected function addCustomParametersToQueryBuilder($queryBuilder, Core_Model_Query $queryParameters=null)
    {
        $this->leftJoinSubject($queryBuilder);
        $this->leftJoinObject($queryBuilder);
        $this->leftJoinPredicate($queryBuilder);
    }

    /**
     * Effectue un leftJoin sur l'association avec Keyword_Model_Keyword en tant que sujet.
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     */
    protected function leftJoinSubject(\Doctrine\ORM\QueryBuilder $queryBuilder)
    {
        $queryBuilder->leftJoin(
                Keyword_Model_Association::getAlias().'.'.Keyword_Model_Association::QUERY_SUBJECT,
                Keyword_Model_Keyword::getAliasAsSubject()
            );
    }

    /**
     * Effectue un leftJoin sur l'association avec Keyword_Model_Keyword en tant qu'objet.
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     */
    protected function leftJoinObject(\Doctrine\ORM\QueryBuilder $queryBuilder)
    {
        $queryBuilder->leftJoin(
                Keyword_Model_Association::getAlias().'.'.Keyword_Model_Association::QUERY_OBJECT,
                Keyword_Model_Keyword::getAliasAsObject()
            );
    }

    /**
     * Effectue un leftJoin sur l'association avec Keyword_Model_Predicate.
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     */
    protected function leftJoinPredicate(\Doctrine\ORM\QueryBuilder $queryBuilder)
    {
        $queryBuilder->leftJoin(
                Keyword_Model_Association::getAlias().'.'.Keyword_Model_Association::QUERY_PREDICATE,
                Keyword_Model_Predicate::getAlias()
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
     * @return Keyword_Model_Association
     */
    public function loadByRefs($subjectKeywordRef, $objectKeywordRef, $predicateRef)
    {
        $entityName = $this->getEntityName();

        $queryBuilderLoadByRefs = $this->createQueryBuilder($entityName::getAlias());
        $queryBuilderLoadByRefs->distinct();
        $this->addCustomParametersToQueryBuilder($queryBuilderLoadByRefs);

        $subjectAlias = Keyword_Model_Keyword::getAliasAsSubject().'.'.Keyword_Model_Keyword::QUERY_REF;
        $subjectBindKey = 'subjectRef';
        $subjectCondition = $queryBuilderLoadByRefs->expr()->eq($subjectAlias, ':'.$subjectBindKey);
        $objectAlias = Keyword_Model_Keyword::getAliasAsObject().'.'.Keyword_Model_Keyword::QUERY_REF;
        $objectBindKey = 'objectRef';
        $objectCondition = $queryBuilderLoadByRefs->expr()->eq($objectAlias, ':'.$objectBindKey);
        $predicateAlias = Keyword_Model_Predicate::getAlias().'.'.Keyword_Model_Predicate::QUERY_REF;
        $predicateBindKey = 'predicateRef';
        $predicateCondition = $queryBuilderLoadByRefs->expr()->eq($predicateAlias, ':'.$predicateBindKey);

        $queryBuilderLoadByRefs->andWhere($subjectCondition);
        $queryBuilderLoadByRefs->andWhere($objectCondition);
        $queryBuilderLoadByRefs->andWhere($predicateCondition);
        $queryBuilderLoadByRefs->setParameter($subjectBindKey, $subjectKeywordRef);
        $queryBuilderLoadByRefs->setParameter($objectBindKey, $objectKeywordRef);
        $queryBuilderLoadByRefs->setParameter($predicateBindKey, $predicateRef);

        $entities = $this->getQueryFromQueryBuilder($queryBuilderLoadByRefs)->getResult();
        if (empty($entities)) {
            $criteria = array($subjectKeywordRef, $objectKeywordRef, $predicateRef);
            throw new Core_Exception_NotFound('No "'.$entityName.'" matching '.var_export($criteria, true));
        } else if (count($entities) > 1) {
            $criteria = array($subjectKeywordRef, $objectKeywordRef, $predicateRef);
            throw new Core_Exception_TooMany('Too many "'.$entityName.'" matching '.var_export($criteria, true));
        }

        return $entities[0];
    }

}