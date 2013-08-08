<?php
/**
 * @author     valentin.claras
 * @package    DW
 * @subpackage Model
 */

/**
 * Repository class
 *
 * @package    DW
 * @subpackage Model
 */
class DW_Model_Repository_Report extends Core_Model_Repository
{

    /**
     * Renvoie un tableau des valeurs du Report donnés.
     *
     * @param DW_Model_Report $report
     *
     * @return array
     */
    public function getValuesForReport($report)
    {
        $numeratorAxis1 = $report->getNumeratorAxis1();
        $numeratorAxis2 = $report->getNumeratorAxis2();
        $denominatorAxis1 = $report->getDenominatorAxis1();
        $denominatorAxis2 = $report->getDenominatorAxis2();

        if ($report->getDenominator() !== null) {
            $isRatio = true;
            // Tableau des identifiants des valeurs du dénominateurs indexées par l'identifiant de celle du numérateur.
            $membersLink = array();
        } else {
            $isRatio = false;
        }

        $values = array();

        // Calcul des valeurs des numérateurs.
        $numeratorResults = $this->getResultForIndicatorAndAxes(
            $report->getNumerator(),
            array($numeratorAxis1, $numeratorAxis2),
            $report->getFilters()
        );

        foreach ($numeratorResults as $result) {
            $identifierValue = '';
            $numeratorMembers = array();
            if ($numeratorAxis1 !== null) {
                $numeratorMember1 = $result->getMemberForAxis($numeratorAxis1);
                $identifierValue .= $numeratorMember1->getId();
                $numeratorMembers[] = $numeratorMember1;
            }
            if ($numeratorAxis2 !== null) {
                $numeratorMember2 = $result->getMemberForAxis($numeratorAxis2);
                $identifierValue .= '#'.$numeratorMember2->getId();
                $numeratorMembers[] = $numeratorMember2;
            }
            if (!isset($values[$identifierValue])) {
                $values[$identifierValue] = array('value' => 0, 'uncertainty' => 0, 'members' => $numeratorMembers);
                if ($isRatio) {
                    // Détermination de l'identifiant du dénominateur.
                    $parentMembersId = '';
                    if (($numeratorAxis1 !== null) && ($numeratorAxis1 === $denominatorAxis1)) {
                        $parentMembersId .= $numeratorMember1->getId();
                    } else if (($numeratorAxis1 !== null) && ($denominatorAxis1 !== null)) {
                        $parentMembersId .= $numeratorMember1->getParentForAxis($denominatorAxis1)->getId();
                    }
                    if (($numeratorAxis2 !== null) && ($numeratorAxis2 === $denominatorAxis2)) {
                        $parentMembersId .= '#'.$numeratorMember2->getId();
                    } else if (($numeratorAxis2 !== null) && ($denominatorAxis2 !== null)) {
                        $parentMembersId .= '#'.$numeratorMember2->getParentForAxis($denominatorAxis2)->getId();
                    }
                    $membersLink[$identifierValue] = $parentMembersId;
                }
            }
            $values[$identifierValue]['value'] += $result->getValue()->digitalValue;
            $values[$identifierValue]['uncertainty'] += pow(
                ($result->getValue()->relativeUncertainty * $result->getValue()->digitalValue),
                2
            );
        }

        // Calcul de l'incertitude relative.
        foreach ($values as $numeratorIdentifier => $value) {
            if ($value['value'] != 0) {
                $values[$numeratorIdentifier]['uncertainty'] = sqrt($value['uncertainty']);
                $values[$numeratorIdentifier]['uncertainty'] /= $value['value'];
            } else {
                $values[$numeratorIdentifier]['uncertainty'] = 0;
            }
        }

        // Calcul des valeurs des dénominateurs.
        if ($isRatio) {
            $ratioValues = array();

            $denominatorResults = $this->getResultForIndicatorAndAxes(
                $report->getDenominator(),
                array($denominatorAxis1, $denominatorAxis2)
            );

            // Tableau des identifiants des valeur des ratios (enfants) indxant l'identifiant des numérateurs (parents).
            foreach ($denominatorResults as $result) {
                $identifierValue = '';
                if ($denominatorAxis1 !== null) {
                    $identifierValue .= $result->getMemberForAxis($denominatorAxis1)->getId();
                    if ($denominatorAxis2 !== null) {
                        $identifierValue .= '#'.$result->getMemberForAxis($denominatorAxis2)->getId();
                    }
                }
                if (!isset($ratioValues[$identifierValue])) {
                    $ratioValues[$identifierValue] = array('value' => 0, 'uncertainty' => 0);
                }
                $ratioValues[$identifierValue]['value'] += $result->getValue()->digitalValue;
                $ratioValues[$identifierValue]['uncertainty'] += pow(
                    ($result->getValue()->relativeUncertainty * $result->getValue()->digitalValue),
                    2
                );
            }

            // Calcul de l'incertitude relative.
            foreach ($ratioValues as $denominatorIdentifier => $ratioValue) {
                $ratioValues[$denominatorIdentifier]['uncertainty'] = sqrt($ratioValue['uncertainty']);
                $ratioValues[$denominatorIdentifier]['uncertainty'] /= $ratioValue['value'];
                if ($ratioValue['value'] != 0) {
                    $ratioValues[$denominatorIdentifier]['uncertainty'] = sqrt($ratioValue['uncertainty']);
                    $ratioValues[$denominatorIdentifier]['uncertainty'] /= $ratioValue['value'];
                } else {
                    $ratioValues[$denominatorIdentifier]['uncertainty'] /= 0;
                }
            }

            // Calcul des ratios.
            foreach ($values as $numeratorIdentifier => $value) {
                $denominatorIdentifier = $membersLink[$numeratorIdentifier];
                if ((isset($ratioValues[$denominatorIdentifier])) && ($ratioValues[$denominatorIdentifier]['value'] != 0)) {
                    $values[$numeratorIdentifier]['value'] /= $ratioValues[$denominatorIdentifier]['value'];
                    $values[$numeratorIdentifier]['uncertainty'] = sqrt(
                        pow($values[$numeratorIdentifier]['uncertainty'], 2)
                        +
                        pow($ratioValues[$denominatorIdentifier]['uncertainty'], 2)
                    );
                } else {
                    // Mettre la valeur à 0 !
                    $values[$numeratorIdentifier]['value'] = 0;
                    $values[$numeratorIdentifier]['uncertainty'] = 0;
                }
            }
        }

        if (($report->getNumeratorAxis2() === null)
            && ($report->getSortType() === DW_Model_Report::SORT_VALUE_INCREASING)) {
            usort($values, array('DW_Model_Repository_Report', 'orderResultByIncreasingValue'));
        } else if (($report->getNumeratorAxis2() === null)
            && ($report->getSortType() === DW_Model_Report::SORT_VALUE_DECREASING)) {
            usort($values, array('DW_Model_Repository_Report', 'orderResultByDecreasingValue'));
        } else {
            usort($values, array('DW_Model_Repository_Report', 'orderResultByMember'));
        }

        return $values;
    }

    /**
     * Donne une série de résultats
     *
     * @param DW_Model_Indicator $indicator
     * @param DW_Model_Axis[] $axes
     * @param DW_Model_Filter[] $filters
     *
     * @return DW_Model_Result[]
     */
    protected function getResultForIndicatorAndAxes(DW_Model_Indicator $indicator, array $axes, $filters=array())
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->select(DW_Model_Result::getAlias());
        $queryBuilder->distinct();
        $queryBuilder->from('DW_Model_Result', DW_Model_Result::getAlias());
        $queryBuilder->where(
            $queryBuilder->expr()->eq(
                DW_Model_Result::getAlias().'.'.DW_Model_Result::QUERY_INDICATOR,
                ':indicator'
            )
        );
        $queryBuilder->setParameter('indicator', $indicator);

        foreach ($axes as $axis) {
            if ($axis !== null) {
                if (!$axis->hasMembers()) {
                    return [];
                }
                $memberAlias = DW_Model_Member::getAlias().'_Axis'.$axis->getRef();
                $queryBuilder->leftJoin(DW_Model_Result::getAlias().'.members', $memberAlias);
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->in(
                        $memberAlias,
                        ':axis'.'_'.$axis->getRef()
                    )
                );
                $queryBuilder->setParameter('axis'.'_'.$axis->getRef(), $axis->getMembers());
            }
        }

        foreach ($filters as $filter) {
            $subSelectAxisFilter = $this->getEntityManager()->createQueryBuilder();

            $subAliasAxis = DW_Model_Result::getAlias().'_filter'.$filter->getAxis()->getRef();
            $subMembersAlias = $subAliasAxis.'_'.DW_Model_Member::getAlias();
            $subSelectAxisFilter->select($subAliasAxis);
            $subSelectAxisFilter->from('DW_Model_Result', $subAliasAxis);
            $subSelectAxisFilter->leftJoin($subAliasAxis.'.members', $subMembersAlias);

            $subSelectAxisFilter->where(
                $subSelectAxisFilter->expr()->in(
                    $subMembersAlias,
                    ':members_'.$subMembersAlias
                )
            );

            $queryBuilder->andWhere(
                $queryBuilder->expr()->in(
                    DW_Model_Result::getAlias(),
                    $subSelectAxisFilter->getDQL()
                )
            );
            $queryBuilder->setParameter('members_'.$subMembersAlias, $filter->getMembers());
        }

        return $this->getQueryFromQueryBuilder($queryBuilder)->getResult();
    }

    /**
     * Ajoute des paramètres personnalisés au QueryBuilder utilisé par le loadList et le countTotal.
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param Core_Model_Query $queryParameters
     */
    protected function addCustomParametersToQueryBuilder($queryBuilder, Core_Model_Query $queryParameters=null)
    {
        // Nothing added by default !
    }

    /**
     * Fonction de tri personnalisé des résultats par valeurs croissantes.
     *
     * @param array $a
     * @param array $b
     *
     * @return int
     */
    protected function orderResultByIncreasingValue($a, $b)
    {
        return $this->orderResultByValue($a, $b, true);
    }

    /**
     * Fonction de tri personnalisé des résultats par valeurs décroissantes.
     *
     * @param array $a
     * @param array $b
     *
     * @return int
     */
    protected function orderResultByDecreasingValue($a, $b)
    {
        return $this->orderResultByValue($a, $b, false);
    }

    /**
     * Fonction de tri personnalisé des résultats par valeur.
     *
     * @param array $a
     * @param array $b
     * @param bool $increasing
     *
     * @return int
     */
    protected function orderResultByValue($a, $b, $increasing=true)
    {
        $multiplier = ($increasing) ? 1 : -1;

        if ($a['value'] > $b['value']) {
            return $multiplier * 1;
        } else if ($a['value'] < $b['value']) {
            return - $multiplier * 1;
        } else if ($a['uncertainty'] > $b['uncertainty']) {
            return $multiplier * 1;
        } else if ($a['uncertainty'] < $b['uncertainty']) {
            return - $multiplier * 1;
        } else {
            return 0;
        }
    }

    /**
     * Fonction de tri personnalisé des résultats par ordre des membres.
     *
     * @param array $a
     * @param array $b
     *
     * @return int
     */
    protected function orderResultByMember($a, $b)
    {
        if ($a['members'][0] === $b['members'][0]) {
            return $a['members'][1]->getPosition() - $b['members'][1]->getPosition();
        }
        return $a['members'][0]->getPosition() - $b['members'][0]->getPosition();
    }

}
