<?php
/**
 * Classe Orga_Model_Repository_Cell
 * @author     valentin.claras
 * @package    Orga
 * @subpackage Model
 */

/**
 * Gère les Cell.
 * @package    Orga
 * @subpackage Repository
 */
class Inventory_Model_Repository_CellDataProvider extends Orga_Model_Repository_Cell
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

        $arrayAuthorisedOrgaQuery = array(
            Orga_Model_Cell::getAlias().Orga_Model_Cell::QUERY_RELEVANT,
            Orga_Model_Cell::getAlias().Orga_Model_Cell::QUERY_ALLPARENTSRELEVANT,
            Orga_Model_Cell::getAlias().Orga_Model_Cell::QUERY_MEMBERS_HASHKEY,
            Orga_Model_Cell::getAlias().Orga_Model_Cell::QUERY_GRANULARITY,
        );
        $arrayAuthorisedAFQuery = array(
            AF_Model_InputSet_Primary::getAlias().AF_Model_InputSet_Primary::QUERY_COMPLETION,
        );

        $needsJoinToOrga = false;
        $needsJoinToAF = false;
        foreach ($queryParameters->filter->getConditions() as $filterConditionArray) {
            if (in_array($filterConditionArray['alias'].$filterConditionArray['name'], $arrayAuthorisedOrgaQuery)) {
                $needsJoinToOrga = true;
            }
            if (in_array($filterConditionArray['alias'].$filterConditionArray['name'], $arrayAuthorisedAFQuery)) {
                $needsJoinToAF = true;
            }
            if ($needsJoinToOrga && $needsJoinToAF) {
                break;
            }
        }
        foreach ($queryParameters->order->getOrders() as $orderArray) {
            if (in_array($orderArray['alias'].$orderArray['name'], $arrayAuthorisedOrgaQuery)) {
                $needsJoinToOrga = true;
            }
            if (in_array($orderArray['alias'].$orderArray['name'], $arrayAuthorisedAFQuery)) {
                $needsJoinToAF = true;
            }
            if ($needsJoinToOrga && $needsJoinToAF) {
                break;
            }
        }
        if (count($queryParameters->getCustomParameters()) > 0) {
            $needsJoinToOrga = true;
        }

        if ($needsJoinToOrga) {
            $queryBuilder->leftJoin(
                Inventory_Model_CellDataProvider::getAlias().'.orgaCell',
                Orga_Model_Cell::getAlias()
            );
        }
        if ($needsJoinToAF) {
            $queryBuilder->leftJoin(
                Inventory_Model_CellDataProvider::getAlias().'.aFInputSetPrimary',
                AF_Model_InputSet_Primary::getAlias()
            );
        }
    }

}