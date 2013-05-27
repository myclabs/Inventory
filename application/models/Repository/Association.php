<?php
/**
 * @author     valentin.claras
 * @package    Core
 * @subpackage Test
 */

/**
 * Repository class
 *
 * @package    Core
 * @subpackage Test
 */
class Default_Model_Repository_Association extends Core_Model_Repository
{

    /**
     * Effectue un leftJoin sur l'association avec Default_Model_Simple.
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     */
    protected function leftJoinSimple(\Doctrine\ORM\QueryBuilder $queryBuilder)
    {
        $queryBuilder->leftJoin(
                Default_Model_Association::getAlias().'.'.Default_Model_Association::QUERY_SIMPLE,
                Default_Model_Simple::getAlias()
            );
    }

    /**
     * Renvoie les Default_Model_Association avec plus que X Simple.
     *
     * @param Core_Model_Query $queryParameters
     */
    public function getAssociationWithMoreThanXSimples(Core_Model_Query $queryParameters)
    {
        $queryBuilder = $this->createQueryBuilder(Default_Model_Association::getAlias());
        $queryBuilder->distinct();
        $queryParameters->rootAlias = Default_Model_Association::getAlias();
        $this->leftJoinSimple($queryBuilder);
        $queryParameters->parseToQueryBuilderWithLimit($queryBuilder);
        $queryBuilder->andWhere(
                $queryBuilder->expr()->in(
                        Default_Model_Association::getAlias().'.'.Default_Model_Association::QUERY_ID,
                        $this->getSubQueryMoreThanXSimples($queryParameters)->getDQL()
                    )
            );

        return $this->getQueryFromQueryBuilder($queryBuilder)->getResult();
    }

    /**
     * Renvoie les Default_Model_Association avec plus que X Simple.
     *
     * @param Core_Model_Query $queryParameters
     */
    public function CountAssociationWithMoreThanXSimples(Core_Model_Query $queryParameters)
    {
        $queryBuilderCountTotal = $this->createQueryBuilder(Default_Model_Association::getAlias());
        $queryBuilderCountTotal->select($queryBuilderCountTotal->expr()->countDistinct(Default_Model_Association::getAlias()));
        $queryParameters->rootAlias = Default_Model_Association::getAlias();
        $this->leftJoinSimple($queryBuilderCountTotal);
        $queryParameters->parseToQueryBuilderWithLimit($queryBuilderCountTotal);
        $queryBuilderCountTotal->andWhere(
                $queryBuilderCountTotal->expr()->in(
                        Default_Model_Association::getAlias().'.'.Default_Model_Association::QUERY_ID,
                        $this->getSubQueryMoreThanXSimples($queryParameters)->getDQL()
                    )
            );

        return $this->getQueryFromQueryBuilder($queryBuilderCountTotal)->getSingleScalarResult();
    }

    /**
     * Fourni la sous requête récupérant les Association possédant plus de X Simple.
     *
     * @param Core_Model_Query $queryParameters
     *
     * @return Doctrine\ORM\QueryBuilder
     */
    protected function getSubQueryMoreThanXSimples(Core_Model_Query $queryParameters)
    {
        $selectMoreThanX = $this->createQueryBuilder('SubAss');
        $selectMoreThanX->select('SubAss.'.Default_Model_Association::QUERY_ID);
        $selectMoreThanX->leftJoin(
                'SubAss.'.Default_Model_Association::QUERY_SIMPLE,
                'Sub'.Default_Model_Simple::getAlias()
            );
        $selectMoreThanX->groupBy('SubAss.'.Default_Model_Association::QUERY_ID);
        $selectMoreThanX->having(
                $selectMoreThanX->expr()->gt(
                        $selectMoreThanX->expr()->count(
                                'Sub'.Default_Model_Simple::getAlias().'.'.Default_Model_Simple::QUERY_ID
                            ),
                        $queryParameters->xSimple
                    )
            );

        return $selectMoreThanX;
    }

}
