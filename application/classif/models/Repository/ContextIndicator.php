<?php
/**
 * Classe Classif_Model_Repository_ContextIndicator
 * @author     valentin.claras
 * @package    Classif
 * @subpackage Model
 */

/**
 * Gère les ContextIndicator.
 * @package    Classif
 * @subpackage Repository
 */
class Classif_Model_Repository_ContextIndicator extends Core_Model_Repository
{

    /**
     * Ajoute des paramètres personnalisés au QueryBuilder utilisé par le loadList et le countTotal.
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param Core_Model_Query $queryParameters
     */
    protected function addCustomParametersToQueryBuilder($queryBuilder, Core_Model_Query $queryParameters=null)
    {
        if ($queryParameters === null) {
            return;
        }

        $arrayAuthorisedContextQuery = array(
            Classif_Model_Context::getAlias().Classif_Model_Context::QUERY_POSITION,
            Classif_Model_Context::getAlias().Classif_Model_Context::QUERY_REF,
            Classif_Model_Context::getAlias().Classif_Model_Context::QUERY_LABEL,
        );
        $arrayAuthorisedIndicatorQuery = array(
            Classif_Model_Indicator::getAlias().Classif_Model_Indicator::QUERY_POSITION,
            Classif_Model_Indicator::getAlias().Classif_Model_Indicator::QUERY_REF,
            Classif_Model_Indicator::getAlias().Classif_Model_Indicator::QUERY_LABEL,
        );

        $needsJoinToContext = false;
        $needsJoinToIndicator = false;
        foreach ($queryParameters->filter->getConditions() as $filterConditionArray) {
            if (in_array($filterConditionArray['alias'].$filterConditionArray['name'], $arrayAuthorisedContextQuery)) {
                $needsJoinToContext = true;
            }
            if (in_array($filterConditionArray['alias'].$filterConditionArray['name'], $arrayAuthorisedIndicatorQuery)) {
                $needsJoinToIndicator = true;
            }
            if ($needsJoinToContext && $needsJoinToIndicator) {
                break;
            }
        }
        foreach ($queryParameters->order->getOrders() as $orderArray) {
            if (in_array($orderArray['alias'].$orderArray['name'], $arrayAuthorisedContextQuery)) {
                $needsJoinToContext = true;
            }
            if (in_array($orderArray['alias'].$orderArray['name'], $arrayAuthorisedIndicatorQuery)) {
                $needsJoinToIndicator = true;
            }
            if ($needsJoinToContext && $needsJoinToIndicator) {
                break;
            }
        }

        if ($needsJoinToContext) {
            $queryBuilder->leftJoin(
                Classif_Model_ContextIndicator::getAlias().'.context',
                Classif_Model_Context::getAlias()
            );
        }
        if ($needsJoinToIndicator) {
            $queryBuilder->leftJoin(
                Classif_Model_ContextIndicator::getAlias().'.indicator',
                Classif_Model_Indicator::getAlias()
            );
        }
    }

}