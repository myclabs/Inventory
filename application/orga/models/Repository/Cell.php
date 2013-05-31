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
class Orga_Model_Repository_Cell extends Core_Model_Repository
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

        $arrayAuthorisedAFQuery = array(
            AF_Model_InputSet_Primary::getAlias().AF_Model_InputSet_Primary::QUERY_COMPLETION,
        );

        $needsJoinToAF = false;
        foreach ($queryParameters->filter->getConditions() as $filterConditionArray) {
            if (in_array($filterConditionArray['alias'].$filterConditionArray['name'], $arrayAuthorisedAFQuery)) {
                $needsJoinToAF = true;
            }
            if ($needsJoinToAF) {
                break;
            }
        }
        foreach ($queryParameters->order->getOrders() as $orderArray) {
            if (in_array($orderArray['alias'].$orderArray['name'], $arrayAuthorisedAFQuery)) {
                $needsJoinToAF = true;
            }
            if ($needsJoinToAF) {
                break;
            }
        }

        if ($needsJoinToAF) {
            $queryBuilder->leftJoin(
                Orga_Model_Cell::getAlias().'.aFInputSetPrimary',
                AF_Model_InputSet_Primary::getAlias()
            );
        }
    }

    /**
     * Charge la liste des Cell possédant l'exact liste des membres données.
     *
     * @param array $arrayMembers Tableau des membres indxés par la granularité.
     * @param Core_Model_Query $queryParameters Paramètres de la requête.
     *
     * @return Orga_Model_Cell[]
     */
    public function loadByMembers($arrayMembers, Core_Model_Query $queryParameters)
    {
        $entityName = $this->getEntityName();
        $cellAlias = $entityName::getAlias();

        $queryBuilder = $this->createQueryBuilder($cellAlias);
        $queryBuilder->distinct();
        $queryParameters->rootAlias = $cellAlias;
        $this->addCustomParametersToQueryBuilder($queryBuilder, $queryParameters);
        $queryParameters->parseToQueryBuilderWithLimit($queryBuilder);
        $this->addMembersFiltersToQueryBuilder($queryParameters, $queryBuilder);
        $this->parseArrayMembersToQueryBuilder($arrayMembers, $queryBuilder);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Compte le nombre de Cell possédant l'exact liste des membres données.
     *
     * @param array $arrayMembers Tableau des membres indxés par la granularité.
     * @param Core_Model_Query $queryParameters Paramètres de la requête.
     *
     * @return Orga_Model_Cell[]
     */
    public function countTotalByMembers($arrayMembers, Core_Model_Query $queryParameters)
    {
        $entityName = $this->getEntityName();
        $cellAlias = $entityName::getAlias();

        $queryBuilder = $this->createQueryBuilder($cellAlias);
        $queryBuilder->select($queryBuilder->expr()->countDistinct($cellAlias));
        $queryParameters->rootAlias = $cellAlias;
        $this->addCustomParametersToQueryBuilder($queryBuilder, $queryParameters);
        $queryParameters->parseToQueryBuilderWithoutLimit($queryBuilder);
        $this->addMembersFiltersToQueryBuilder($queryParameters, $queryBuilder);
        $this->parseArrayMembersToQueryBuilder($arrayMembers, $queryBuilder);

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @param Core_Model_Query $queryParameters
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     */
    protected function addMembersFiltersToQueryBuilder($queryParameters, $queryBuilder)
    {
        foreach ($queryParameters->getCustomParameters() as $memberFilter) {
            $queryBuilder->leftJoin(
                Orga_Model_Cell::getAlias().'.members',
                Orga_Model_Member::getAlias().$memberFilter['name']
            );
            if ((is_array($memberFilter['value'])) || (strpos($memberFilter['value'], ','))) {
                $orMembers = $queryBuilder->expr()->orX();
                if (strpos($memberFilter['value'], ',')) {
                    $memberFilter['value'] = explode(',', $memberFilter['value']);
                }
                foreach ($memberFilter['value'] as $indexValue => $memberFilterValue) {
                    $orMembers->add(
                        $queryBuilder->expr()->eq(
                            Orga_Model_Member::getAlias().$memberFilter['name'].'.'.Orga_Model_Member::QUERY_REF,
                            ':members_'.$memberFilter['name'].'_'.$indexValue
                        )
                    );
                    $queryBuilder->setParameter('members_'.$memberFilter['name'].'_'.$indexValue, $memberFilterValue);
                }
                $queryBuilder->andWhere($orMembers);
            } else {
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->eq(
                        Orga_Model_Member::getAlias().$memberFilter['name'].'.'.Orga_Model_Member::QUERY_REF,
                        ':members_'.$memberFilter['name']
                    )
                );
                $queryBuilder->setParameter('members_'.$memberFilter['name'], $memberFilter['value']);
            }
        }
    }

    /**
     * Ajoute les membres du tableau au queryBuilder.
     *
     * @param array $arrayMembers
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     */
    protected function parseArrayMembersToQueryBuilder($arrayMembers, $queryBuilder)
    {
        $queryBuilder->leftJoin(
            Orga_Model_Cell::getAlias().'.members',
            Orga_Model_Member::getAlias()
        );

        $orExpression = $queryBuilder->expr()->orx();
        foreach ($arrayMembers as $granularityIndex => $granularityMembers) {
            $granularityMembersExpression = $this->getGranularityMembersNeededExpression(
                $granularityIndex,
                $granularityMembers,
                $queryBuilder
            );
            $orExpression->add($granularityMembersExpression['expression']);
            foreach ($granularityMembersExpression['parameters'] as $indexParameter => $parameter) {
                $queryBuilder->setParameter($indexParameter, $parameter);
            }
        }

        if ($orExpression->count() > 0) {
            $queryBuilder->andWhere($orExpression);
        }
    }

    /**
     * Renvoie l'expression des cellules d'une granularité données possédant les membres souhaités.
     *  Ajoute les paramètres au queryBuilder principal.
     *
     * @param int $granularityIndex
     * @param array $granularityMembers
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     *
     * @return array('expression' => \Doctrine\ORM\Query\Expr\Andx, 'parameters' => array())
     */
    protected function getGranularityMembersNeededExpression($granularityIndex, $granularityMembers, $queryBuilder)
    {
        $cellAlias = Orga_Model_Cell::getAlias();

        $granularityMembersExpression = $queryBuilder->expr()->andX();
        $granularityMembersExpression->add(
            $queryBuilder->expr()->eq(
                $cellAlias.'.'.Orga_Model_Cell::QUERY_GRANULARITY,
                ':granularity'.$granularityIndex
            )
        );

        $parameters = array();
        if (!empty($granularityMembers['members'])) {
            foreach ($granularityMembers['members'] as $refAxis => $axeMembers) {
                if (empty($axeMembers)) {
                    continue;
                }
                $subCellAlias = $cellAlias.'_'.$granularityIndex.'_'.$refAxis;
                $subMembersAlias = Orga_Model_Member::getAlias().'_'.$granularityIndex.'_'.$refAxis;
                $selectMembers = $this->_em->createQueryBuilder()->select($subCellAlias);
                $selectMembers->from('Orga_Model_Cell', $subCellAlias);
                $selectMembers->distinct();
                $selectMembers->leftJoin($subCellAlias.'.members', $subMembersAlias);
                $selectMembers->where(
                    $selectMembers->expr()->in(
                        $subMembersAlias,
                        ':members_'.$subMembersAlias
                    )
                );
                $parameters['members_'.$subMembersAlias] = $axeMembers;
                $granularityMembersExpression->add(
                    $queryBuilder->expr()->in(
                        Orga_Model_Cell::getAlias(),
                        $selectMembers->getDQL()
                    )
                );
            }
        }
        $parameters['granularity'.$granularityIndex] = $granularityMembers['granularity'];

        return array('expression' => $granularityMembersExpression, 'parameters' => $parameters);
    }

}