<?php
/**
 * @author diana.dragusin
 * @package Classification
 */

use Classification\Domain\ContextIndicator;
use Classification\Domain\Axis;
use Classification\Domain\Indicator;
use Core\Annotation\Secure;

/**
 * Controller du datagrid de coherence
 * @package Classification
 */
class Classification_Datagrid_ConsistencyController extends UI_Controller_Datagrid
{
    /**
     * Methode appelee pour remplir le tableau.
     *
     * @Secure("viewClassification")
     */
    public function getelementsAction()
    {
        $listAxisWithoutMember = array();
        $listAxisWithMemberNotLinkedToBroader = array();
        $listAxisWithMemberNotLinkedToNarrower = array();
        $listIndicatorsWithNonexistentUnits = array();
        $listIndicatorsWithNoncoherentUnits = array();
        $listContextIndicatorsWithLinkedAxes = array();

        foreach (Axis::loadList() as $axis) {
            if (!$axis->hasMembers()) {
                $listAxisWithoutMember[] = $this->translator->get($axis->getLabel());
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
                            if (!isset($listAxisWithMemberNotLinkedToNarrower[$axis->getId()][$narrowerAxis->getId()])) {
                                $listAxisWithMemberNotLinkedToNarrower[$axis->getId()][$narrowerAxis->getId()] = array();
                            }
                            $listAxisWithMemberNotLinkedToNarrower[$axis->getId()][$narrowerAxis->getId()][] = $this->translator->get($member->getLabel());
                        }
                    }
                    foreach ($broaderAxes as $broaderAxis) {
                        $intersectMemberBroaderMembers = array_uintersect(
                                $member->getDirectParents(),
                                $broaderAxis->getMembers(),
                                function($a, $b){return (($a === $b) ? 0 : 1);}
                            );
                        if (count($intersectMemberBroaderMembers) !== 1) {
                            if (!isset($listAxisWithMemberNotLinkedToBroader[$axis->getId()][$broaderAxis->getId()])) {
                                $listAxisWithMemberNotLinkedToBroader[$axis->getId()][$broaderAxis->getId()] = array();
                            }
                            $listAxisWithMemberNotLinkedToBroader[$axis->getId()][$broaderAxis->getId()][] = $this->translator->get($member->getLabel());
                        }
                    }
                }
            }
        }

        foreach (Indicator::loadList() as $indicator) {
            $unit = $indicator->getUnit();
            $ratioUnit = $indicator->getRatioUnit();
            try {
                $listCompatibleUnits = $unit->getNormalizedUnit();
            } catch (Core_Exception_NotFound $e) {
                $listCompatibleUnits = array();
                $listIndicatorsWithNonexistentUnits[$indicator->getRef()][] = $unit->getLabel();
            }
            try {
                $listCompatibleRatioUnits = $ratioUnit->getNormalizedUnit();
            } catch (Core_Exception_NotFound $e) {
                $listCompatibleRatioUnits = array();
                $listIndicatorsWithNonexistentUnits[$indicator->getRef()][] = $ratioUnit->getLabel();
            }
            if ($listCompatibleUnits != $listCompatibleRatioUnits) {
                $listIndicatorsWithNoncoherentUnits[$indicator->getRef()][] = $unit->getLabel();
                $listIndicatorsWithNoncoherentUnits[$indicator->getRef()][] = $ratioUnit->getLabel();
            }
        }

        foreach (ContextIndicator::loadList() as $contextIndicator) {
            $contextIndicatorAxes = $contextIndicator->getAxes();
            $contextIndicatorErrors = array();
            foreach ($contextIndicatorAxes as $contextIndicatorAxis) {
                foreach ($contextIndicatorAxes as $contextIndicatorAxisVerif) {
                    if (($contextIndicatorAxis !== $contextIndicatorAxisVerif)
                        && ($contextIndicatorAxis->isNarrowerThan($contextIndicatorAxisVerif))) {
                        $contextIndicatorErrors[] = '(' . $this->translator->get($contextIndicatorAxis->getLabel()) . ' - ' . $this->translator->get($contextIndicatorAxisVerif->getLabel()) . ')';
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
        $data['control'] = __('Classification', 'control', 'axisWithNoMember');
        $data['diag'] = empty($listAxisWithoutMember);
        $data['fail'] = implode(', ', $listAxisWithoutMember);
        $this->addLine($data);

        $data['index'] = 'axisWithMemberNotLinkedToNarrower';
        $data['control'] = __('Classification', 'control', 'memberWithNoDirectChild');
        $data['diag'] = empty($listAxisWithMemberNotLinkedToNarrower);
        $data['fail'] = '';
        foreach ($listAxisWithMemberNotLinkedToNarrower as $axisId => $members) {
            $axis = Axis::load($axisId);
            $data['fail'] .= $this->translator->get($axis->getLabel()) . ' : { ';
            foreach ($members as $narrowerAxisId => $refMember) {
                $narrowerAxis = Axis::load($narrowerAxisId);
                $data['fail'] .= $this->translator->get($narrowerAxis->getLabel()) . ' : [' . implode(', ', $refMember) . '], ';
            }
            $data['fail'] = substr($data['fail'], 0, -2);
            $data['fail'] .= ' }, ';
        }
        if (strlen($data['fail']) > 0) {
            $data['fail'] = substr($data['fail'], 0, -2);
        }
        $this->addLine($data);

        $data['index'] = 'axisWithMemberNotLinkedToBroader';
        $data['control'] = __('Classification', 'control', 'memberWithMissingDirectParent');
        $data['diag'] = empty($listAxisWithMemberNotLinkedToBroader);
        $data['fail'] = '';
        foreach ($listAxisWithMemberNotLinkedToBroader as $axisId => $members) {
            $axis = Axis::load($axisId);
            $data['fail'] .= $this->translator->get($axis->getLabel()) . ' : { ';
            foreach ($members as $broaderAxisId => $refMember) {
                $broaderAxis = Axis::load($broaderAxisId);
                $data['fail'] .= $this->translator->get($broaderAxis->getLabel()) . ' : [' . implode(', ', $refMember) . '], ';
            }
            $data['fail'] = substr($data['fail'], 0, -2);
            $data['fail'] .= ' }, ';
        }
        if (strlen($data['fail']) > 0) {
            $data['fail'] = substr($data['fail'], 0, -2);
        }
        $this->addLine($data);

        $data['index'] = 'contextIndicatorsWithLinkedAxes';
        $data['control'] = __('Classification', 'control', 'contextIndicatorsWithLinkedAxes');
        $data['diag'] = empty($listContextIndicatorsWithLinkedAxes);
        $data['fail'] = '';
        foreach ($listContextIndicatorsWithLinkedAxes as $contextIndicatorArray) {
            $data['fail'] .= $this->translator->get($contextIndicatorArray['contextIndicator']->getContext()->getLabel()) . ' - ' .
                $this->translator->get($contextIndicatorArray['contextIndicator']->getIndicator()->getLabel()) .
                ' : { ' . implode(', ', $contextIndicatorArray['axes']) . ' }, ';
        }
        if (strlen($data['fail']) > 0) {
            $data['fail'] = substr($data['fail'], 0, -2);
        }
        $this->addLine($data);

        $this->send();
    }

}
