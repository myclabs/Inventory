<?php
/**
 * @author valentin.claras
 * @author cyril.perraud
 * @package    DW
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use DW\Application\DWFormConfiguration;
use DW\Application\Service\Export\ExcelReport;
use DW\Application\Service\Export\PdfReport;
use DW\Application\Service\ReportService;
use DW\Application\DWViewConfiguration;
use DW\Domain\Cube;
use DW\Domain\Filter;
use DW\Domain\Report;

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
     * @Inject("DW\Application\Service\ReportService")
     * @var ReportService
     */
    private $reportService;

    /**
     * Récupère un report enregistré en session par son hash.
     *
     * @param string $hash
     * @param Report $report
     */
    protected function setReportInSession($hash, $report)
    {
        $zendSessionReport = new Zend_Session_Namespace($this->sessionStorageName);

        $this->entityManager->clear();

        $zendSessionReport->$hash = $this->reportService->getReportAsJson($report);
    }

    /**
     * Récupère un report enregistré en session par son hash.
     *
     * @param string $hash
     *
     * @return Report
     */
    protected function getReportFromSession($hash)
    {
        $zendSessionReport = new Zend_Session_Namespace($this->sessionStorageName);

        return $this->reportService->getReportFromJson($zendSessionReport->$hash);
    }

    /**
     * Détails d'un Report.
     * @Secure("viewReport")
     */
    public function detailsAction()
    {
        $report = null;
        if ($this->hasParam('hashReport')) {
            $report = $this->getReportFromSession($this->getParam('hashReport'));
        }
        if (!isset($report) || !($report instanceof Report)) {
            if ($this->hasParam('idReport')) {
                $report = Report::load($this->getParam('idReport'));
            } else {
                $report = new Report(Cube::load($this->getParam('idCube')));
                $this->translator->set($report->getLabel(), __('DW', 'report', 'newReportDefaultLabelPage'));
            }
        }
        if ($report->getKey() != []) {
            $this->view->assign('isNew', false);
        } else {
            $this->view->assign('isNew', true);
        }
        $hash = ($this->hasParam('hashReport')) ? $this->getParam('hashReport') : (string) spl_object_hash($report);

        $this->view->assign('idCube', $report->getCube()->getId());
        $this->view->assign('hashReport', $hash);
        $this->view->assign('reportLabel', $this->translator->get($report->getLabel()));
        $this->view->assign('configurationForm', new DWFormconfiguration($report, $hash, $this->translator));

        if ($this->hasParam('viewConfiguration')) {
            $this->view->assign('viewConfiguration', $this->getParam('viewConfiguration'));
        } else {
            $viewConfiguration = new DWViewConfiguration();
            $viewConfiguration->setOutputUrl('index/report/idCube/'.$report->getCube()->getId());
            $viewConfiguration->setSaveURL('dw/report/details');
            $this->view->assign('viewConfiguration', $viewConfiguration);
        }

        $this->setReportInSession($hash, $report);
    }

    /**
     * Applique la nouvelle configuration d'un formulaire.
     * @Secure("viewReport")
     */
    public function applyconfigurationAction()
    {
        $report = $this->getReportFromSession($this->getParam('hashReport'));

        $errors = [];

        // Options de configuration.
        if ($this->getParam('typeSumRatioChoice') === 'ratio') {
            $numeratorIndicatorRef = $this->getParam('numeratorIndicator');
            try {
                $numeratorIndicator = $report->getCube()->getIndicatorByRef($numeratorIndicatorRef);
                $report->setNumeratorIndicator($numeratorIndicator);
            } catch (Core_Exception_NotFound $e) {
                $errors['numeratorIndicator'] = __('DW', 'configValidation', 'numeratorIsRequired');
            }

            $denominatorIndicatorRef = $this->getParam('denominatorIndicator');
            try {
                $denominatorIndicator = $report->getCube()->getIndicatorByRef($denominatorIndicatorRef);
                $report->setDenominatorIndicator($denominatorIndicator);
            } catch (Core_Exception_NotFound $e) {
                $errors['denominatorIndicator'] = __('DW', 'configValidation', 'denominatorIsRequired');
            }

            $report->setNumeratorAxis1(null);
            $report->setNumeratorAxis2(null);
            $numeratorAxisOneRef = $this->getParam('ratioNumeratorAxisOne');
            try {
                $numeratorAxisOne = $report->getCube()->getAxisByRef($numeratorAxisOneRef);
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
                        $numeratorAxisTwo = $report->getCube()->getAxisByRef($numeratorAxisTwoRef);
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
                $denominatorAxisOne = $report->getCube()->getAxisByRef($denominatorAxisOneRef);
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
                        $denominatorAxisTwo = $report->getCube()->getAxisByRef($denominatorAxisTwoRef);
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
            $report->setDenominatorIndicator(null);
            $report->setDenominatorAxis1(null);
            $report->setDenominatorAxis2(null);

            $indicatorRef = $this->getParam('numeratorIndicator');
            try {
                $indicator = $report->getCube()->getIndicatorByRef($indicatorRef);
                $report->setNumeratorIndicator($indicator);
            } catch (Core_Exception_NotFound $e) {
                $errors['numeratorIndicator'] = __('DW', 'configValidation', 'indicatorIsRequired');
            }

            $report->setNumeratorAxis1(null);
            $report->setNumeratorAxis2(null);
            $sumAxisOneRef = $this->getParam('sumAxisOne');
            try {
                $sumAxisOne = $report->getCube()->getAxisByRef($sumAxisOneRef);
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
                        $sumAxisTwo = $report->getCube()->getAxisByRef($sumAxisTwoRef);
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
            Report::SORT_VALUE_INCREASING,
            Report::SORT_VALUE_DECREASING,
            Report::SORT_CONVENTIONAL,
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
                $filter = new Filter($report, $axis);

                if ($this->getParam($axis->getRef().'_memberNumberChoice') === 'several') {
                    foreach ($this->getParam($axis->getRef().'_members') as $filterMemberRef) {
                        try {
                            $filterMember = $axis->getMemberByRef($filterMemberRef);
                            $filter->addMember($filterMember);
                        } catch (Core_Exception_NotFound $e) {
                            $errors[$axis->getRef().'_members'] = __('DW', 'configValidation', 'filterMemberInvalid');
                        }
                    }
                } else if ($this->getParam($axis->getRef().'_memberNumberChoice') === 'one') {
                    $filterMemberRef = $this->getParam($axis->getRef().'_members');
                    try {
                        $filterMember = $axis->getMemberByRef(reset($filterMemberRef));
                        $filter->addMember($filterMember);
                    } catch (Core_Exception_NotFound $e) {
                        $errors[$axis->getRef().'_members'] = __('DW', 'configValidation', 'filterMemberInvalid');
                    }
                }
            }
        }

        if (empty($errors)) {
            $this->setReportInSession($this->getParam('hashReport'), $report);
            $this->sendJsonResponse(
                [
                    'message' => __('DW', 'report', 'reportConfigurationParsed'),
                    'type'    => 'success'
                ]
            );
        } else {
            $this->getResponse()->setHttpResponseCode(400);
            $this->entityManager->clear();
            $this->sendJsonResponse(
                [
                    'errorMessages' => $errors,
                    'message'       => __('DW', 'report', 'invalidConfig'),
                    'type'          => 'warning'
                ]
            );
        }
    }

    /**
     * Sauvegarde du report.
     * @Secure("editReport")
     */
    public function saveAction()
    {
        $report = $this->getReportFromSession($this->getParam('hashReport'));

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
                && ($this->hasParam('saveReportType'))
                && ($this->getParam('saveReportType') == 'duplicate')
            ) {
                $this->entityManager->clear();
                $newReport = $this->reportService->duplicateReport($report);
                $report = $newReport;
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
        $report = $this->getReportFromSession($this->getParam('hashReport'));
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
        $report = $this->getReportFromSession($this->getParam('hashReport'));

        $this->view->assign('chart', $this->reportService->getChartReport($report));
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
        $report = $this->getReportFromSession($this->getParam('hashReport'));

        $export = new ExcelReport($report, $this->translator);

        $this->entityManager->clear();

        $export->display();
    }

    /**
     * Stream l'export pdf d'un report.
     * @Secure("viewReport")
     */
    public function pdfAction()
    {
        $report = $this->getReportFromSession($this->getParam('hashReport'));

        $export = new PdfReport($report, $this->translator);

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
