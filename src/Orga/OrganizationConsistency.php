<?php
/**
 * @author valentin.claras
 * @author diana.dragusin
 * @package Orga
 */

/**
 * Controller du datagrid de coherence
 * @package Orga
 */
class Orga_OrganizationConsistency
{
    /**
     * Methode qui vérifie la cohérence d'un cube.
     *
     * @param Orga_Model_Organization $organization
     * @return array();
     */
    public function check($organization)
    {
        $listAxes = array();
        $listParentsAxes = array();
        $listParentsMembers = array();
        $listChildrenAxes = array();
        $listChildrenMembers = array();
        $listCrossedGranularities = array();

        $checkAxes = __('Orga', 'control', 'axisWithNoMember');
        $checkBroaderMember = __('Orga', 'control', 'memberWithMissingDirectParent');
        $checkNarrowerMember = __('Orga', 'control', 'memberWithNoDirectChild');
        $checkCrossedGranularities = __('Orga', 'control', 'crossedGranularities');

        foreach ($organization->getAxes() as $axis) {
            if (!$axis->hasMembers()) {
                $listAxes[] = $axis->getLabel();
            }
            if ($axis->hasDirectBroaders()) {
                foreach ($axis->getDirectBroaders() as $broaderAxis) {
                    foreach ($axis->getMembers() as $member) {
                        try {
                            $member->getParentForAxis($broaderAxis);
                        } catch (Core_Exception_NotFound $e) {
                            $listParentsAxes[] = $axis->getLabel();
                            $listParentsMembers[] = $member->getLabel();
                        }
                    }
                    foreach ($broaderAxis->getMembers() as $parentMember) {
                        if (count($parentMember->getChildrenForAxis($axis)) === 0) {
                            $listChildrenAxes[] = $broaderAxis->getLabel();
                            $listChildrenMembers[] = $parentMember->getLabel();
                        }
                    }
                }
            }
        }

        try {
            $granularityForInventoryStatus = $organization->getGranularityForInventoryStatus();
        } catch (Core_Exception_UndefinedAttribute $e) {
            $granularityForInventoryStatus = null;
        }
        if ($granularityForInventoryStatus !== null) {
            foreach ($organization->getGranularities() as $granularity) {
                if ($granularity->getCellsWithACL()) {
                    try {
                        $granularityForInventoryStatus->getCrossedGranularity($granularity);
                    } catch (Core_Exception_NotFound $e) {
                        $listCrossedGranularities[] = $granularity;
                    }
                }
            }
        }

        $n = count($listAxes);
        $text1 = '';
        $i = 0;
        foreach ($listAxes as $l1) {
            if ($i == $n-1) {
                $text1 = $text1.$l1;
            } else {
                $text1 = $text1.$l1.'; ';
            }
            $i++;
        }

        $text2 = '';
        $m = count($listParentsAxes);
        for ($i = 0; $i <= $m-1; $i++) {
            if ($i == $m-1) {
                $text2 = $text2 . __('UI', 'name', 'axis') . __('UI', 'other', ':') . $listParentsAxes[$i]
                    . __('UI', 'other', ';') . __('UI', 'name', 'elementSmallCap') . __('UI', 'other', ':') . $listParentsMembers[$i];
            } else {
                $text2 = $text2 . __('UI', 'name', 'axis') . __('UI', 'other', ':') . $listParentsAxes[$i]
                    . __('UI', 'other', ';') . __('UI', 'name', 'elementSmallCap') . __('UI', 'other', ':') . $listParentsMembers[$i] . ' / ';
            }
        }

        $text3 = '';
        $l = count($listChildrenAxes);
        for ($i = 0; $i <= $l-1; $i++) {
            if ($i == $l-1) {
                $text3 = $text3 . __('UI', 'name', 'axis') . __('UI', 'other', ':') . $listChildrenAxes[$i]
                    . __('UI', 'other', ';') . __('UI', 'name', 'elementSmallCap') . __('UI', 'other', ':') . $listChildrenMembers[$i];
            } else {
                $text3 = $text3 . __('UI', 'name', 'axis') . __('UI', 'other', ':') . $listChildrenAxes[$i]
                    . __('UI', 'other', ';') . __('UI', 'name', 'elementSmallCap') . __('UI', 'other', ':') . $listChildrenMembers[$i]  . ' / ';
            }
        }

        if ($granularityForInventoryStatus !== null) {
            $text4 = '';
            if (count($listCrossedGranularities) > 0) {
                /** @var Orga_Model_Granularity $granularity */
                foreach ($listCrossedGranularities as $granularity) {
                    $currentAxes = $granularity->getAxes();
                    $crossingAxes = $granularityForInventoryStatus->getAxes();

                    foreach ($granularity->getAxes() as $currentIndex => $currentAxis) {
                        foreach ($granularityForInventoryStatus->getAxes() as $crossingIndex => $crossingAxis) {
                            if (($currentAxis->isNarrowerThan($crossingAxis)) || ($currentAxis === $crossingAxis)) {
                                unset($crossingAxes[$crossingIndex]);
                            } else if ($currentAxis->isBroaderThan($crossingAxis)) {
                                unset($currentAxes[$currentIndex]);
                            }
                        }
                    }
                    $axes = array_merge($currentAxes, $crossingAxes);
                    @uasort($axes, [Orga_Model_Axis::class, 'orderAxes']);
                    foreach ($axes as $axis) {
                        $labelParts[] = $axis->getLabel();
                    }
                    $label = implode(Orga_Model_Granularity::LABEL_SEPARATOR, $labelParts);
                    $text4 .= $label .' / ';
                }
                $text4 = substr($text4, 0, -3);
            }
        } else {
            $text4 = '';
        }

        $result  = array();
        $result['okAxis'] = empty($listAxes);
        $result['controlAxis'] = $checkAxes;
        $result['failureAxis'] = $text1;

        $result['okMemberParents'] = empty($listParentsAxes);
        $result['controlMemberParents'] = $checkBroaderMember;
        $result['failureMemberParents'] = $text2;

        $result['okMemberChildren'] = empty($listChildrenAxes);
        $result['controlMemberChildren'] = $checkNarrowerMember;
        $result['failureMemberChildren'] = $text3;

        $result['okCrossedGranularities'] = empty($listCrossedGranularities);
        $result['controlCrossedGranularities'] = $checkCrossedGranularities;
        $result['failureCrossedGranularities'] = $text4;

        return $result;
    }
}