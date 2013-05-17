<?php
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
class DW_Form_configuration extends UI_Form
{
    /**
     * Génération du formulaire
     *
     * @param Classif_Model_Report $report
     */
    public function __construct(DW_Model_Report $report, $hash)
    {
        $numeratorAxis1 = $report->getNumeratorAxis1();
        $numeratorAxis2 = $report->getNumeratorAxis2();
        $denominatorAxis1 = $report->getDenominatorAxis1();
        $denominatorAxis2 = $report->getDenominatorAxis2();

        parent::__construct($hash);
        $idCube = $report->getCube()->getKey()['id'];
        $this->setAction('dw/report/applyconfiguration/idCube/'.$idCube.'/hashReport/'.$hash);
        $this->setAjax(null, 'parseConfigurationForm');


        // Groupe de sélection des Indicators.

        $groupValue = new UI_Form_Element_Group('value');
        $groupValue->setLabel(__('UI', 'name', 'values'));
        $groupValue->foldaway = false;

        $radioIndicatorRatio = new UI_Form_Element_Radio('indicatorRatio');

        $optionIndicator = new UI_Form_Element_Option('indicatorO', 'indicator', __('DW', 'name', 'indicator'));
        $radioIndicatorRatio->addOption($optionIndicator);

        $optionRatio = new UI_Form_Element_Option('ratioO', 'ratio', __('DW', 'name', 'ratio'));
        $radioIndicatorRatio->addOption($optionRatio);

        if ($report->getDenominator() !== null) {
            $radioIndicatorRatio->setValue($optionRatio->value);
        } else if ($report->getNumerator() !== null) {
            $radioIndicatorRatio->setValue($optionIndicator->value);
        }
        $groupValue->addElement($radioIndicatorRatio);

        $selectIndicator = new UI_Form_Element_Select('indicator');
        if (($report->getNumerator() === null) || ($report->getDenominator() !== null)) {
            $selectIndicator->getElement()->hidden = true;
        }

        foreach ($report->getCube()->getIndicators() as $indicator) {
            $indicatorOption = new UI_Form_Element_Option('indicator'.$indicator->getRef().'O', $indicator->getRef(), $indicator->getLabel());
            $selectIndicator->addOption($indicatorOption);
        }

        if (($report->getNumerator() !== null) && ($report->getDenominator() === null)) {
            $selectIndicator->setValue($report->getNumerator()->getRef());
        }
        $groupValue->addElement($selectIndicator);

        $optionIndicatorSelected = new UI_Form_Condition_Elementary(
            'optionIndicatorSelected',
            $radioIndicatorRatio,
            UI_Form_Condition_Elementary::EQUAL,
            $optionIndicator->value
        );

        $showSelectIndicator = new UI_Form_Action_Show('showSelectIndicator');
        $showSelectIndicator->condition = $optionIndicatorSelected;
        $selectIndicator->getElement()->addAction($showSelectIndicator);

        $selectNumerator = new UI_Form_Element_Select('numerator');
        if (($report->getNumerator() === null) || ($report->getDenominator() === null)) {
            $selectNumerator->getElement()->hidden = true;
        }

        foreach ($report->getCube()->getIndicators() as $indicator) {
            $indicatorOption = new UI_Form_Element_Option('numerator'.$indicator->getRef().'O', $indicator->getRef(), $indicator->getLabel());
            $selectNumerator->addOption($indicatorOption);
        }

        if (($report->getNumerator() !== null) && ($report->getDenominator() !== null)) {
            $selectNumerator->setValue($report->getNumerator()->getRef());
        }
        $groupValue->addElement($selectNumerator);

        $selectDenominator = new UI_Form_Element_Select('denominator');
        $selectDenominator->setLabel(__('DW', 'config', 'denominatorSelect'));
        if (($report->getNumerator() === null) || ($report->getDenominator() === null)) {
            $selectDenominator->getElement()->hidden = true;
        }

        foreach ($report->getCube()->getIndicators() as $indicator) {
            $indicatorOption = new UI_Form_Element_Option('denominator'.$indicator->getRef().'O', $indicator->getRef(), $indicator->getLabel());
            $selectDenominator->addOption($indicatorOption);
        }

        if ($report->getDenominator() !== null) {
            $selectDenominator->setValue($report->getDenominator()->getRef());
        }
        $groupValue->addElement($selectDenominator);

        $optionRatioSelected = new UI_Form_Condition_Elementary(
            'optionRatioSelected',
            $radioIndicatorRatio,
            UI_Form_Condition_Elementary::EQUAL,
            $optionRatio->value
        );

        $showSelectNumerator = new UI_Form_Action_Show('showSelectNumerator');
        $showSelectNumerator->condition = $optionRatioSelected;
        $selectNumerator->getElement()->addAction($showSelectNumerator);

        $showSelectDenominator = new UI_Form_Action_Show('showSelectDenominator');
        $showSelectDenominator->condition = $optionRatioSelected;
        $selectDenominator->getElement()->addAction($showSelectDenominator);

        $this->addElement($groupValue);


        // Groupe des Axes de l'Indicator.

        $groupIndicatorAxes = new UI_Form_Element_Group('indicatorAxes');
        $groupIndicatorAxes->setLabel(__('UI', 'name', 'axes'));
        $groupIndicatorAxes->foldaway = false;
        if (($report->getNumerator() === null) || ($report->getDenominator() !== null)) {
            $groupIndicatorAxes->getElement()->hidden = true;
        }

        $radioIndicatorAxesNumber = new UI_Form_Element_Radio('indicatorAxesNumber');

        $optionIndicatorAxisOne = new UI_Form_Element_Option('indicatorAxesNumber1O', '1', __('DW', 'config', 'oneAxisOption'));
        $radioIndicatorAxesNumber->addOption($optionIndicatorAxisOne);

        $optionIndicatorAxesTwo = new UI_Form_Element_Option('indicatorAxesNumber2O', '2', __('DW', 'config', 'twoAxesOption'));
        $radioIndicatorAxesNumber->addOption($optionIndicatorAxesTwo);

        if (($report->getNumerator() !== null) && ($report->getDenominator() === null) && ($numeratorAxis2 !== null)) {
            $radioIndicatorAxesNumber->setValue($optionIndicatorAxesTwo->value);
        } else {
            $radioIndicatorAxesNumber->setValue($optionIndicatorAxisOne->value);
        }
        $groupIndicatorAxes->addElement($radioIndicatorAxesNumber);

        $selectIndicatorAxisOne = new UI_Form_Element_Select('indicatorAxisOne');

        foreach ($report->getCube()->getFirstOrderedAxes() as $axis) {
            $axisOption = new UI_Form_Element_Option('indicatorAxisOne'.$axis->getRef(), $axis->getRef(), $axis->getLabel());
            $selectIndicatorAxisOne->addOption($axisOption);
        }

        if (($report->getDenominator() === null) && ($numeratorAxis1 !== null)) {
            $selectIndicatorAxisOne->setValue($numeratorAxis1->getRef());
        }
        $groupIndicatorAxes->addElement($selectIndicatorAxisOne);

        $selectIndicatorAxisTwo = new UI_Form_Element_Select('indicatorAxisTwo');
        if (($report->getNumerator() === null) || ($report->getDenominator() !== null) || ($numeratorAxis2 === null)) {
            $selectIndicatorAxisTwo->getElement()->hidden = true;
        }

        foreach ($report->getCube()->getFirstOrderedAxes() as $axis) {
            $axisOption = new UI_Form_Element_Option('indicatorAxisTwo'.$axis->getRef(), $axis->getRef(), $axis->getLabel());
            $selectIndicatorAxisTwo->addOption($axisOption);
        }

        if ($numeratorAxis2 !== null) {
            $selectIndicatorAxisTwo->setValue($numeratorAxis2->getRef());
        }
        $groupIndicatorAxes->addElement($selectIndicatorAxisTwo);

        $optionIndicatorAxesTwoSelected = new UI_Form_Condition_Elementary(
            'optionIndicatorTwoSelected',
            $radioIndicatorAxesNumber,
            UI_Form_Condition_Elementary::EQUAL,
            $optionIndicatorAxesTwo->value
        );

        $showSelectAxisTwo = new UI_Form_Action_Show('showSelectIndicatorAxisTwo');
        $showSelectAxisTwo->condition = $optionIndicatorAxesTwoSelected;
        $selectIndicatorAxisTwo->getElement()->addAction($showSelectAxisTwo);

        $this->addElement($groupIndicatorAxes);

        $showGroupIndicatorAxes = new UI_Form_Action_Show('showGroupIndicatorAxes');
        $showGroupIndicatorAxes->condition = $optionIndicatorSelected;
        $groupIndicatorAxes->getElement()->addAction($showGroupIndicatorAxes);


        // Groupe des Axes du Numerator.

        $groupNumeratorAxes = new UI_Form_Element_Group('numeratorAxes');
        $groupNumeratorAxes->setLabel(__('DW', 'config', 'numeratorAxesGroup'));
        $groupNumeratorAxes->foldaway = false;
        if ($report->getDenominator() === null) {
            $groupNumeratorAxes->getElement()->hidden = true;
        }

        $radioNumeratorAxesNumber = new UI_Form_Element_Radio('numeratorAxesNumber');

        $optionRatioAxisOne = new UI_Form_Element_Option('numeratorAxesNumber1O', '1', __('DW', 'config', 'oneAxisOption'));
        $radioNumeratorAxesNumber->addOption($optionRatioAxisOne);

        $optionRatioAxesTwo = new UI_Form_Element_Option('numeratorAxesNumber2O', '2', __('DW', 'config', 'twoAxesOption'));
        $radioNumeratorAxesNumber->addOption($optionRatioAxesTwo);

        if (($report->getDenominator() !== null) && ($numeratorAxis2 !== null)) {
            $radioNumeratorAxesNumber->setValue($optionRatioAxesTwo->value);
        } else {
            $radioNumeratorAxesNumber->setValue($optionRatioAxisOne->value);
        }
        $groupNumeratorAxes->addElement($radioNumeratorAxesNumber);

        $selectNumeratorAxisOne = new UI_Form_Element_Select('numeratorAxisOne');

        foreach ($report->getCube()->getFirstOrderedAxes() as $axis) {
            $axisOption = new UI_Form_Element_Option('numeratorAxisOne'.$axis->getRef(), $axis->getRef(), $axis->getLabel());
            $selectNumeratorAxisOne->addOption($axisOption);
        }

        if (($report->getDenominator() !== null) && ($numeratorAxis1 !== null)) {
            $selectNumeratorAxisOne->setValue($numeratorAxis1->getRef());
        }
        $groupNumeratorAxes->addElement($selectNumeratorAxisOne);

        $selectNumeratorAxisTwo = new UI_Form_Element_Select('numeratorAxisTwo');
        if (($report->getDenominator() === null) || ($numeratorAxis2 === null)) {
            $selectNumeratorAxisTwo->getElement()->hidden = true;
        }

        foreach ($report->getCube()->getFirstOrderedAxes() as $axis) {
            $axisOption = new UI_Form_Element_Option('numeratorAxisTwo'.$axis->getRef(), $axis->getRef(), $axis->getLabel());
            $selectNumeratorAxisTwo->addOption($axisOption);
        }

        if (($report->getDenominator() !== null) && ($numeratorAxis2 !== null)) {
            $selectNumeratorAxisTwo->setValue($numeratorAxis2->getRef());
        }
        $groupNumeratorAxes->addElement($selectNumeratorAxisTwo);

        $optionRatioAxesTwoSelected = new UI_Form_Condition_Elementary(
            'optionNumeratorTwoSelected',
            $radioNumeratorAxesNumber,
            UI_Form_Condition_Elementary::EQUAL,
            $optionRatioAxesTwo->value
        );

        $showSelectAxisTwo = new UI_Form_Action_Show('showSelectNumeratorAxisTwo');
        $showSelectAxisTwo->condition = $optionRatioAxesTwoSelected;
        $selectNumeratorAxisTwo->getElement()->addAction($showSelectAxisTwo);

        $this->addElement($groupNumeratorAxes);

        $showGroupNumeratorAxes = new UI_Form_Action_Show('showGroupNumeratorAxes');
        $showGroupNumeratorAxes->condition = $optionRatioSelected;
        $groupNumeratorAxes->getElement()->addAction($showGroupNumeratorAxes);


        // Groupe des Axes du Denominator.

        $groupDenominatorAxes = new UI_Form_Element_Group('denominatorAxes');
        $groupDenominatorAxes->setLabel(__('DW', 'config', 'denominatorAxesGroup'));
        $groupDenominatorAxes->foldaway = false;
        if ($report->getDenominator() === null) {
            $groupDenominatorAxes->getElement()->hidden = true;
        }

        $selectDenominatorAxisOne = new UI_Form_Element_Select('denominatorAxisOne');
        $selectDenominatorAxisOne->addNullOption('', null);

        $numeratorAxisChange = new UI_Form_Condition_Elementary(
            'numeratorAxisOneChange',
            $selectNumeratorAxisOne,
            UI_Form_Condition_Elementary::NEQUAL,
            null
        );

        $setDenominatorAxisOneNullValue = new UI_Form_Action_SetValue('setDenominatorAxisOneNullValue');
        $setDenominatorAxisOneNullValue->value = null;
        $setDenominatorAxisOneNullValue->condition = $numeratorAxisChange;
        $selectDenominatorAxisOne->getElement()->addAction($setDenominatorAxisOneNullValue);

        if (($report->getDenominator() !== null) && ($numeratorAxis1 !== null)) {
            $selectedNumeratorAxisOne = $numeratorAxis1;
        } else {
            $allAxes = $report->getCube()->getFirstOrderedAxes();
            $selectedNumeratorAxisOne = array_shift($allAxes);
        }
        foreach ($report->getCube()->getFirstOrderedAxes() as $axis) {
            $axisOption = new UI_Form_Element_Option('denominatorAxisOne'.$axis->getRef(), $axis->getRef(), $axis->getLabel());
            if (!($selectedNumeratorAxisOne->isNarrowerThan($axis)) && ($selectedNumeratorAxisOne !== $axis)) {
                $axisOption->hidden = true;
            }
            $selectDenominatorAxisOne->addOption($axisOption);

            $broaderAxesOneSelected = new UI_Form_Condition_Expression('broaderAxesOne'.$axis->getRef().'Selected');
            $broaderAxesOneSelected->expression = UI_Form_Condition_Expression::OR_SIGN;

            $narrowerAxis = $axis;
            while ($narrowerAxis !== null) {
                $broaderAxisSelected = new UI_Form_Condition_Elementary(
                    'option'.$axis->getRef().'NumeratorAxis'.$narrowerAxis->getRef().'Selected',
                    $selectNumeratorAxisOne,
                    UI_Form_Condition_Elementary::EQUAL,
                    $narrowerAxis->getRef()
                );
                $broaderAxesOneSelected->addCondition($broaderAxisSelected);
                $narrowerAxis = $narrowerAxis->getDirectNarrower();
            }

            $showOptionAxisOne = new UI_Form_Action_Show('showOptionAxisOne'.$axis->getRef());
            $showOptionAxisOne->setOption($axisOption);
            $showOptionAxisOne->condition = $broaderAxesOneSelected;
            $selectDenominatorAxisOne->getElement()->addAction($showOptionAxisOne);
        }

        if ($denominatorAxis1 !== null) {
            $selectDenominatorAxisOne->setValue($denominatorAxis1->getRef());
        }
        $groupDenominatorAxes->addElement($selectDenominatorAxisOne);

        $selectDenominatorAxisTwo = new UI_Form_Element_Select('denominatorAxisTwo');
        if (($report->getDenominator() === null) || ($numeratorAxis2 === null)) {
            $selectDenominatorAxisTwo->getElement()->hidden = true;
        }

        $selectDenominatorAxisTwo->addNullOption('', null);

        $numeratorAxisChange = new UI_Form_Condition_Elementary(
            'numeratorAxisTwoChange',
            $selectNumeratorAxisTwo,
            UI_Form_Condition_Elementary::NEQUAL,
            null
        );

        $setDenominatorAxisTwoNullValue = new UI_Form_Action_SetValue('setDenominatorAxisTwoNullValue');
        $setDenominatorAxisTwoNullValue->value = null;
        $setDenominatorAxisTwoNullValue->condition = $numeratorAxisChange;
        $selectDenominatorAxisTwo->getElement()->addAction($setDenominatorAxisTwoNullValue);

        if (($report->getDenominator() !== null) && ($numeratorAxis2 !== null)) {
            $selectedNumeratorAxisTwo = $numeratorAxis2;
        } else {
            $allAxes = $report->getCube()->getFirstOrderedAxes();
            $selectedNumeratorAxisTwo = array_shift($allAxes);
        }
        foreach ($report->getCube()->getFirstOrderedAxes() as $axis) {
            $axisOption = new UI_Form_Element_Option('denominatorAxisTwo'.$axis->getRef(), $axis->getRef(), $axis->getLabel());
            if (!($selectedNumeratorAxisTwo->isNarrowerThan($axis)) && ($selectedNumeratorAxisTwo !== $axis)) {
                $axisOption->hidden = true;
            }
            $selectDenominatorAxisTwo->addOption($axisOption);

            $broaderAxesTwoSelected = new UI_Form_Condition_Expression('broaderAxesTwo'.$axis->getRef().'Selected');
            $broaderAxesTwoSelected->expression = UI_Form_Condition_Expression::OR_SIGN;

            $narrowerAxis = $axis;
            while ($narrowerAxis !== null) {
                $broaderAxisSelected = new UI_Form_Condition_Elementary(
                    'option'.$axis->getRef().'NumeratorAxis'.$narrowerAxis->getRef().'Selected',
                    $selectNumeratorAxisTwo,
                    UI_Form_Condition_Elementary::EQUAL,
                    $narrowerAxis->getRef()
                );
                $broaderAxesTwoSelected->addCondition($broaderAxisSelected);
                $narrowerAxis = $narrowerAxis->getDirectNarrower();
            }

            $showOptionAxisTwo = new UI_Form_Action_Show('showOptionAxisTwo'.$axis->getRef());
            $showOptionAxisTwo->setOption($axisOption);
            $showOptionAxisTwo->condition = $broaderAxesTwoSelected;
            $selectDenominatorAxisTwo->getElement()->addAction($showOptionAxisTwo);
        }

        if ($denominatorAxis2 !== null) {
            $selectDenominatorAxisTwo->setValue($denominatorAxis2->getRef());
        }
        $groupDenominatorAxes->addElement($selectDenominatorAxisTwo);

        $showSelectAxisTwo = new UI_Form_Action_Show('showSelectDenominatorAxisTwo');
        $showSelectAxisTwo->condition = $optionRatioAxesTwoSelected;
        $selectDenominatorAxisTwo->getElement()->addAction($showSelectAxisTwo);

        $this->addElement($groupDenominatorAxes);

        $showGroupDenominatorAxes = new UI_Form_Action_Show('showGroupDenominatorAxes');
        $showGroupDenominatorAxes->condition = $optionRatioSelected;
        $groupDenominatorAxes->getElement()->addAction($showGroupDenominatorAxes);


        // Groupe de sélection de l'affichage.

        $groupDisplay = new UI_Form_Element_Group('display');
        $groupDisplay->setLabel(__('UI', 'name', 'display'));
        $groupDisplay->foldaway = false;

        $twoAxisSelected = new UI_Form_Condition_Expression('twoAxisSelected');
        $twoAxisSelected->expression = UI_Form_Condition_Expression::OR_SIGN;

        $twoAxisIndicatorSelected = new UI_Form_Condition_Expression('twoAxisIndicatorSelected');
        $twoAxisIndicatorSelected->expression = UI_Form_Condition_Expression::AND_SIGN;
        $twoAxisIndicatorSelected->addCondition($optionIndicatorSelected);
        $twoAxisIndicatorSelected->addCondition($optionIndicatorAxesTwoSelected);

        $twoAxisRatioSelected = new UI_Form_Condition_Expression('twoAxisRatioSelected');
        $twoAxisRatioSelected->expression = UI_Form_Condition_Expression::AND_SIGN;
        $twoAxisRatioSelected->addCondition($optionRatioSelected);
        $twoAxisRatioSelected->addCondition($optionRatioAxesTwoSelected);

        $twoAxisSelected->addCondition($twoAxisIndicatorSelected);
        $twoAxisSelected->addCondition($twoAxisRatioSelected);

        $selectChartType = new UI_Form_Element_Select('chartType');
        $selectChartType->addNullOption('', null);

        $indicatorRatioChange = new UI_Form_Condition_Elementary(
            'indicatorRatioChange',
            $radioIndicatorRatio,
            UI_Form_Condition_Elementary::NEQUAL,
            null
        );

        $indicatorAxisNumberChange = new UI_Form_Condition_Elementary(
            'indicatorAxisNumberChange',
            $radioIndicatorAxesNumber,
            UI_Form_Condition_Elementary::NEQUAL,
            null
        );

        $numeratorAxisNumberChange = new UI_Form_Condition_Elementary(
            'numeratorAxisNumberChange',
            $radioNumeratorAxesNumber,
            UI_Form_Condition_Elementary::NEQUAL,
            null
        );

        $axisNumberChange = new UI_Form_Condition_Expression('axisNumberChange');
        $axisNumberChange->expression = UI_Form_Condition_Expression::OR_SIGN;
        $axisNumberChange->addCondition($indicatorRatioChange);
        $axisNumberChange->addCondition($indicatorAxisNumberChange);
        $axisNumberChange->addCondition($numeratorAxisNumberChange);

        $setChartTypeNullValue = new UI_Form_Action_SetValue('setChartTypeNullValue');
        $setChartTypeNullValue->value = null;
        $setChartTypeNullValue->condition = $axisNumberChange;
        $selectChartType->getElement()->addAction($setChartTypeNullValue);

        // Camembert
        $optionChartPie = new UI_Form_Element_Option(
            'chartPie',
            DW_Model_Report::CHART_PIE,
            __('DW', 'config', 'chartTypePieOption')
        );
        $optionChartPie->hidden = ($numeratorAxis2 !== null);
        $selectChartType->addOption($optionChartPie);

        $showChartPieOneAxes = new UI_Form_Action_Hide('showChartPieOneAxes');
        $showChartPieOneAxes->setOption($optionChartPie);
        $showChartPieOneAxes->condition = $twoAxisSelected;
        $selectChartType->getElement()->addAction($showChartPieOneAxes);

        // Histogramme 1D vertical
        $optionChartVertical = new UI_Form_Element_Option(
            'chartVertical',
            DW_Model_Report::CHART_VERTICAL,
            __('DW', 'config', 'chartTypeVerticalOption')
        );
        $optionChartVertical->hidden = ($numeratorAxis2 !== null);
        $selectChartType->addOption($optionChartVertical);

        $showChartVerticalOneAxes = new UI_Form_Action_Hide('showChartVerticalOneAxes');
        $showChartVerticalOneAxes->setOption($optionChartVertical);
        $showChartVerticalOneAxes->condition = $twoAxisSelected;
        $selectChartType->getElement()->addAction($showChartVerticalOneAxes);

        // Histogramme 1D horizontal
        $optionChartHorizontal = new UI_Form_Element_Option(
            'chartHorizontal',
            DW_Model_Report::CHART_HORIZONTAL,
            __('DW', 'config', 'chartTypeHorizontalOption')
        );
        $optionChartHorizontal->hidden = ($numeratorAxis2 !== null);
        $selectChartType->addOption($optionChartHorizontal);

        $showChartHorizontalOneAxes = new UI_Form_Action_Hide('showChartHorizontalOneAxes');
        $showChartHorizontalOneAxes->setOption($optionChartHorizontal);
        $showChartHorizontalOneAxes->condition = $twoAxisSelected;
        $selectChartType->getElement()->addAction($showChartHorizontalOneAxes);

        // Histogramme 2D vertical empilé
        $optionChartVerticalStacked = new UI_Form_Element_Option(
            'chartVerticalStacked',
            DW_Model_Report::CHART_VERTICAL_STACKED,
            __('DW', 'config', 'chartTypeVerticalStackedOption')
        );
        $optionChartVerticalStacked->hidden = ($numeratorAxis2 === null);
        $selectChartType->addOption($optionChartVerticalStacked);

        $showChartVerticalStackedTwoAxes = new UI_Form_Action_Show('showChartVerticalStackedTwoAxes');
        $showChartVerticalStackedTwoAxes->setOption($optionChartVerticalStacked);
        $showChartVerticalStackedTwoAxes->condition = $twoAxisSelected;
        $selectChartType->getElement()->addAction($showChartVerticalStackedTwoAxes);

        // Histogramme 2D vertical groupé
        $optionChartVerticalGrouped = new UI_Form_Element_Option(
            'chartVerticalGrouped',
            DW_Model_Report::CHART_VERTICAL_GROUPED,
            __('DW', 'config', 'chartTypeVerticalGroupedOption')
        );
        $optionChartVerticalGrouped->hidden = ($numeratorAxis2 === null);
        $selectChartType->addOption($optionChartVerticalGrouped);

        $showChartVerticalGroupedTwoAxes = new UI_Form_Action_Show('showChartVerticalGroupedTwoAxes');
        $showChartVerticalGroupedTwoAxes->setOption($optionChartVerticalGrouped);
        $showChartVerticalGroupedTwoAxes->condition = $twoAxisSelected;
        $selectChartType->getElement()->addAction($showChartVerticalGroupedTwoAxes);

        // Histogramme 2D horizontal empilé
        $optionChartHorizontalStacked = new UI_Form_Element_Option(
            'chartHorizontalStacked',
            DW_Model_Report::CHART_HORIZONTAL_STACKED,
            __('DW', 'config', 'chartTypeHorizontalStackedOption')
        );
        $optionChartHorizontalStacked->hidden = ($numeratorAxis2 === null);
        $selectChartType->addOption($optionChartHorizontalStacked);

        $showChartHorizontalStackedTwoAxes = new UI_Form_Action_Show('showChartHorizontalStackedTwoAxes');
        $showChartHorizontalStackedTwoAxes->setOption($optionChartHorizontalStacked);
        $showChartHorizontalStackedTwoAxes->condition = $twoAxisSelected;
        $selectChartType->getElement()->addAction($showChartHorizontalStackedTwoAxes);

        // Histogramme 2D horizontal groupé
        $optionChartHorizontalGrouped = new UI_Form_Element_Option(
            'chartHorizontalGrouped',
            DW_Model_Report::CHART_HORIZONTAL_GROUPED,
            __('DW', 'config', 'chartTypeHorizontalGroupedOption')
        );
        $optionChartHorizontalGrouped->hidden = ($numeratorAxis2 === null);
        $selectChartType->addOption($optionChartHorizontalGrouped);

        $showChartHorizontalGroupedTwoAxes = new UI_Form_Action_Show('showChartHorizontalGroupedTwoAxes');
        $showChartHorizontalGroupedTwoAxes->setOption($optionChartHorizontalGrouped);
        $showChartHorizontalGroupedTwoAxes->condition = $twoAxisSelected;
        $selectChartType->getElement()->addAction($showChartHorizontalGroupedTwoAxes);

        $selectChartType->setValue($report->getChartType());
        $groupDisplay->addElement($selectChartType);

        $selectSortType = new UI_Form_Element_Select('sortType');
        if (($report->getNumerator() === null) || ($report->getDenominator() !== null) || ($numeratorAxis2 !== null)) {
            $selectSortType->getElement()->hidden = true;
        }
        $selectSortType->setValue($report->getSortType());

        $optionSortByDecreasingValue = new UI_Form_Element_Option(
            'sortByDecreasingValue',
            DW_Model_Report::SORT_VALUE_DECREASING,
            __('DW', 'config', 'sortByDecreasingValues')
        );
        $selectSortType->addOption($optionSortByDecreasingValue);

        $optionSortByIncreasingValue = new UI_Form_Element_Option(
            'sortByIncreasingValue',
            DW_Model_Report::SORT_VALUE_INCREASING,
            __('DW', 'config', 'sortByIncreasingValues')
        );
        $selectSortType->addOption($optionSortByIncreasingValue);

        $optionSortByMembers = new UI_Form_Element_Option(
            'sortByMembers',
            DW_Model_Report::SORT_CONVENTIONAL,
            __('DW', 'config', 'sortByMembers')
        );
        $selectSortType->addOption($optionSortByMembers);

        $groupDisplay->addElement($selectSortType);

        $hideSortType = new UI_Form_Action_Hide('hideSortType');
        $hideSortType->condition = $twoAxisSelected;
        $selectSortType->getElement()->addAction($hideSortType);

        $checkboxWithUncertainty = new UI_Form_Element_MultiCheckbox('withUncertainty');
        if ($report->getChartType() === DW_Model_Report::CHART_PIE) {
            $checkboxWithUncertainty->getElement()->hidden = true;
        }

        $optionWithUncertainty = new UI_Form_Element_Option(
            'uncertaintyO',
            1,
            __('DW', 'config', 'withUncertaintyCheckbox')
        );
        $checkboxWithUncertainty->addOption($optionWithUncertainty);

        if ($report->getWithUncertainty()) {
            $checkboxWithUncertainty->setValue(1);
        }
        $groupDisplay->addElement($checkboxWithUncertainty);

        $chartPieSelected = new UI_Form_Condition_Elementary(
            'chartPieSelected',
            $selectChartType,
            UI_Form_Condition_Elementary::EQUAL,
            $optionChartPie->value
        );

        $hideWithUncertainty = new UI_Form_Action_Hide('hideWithUncertainty');
        $hideWithUncertainty->condition = $chartPieSelected;
        $checkboxWithUncertainty->getElement()->addAction($hideWithUncertainty);

        $this->addElement($groupDisplay);


        // Groupe des filtres.

        $groupFilters = new UI_Form_Element_Group('filters');
        $groupFilters->setLabel(__('UI', 'name', 'filters'));
        $groupFilters->folded = !$report->hasFilters();

        foreach ($report->getCube()->getFirstOrderedAxes() as $axis) {
            $groupAxisFilter = new UI_Form_Element_Group('filter'.$axis->getRef());
            $groupAxisFilter->setLabel($axis->getLabel());
            $groupAxisFilter->foldaway = false;

            $hiddenAxis = new UI_Form_Element_Hidden('refAxis');
            $hiddenAxis->setValue($axis->getRef());
            $groupAxisFilter->addElement($hiddenAxis);

            $radioFilterAxisNumberMembers = new UI_Form_Element_Radio('filterAxis'.$axis->getRef().'NumberMembers');

            $optionAll = new UI_Form_Element_Option('allO', 'all', __('UI', 'other', 'all'));
            $radioFilterAxisNumberMembers->addOption($optionAll);

            $optionOne = new UI_Form_Element_Option('oneO', 'one', __('UI', 'other', 'one'));
            $radioFilterAxisNumberMembers->addOption($optionOne);

            $optionSome = new UI_Form_Element_Option('someO', 'some', __('UI', 'other', 'several'));
            $radioFilterAxisNumberMembers->addOption($optionSome);

            $reportFilterAxis = $report->getFilterForAxis($axis);
            if ($reportFilterAxis !== null) {
                if (count($reportFilterAxis->getMembers()) > 1) {
                    $radioFilterAxisNumberMembers->setValue($optionSome->value);
                } else {
                    $radioFilterAxisNumberMembers->setValue($optionOne->value);
                }
            } else {
                $radioFilterAxisNumberMembers->setValue($optionAll->value);
            }
            $groupAxisFilter->addElement($radioFilterAxisNumberMembers);

            $selectAxisFilterMember = new UI_Form_Element_Select('selectAxis'.$axis->getRef().'MemberFilter');
            if ($radioFilterAxisNumberMembers->getValue() !== $optionOne->value) {
                $selectAxisFilterMember->getElement()->hidden = true;
            }

            $selectAxisFilterMembers = new UI_Form_Element_MultiSelect('selectAxis'.$axis->getRef().'MembersFilter');
            if ($radioFilterAxisNumberMembers->getValue() !== $optionSome->value) {
                $selectAxisFilterMembers->getElement()->hidden = true;
            }

            foreach ($axis->getMembers() as $member) {
                $optionMember = new UI_Form_Element_Option($member->getRef().'O', $member->getRef(), $member->getLabel());
                $selectAxisFilterMember->addOption($optionMember);
                $selectAxisFilterMembers->addOption($optionMember);
            }

            if ($radioFilterAxisNumberMembers->getValue() === $optionOne->value) {
                $selectAxisFilterMember->setValue($reportFilterAxis->getMembers()[0]->getRef());
            }
            $groupAxisFilter->addElement($selectAxisFilterMember);
            if ($radioFilterAxisNumberMembers->getValue() === $optionSome->value) {
                $refMembers = array();
                foreach ($reportFilterAxis->getMembers() as $member) {
                    $refMembers[] = $member->getRef();
                }
                $selectAxisFilterMembers->setValue($refMembers);
            }
            $groupAxisFilter->addElement($selectAxisFilterMembers);

            $groupFilters->addElement($groupAxisFilter);

            $oneMemberSelected = new UI_Form_Condition_Elementary(
                'oneMember'.$axis->getRef().'Selected',
                $radioFilterAxisNumberMembers,
                UI_Form_Condition_Elementary::EQUAL,
                $optionOne->value
            );

            $showSelectAxisFilterMember = new UI_Form_Action_Show('showSelectAxis'.$axis->getRef().'FilterMember');
            $showSelectAxisFilterMember->condition = $oneMemberSelected;
            $selectAxisFilterMember->getElement()->addAction($showSelectAxisFilterMember);

            $someMembersSelected = new UI_Form_Condition_Elementary(
                'someMembers'.$axis->getRef().'Selected',
                $radioFilterAxisNumberMembers,
                UI_Form_Condition_Elementary::EQUAL,
                $optionSome->value
            );

            $showSelectAxisFilterMembers = new UI_Form_Action_Show('showSelectAxis'.$axis->getRef().'FilterMembers');
            $showSelectAxisFilterMembers->condition = $someMembersSelected;
            $selectAxisFilterMembers->getElement()->addAction($showSelectAxisFilterMembers);
        }

        $this->addElement($groupFilters);


        // Groupe de validation du formulaire.

        $saveButton = new UI_Form_Element_Submit('applyReportConfiguration');
        $saveButton->setLabel(__('UI', 'verb', 'launch'));
        $this->addActionElement($saveButton);

        $resetButton = new UI_Form_Element_Reset('resetReportConfiguration');
        $resetButton->setLabel(__('UI', 'verb', 'reset'));
        $this->addActionElement($resetButton);
    }

}
