<?php

namespace Classification\Architecture\Repository;

use Classification\Domain\ContextIndicator;
use Classification\Domain\Context;
use Classification\Domain\Indicator;
use Core_Model_Query;
use Core_Model_Repository;
use Doctrine\ORM\QueryBuilder;

/**
 * Repository d'indicateurs contextualisés.
 *
 * @author valentin.claras
 */
class ContextIndicatorRepository extends Core_Model_Repository
{
    /**
     * Ajoute des paramètres personnalisés au QueryBuilder utilisé par le loadList et le countTotal.
     *
     * @param QueryBuilder     $queryBuilder
     * @param Core_Model_Query $queryParameters
     */
    protected function addCustomParametersToQueryBuilder($queryBuilder, Core_Model_Query $queryParameters = null)
    {
        if ($queryParameters === null) {
            return;
        }

        $arrayAuthorisedContextQuery = array(
            Context::getAlias() . Context::QUERY_POSITION,
            Context::getAlias() . Context::QUERY_REF,
            Context::getAlias() . Context::QUERY_LABEL,
        );
        $arrayAuthorisedIndicatorQuery = array(
            Indicator::getAlias() . Indicator::QUERY_POSITION,
            Indicator::getAlias() . Indicator::QUERY_REF,
            Indicator::getAlias() . Indicator::QUERY_LABEL,
        );

        $needsJoinToContext = false;
        $needsJoinToIndicator = false;
        foreach ($queryParameters->filter->getConditions() as $filterConditionArray) {
            if (in_array($filterConditionArray['alias'] . $filterConditionArray['name'], $arrayAuthorisedContextQuery)
            ) {
                $needsJoinToContext = true;
            }
            if (in_array($filterConditionArray['alias'] . $filterConditionArray['name'], $arrayAuthorisedIndicatorQuery)
            ) {
                $needsJoinToIndicator = true;
            }
            if ($needsJoinToContext && $needsJoinToIndicator) {
                break;
            }
        }
        foreach ($queryParameters->order->getOrders() as $orderArray) {
            if (in_array($orderArray['alias'] . $orderArray['name'], $arrayAuthorisedContextQuery)) {
                $needsJoinToContext = true;
            }
            if (in_array($orderArray['alias'] . $orderArray['name'], $arrayAuthorisedIndicatorQuery)) {
                $needsJoinToIndicator = true;
            }
            if ($needsJoinToContext && $needsJoinToIndicator) {
                break;
            }
        }

        if ($needsJoinToContext) {
            $queryBuilder->leftJoin(
                ContextIndicator::getAlias() . '.context',
                Context::getAlias()
            );
        }
        if ($needsJoinToIndicator) {
            $queryBuilder->leftJoin(
                ContextIndicator::getAlias() . '.indicator',
                Indicator::getAlias()
            );
        }
    }

    /**
     * Renvoie le nombre d'éléments total que le loadList peut charger.
     *
     * @param Core_Model_Query $queryParameters Paramètres de la requête
     *
     * @return int
     */
    public function countTotal(Core_Model_Query $queryParameters = null)
    {
        $entityName = $this->getEntityName();
        $entityAlias = $entityName::getAlias();

        $queryParameters->rootAlias = $entityAlias;
        $queryParameters->entityName = $entityName;

        $queryBuilderCountTotal = $this->createQueryBuilder($entityAlias);
        $queryBuilderCountTotal->select($queryBuilderCountTotal->expr()->count($entityAlias));
        $this->addCustomParametersToQueryBuilder($queryBuilderCountTotal, $queryParameters);
        $queryParameters->parseToQueryBuilderWithoutLimit($queryBuilderCountTotal);

        return $this->getQueryFromQueryBuilder($queryBuilderCountTotal)->getSingleScalarResult();
    }
}
