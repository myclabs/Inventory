<?php
/**
 * @author     valentin.claras
 */

namespace DW\Architecture\Repository;

use Core_Model_Query;
use Core_Model_Repository;
use Doctrine\ORM\QueryBuilder;
use DW\Domain\Axis;
use DW\Domain\Filter;
use DW\Domain\Indicator;
use DW\Domain\Member;
use DW\Domain\Report;
use DW\Domain\Result;

/**
 * @package    DW
 * @subpackage Repository
 */
class ReportRepository extends Core_Model_Repository
{
    /**
     * @var array Contient les ref des membres de l'axe 1 triés par ordre ascendant de la somme des valeurs
     * Utilisé pour le trie des valeurs dans le cas d'un report à deux axes,
     * les valeurs sont la somme des valeurs pour l'axe 2
     */
    protected $orderedAxis1;

    /**
     * @param Report $report
     * @return array
     */
    public function getValuesForReport(Report $report)
    {
        $numerator = $report->getNumeratorIndicator();
        $denominator = $report->getDenominatorIndicator();
        $numeratorAxis1 = $report->getNumeratorAxis1();
        $numeratorAxis2 = $report->getNumeratorAxis2();
        $denominatorAxis1 = $report->getDenominatorAxis1();
        $denominatorAxis2 = $report->getDenominatorAxis2();

        $isRatio = false;
        if ($denominator !== null) {
            $isRatio = true;
            $numeratorConversionFactor = $numerator->getRatioUnit()->getConversionFactor(
                $numerator->getUnit()->getRef()
            );
            $denominatorConversionFactor = $denominator->getRatioUnit()->getConversionFactor(
                $denominator->getUnit()->getRef()
            );
            $conversionFactor = $numeratorConversionFactor / $denominatorConversionFactor;
            // Tableau des identifiants des valeurs du dénominateur indexées par l'identifiant de celle du numérateur.
            $membersLink = [];
        }

        $values = [];

        // Calcul des valeurs des numérateurs.
        $numeratorResults = $this->getResultForIndicatorAndAxes(
            $numerator,
            [$numeratorAxis1, $numeratorAxis2],
            $report->getFilters()
        );

        foreach ($numeratorResults as $result) {
            $identifierValue = '';
            $numeratorMembers = [];
            if ($numeratorAxis1 !== null) {
                $numeratorMember1 = $result->getMemberForAxis($numeratorAxis1);
                $identifierValue .= $numeratorMember1->getId();
                $numeratorMembers[] = $numeratorMember1;
            }
            if ($numeratorAxis2 !== null) {
                $numeratorMember2 = $result->getMemberForAxis($numeratorAxis2);
                $identifierValue .= '#' . $numeratorMember2->getId();
                $numeratorMembers[] = $numeratorMember2;
            }
            if (!isset($values[$identifierValue])) {
                $values[$identifierValue] = ['value' => 0, 'uncertainty' => 0, 'members' => $numeratorMembers];
                if ($isRatio) {
                    // Détermination de l'identifiant du dénominateur.
                    $parentMembersId = '';
                    if (($numeratorAxis1 !== null) && ($numeratorAxis1 === $denominatorAxis1)) {
                        $parentMembersId .= $numeratorMember1->getId();
                    } elseif (($numeratorAxis1 !== null) && ($denominatorAxis1 !== null)) {
                        $parentMembersId .= $numeratorMember1->getParentForAxis($denominatorAxis1)->getId();
                    }
                    if (($numeratorAxis2 !== null) && ($numeratorAxis2 === $denominatorAxis2)) {
                        $parentMembersId .= '#' . $numeratorMember2->getId();
                    } elseif (($numeratorAxis2 !== null) && ($denominatorAxis2 !== null)) {
                        $parentMembersId .= '#' . $numeratorMember2->getParentForAxis($denominatorAxis2)->getId();
                    }
                    $membersLink[$identifierValue] = $parentMembersId;
                }
            }
            $values[$identifierValue]['value'] += $result->getValue()->getDigitalValue();
            $values[$identifierValue]['uncertainty'] += pow(
                ($result->getValue()->getRelativeUncertainty() * $result->getValue()->getDigitalValue()),
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
            $ratioValues = [];

            $denominatorResults = $this->getResultForIndicatorAndAxes(
                $denominator,
                [$denominatorAxis1, $denominatorAxis2]
            );

            // Tableau des identifiants des valeur des ratios (enfants) indxant l'identifiant des numérateurs (parents).
            foreach ($denominatorResults as $result) {
                $identifierValue = '';
                if ($denominatorAxis1 !== null) {
                    $identifierValue .= $result->getMemberForAxis($denominatorAxis1)->getId();
                    if ($denominatorAxis2 !== null) {
                        $identifierValue .= '#' . $result->getMemberForAxis($denominatorAxis2)->getId();
                    }
                }
                if (!isset($ratioValues[$identifierValue])) {
                    $ratioValues[$identifierValue] = ['value' => 0, 'uncertainty' => 0];
                }
                $ratioValues[$identifierValue]['value'] += $result->getValue()->getDigitalValue();
                $ratioValues[$identifierValue]['uncertainty'] += pow(
                    ($result->getValue()->getRelativeUncertainty() * $result->getValue()->getDigitalValue()),
                    2
                );
            }

            // Calcul de l'incertitude relative.
            foreach ($ratioValues as $denominatorIdentifier => $ratioValue) {
                if ($ratioValue['value'] != 0) {
                    $ratioValues[$denominatorIdentifier]['uncertainty'] = sqrt($ratioValue['uncertainty']);
                    $ratioValues[$denominatorIdentifier]['uncertainty'] /= $ratioValue['value'];
                } else {
                    $ratioValues[$denominatorIdentifier]['uncertainty'] = 0;
                }
            }

            // Calcul des ratios.
            foreach ($values as $numeratorIdentifier => $value) {
                $denominatorIdentifier = $membersLink[$numeratorIdentifier];
                if ((isset($ratioValues[$denominatorIdentifier]))
                    && ($ratioValues[$denominatorIdentifier]['value'] != 0)
                ) {
                    $values[$numeratorIdentifier]['value'] /= $ratioValues[$denominatorIdentifier]['value'];
                    $values[$numeratorIdentifier]['uncertainty'] = sqrt(
                        pow($values[$numeratorIdentifier]['uncertainty'], 2)
                        +
                        pow($ratioValues[$denominatorIdentifier]['uncertainty'], 2)
                    );
                    // Conversion dans l'unité pour ratio.
                    $values[$numeratorIdentifier]['value'] *= $conversionFactor;
                } else {
                    // Mettre la valeur à 0 !f
                    $values[$numeratorIdentifier]['value'] = 0;
                    $values[$numeratorIdentifier]['uncertainty'] = 0;
                }
            }
        }

        if ($report->getSortType() === Report::SORT_VALUE_INCREASING) {
            if ($report->getNumeratorAxis2() === null) {
                $sortingMethod = 'orderResultByIncreasingValue';
            }
            else {
                $sum = $this->getAxis1Sum($values);
                asort($sum);
                $this->orderedAxis1 = array_keys($sum);
                $sortingMethod = 'orderResultByIncreasingValueAndByMember';
            }
            usort(
                $values,
                ['DW\Architecture\Repository\ReportRepository', $sortingMethod]
            );
        } elseif ($report->getSortType() === Report::SORT_VALUE_DECREASING) {
            if ($report->getNumeratorAxis2() === null) {
                $sortingMethod = 'orderResultByDecreasingValue';
            }
            else {
                $sum = $this->getAxis1Sum($values);
                asort($sum);
                $this->orderedAxis1 = array_keys($sum);
                $sortingMethod = 'orderResultByDecreasingValueAndByMember';
            }
            usort(
                $values,
                ['DW\Architecture\Repository\ReportRepository', $sortingMethod]
            );
        } else {
            usort(
                $values,
                ['DW\Architecture\Repository\ReportRepository', 'orderResultByMember']
            );
        }

        return $values;
    }

    /**
     * @param Indicator $indicator
     * @param Axis[] $axes
     * @param Filter[] $filters
     * @return Result[]
     */
    protected function getResultForIndicatorAndAxes(Indicator $indicator, array $axes, $filters = [])
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->select(Result::getAlias());
        $queryBuilder->distinct();
        $queryBuilder->from('DW\Domain\Result', Result::getAlias());
        $queryBuilder->where(
            $queryBuilder->expr()->eq(
                Result::getAlias() . '.' . Result::QUERY_INDICATOR,
                ':indicator'
            )
        );
        $queryBuilder->setParameter('indicator', $indicator);

        foreach ($axes as $axis) {
            if ($axis !== null) {
                if (!$axis->hasMembers()) {
                    return [];
                }
                $memberAlias = Member::getAlias() . '_Axis' . $axis->getRef();
                $queryBuilder->leftJoin(Result::getAlias() . '.members', $memberAlias);
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->in(
                        $memberAlias,
                        ':axis' . '_' . $axis->getRef()
                    )
                );
                $queryBuilder->setParameter('axis' . '_' . $axis->getRef(), $axis->getMembers());
            }
        }

        foreach ($filters as $filter) {
            $subSelectAxisFilter = $this->getEntityManager()->createQueryBuilder();

            $subAliasAxis = Result::getAlias() . '_filter' . $filter->getAxis()->getRef();
            $subMembersAlias = $subAliasAxis . '_' . Member::getAlias();
            $subSelectAxisFilter->select($subAliasAxis);
            $subSelectAxisFilter->from('DW\Domain\Result', $subAliasAxis);
            $subSelectAxisFilter->leftJoin($subAliasAxis . '.members', $subMembersAlias);

            $subSelectAxisFilter->where(
                $subSelectAxisFilter->expr()->in(
                    $subMembersAlias,
                    ':members_' . $subMembersAlias
                )
            );

            $queryBuilder->andWhere(
                $queryBuilder->expr()->in(
                    Result::getAlias(),
                    $subSelectAxisFilter->getDQL()
                )
            );
            $queryBuilder->setParameter('members_' . $subMembersAlias, $filter->getMembers()->toArray());
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Fonction de tri personnalisé des résultats par valeurs croissantes.
     *
     * @param array $a
     * @param array $b
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
     * @return int
     */
    protected function orderResultByValue($a, $b, $increasing = true)
    {
        $multiplier = ($increasing) ? 1 : -1;

        if ($a['value'] > $b['value']) {
            return $multiplier * 1;
        } elseif ($a['value'] < $b['value']) {
            return -$multiplier * 1;
        } elseif ($a['uncertainty'] > $b['uncertainty']) {
            return $multiplier * 1;
        } elseif ($a['uncertainty'] < $b['uncertainty']) {
            return -$multiplier * 1;
        } else {
            return 0;
        }
    }

    /**
     * Renvoie la liste des membres de l'axe 1 ordonné selon la somme des valeurs
     * pour tous les membres de l'axe 2
     *
     * @param $values
     * @return array Liste des ref des membres de l'axe 1
     */
    protected function getAxis1MembersOrderedByAxis2Sum($values) {
        $sum = $this->getAxis1Sum($values);
        asort($sum);
        $this->orderedAxis1 = array_keys($sum);
        return $this->orderedAxis1;
    }

    /**
     * Renvoie un tableau comportant la somme des valeurs sur l'axe 2 par membre de l'axe 1
     *
     * @param $values
     * @return array Tableau indexé par les ref des membres de l'axe 1, chaque valeur
     *               est la somme des valeurs pour tous les membres de l'axe 2
     */
    protected function getAxis1Sum($values) {
        $sum = [];
        foreach ($values as $value) {
            /** @var $memberAxis1 Member **/
            $memberAxis1 = $value['members'][0];
            if (!array_key_exists($memberAxis1->getRef(), $sum)) {
                $sum[$memberAxis1->getRef()] = 0;
            }
            $sum[$memberAxis1->getRef()] += $value['value'];
        }
        return $sum;
    }

    /**
     * Fonction de tri personnalisé des résultats par ordre des membres.
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    protected function orderResultByMember($a, $b)
    {
        if ($a['members'][0] === $b['members'][0]) {
            return $a['members'][1]->getPosition() - $b['members'][1]->getPosition();
        }
        return $a['members'][0]->getPosition() - $b['members'][0]->getPosition();
    }

    /**
     * Fonction de tri personnalisé des résultats par ordre croissant de la somme des valeurs
     * par membre de l'axe 1 puis par ordre des membres pour l'axe 2
     *
     * @param $a
     * @param $b
     * @return mixed
     */
    protected function orderResultByIncreasingValueAndByMember($a, $b)
    {
        if ($a['members'][0] === $b['members'][0]) {
            return $a['members'][1]->getPosition() - $b['members'][1]->getPosition();
        }
        $aPos = array_search($a['members'][0]->getRef(), $this->orderedAxis1);
        $bPos = array_search($b['members'][0]->getRef(), $this->orderedAxis1);
        return $aPos - $bPos;
    }

    /**
     * Fonction de tri personnalisé des résultats par ordre décroissant de la somme des valeurs
     * par membre de l'axe 1 puis par ordre des membres pour l'axe 2
     *
     * @param $a
     * @param $b
     * @return mixed
     */
    protected function orderResultByDecreasingValueAndByMember($a, $b)
    {
        if ($a['members'][0] === $b['members'][0]) {
            return $a['members'][1]->getPosition() - $b['members'][1]->getPosition();
        }
        $aPos = array_search($a['members'][0]->getRef(), $this->orderedAxis1);
        $bPos = array_search($b['members'][0]->getRef(), $this->orderedAxis1);
        return $bPos - $aPos;
    }
}
