<?php
/**
 * @author valentin.claras
 * @author cyril.perraud
 * @package    DW
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Classe du controler de Data Warehouse
 * @package DW
 */
class DW_ReportController extends Core_Controller
{
    /**
     * Récupère un report enregistré en session par son hash.
     *
     * @param string $hash
     *
     * @return DW_Model_Report
     */
    protected function getReportByHash($hash)
    {
        $configuration = Zend_Registry::get('configuration');
        $sessionName = $configuration->sessionStorage->name.'_'.APPLICATION_ENV;
        $zendSessionReport = new Zend_Session_Namespace($sessionName);

        return DW_Model_Report::getFromString($zendSessionReport->$hash);
    }

    /**
     * Récupère un report enregistré en session par son hash.
     *
     * @param string $hash
     * @param DW_Model_Report $report
     */
    protected function setReportByHash($hash, $report)
    {
        $configuration = Zend_Registry::get('configuration');
        $sessionName = $configuration->sessionStorage->name.'_'.APPLICATION_ENV;
        $zendSessionReport = new Zend_Session_Namespace($sessionName);

        $this->entityManager->clear();

        $zendSessionReport->$hash = $report->getAsString();
    }

    /**
     * Détails d'un Report.
     * @Secure("viewReport")
     */
    public function detailsAction()
    {
        $report = null;
        if ($this->hasParam('hashReport')) {
            $report = $this->getReportByHash($this->getParam('hashReport'));
        }
        if (!isset($report) || !($report instanceof DW_Model_Report)) {
            if ($this->hasParam('idReport')) {
                $report = DW_Model_Report::load($this->getParam('idReport'));
            } else {
                $report = new DW_Model_Report(DW_Model_Cube::load($this->getParam('idCube')));
                $report->setLabel(__('DW', 'report', 'newReportDefaultLabelPage'));
            }
        }
        if ($report->getKey() != array()) {
            $this->view->isNew = false;
        } else {
            $this->view->isNew = true;
        }
        $hash = ($this->hasParam('hashReport')) ? $this->getParam('hashReport') : (string) spl_object_hash($report);

        $this->view->headLink()->appendStylesheet('css/dw/report.css');
        $this->view->idCube = $report->getCube()->getId();;
        $this->view->hashReport = $hash;
        $this->view->reportLabel = $report->getLabel();
        require_once (dirname(__FILE__).'/../forms/Configuration.php');
        $this->view->configurationForm = new DW_Form_configuration($report, $hash);

        if ($this->hasParam('viewConfiguration')) {
            $this->view->viewConfiguration = $this->getParam('viewConfiguration');
        } else {
            $this->view->viewConfiguration = new DW_ViewConfiguration();
            $this->view->viewConfiguration->setOutputUrl('index/report/idCube/'.$report->getCube()->getId());
            $this->view->viewConfiguration->setSaveURL('dw/report/details');
        }

        $this->setReportByHash($hash, $report);
    }

    /**
     * Applique la nouvelle configuration d'un formulaire.
     * @Secure("viewReport")
     */
    public function applyconfigurationAction()
    {
        $report = $this->getReportByHash($this->getParam('hashReport'));

        $configurationPost = json_decode($this->getParam($this->getParam('hashReport')), true);
        $errors = array();

        // Options de configuration.
        if ($configurationPost['value']['elements']['indicatorRatio']['value'] === 'ratio') {
            $numeratorIndicatorRef = $configurationPost['value']['elements']['numerator']['value'];
            try {
                $numeratorIndicator = DW_Model_Indicator::loadByRefAndCube($numeratorIndicatorRef, $report->getCube());
            } catch (Core_Exception_NotFound $e) {
                $errors['numerator'] = __('DW', 'configValidation', 'numeratorIsRequired');
            }
            $report->setNumerator($numeratorIndicator);

            $denominatorIndicatorRef = $configurationPost['value']['elements']['denominator']['value'];
            try {
                $denominatorIndicator = DW_Model_Indicator::loadByRefAndCube($denominatorIndicatorRef, $report->getCube());
            } catch (Core_Exception_NotFound $e) {
                $errors['denominator'] = __('DW', 'configValidation', 'denominatorIsRequired');
            }
            $report->setDenominator($denominatorIndicator);

            $report->setNumeratorAxis1(null);
            $report->setNumeratorAxis2(null);
            $numeratorAxisOneRef = $configurationPost['numeratorAxes']['elements']['numeratorAxisOne']['value'];
            try {
                $numeratorAxisOne = DW_Model_Axis::loadByRefAndCube($numeratorAxisOneRef, $report->getCube());
                $report->setNumeratorAxis1($numeratorAxisOne);
            } catch (Core_Exception_NotFound $e) {
                $errors['numeratorAxisOne'] = __('DW', 'configValidation', 'numeratorAxisOneInvalid');
            }
            if ($configurationPost['numeratorAxes']['elements']['numeratorAxesNumber']['value'] === '2') {
                $numeratorAxisTwoRef = $configurationPost['numeratorAxes']['elements']['numeratorAxisTwo']['value'];
                if ($numeratorAxisTwoRef === $numeratorAxisOneRef) {
                    $errors['numeratorAxisTwo'] = __('DW', 'configValidation', 'axisTwoSameAsOne');
                } else {
                    try {
                        $numeratorAxisTwo = DW_Model_Axis::loadByRefAndCube($numeratorAxisTwoRef, $report->getCube());
                        if (!$numeratorAxisTwo->isTransverseWith($numeratorAxisOne)) {
                            $errors['numeratorAxisTwo'] = __('DW', 'configValidation', 'axisTwoLinkedToOne');
                        } else {
                            $report->setNumeratorAxis2($numeratorAxisTwo);
                        }
                    } catch (Core_Exception_NotFound $e) {
                        $errors['numeratorAxisTwo'] = __('DW', 'configValidation', 'numeratorAxisTwoInvalid');
                    }
                }
            }

            $report->setDenominatorAxis1(null);
            $report->setDenominatorAxis2(null);
            $denominatorAxisOneRef = $configurationPost['denominatorAxes']['elements']['denominatorAxisOne']['value'];
            try {
                $denominatorAxisOne = DW_Model_Axis::loadByRefAndCube($denominatorAxisOneRef, $report->getCube());
                $report->setDenominatorAxis1($denominatorAxisOne);
            } catch (Core_Exception_NotFound $e) {
                // Possibilité de ne pas avoir d'axe au dénominateur.
            }
            if ($configurationPost['numeratorAxes']['elements']['numeratorAxesNumber']['value'] === '2') {
                $denominatorAxisTwoRef = $configurationPost['denominatorAxes']['elements']['denominatorAxisTwo']['value'];
                if (($denominatorAxisTwoRef != null) && ($denominatorAxisTwoRef === $denominatorAxisOneRef)) {
                    $errors['denominatorAxisTwo'] = __('DW', 'configValidation', 'axisTwoSameAsOne');
                } else {
                    try {
                        $denominatorAxisTwo = DW_Model_Axis::loadByRefAndCube($denominatorAxisTwoRef, $report->getCube());
                        if (isset($denominatorAxisOne) && (!$denominatorAxisTwo->isTransverseWith($denominatorAxisOne))) {
                            $errors['denominatorAxisTwo'] = __('DW', 'configValidation', 'axisTwoLinkedToOne');
                        } else {
                            $report->setDenominatorAxis2($denominatorAxisTwo);
                        }
                    } catch (Core_Exception_NotFound $e) {
                        // Possibilité de ne pas avoir d'axe au dénominateur.
                    }
                }
            }
        } else if ($configurationPost['value']['elements']['indicatorRatio']['value'] === 'indicator') {
            // Suppression des anciens dénominateurs.
            $report->setDenominator(null);
            $report->setDenominatorAxis1(null);
            $report->setDenominatorAxis2(null);

            $indicatorRef = $configurationPost['value']['elements']['indicator']['value'];
            try {
                $indicator = DW_Model_Indicator::loadByRefAndCube($indicatorRef, $report->getCube());
            } catch (Core_Exception_NotFound $e) {
                $errors['indicator'] = __('DW', 'configValidation', 'indicatorIsRequired');
            }
            $report->setNumerator($indicator);

            $report->setNumeratorAxis1(null);
            $report->setNumeratorAxis2(null);
            $indicatorAxisOneRef = $configurationPost['indicatorAxes']['elements']['indicatorAxisOne']['value'];
            try {
                $indicatorAxisOne = DW_Model_Axis::loadByRefAndCube($indicatorAxisOneRef, $report->getCube());
                $report->setNumeratorAxis1($indicatorAxisOne);
            } catch (Core_Exception_NotFound $e) {
                $errors['indicatorAxisOne'] = __('DW', 'configValidation', 'indicatorAxisOneInvalid');
            }
            if ($configurationPost['indicatorAxes']['elements']['indicatorAxesNumber']['value'] === '2') {
                $indicatorAxisTwoRef = $configurationPost['indicatorAxes']['elements']['indicatorAxisTwo']['value'];
                if ($indicatorAxisTwoRef === $indicatorAxisOneRef) {
                    $errors['indicatorAxisTwo'] = __('DW', 'configValidation', 'axisTwoSameAsOne');
                } else {
                    try {
                        $indicatorAxisTwo = DW_Model_Axis::loadByRefAndCube($indicatorAxisTwoRef, $report->getCube());
                        if (!$indicatorAxisTwo->isTransverseWith($indicatorAxisOne)) {
                            $errors['indicatorAxisTwo'] = __('DW', 'configValidation', 'axisTwoLinkedToOne');
                        } else {
                            $report->setNumeratorAxis2($indicatorAxisTwo);
                        }
                    } catch (Core_Exception_NotFound $e) {
                        $errors['indicatorAxisTwo'] = __('DW', 'configValidation', 'indicatorAxisTwoInvalid');
                    }
                }
            }
        } else {
            $errors['indicatorRatio'] = __('DW', 'configValidation', 'reportTypeMandatory');
        }

        // Options d'affichage.
        try {
            $report->setChartType($configurationPost['display']['elements']['chartType']['value']);
        } catch (Core_Exception_InvalidArgument $e) {
            $errors['chartType'] = __('DW', 'configValidation', 'chartTypeInvalid');
        }
        $acceptedSortType = array(
            DW_Model_Report::SORT_VALUE_INCREASING,
            DW_Model_Report::SORT_VALUE_DECREASING,
            DW_Model_Report::SORT_CONVENTIONAL,
        );
        if (in_array($configurationPost['display']['elements']['sortType']['value'], $acceptedSortType)) {
            $report->setSortType($configurationPost['display']['elements']['sortType']['value']);
        }
        if ($configurationPost['display']['elements']['withUncertainty']['value'] == array('1')) {
            $report->setWithUncertainty(true);
        } else {
            $report->setWithUncertainty(false);
        }

        // Filtres.
        foreach ($report->getFilters() as $oldFilter) {
            $report->removeFilter($oldFilter);
        }
        foreach ($configurationPost['filters']['elements'] as $filterArray) {
            $filterAxisRef = $filterArray['elements']['refAxis']['hiddenValues']['refAxis'];
            if ($filterArray['elements']['filterAxis'.$filterAxisRef.'NumberMembers']['value'] !== 'all') {
                try {
                    $filterAxis = DW_Model_Axis::loadByRefAndCube($filterAxisRef, $report->getCube());
                } catch (Core_Exception_NotFound $e) {
                    $errors['filterAxis'.$filterAxisRef.'NumberMembers'] = __('DW', 'configValidation', 'filterAxisInvalid');
                }
                $filter = new DW_Model_Filter($report, $filterAxis);

                if ($filterArray['elements']['filterAxis'.$filterAxisRef.'NumberMembers']['value'] === 'some') {
                    $filterMemberRefs = $filterArray['elements']['selectAxis'.$filterAxisRef.'MembersFilter']['value'];
                    foreach ($filterMemberRefs as $filterMemberRef) {
                        try {
                            $filterMember = DW_Model_Member::loadByRefAndAxis($filterMemberRef, $filterAxis);
                            $filter->addMember($filterMember);
                        } catch (Core_Exception_NotFound $e) {
                            $errors['selectAxis'.$filterAxisRef.'MembersFilter'] = __('DW', 'configValidation', 'filterMemberInvalid');
                        }
                    }
                } else if ($filterArray['elements']['filterAxis'.$filterAxisRef.'NumberMembers']['value'] === 'one') {
                    $filterMemberRef = $filterArray['elements']['selectAxis'.$filterAxisRef.'MemberFilter']['value'];
                    try {
                        $filterMember = DW_Model_Member::loadByRefAndAxis($filterMemberRef, $filterAxis);
                        $filter->addMember($filterMember);
                    } catch (Core_Exception_NotFound $e) {
                        $errors['selectAxis'.$filterAxisRef.'MemberFilter'] = __('DW', 'configValidation', 'filterMemberInvalid');
                    }
                }

                $report->addFilter($filter);
            }
        }

        if (empty($errors)) {
            $this->setReportByHash($this->getParam('hashReport'), $report);
            $this->sendJsonResponse(
                array(
                    'message' => __('DW', 'report', 'reportConfigurationParsed'),
                    'type'    => 'success'
                )
            );
        } else {
            $this->getResponse()->setHttpResponseCode(400);
            $this->entityManager->clear();
            $this->sendJsonResponse(
                array(
                    'errorMessages' => $errors,
                    'message'       => __('DW', 'report', 'invalidConfig'),
                    'type'          => 'warning'
                )
            );
        }
    }

    /**
     * Sauvegarde du report.
     * @Secure("editReport")
     */
    public function saveAction()
    {
        $report = $this->getReportByHash($this->getParam('hashReport'));

        $savePost = json_decode($this->getParam('saveReportAs'), JSON_OBJECT_AS_ARRAY);
        $reportLabel = $savePost['saveLabelReport']['value'];
        if (empty($reportLabel)) {
            $this->entityManager->clear();
            $this->getResponse()->setHttpResponseCode(400);
            $this->sendJsonResponse(
                array(
                    'errorMessages' => array('saveLabelReport' => __('DW', 'report', 'reportLabelInvalid')),
                    'message'       => '',
                    'type'          => 'warning'
                )
            );
        } else {
            if (($savePost['isNew']['value'] != '1')
                && (isset($savePost['saveType']))
                && ($savePost['saveType']['value'] == 'saveAs')
            ) {
                $clonedReport = clone $report;
                $this->entityManager->refresh($report);
                $report = $clonedReport;
            }

            $report->setLabel($reportLabel);
            $report->save();
            $this->entityManager->flush($report);

            $this->sendJsonResponse(
                [
                    'message'  => __('UI', 'message', 'updated'),
                    'type'     => 'success',
                    'idReport' => $report->getId()
                ]
            );

            $this->entityManager->clear();
        }

    }

    /**
     * Vues des valeurs d'un Report.
     * @Secure("viewReport")
     */
    public function valuesAction()
    {
        $report = $this->getReportByHash($this->getParam('hashReport'));
        $this->view->idCube = $this->getParam('idCube');
        $this->view->hashReport = $this->getParam('hashReport');
        $this->view->numeratorAxis1 = $report->getNumeratorAxis1();
        $this->view->numeratorAxis2 = $report->getNumeratorAxis2();
        $this->view->valueUnit = $report->getValuesUnitSymbol();
        $this->_helper->layout()->disableLayout();

        $this->entityManager->clear();
    }

    /**
     * Vues du graphiques d'un Report.
     * @Secure("viewReport")
     */
    public function graphAction()
    {
        $report = $this->getReportByHash($this->getParam('hashReport'));

        $this->view->chart = $report->getChart();
        $this->view->valueUnit = $report->getValuesUnitSymbol();
        $this->_helper->layout()->disableLayout();

        $this->entityManager->clear();
    }

    /**
     * Stream l'export excel d'un report.
     * @Secure("viewReport")
     */
    public function excelAction()
    {
        $report = $this->getReportByHash($this->getParam('hashReport'));

        $export = new DW_Export_Report_Excel($report);

        $this->entityManager->clear();

        $export->display();
    }

    /**
     * Stream l'export pdf d'un report.
     * @Secure("viewReport")
     */
    public function pdfAction()
    {
        $report = $this->getReportByHash($this->getParam('hashReport'));

        $export = new DW_Export_Report_Pdf($report);

        $this->entityManager->clear();

        $export->display();
    }

    /**
     * Génère l'image pour l'export de l'analyse
     * @Secure("public")
     */
    public function saveimagedataAction()
    {
        $this->sendJsonResponse(
            file_put_contents(
                PACKAGE_PATH.'/public/temp/'.$this->getParam('name').'.png',
                base64_decode(explode(',', $this->getParam('image'))[1])
            )
        );
    }
}