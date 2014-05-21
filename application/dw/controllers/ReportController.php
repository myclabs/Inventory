<?php
/**
 * @author valentin.claras
 * @author cyril.perraud
 * @package    DW
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use User\Domain\User;

/**
 * Classe du controler de Data Warehouse
 * @package DW
 */
class DW_ReportController extends Core_Controller
{
    /**
     * @Inject("session.storage.name")
     */
    private $sessionStorageName;

    /**
     * Récupère un report enregistré en session par son hash.
     *
     * @param string $hash
     *
     * @return DW_Model_Report
     */
    protected function getReportByHash($hash)
    {
        $zendSessionReport = new Zend_Session_Namespace($this->sessionStorageName);

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
        $zendSessionReport = new Zend_Session_Namespace($this->sessionStorageName);

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
                $this->translator->set($report->getLabel(), __('DW', 'report', 'newReportDefaultLabelPage'));
            }
        }
        if ($report->getKey() != array()) {
            $this->view->assign('isNew', false);
        } else {
            $this->view->assign('isNew', true);
        }
        $hash = ($this->hasParam('hashReport')) ? $this->getParam('hashReport') : (string) spl_object_hash($report);

        $this->view->assign('idCube', $report->getCube()->getId());
        $this->view->assign('hashReport', $hash);
        $this->view->assign('reportLabel', $this->translator->get($report->getLabel()));
        require_once (dirname(__FILE__).'/../forms/Configuration.php');
        $this->view->assign('configurationForm', new DW_Form_configuration($report, $hash, $this->translator));

        if ($this->hasParam('viewConfiguration')) {
            $this->view->assign('viewConfiguration', $this->getParam('viewConfiguration'));
        } else {
            $viewConfiguration = new DW_ViewConfiguration();
            $viewConfiguration->setOutputUrl('index/report/idCube/'.$report->getCube()->getId());
            $viewConfiguration->setSaveURL('dw/report/details');
            $this->view->assign('viewConfiguration', $viewConfiguration);
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

        $errors = [];

        // Options de configuration.
        if ($this->getParam('typeSumRatioChoice') === 'ratio') {
            $numeratorIndicatorRef = $this->getParam('numeratorIndicator');
            try {
                $numeratorIndicator = DW_Model_Indicator::loadByRefAndCube($numeratorIndicatorRef, $report->getCube());
            } catch (Core_Exception_NotFound $e) {
                $errors['numeratorIndicator'] = __('DW', 'configValidation', 'numeratorIsRequired');
            }
            $report->setNumerator($numeratorIndicator);

            $denominatorIndicatorRef = $this->getParam('denominatorIndicator');
            try {
                $denominatorIndicator = DW_Model_Indicator::loadByRefAndCube($denominatorIndicatorRef, $report->getCube());
            } catch (Core_Exception_NotFound $e) {
                $errors['denominatorIndicator'] = __('DW', 'configValidation', 'denominatorIsRequired');
            }
            $report->setDenominator($denominatorIndicator);

            $report->setNumeratorAxis1(null);
            $report->setNumeratorAxis2(null);
            $numeratorAxisOneRef = $this->getParam('ratioNumeratorAxisOne');
            try {
                $numeratorAxisOne = DW_Model_Axis::loadByRefAndCube($numeratorAxisOneRef, $report->getCube());
                $report->setNumeratorAxis1($numeratorAxisOne);
            } catch (Core_Exception_NotFound $e) {
                $errors['ratioNumeratorAxisOne'] = __('DW', 'configValidation', 'numeratorAxisOneInvalid');
            }
            if ($this->getParam('ratioAxisNumberChoice') === 'two') {
                $numeratorAxisTwoRef = $this->getParam('ratioNumeratorAxisTwo');
                if ($numeratorAxisTwoRef === $numeratorAxisOneRef) {
                    $errors['ratioNumeratorAxisTwo'] = __('DW', 'configValidation', 'axisTwoSameAsOne');
                } else {
                    try {
                        $numeratorAxisTwo = DW_Model_Axis::loadByRefAndCube($numeratorAxisTwoRef, $report->getCube());
                        if (!$numeratorAxisTwo->isTransverseWith($numeratorAxisOne)) {
                            $errors['ratioNumeratorAxisTwo'] = __('DW', 'configValidation', 'axisTwoLinkedToOne');
                        } else {
                            $report->setNumeratorAxis2($numeratorAxisTwo);
                        }
                    } catch (Core_Exception_NotFound $e) {
                        $errors['ratioNumeratorAxisTwo'] = __('DW', 'configValidation', 'numeratorAxisTwoInvalid');
                    }
                }
            }

            $report->setDenominatorAxis1(null);
            $report->setDenominatorAxis2(null);
            $denominatorAxisOneRef = $this->getParam('ratioDenominatorAxisOne');
            try {
                $denominatorAxisOne = DW_Model_Axis::loadByRefAndCube($denominatorAxisOneRef, $report->getCube());
                $report->setDenominatorAxis1($denominatorAxisOne);
            } catch (Core_Exception_NotFound $e) {
                // Possibilité de ne pas avoir d'axe au dénominateur.
            }
            if ($this->getParam('ratioAxisNumberChoice') === 'two') {
                $denominatorAxisTwoRef = $this->getParam('ratioDenominatorAxisTwo');
                if (($denominatorAxisTwoRef != null) && ($denominatorAxisTwoRef === $denominatorAxisOneRef)) {
                    $errors['ratioDenominatorAxisTwo'] = __('DW', 'configValidation', 'axisTwoSameAsOne');
                } else {
                    try {
                        $denominatorAxisTwo = DW_Model_Axis::loadByRefAndCube($denominatorAxisTwoRef, $report->getCube());
                        if (isset($denominatorAxisOne) && (!$denominatorAxisTwo->isTransverseWith($denominatorAxisOne))) {
                            $errors['ratioDenominatorAxisTwo'] = __('DW', 'configValidation', 'axisTwoLinkedToOne');
                        } else {
                            $report->setDenominatorAxis2($denominatorAxisTwo);
                        }
                    } catch (Core_Exception_NotFound $e) {
                        // Possibilité de ne pas avoir d'axe au dénominateur.
                    }
                }
            }
        } else if ($this->getParam('typeSumRatioChoice') === 'sum') {
            // Suppression des anciens dénominateurs.
            $report->setDenominator(null);
            $report->setDenominatorAxis1(null);
            $report->setDenominatorAxis2(null);

            $indicatorRef = $this->getParam('numeratorIndicator');
            try {
                $indicator = DW_Model_Indicator::loadByRefAndCube($indicatorRef, $report->getCube());
            } catch (Core_Exception_NotFound $e) {
                $errors['numeratorIndicator'] = __('DW', 'configValidation', 'indicatorIsRequired');
            }
            $report->setNumerator($indicator);

            $report->setNumeratorAxis1(null);
            $report->setNumeratorAxis2(null);
            $sumAxisOneRef = $this->getParam('sumAxisOne');
            try {
                $sumAxisOne = DW_Model_Axis::loadByRefAndCube($sumAxisOneRef, $report->getCube());
                $report->setNumeratorAxis1($sumAxisOne);
            } catch (Core_Exception_NotFound $e) {
                $errors['sumAxisOne'] = __('DW', 'configValidation', 'indicatorAxisOneInvalid');
            }
            if ($this->getParam('sumAxisNumberChoice') === 'two') {
                $sumAxisTwoRef = $this->getParam('sumAxisTwo');
                if ($sumAxisTwoRef === $sumAxisOneRef) {
                    $errors['sumAxisTwo'] = __('DW', 'configValidation', 'axisTwoSameAsOne');
                } else {
                    try {
                        $sumAxisTwo = DW_Model_Axis::loadByRefAndCube($sumAxisTwoRef, $report->getCube());
                        if (!$sumAxisTwo->isTransverseWith($sumAxisOne)) {
                            $errors['sumAxisTwo'] = __('DW', 'configValidation', 'axisTwoLinkedToOne');
                        } else {
                            $report->setNumeratorAxis2($sumAxisTwo);
                        }
                    } catch (Core_Exception_NotFound $e) {
                        $errors['sumAxisTwo'] = __('DW', 'configValidation', 'indicatorAxisTwoInvalid');
                    }
                }
            }
        } else {
            $errors['typeSumRatioChoice'] = __('DW', 'configValidation', 'reportTypeMandatory');
        }

        // Options d'affichage.
        try {
            $report->setChartType($this->getParam('displayType'));
        } catch (Core_Exception_InvalidArgument $e) {
            $errors['displayType'] = __('DW', 'configValidation', 'chartTypeInvalid');
        }
        $acceptedSortType = array(
            DW_Model_Report::SORT_VALUE_INCREASING,
            DW_Model_Report::SORT_VALUE_DECREASING,
            DW_Model_Report::SORT_CONVENTIONAL,
        );
        if (in_array($this->getParam('resultsOrder'), $acceptedSortType)) {
            $report->setSortType($this->getParam('resultsOrder'));
        }
        if ($this->getParam('uncertaintyChoice') == 'withUncertainty') {
            $report->setWithUncertainty(true);
        } else {
            $report->setWithUncertainty(false);
        }

        // Filtres.
        foreach ($report->getFilters() as $oldFilter) {
            $report->removeFilter($oldFilter);
        }
        foreach ($report->getCube()->getAxes() as $axis) {
            if ($this->getParam($axis->getRef().'_memberNumberChoice') !== 'all') {
                $filter = new DW_Model_Filter($report, $axis);

                if ($this->getParam($axis->getRef().'_memberNumberChoice') === 'several') {
                    foreach ($this->getParam($axis->getRef().'_members') as $filterMemberRef) {
                        try {
                            $filterMember = DW_Model_Member::loadByRefAndAxis($filterMemberRef, $axis);
                            $filter->addMember($filterMember);
                        } catch (Core_Exception_NotFound $e) {
                            $errors[$axis->getRef().'_members'] = __('DW', 'configValidation', 'filterMemberInvalid');
                        }
                    }
                } else if ($this->getParam($axis->getRef().'_memberNumberChoice') === 'one') {

                    $filterMemberRef = $this->getParam($axis->getRef().'_members');
                    try {
                        $filterMember = DW_Model_Member::loadByRefAndAxis(reset($filterMemberRef), $axis);
                        $filter->addMember($filterMember);
                    } catch (Core_Exception_NotFound $e) {
                        $errors[$axis->getRef().'_members'] = __('DW', 'configValidation', 'filterMemberInvalid');
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

        $reportLabel = $this->getParam('reportLabel');
        if (empty($reportLabel)) {
            $this->entityManager->clear();
            $this->getResponse()->setHttpResponseCode(400);
            $this->sendJsonResponse(
                [
                    'errorMessages' => ['reportLabel' => __('DW', 'report', 'reportLabelInvalid')],
                    'message'       => '',
                    'type'          => 'warning'
                ]
            );
        } else {
            if (($this->getParam('isNew') != '1')
                && ($this->hasParam('saveType'))
                && ($this->getParam('saveType') == 'saveAs')
            ) {
                $clonedReport = clone $report;
                $this->entityManager->refresh($report);
                $report = $clonedReport;
            }

            $this->translator->set($report->getLabel(), $reportLabel);
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
        $this->view->assign('idCube', $this->getParam('idCube'));
        $this->view->assign('hashReport', $this->getParam('hashReport'));
        $this->view->assign('numeratorAxis1', $report->getNumeratorAxis1());
        $this->view->assign('numeratorAxis2', $report->getNumeratorAxis2());
        $this->view->assign('valueUnit', $this->translator->get($report->getValuesUnitSymbol()));
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

        $this->view->assign('chart', $report->getChart());
        $this->view->assign('valueUnit', $this->translator->get($report->getValuesUnitSymbol()));
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

        $export = new DW_Export_Report_Excel($report, $this->translator);

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

        $export = new DW_Export_Report_Pdf($report, $this->translator);

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
