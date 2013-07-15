<?php
/**
 * @author diana.dragusin
 * @package Classif
 */

use Core\Annotation\Secure;

/**
 * Controller du datagrid de coherence
 * @package Classif
 */
class Classif_Datagrid_ConsistencyController extends UI_Controller_Datagrid
{
    /**
     * Methode appelee pour remplir le tableau.
     *
     * @Secure("viewClassif")
     */
    public function getelementsAction()
    {
        $listAxisWithoutMember = array();
        $listAxisWithMemberNotLinkedToBroader = array();
        $listAxisWithMemberNotLinkedToNarrower = array();
        $listIndicatorsWithNonexistentUnits = array();
        $listIndicatorsWithNoncoherentUnits = array();
        $listContextIndicatorsWithLinkedAxes = array();

        foreach (Classif_Model_Axis::loadList() as $axis) {
            if (!$axis->hasMembers()) {
                $listAxisWithoutMember[] = $axis->getRef();
            } else {
                $narrowerAxis = $axis->getDirectNarrower();
                $broaderAxes = $axis->getDirectBroaders();

                foreach ($axis->getMembers() as $member) {
                    if ($narrowerAxis !== null) {
                        $intersectMemberNarrowerMembers = array_uintersect(
                                $member->getDirectChildren(),
                                $narrowerAxis->getMembers(),
                                function($a, $b){return (($a === $b) ? 0 : 1);}
                            );
                        if (count($intersectMemberNarrowerMembers) < 1) {
                            if (!isset($listAxisWithMemberNotLinkedToNarrower[$axis->getRef()][$narrowerAxis->getRef()])) {
                                $listAxisWithMemberNotLinkedToNarrower[$axis->getRef()][$narrowerAxis->getRef()] = array();
                            }
                            $listAxisWithMemberNotLinkedToNarrower[$axis->getRef()][$narrowerAxis->getRef()][] = $member->getRef();
                        }
                    }
                    foreach ($broaderAxes as $broaderAxis) {
                        $intersectMemberBroaderMembers = array_uintersect(
                                $member->getDirectParents(),
                                $broaderAxis->getMembers(),
                                function($a, $b){return (($a === $b) ? 0 : 1);}
                            );
                        if (count($intersectMemberBroaderMembers) !== 1) {
                            if (!isset($listAxisWithMemberNotLinkedToBroader[$axis->getRef()][$broaderAxis->getRef()])) {
                                $listAxisWithMemberNotLinkedToBroader[$axis->getRef()][$broaderAxis->getRef()] = array();
                            }
                            $listAxisWithMemberNotLinkedToBroader[$axis->getRef()][$broaderAxis->getRef()][] = $member->getRef();
                        }
                    }
                }
            }
        }

        foreach (Classif_Model_Indicator::loadList() as $indicator) {
            $unit = $indicator->getUnit();
            $ratioUnit = $indicator->getRatioUnit();
            try {
                $listCompatibleUnits = $unit->getNormalizedUnit();
            } catch (Core_Exception_NotFound $e) {
                $listCompatibleUnits = array();
                $listIndicatorsWithNonexistentUnits[$indicator->getRef()][] = $unit->getRef();
            }
            try {
                $listCompatibleRatioUnits = $ratioUnit->getNormalizedUnit();
            } catch (Core_Exception_NotFound $e) {
                $listCompatibleRatioUnits = array();
                $listIndicatorsWithNonexistentUnits[$indicator->getRef()][] = $ratioUnit->getRef();
            }
            if ($listCompatibleUnits != $listCompatibleRatioUnits) {
                $listIndicatorsWithNoncoherentUnits[$indicator->getRef()][] = $unit->getRef();
                $listIndicatorsWithNoncoherentUnits[$indicator->getRef()][] = $ratioUnit->getRef();
            }
        }

        foreach (Classif_Model_ContextIndicator::loadList() as $contextIndicator) {
            $contextIndicatorAxes = $contextIndicator->getAxes();
            $contextIndicatorErrors = array();
            foreach ($contextIndicatorAxes as $contextIndicatorAxis) {
                foreach ($contextIndicatorAxes as $contextIndicatorAxisVerif) {
                    if (($contextIndicatorAxis !== $contextIndicatorAxisVerif)
                        && ($contextIndicatorAxis->isNarrowerThan($contextIndicatorAxisVerif))) {
                        $contextIndicatorErrors[] = '(' . $contextIndicatorAxis->getRef() . ' - ' . $contextIndicatorAxisVerif->getRef() . ')';
                    }
                }
            }
            if (count($contextIndicatorErrors) > 0) {
                $listContextIndicatorsWithLinkedAxes[] = array(
                    'contextIndicator' => $contextIndicator,
                    'axes' => $contextIndicatorErrors
                );
            }
        }

        $data['index'] = 'axisWithoutMember';
        $data['control'] = __('Classif', 'control', 'axisWithNoMember');
        $data['diag'] = empty($listAxisWithoutMember);
        $data['fail'] = implode(', ', $listAxisWithoutMember);
        $this->addLine($data);

        $data['index'] = 'axisWithMemberNotLinkedToNarrower';
        $data['control'] = __('Classif', 'control', 'memberWithNoDirectChild');
        $data['diag'] = empty($listAxisWithMemberNotLinkedToNarrower);
        $data['fail'] = '';
        foreach ($listAxisWithMemberNotLinkedToNarrower as $refAxis => $members) {
            $data['fail'] .= $refAxis . ' : { ';
            foreach ($members as $refNarrowerAxis => $refMember) {
                $data['fail'] .= $refNarrowerAxis . ' : [' . implode(', ', $refMember) . '], ';
            }
            $data['fail'] = substr($data['fail'], 0, -2);
            $data['fail'] .= ' }, ';
        }
        if (strlen($data['fail']) > 0) {
            $data['fail'] = substr($data['fail'], 0, -2);
        }
        $this->addLine($data);

        $data['index'] = 'axisWithMemberNotLinkedToBroader';
        $data['control'] = __('Classif', 'control', 'memberWithMissingDirectParent');
        $data['diag'] = empty($listAxisWithMemberNotLinkedToBroader);
        $data['fail'] = '';
        foreach ($listAxisWithMemberNotLinkedToBroader as $refAxis => $members) {
            $data['fail'] .= $refAxis . ' : { ';
            foreach ($members as $refBroaderAxis => $refMember) {
                $data['fail'] .= $refBroaderAxis . ' : [' . implode(', ', $refMember) . '], ';
            }
            $data['fail'] = substr($data['fail'], 0, -2);
            $data['fail'] .= ' }, ';
        }
        if (strlen($data['fail']) > 0) {
            $data['fail'] = substr($data['fail'], 0, -2);
        }
        $this->addLine($data);

        $data['index'] = 'contextIndicatorsWithLinkedAxes';
        $data['control'] = __('Classif', 'control', 'contextIndicatorsWithLinkedAxes');
        $data['diag'] = empty($listContextIndicatorsWithLinkedAxes);
        $data['fail'] = '';
        foreach ($listContextIndicatorsWithLinkedAxes as $contextIndicatorArray) {
            $data['fail'] .= $contextIndicatorArray['contextIndicator']->getContext()->getRef() . ' - ' .
                $contextIndicatorArray['contextIndicator']->getIndicator()->getRef() .
                ' : { ' . implode(', ', $contextIndicatorArray['axes']) . ' }, ';
        }
        if (strlen($data['fail']) > 0) {
            $data['fail'] = substr($data['fail'], 0, -2);
        }
        $this->addLine($data);

        $this->send();
    }

}