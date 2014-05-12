<?php

use Mnapoli\Translated\TranslationHelper;
use MyCLabs\MUIH\Button;
use MyCLabs\MUIH\Collapse;
use MyCLabs\MUIH\GenericTag;
use MyCLabs\MUIH\GenericVoidTag;

/**
 * Classe DW_Form_configuration.
 * @author valentin.claras
 * @author cyril.perraud
 * @package    DW
 * @subpackage Form
 */

/**
 * Classe permettant de générer le formulaire de gestion des rapports.
 * @package    DW
 * @subpackage Form
 */
class DW_Form_configuration extends GenericTag
{
    /**
     * @var Mnapoli\Translated\TranslationHelper
     */
    private $translationHelper;

    /**
     * Génération du formulaire
     *
     * @param DW_Model_Report   $report
     * @param string            $hash
     * @param TranslationHelper $translationHelper
     */
    public function __construct(DW_Model_Report $report, $hash, TranslationHelper $translationHelper)
    {
        $this->translationHelper = $translationHelper;

        $reportNumeratorIndicator = $report->getNumerator();
        $reportDenominatorIndicator = $report->getDenominator();
        $numeratorAxisOne = $report->getNumeratorAxis1();
        $numeratorAxisTwo = $report->getNumeratorAxis2();
        $denominatorAxisOne = $report->getDenominatorAxis1();
        $denominatorAxisTwo = $report->getDenominatorAxis2();

        $idCube = $report->getCube()->getId();

        parent::__construct('form');
        $this->setAttribute('method', 'POST');
        $this->setAttribute('action', 'dw/report/applyconfiguration/idCube/'.$idCube.'/hashReport/'.$hash);
        $this->setAttribute('id', 'form_' . $hash);


        // Fieldset de sélection des valeurs.
        $valuesWrapper = new GenericTag('fieldset');
        $valuesLegend = new GenericTag('legend', __('UI', 'name', 'values'));
        $valuesWrapper->appendContent($valuesLegend);
        $this->appendContent($valuesWrapper);

        // Groupe de sélection du type de rapport.
        $typeSumRatioGroup = new GenericTag('div');
        $typeSumRatioGroup->addClass('form-group');
        $valuesWrapper->appendContent($typeSumRatioGroup);

        $sumChoiceInput = new GenericVoidTag('input');
        $sumChoiceInput->setAttribute('type', 'radio');
        $sumChoiceInput->setAttribute('name', 'typeSumRatioChoice');
        $sumChoiceInput->setAttribute('value', 'sum');
        $sumChoiceLabel = new GenericTag('label', __('DW', 'name', 'indicator'));
        $sumChoiceLabel->prependContent($sumChoiceInput);
        $sumChoiceLabel->addClass('radio-inline');
        $typeSumRatioGroup->appendContent($sumChoiceLabel);

        $ratioChoiceInput = new GenericVoidTag('input');
        $ratioChoiceInput->setAttribute('type', 'radio');
        $ratioChoiceInput->setAttribute('name', 'typeSumRatioChoice');
        $ratioChoiceInput->setAttribute('value', 'ratio');
        $ratioChoiceLabel = new GenericTag('label', __('DW', 'name', 'ratio'));
        $ratioChoiceLabel->prependContent($ratioChoiceInput);
        $ratioChoiceLabel->addClass('radio-inline');
        $typeSumRatioGroup->appendContent($ratioChoiceLabel);

        // Groupe de sélection de l'indicateur numérateur.
        $numeratorIndicatorGroup = new GenericTag('div');
        $numeratorIndicatorGroup->addClass('form-group');
        $numeratorIndicatorGroup->addClass('hide');
        $valuesWrapper->appendContent($numeratorIndicatorGroup);

        $numeratorIndicatorSelect = new GenericTag('select');
        $numeratorIndicatorSelect->setAttribute('name', 'numeratorIndicator');
        $numeratorIndicatorSelect->addClass('form-control');
        foreach ($report->getCube()->getIndicators() as $indicator) {
            $indicatorOption = new GenericTag('option', $this->translationHelper->toString($indicator->getLabel()));
            $indicatorOption->setAttribute('value', $indicator->getRef());
            $numeratorIndicatorSelect->appendContent($indicatorOption);
        }
        $numeratorIndicatorGroup->appendContent($numeratorIndicatorSelect);

        // Groupe de sélection de l'indicateur dénominateur.
        $denominatorIndicatorGroup = new GenericTag('div');
        $denominatorIndicatorGroup->addClass('form-group');
        $denominatorIndicatorGroup->addClass('hide');
        $valuesWrapper->appendContent($denominatorIndicatorGroup);

        $denominatorIndicatorLabel = new GenericTag('div', __('DW', 'config', 'denominatorSelect'));
        $denominatorIndicatorLabel->addClass('form-control-static');
        $denominatorIndicatorGroup->appendContent($denominatorIndicatorLabel);

        $denominatorIndicatorSelect = new GenericTag('select');
        $denominatorIndicatorSelect->setAttribute('name', 'denominatorIndicator');
        $denominatorIndicatorSelect->addClass('form-control');
        foreach ($report->getCube()->getIndicators() as $indicator) {
            $indicatorOption = new GenericTag('option', $this->translationHelper->toString($indicator->getLabel()));
            $indicatorOption->setAttribute('value', $indicator->getRef());
            $denominatorIndicatorSelect->appendContent($indicatorOption);
        }
        $denominatorIndicatorGroup->appendContent($denominatorIndicatorSelect);


        // Fieldset de sélection de l'axe pour le type sum.
        $sumAxisWrapper = new GenericTag('fieldset');
        $sumAxisWrapper->addClass('hide');
        $sumAxisLegend = new GenericTag('legend', __('UI', 'name', 'axes'));
        $sumAxisWrapper->appendContent($sumAxisLegend);
        $this->appendContent($sumAxisWrapper);

        // Groupe de sélection du nombre d'axes pour le type sum.
        $sumAxisNumberGroup = new GenericTag('div');
        $sumAxisNumberGroup->addClass('form-group');
        $sumAxisWrapper->appendContent($sumAxisNumberGroup);

        $oneAxisSumChoiceInput = new GenericVoidTag('input');
        $oneAxisSumChoiceInput->setAttribute('type', 'radio');
        $oneAxisSumChoiceInput->setAttribute('name', 'sumAxisNumberChoice');
        $oneAxisSumChoiceInput->setAttribute('value', 'one');
        $oneAxisSumChoiceInput->setBooleanAttribute('checked');
        $oneAxisSumChoiceLabel = new GenericTag('label', __('DW', 'config', 'oneAxisOption'));
        $oneAxisSumChoiceLabel->prependContent($oneAxisSumChoiceInput);
        $oneAxisSumChoiceLabel->addClass('radio-inline');
        $sumAxisNumberGroup->appendContent($oneAxisSumChoiceLabel);

        $twoAxesSumChoiceInput = new GenericVoidTag('input');
        $twoAxesSumChoiceInput->setAttribute('type', 'radio');
        $twoAxesSumChoiceInput->setAttribute('name', 'sumAxisNumberChoice');
        $twoAxesSumChoiceInput->setAttribute('value', 'two');
        $twoAxesSumChoiceLabel = new GenericTag('label', __('DW', 'config', 'twoAxesOption'));
        $twoAxesSumChoiceLabel->prependContent($twoAxesSumChoiceInput);
        $twoAxesSumChoiceLabel->addClass('radio-inline');
        $sumAxisNumberGroup->appendContent($twoAxesSumChoiceLabel);

        // Groupe de sélection de l'axe 1 du numérateur pour le type sum.
        $sumAxisOneGroup = new GenericTag('div');
        $sumAxisOneGroup->addClass('form-group');
        $sumAxisWrapper->appendContent($sumAxisOneGroup);

        $sumAxisOneSelect = new GenericTag('select');
        $sumAxisOneSelect->setAttribute('name', 'sumAxisOne');
        $sumAxisOneSelect->addClass('form-control');
        foreach ($report->getCube()->getAxes() as $axis) {
            $axisOption = new GenericTag('option', $this->translationHelper->toString($axis->getLabel()));
            $axisOption->setAttribute('value', $axis->getRef());
            $sumAxisOneSelect->appendContent($axisOption);
        }
        $sumAxisOneGroup->appendContent($sumAxisOneSelect);

        // Groupe de sélection de l'axe 2 du numérateur pour le type sum.
        $sumAxisTwoGroup = new GenericTag('div');
        $sumAxisTwoGroup->addClass('form-group');
        $sumAxisTwoGroup->addClass('hide');
        $sumAxisWrapper->appendContent($sumAxisTwoGroup);

        $sumAxisTwoSelect = new GenericTag('select');
        $sumAxisTwoSelect->setAttribute('name', 'sumAxisTwo');
        $sumAxisTwoSelect->addClass('form-control');
        foreach ($report->getCube()->getAxes() as $axis) {
            $axisOption = new GenericTag('option', $this->translationHelper->toString($axis->getLabel()));
            $axisOption->setAttribute('value', $axis->getRef());
            $sumAxisTwoSelect->appendContent($axisOption);
        }
        $sumAxisTwoGroup->appendContent($sumAxisTwoSelect);


        // Fieldset de sélection de l'axe du numérateur pour le type ratio.
        $ratioNumeratorAxisWrapper = new GenericTag('fieldset');
        $ratioNumeratorAxisWrapper->addClass('hide');
        $ratioNumeratorAxisLegend = new GenericTag('legend', __('DW', 'config', 'numeratorAxesGroup'));
        $ratioNumeratorAxisWrapper->appendContent($ratioNumeratorAxisLegend);
        $this->appendContent($ratioNumeratorAxisWrapper);

        // Groupe de sélection du nombre d'axes pour le type ratio.
        $ratioAxisNumberGroup = new GenericTag('div');
        $ratioAxisNumberGroup->addClass('form-group');
        $ratioNumeratorAxisWrapper->appendContent($ratioAxisNumberGroup);

        $oneAxisRatioChoiceInput = new GenericVoidTag('input');
        $oneAxisRatioChoiceInput->setAttribute('type', 'radio');
        $oneAxisRatioChoiceInput->setAttribute('name', 'ratioAxisNumberChoice');
        $oneAxisRatioChoiceInput->setAttribute('value', 'one');
        $oneAxisRatioChoiceInput->setBooleanAttribute('checked');
        $oneAxisRatioChoiceLabel = new GenericTag('label', __('DW', 'config', 'oneAxisOption'));
        $oneAxisRatioChoiceLabel->prependContent($oneAxisRatioChoiceInput);
        $oneAxisRatioChoiceLabel->addClass('radio-inline');
        $ratioAxisNumberGroup->appendContent($oneAxisRatioChoiceLabel);

        $twoAxesRatioChoiceInput = new GenericVoidTag('input');
        $twoAxesRatioChoiceInput->setAttribute('type', 'radio');
        $twoAxesRatioChoiceInput->setAttribute('name', 'ratioAxisNumberChoice');
        $twoAxesRatioChoiceInput->setAttribute('value', 'two');
        $twoAxesRatioChoiceLabel = new GenericTag('label', __('DW', 'config', 'twoAxesOption'));
        $twoAxesRatioChoiceLabel->prependContent($twoAxesRatioChoiceInput);
        $twoAxesRatioChoiceLabel->addClass('radio-inline');
        $ratioAxisNumberGroup->appendContent($twoAxesRatioChoiceLabel);

        // Groupe de sélection de l'axe 1 du numérateur pour le type ratio.
        $ratioNumeratorAxisOneGroup = new GenericTag('div');
        $ratioNumeratorAxisOneGroup->addClass('form-group');
        $ratioNumeratorAxisWrapper->appendContent($ratioNumeratorAxisOneGroup);

        $ratioNumeratorAxisOneSelect = new GenericTag('select');
        $ratioNumeratorAxisOneSelect->setAttribute('name', 'ratioNumeratorAxisOne');
        $ratioNumeratorAxisOneSelect->addClass('form-control');
        foreach ($report->getCube()->getAxes() as $axis) {
            $axisOption = new GenericTag('option', $this->translationHelper->toString($axis->getLabel()));
            $axisOption->setAttribute('value', $axis->getRef());
            $ratioNumeratorAxisOneSelect->appendContent($axisOption);
        }
        $ratioNumeratorAxisOneGroup->appendContent($ratioNumeratorAxisOneSelect);

        // Groupe de sélection de l'axe 2 du numérateur pour le type ratio.
        $ratioNumeratorAxisTwoGroup = new GenericTag('div');
        $ratioNumeratorAxisTwoGroup->addClass('form-group');
        $ratioNumeratorAxisTwoGroup->addClass('hide');
        $ratioNumeratorAxisWrapper->appendContent($ratioNumeratorAxisTwoGroup);

        $ratioNumeratorAxisTwoSelect = new GenericTag('select');
        $ratioNumeratorAxisTwoSelect->setAttribute('name', 'ratioNumeratorAxisTwo');
        $ratioNumeratorAxisTwoSelect->addClass('form-control');
        foreach ($report->getCube()->getAxes() as $axis) {
            $axisOption = new GenericTag('option', $this->translationHelper->toString($axis->getLabel()));
            $axisOption->setAttribute('value', $axis->getRef());
            $ratioNumeratorAxisTwoSelect->appendContent($axisOption);
        }
        $ratioNumeratorAxisTwoGroup->appendContent($ratioNumeratorAxisTwoSelect);


        // Fieldset de sélection de l'axe du dénominateur pour le type ratio.
        $ratioDenominatorAxisWrapper = new GenericTag('fieldset');
        $ratioDenominatorAxisWrapper->addClass('hide');
        $ratioDenominatorAxisLegend = new GenericTag('legend', __('DW', 'config', 'denominatorAxesGroup'));
        $ratioDenominatorAxisWrapper->appendContent($ratioDenominatorAxisLegend);
        $this->appendContent($ratioDenominatorAxisWrapper);

        // Groupe de sélection de l'axe 1 du dénominateur pour le type ratio.
        $ratioDenominatorAxisOneGroup = new GenericTag('div');
        $ratioDenominatorAxisOneGroup->addClass('form-group');
        $ratioDenominatorAxisWrapper->appendContent($ratioDenominatorAxisOneGroup);

        $ratioDenominatorAxisOneSelect = new GenericTag('select');
        $ratioDenominatorAxisOneSelect->setAttribute('name', 'ratioDenominatorAxisOne');
        $ratioDenominatorAxisOneSelect->addClass('form-control');
        $emptyOption = new GenericTag('option', '');
        $emptyOption->setAttribute('value', '');
        $ratioDenominatorAxisOneSelect->appendContent($emptyOption);
        foreach ($report->getCube()->getAxes() as $axis) {
            $axisOption = new GenericTag('option', $this->translationHelper->toString($axis->getLabel()));
            $axisOption->setAttribute('value', $axis->getRef());
            $ratioDenominatorAxisOneSelect->appendContent($axisOption);
        }
        $ratioDenominatorAxisOneGroup->appendContent($ratioDenominatorAxisOneSelect);

        // Groupe de sélection de l'axe 2 du dénominateur pour le type ratio.
        $ratioDenominatorAxisTwoGroup = new GenericTag('div');
        $ratioDenominatorAxisTwoGroup->addClass('form-group');
        $ratioDenominatorAxisTwoGroup->addClass('hide');
        $ratioDenominatorAxisWrapper->appendContent($ratioDenominatorAxisTwoGroup);

        $ratioDenominatorAxisTwoSelect = new GenericTag('select');
        $ratioDenominatorAxisTwoSelect->setAttribute('name', 'ratioDenominatorAxisTwo');
        $ratioDenominatorAxisTwoSelect->addClass('form-control');
        $emptyOption = new GenericTag('option', '');
        $emptyOption->setAttribute('value', '');
        $ratioDenominatorAxisTwoSelect->appendContent($emptyOption);
        foreach ($report->getCube()->getAxes() as $axis) {
            $axisOption = new GenericTag('option', $this->translationHelper->toString($axis->getLabel()));
            $axisOption->setAttribute('value', $axis->getRef());
            $ratioDenominatorAxisTwoSelect->appendContent($axisOption);
        }
        $ratioDenominatorAxisTwoGroup->appendContent($ratioDenominatorAxisTwoSelect);


        // Fieldset de sélection du type d'affichage du rapport.
        $displayWrapper = new GenericTag('fieldset');
        $displayLegend = new GenericTag('legend', __('UI', 'name', 'display'));
        $displayWrapper->appendContent($displayLegend);
        $this->appendContent($displayWrapper);

        // Groupe de sélection du type d'affichage du rapport.
        $displayTypeGroup = new GenericTag('div');
        $displayTypeGroup->addClass('form-group');
        $displayWrapper->appendContent($displayTypeGroup);

        $displayTypeSelect = new GenericTag('select');
        $displayTypeSelect->setAttribute('name', 'displayType');
        $displayTypeSelect->addClass('form-control');
        $displayTypeGroup->appendContent($displayTypeSelect);
        $emptyOption = new GenericTag('option', '');
        $emptyOption->setAttribute('value', '');
        $emptyOption->addClass('one');
        $emptyOption->addClass('two');
        $displayTypeSelect->appendContent($emptyOption);
        // Camembert.
        $displayTypePieOption = new GenericTag('option', __('DW', 'config', 'chartTypePieOption'));
        $displayTypePieOption->setAttribute('value', DW_Model_Report::CHART_PIE);
        $displayTypePieOption->addClass('one');
        $displayTypeSelect->appendContent($displayTypePieOption);
        // Histogramme 1D vertical.
        $displayTypeVerticalOption = new GenericTag('option', __('DW', 'config', 'chartTypeVerticalOption'));
        $displayTypeVerticalOption->setAttribute('value', DW_Model_Report::CHART_VERTICAL);
        $displayTypeVerticalOption->addClass('one');
        $displayTypeSelect->appendContent($displayTypeVerticalOption);
        // Histogramme 1D horizontal.
        $displayTypeHorizontalOption = new GenericTag('option', __('DW', 'config', 'chartTypeHorizontalOption'));
        $displayTypeHorizontalOption->setAttribute('value', DW_Model_Report::CHART_HORIZONTAL);
        $displayTypeHorizontalOption->addClass('one');
        $displayTypeSelect->appendContent($displayTypeHorizontalOption);
        // Histogramme 2D vertical empilé.
        $displayTypeVerticalStackedOption = new GenericTag('option', __('DW', 'config', 'chartTypeVerticalStackedOption'));
        $displayTypeVerticalStackedOption->setAttribute('value', DW_Model_Report::CHART_VERTICAL_STACKED);
        $displayTypeVerticalStackedOption->addClass('two');
        $displayTypeSelect->appendContent($displayTypeVerticalStackedOption);
        // Histogramme 2D vertical groupé.
        $displayTypeVerticalGroupedOption = new GenericTag('option', __('DW', 'config', 'chartTypeVerticalGroupedOption'));
        $displayTypeVerticalGroupedOption->setAttribute('value', DW_Model_Report::CHART_VERTICAL_GROUPED);
        $displayTypeVerticalGroupedOption->addClass('two');
        $displayTypeSelect->appendContent($displayTypeVerticalGroupedOption);
        // Histogramme 2D horizontal empilé.
        $displayTypeHorizontalStackedOption = new GenericTag('option', __('DW', 'config', 'chartTypeHorizontalStackedOption'));
        $displayTypeHorizontalStackedOption->setAttribute('value', DW_Model_Report::CHART_HORIZONTAL_STACKED);
        $displayTypeHorizontalStackedOption->addClass('two');
        $displayTypeSelect->appendContent($displayTypeHorizontalStackedOption);
        // Histogramme 2D horizontal groupé.
        $displayTypeHorizontalGroupedOption = new GenericTag('option', __('DW', 'config', 'chartTypeHorizontalGroupedOption'));
        $displayTypeHorizontalGroupedOption->setAttribute('value', DW_Model_Report::CHART_HORIZONTAL_GROUPED);
        $displayTypeHorizontalGroupedOption->addClass('two');
        $displayTypeSelect->appendContent($displayTypeHorizontalGroupedOption);

        // Groupe de sélection de l'ordre des résultats.
        $resultsOrderGroup = new GenericTag('div');
        $resultsOrderGroup->addClass('form-group');
        $displayWrapper->appendContent($resultsOrderGroup);

        $resultsOrderSelect = new GenericTag('select');
        $resultsOrderSelect->setAttribute('name', 'resultsOrder');
        $resultsOrderSelect->addClass('form-control');
        $resultsOrderGroup->appendContent($resultsOrderSelect);
        // Valeurs décroissantes.
        $resultsOrderDecreasingOption = new GenericTag('option', __('DW', 'config', 'sortByDecreasingValues'));
        $resultsOrderDecreasingOption->setAttribute('value', DW_Model_Report::SORT_VALUE_DECREASING);
        $resultsOrderSelect->appendContent($resultsOrderDecreasingOption);
        // Valeurs croissantes.
        $resultsOrderIncreasingOption = new GenericTag('option', __('DW', 'config', 'sortByIncreasingValues'));
        $resultsOrderIncreasingOption->setAttribute('value', DW_Model_Report::SORT_VALUE_INCREASING);
        $resultsOrderSelect->appendContent($resultsOrderIncreasingOption);
        // Valeurs décroissantes.
        $resultOrderConventionalOption = new GenericTag('option', __('DW', 'config', 'sortByMembers'));
        $resultOrderConventionalOption->setAttribute('value', DW_Model_Report::SORT_CONVENTIONAL);
        $resultsOrderSelect->appendContent($resultOrderConventionalOption);

        // Groupe de sélection de l'affichage de l'incertitude.
        $uncertaintyGroup = new GenericTag('div');
        $uncertaintyGroup->addClass('form-group');
        $displayWrapper->appendContent($uncertaintyGroup);

        $uncertaintyChoiceInput = new GenericVoidTag('input');
        $uncertaintyChoiceInput->setAttribute('type', 'checkbox');
        $uncertaintyChoiceInput->setAttribute('name', 'uncertaintyChoice');
        $uncertaintyChoiceInput->setAttribute('value', 'withUncertainty');
        $uncertaintyChoiceLabel = new GenericTag('label', __('DW', 'config', 'withUncertaintyCheckbox'));
        $uncertaintyChoiceLabel->prependContent($uncertaintyChoiceInput);
        $uncertaintyChoiceLabel->addClass('radio-inline');
        $uncertaintyChoiceDiv = new GenericTag('div', $uncertaintyChoiceLabel);
        $uncertaintyChoiceDiv->addClass('checkbox');
        $uncertaintyGroup->appendContent($uncertaintyChoiceDiv);


        // Collapse des filtres du rapport.
        $filtersCollapse = new Collapse('filters'.$hash, __('UI', 'name', 'filters'));
        $this->appendContent($filtersCollapse);

        foreach ($report->getCube()->getAxes() as $axis) {
            $axisFilterWrapper = new GenericTag('fieldset');
            $axisFilterLegend = new GenericTag('legend', $axis->getLabel());
            $axisFilterWrapper->appendContent($axisFilterLegend);
            $filtersCollapse->appendContent($axisFilterWrapper);

            // Groupe de séléction du nombre de membres pour le filtre suivant l'axe.
            $memberNumberGroup = new GenericTag('div');
            $memberNumberGroup->addClass('form-group');
            $axisFilterWrapper->appendContent($memberNumberGroup);

            $allMembersChoiceInput = new GenericVoidTag('input');
            $allMembersChoiceInput->setAttribute('type', 'radio');
            $allMembersChoiceInput->setAttribute('name', $axis->getRef().'_memberNumberChoice');
            $allMembersChoiceInput->setAttribute('value', 'all');
            $allMembersChoiceInput->addClass('filterMemberNumber');
            $allMembersChoiceLabel = new GenericTag('label', __('UI', 'other', 'all'));
            $allMembersChoiceLabel->prependContent($allMembersChoiceInput);
            $allMembersChoiceLabel->addClass('radio-inline');
            $memberNumberGroup->appendContent($allMembersChoiceLabel);

            $oneMemberChoiceInput = new GenericVoidTag('input');
            $oneMemberChoiceInput->setAttribute('type', 'radio');
            $oneMemberChoiceInput->setAttribute('name', $axis->getRef().'_memberNumberChoice');
            $oneMemberChoiceInput->setAttribute('value', 'one');
            $oneMemberChoiceInput->addClass('filterMemberNumber');
            $oneMemberChoiceLabel = new GenericTag('label', __('UI', 'other', 'one'));
            $oneMemberChoiceLabel->prependContent($oneMemberChoiceInput);
            $oneMemberChoiceLabel->addClass('radio-inline');
            $memberNumberGroup->appendContent($oneMemberChoiceLabel);

            $severalMembersChoiceInput = new GenericVoidTag('input');
            $severalMembersChoiceInput->setAttribute('type', 'radio');
            $severalMembersChoiceInput->setAttribute('name', $axis->getRef().'_memberNumberChoice');
            $severalMembersChoiceInput->setAttribute('value', 'several');
            $severalMembersChoiceInput->addClass('filterMemberNumber');
            $severalMembersChoiceLabel = new GenericTag('label', __('UI', 'other', 'several'));
            $severalMembersChoiceLabel->prependContent($severalMembersChoiceInput);
            $severalMembersChoiceLabel->addClass('radio-inline');
            $memberNumberGroup->appendContent($severalMembersChoiceLabel);

            // Groupe de sélection du ou des membres de l'axe.
            $membersGroup = new GenericTag('div');
            $membersGroup->addClass('form-group');
            $axisFilterWrapper->appendContent($membersGroup);

            $membersSelect = new GenericTag('select');
            $membersSelect->setAttribute('name', $axis->getRef().'_members');
            $membersSelect->addClass('form-control');
            $membersGroup->appendContent($membersSelect);

            $reportFilterForAxis = $report->getFilterForAxis($axis);
            if ($reportFilterForAxis !== null) {
                $reportMembersFilteredForAxis = $reportFilterForAxis->getMembers()->toArray();
                if (count($reportMembersFilteredForAxis) > 1) {
                    $membersSelect->setBooleanAttribute('multiple');
                    $severalMembersChoiceInput->setBooleanAttribute('checked');
                } else {
                    $oneMemberChoiceInput->setBooleanAttribute('checked');
                }
            } else {
                $membersGroup->addClass('hide');
                $allMembersChoiceInput->setBooleanAttribute('checked');
            }

            foreach ($axis->getMembers() as $member) {
                $memberOption = new GenericTag('option', $this->translationHelper->toString($member->getLabel()));
                $memberOption->setAttribute('value', $member->getRef());
                $membersSelect->appendContent($memberOption);

                if (($reportFilterForAxis !== null) && ($reportFilterForAxis->hasMember($member))) {
                    $memberOption->setBooleanAttribute('selected');
                }
            }
        }


        // Groupe des boutons d'action.
        $actionsGroup = new GenericTag('div');
        $actionsGroup->addClass('form-group');
        $actionsGroup->addClass('actions');
        $actionsGroup->addClass('text-center');
        $this->appendContent($actionsGroup);

        $submitButton = new Button(__('UI', 'verb', 'launch'), Button::TYPE_PRIMARY);
        $submitButton->setAttribute('type', 'submit');
        $actionsGroup->appendContent($submitButton);

        $actionsGroup->appendContent(' ');

        $resetButton = new Button(__('UI', 'verb', 'reset'));
        $resetButton->setAttribute('type', 'reset');
        $actionsGroup->appendContent($resetButton);



        // Configuration initiale du rapport.
        if ($reportNumeratorIndicator !== null) {
            // Vérfication SUM / RATIO
            if ($reportDenominatorIndicator !== null) {
                $ratioChoiceInput->setBooleanAttribute('checked');
                $denominatorIndicatorGroup->removeClass('hide');
                $ratioNumeratorAxisWrapper->removeClass('hide');
                $ratioDenominatorAxisWrapper->removeClass('hide');
            } else {
                $sumChoiceInput->setBooleanAttribute('checked');
                $sumAxisWrapper->removeClass('hide');
            }

            $numeratorIndicatorGroup->removeClass('hide');
            foreach ($numeratorIndicatorSelect->getContent() as $option) {
                /** @var GenericTag $option */
                if ($option->getAttribute('value') === $reportNumeratorIndicator->getRef()) {
                    $option->setBooleanAttribute('selected');
                    break;
                }
            }

            if ($reportDenominatorIndicator !== null) {
                $denominatorIndicatorGroup->removeClass('hide');
                foreach ($denominatorIndicatorSelect->getContent() as $option) {
                    /** @var GenericTag $option */
                    if ($option->getAttribute('value') === $reportDenominatorIndicator->getRef()) {
                        $option->setBooleanAttribute('selected');
                        break;
                    }
                }
            }

            if (($numeratorAxisOne !== null) && ($numeratorAxisTwo !== null)) {
                $twoAxesSumChoiceInput->setBooleanAttribute('checked');
                $twoAxesRatioChoiceInput->setBooleanAttribute('checked');
                $sumAxisTwoGroup->removeClass('hide');
                $ratioNumeratorAxisTwoGroup->removeClass('hide');
                $ratioDenominatorAxisTwoGroup->removeClass('hide');
            }

            if ($numeratorAxisOne !== null) {
                foreach ($sumAxisOneSelect->getContent() as $option) {
                    /** @var GenericTag $option */
                    if ($option->getAttribute('value') === $numeratorAxisOne->getRef()) {
                        $option->setBooleanAttribute('selected');
                        break;
                    }
                }
                foreach ($ratioNumeratorAxisOneSelect->getContent() as $option) {
                    /** @var GenericTag $option */
                    if ($option->getAttribute('value') === $numeratorAxisOne->getRef()) {
                        $option->setBooleanAttribute('selected');
                        break;
                    }
                }

                if ($denominatorAxisOne !== null) {
                    foreach ($ratioDenominatorAxisOneSelect->getContent() as $option) {
                        /** @var GenericTag $option */
                        if ($option->getAttribute('value') === $denominatorAxisOne->getRef()) {
                            $option->setBooleanAttribute('selected');
                            break;
                        }
                    }
                }

                if ($numeratorAxisTwo !== null) {
                    foreach ($sumAxisTwoSelect->getContent() as $option) {
                        /** @var GenericTag $option */
                        if ($option->getAttribute('value') === $numeratorAxisTwo->getRef()) {
                            $option->setBooleanAttribute('selected');
                            break;
                        }
                    }
                    foreach ($ratioNumeratorAxisTwoSelect->getContent() as $option) {
                        /** @var GenericTag $option */
                        if ($option->getAttribute('value') === $numeratorAxisTwo->getRef()) {
                            $option->setBooleanAttribute('selected');
                            break;
                        }
                    }

                    if ($denominatorAxisTwo !== null) {
                        foreach ($ratioDenominatorAxisOneSelect->getContent() as $option) {
                            /** @var GenericTag $option */
                            if ($option->getAttribute('value') === $denominatorAxisTwo->getRef()) {
                                $option->setBooleanAttribute('selected');
                                break;
                            }
                        }
                    }
                }
            }
        }

        switch ($report->getChartType()) {
            case DW_Model_Report::CHART_PIE:
                $uncertaintyGroup->addClass('hide');
                $displayTypePieOption->setBooleanAttribute('selected');
                break;
            case DW_Model_Report::CHART_VERTICAL:
                $displayTypeVerticalOption->setBooleanAttribute('selected');
                break;
            case DW_Model_Report::CHART_HORIZONTAL:
                $displayTypeHorizontalOption->setBooleanAttribute('selected');
                break;
            case DW_Model_Report::CHART_VERTICAL_STACKED:
                $displayTypeVerticalStackedOption->setBooleanAttribute('selected');
                break;
            case DW_Model_Report::CHART_VERTICAL_GROUPED:
                $displayTypeVerticalGroupedOption->setBooleanAttribute('selected');
                break;
            case DW_Model_Report::CHART_HORIZONTAL_STACKED:
                $displayTypeHorizontalStackedOption->setBooleanAttribute('selected');
                break;
            case DW_Model_Report::CHART_HORIZONTAL_GROUPED:
                $displayTypeHorizontalGroupedOption->setBooleanAttribute('selected');
                break;
        }

        switch ($report->getSortType()) {
            case DW_Model_Report::SORT_VALUE_DECREASING:
                $resultsOrderDecreasingOption->setBooleanAttribute('selected');
                break;
            case DW_Model_Report::SORT_VALUE_INCREASING:
                $resultsOrderIncreasingOption->setBooleanAttribute('selected');
                break;
            case DW_Model_Report::SORT_CONVENTIONAL:
                $resultOrderConventionalOption->setBooleanAttribute('selected');
                break;
        }

        if ($report->getWithUncertainty()) {
            $uncertaintyChoiceInput->setBooleanAttribute('checked');
        }

        if ($report->hasFilters()) {
            $filtersCollapse->show();
        }
    }
}
