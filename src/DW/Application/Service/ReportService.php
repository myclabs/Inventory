<?php
/**
 * @author valentin.claras
 */

namespace DW\Application\Service;

use Core\Translation\TranslatedString;
use Core_Exception_InvalidArgument;
use DW\Domain\Cube;
use DW\Domain\Member;
use DW\Domain\Report;
use DW\Domain\Filter;
use Mnapoli\Translated\Translator;
use stdClass;
use UI_Chart_Bar;
use UI_Chart_Pie;
use UI_Chart_Serie;

/**
 * @author valentin.claras
 */
class ReportService
{
    /**
     * @var Translator
     */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }


    /**
     * @param Report $report
     * @param string $idChart
     * @return UI_Chart_Bar|UI_Chart_Pie
     * @throws Core_Exception_InvalidArgument
     */
    public function getChartReport(Report $report, $idChart = 'reportChart')
    {
        if ($report->getNumeratorAxis1() === null) {
            throw new Core_Exception_InvalidArgument('At least one numerator axis is needed to drow a chart');
        }

        $chartType = $report->getChartType();
        if ($chartType === Report::CHART_PIE) {
            $chart = new UI_Chart_Pie($idChart);
            $chart->addAttribute('chartArea', '{width:"85%", height:"85%"}');

            foreach ($report->getValues() as $value) {
                $serie = new UI_Chart_Serie($this->translator->get($value['members'][0]->getLabel()));
                $serie->values[] = $value['value'];
                $chart->addSerie($serie);
            }
        } else {
            $chart = new UI_Chart_Bar($idChart);
            if ($report->getWithUncertainty() === true) {
                $chart->displayUncertainty = true;
            }

            if (($chartType === Report::CHART_HORIZONTAL_STACKED)
                || ($chartType === Report::CHART_VERTICAL_STACKED)
                || ($chartType === Report::CHART_HORIZONTAL_STACKEDGROUPED)
                || ($chartType === Report::CHART_VERTICAL_STACKEDGROUPED)
            ) {
                $chart->stacked = true;
            }
            if (($chartType === Report::CHART_HORIZONTAL)
                || ($chartType === Report::CHART_HORIZONTAL_GROUPED)
                || ($chartType === Report::CHART_HORIZONTAL_STACKED)
                || ($chartType === Report::CHART_HORIZONTAL_STACKEDGROUPED)
            ) {
                $chart->vertical = false;
                $chart->addAttribute('chartArea', '{top:"5%", left:"25%", width:"50%", height:"75%"}');
                $chart->addAttribute(
                    'hAxis',
                    '{title: \'' .
                    $this->translator->get($report->getValuesUnitSymbol()) . '\',  titleTextStyle: {color: \'#9E0000\'}}'
                );
            } else {
                $chart->addAttribute('chartArea', '{top:"5%", left:"15%", width:"50%", height:"65%"}');
                $chart->addAttribute(
                    'vAxis',
                    '{title: \'' .
                    $this->translator->get($report->getValuesUnitSymbol()) . '\',  titleTextStyle: {color: \'#9E0000\'}}'
                );
            }

            if ($report->getNumeratorAxis2() === null) {
                $chart->displaySeriesLabels = false;

                $serieAxis = new UI_Chart_Serie('axis');
                $serieAxis->type = 'string';
                $serieValues = new UI_Chart_Serie('');
                $numberValues = 0;
                foreach ($report->getValues() as $value) {
                    $serieAxis->values[] = $this->translator->get($value['members'][0]->getLabel());
                    $serieValues->values[] = $value['value'];
                    $serieValues->uncertainties[] = $value['uncertainty'];
                    $numberValues++;
                    if ($numberValues >= 15) {
                        break;
                    }
                }
                if ($numberValues > 8) {
                    $chart->slantedTextAngle = 90;
                }
                $chart->addSerie($serieAxis);
                $chart->addSerie($serieValues);
            } else {
                $numeratorAxis1MembersUsed = array();
                $numeratorAxis2MembersUsed = array();

                $serieAxis = new UI_Chart_Serie('axis');
                $serieAxis->type = 'string';
                $chart->addSerie($serieAxis);

                $seriesAxisLabel = array();
                $seriesValues = array();
                foreach ($report->getValues() as $value) {
                    $numeratorAxis1MembersUsed[$value['members'][0]->getId()] = $value['members'][0];
                    $numeratorAxis2MembersUsed[$value['members'][1]->getId()] = $value['members'][1];

                    $seriesAxisLabel[$value['members'][0]->getId()] = $this->translator->get(
                        $value['members'][0]->getLabel()
                    );

                    $serieValueId = 'serieValue' . $value['members'][1]->getId();
                    if (!isset($seriesValues[$serieValueId])) {
                        $seriesValues[$serieValueId] = array();
                    }
                    $seriesValues[$serieValueId][$value['members'][0]->getId()] = $value;
                }

                // Complément des paires de membres sans résultats et tris des valeurs.
                foreach ($numeratorAxis2MembersUsed as $member2Id => $member2) {
                    $serieValueId = 'serieValue' . $member2Id;

                    foreach ($numeratorAxis1MembersUsed as $member1Id => $member1) {
                        if (!(isset($seriesValues[$serieValueId][$member1Id]))) {
                            $seriesValues[$serieValueId][$member1Id] = array('value' => 0, 'uncertainty' => 0);
                        }
                    }
                    uksort(
                        $seriesValues[$serieValueId],
                        function ($a, $b) {
                            $memberA = Member::load($a);
                            $memberB = Member::load($b);
                            return $memberA->getPosition() - $memberB->getPosition();
                        }
                    );
                }
                uksort(
                    $seriesAxisLabel,
                    function ($a, $b) {
                        $memberA = Member::load($a);
                        $memberB = Member::load($b);
                        return $memberA->getPosition() - $memberB->getPosition();
                    }
                );

                // Ajout des séries et limitation de l'affichage.
                $numberAxes = 0;
                foreach ($seriesAxisLabel as $member1Id => $axisLabel) {
                    $serieAxis->values[$member1Id] = $axisLabel;

                    $numberAxes++;
                    if ($numberAxes >= 15) {
                        break;
                    }
                }
                if ($numberAxes > 8) {
                    $chart->slantedTextAngle = 90;
                }

                foreach ($seriesValues as $serieValueId => $values) {
                    $serie = new UI_Chart_Serie(
                        $this->translator->get(
                            $numeratorAxis2MembersUsed[explode('serieValue', $serieValueId)[1]]->getLabel()
                        )
                    );
                    $numberValues = 0;
                    foreach ($values as $memberIndex => $value) {
                        $serie->values[$memberIndex] = $value['value'];
                        $serie->uncertainties[$memberIndex] = $value['uncertainty'];

                        $numberValues++;
                        if ($numberValues >= 15) {
                            break;
                        }
                    }
                    $chart->addSerie($serie);
                }
            }
        }

        return $chart;
    }

    /**
     * @param Report $report
     * @return string
     */
    public function getReportAsJson(Report $report)
    {
        return $this->encodeStdReportAsJson($this->getReportAsStdReport($report));
    }

    /**
     * @param Report $report
     * @return stdClass
     */
    public function getReportAsStdReport(Report $report)
    {
        $stdReport = new stdClass();

        $stdReport->id = $report->getId();

        // Cube.
        if ($report->getCube() !== null) {
            $stdReport->idCube = $report->getCube()->getId();
        } else {
            $stdReport->idCube = null;
        }

        // Label.
        $stdReport->label = new StdClass();
        foreach ($report->getLabel()->getAll() as $language => $translatedLabel) {
            $stdReport->label->$language = $translatedLabel;
        }

        // Numerator Indicator.
        if ($report->getNumeratorIndicator() !== null) {
            $stdReport->refNumeratorIndicator = $report->getNumeratorIndicator()->getRef();
        } else {
            $stdReport->refNumeratorIndicator = null;
        }

        // Numerator Axes.
        if ($report->getNumeratorAxis1() != null) {
            $stdReport->refNumeratorAxis1 = $report->getNumeratorAxis1()->getRef();
        } else {
            $stdReport->refNumeratorAxis1 = null;
        }
        if ($report->getNumeratorAxis2() != null) {
            $stdReport->refNumeratorAxis2 = $report->getNumeratorAxis2()->getRef();
        } else {
            $stdReport->refNumeratorAxis2 = null;
        }

        // Denominator Axes.
        if ($report->getDenominatorIndicator() !== null) {
            $stdReport->refDenominatorIndicator = $report->getDenominatorIndicator()->getRef();
        } else {
            $stdReport->refDenominatorIndicator = null;
        }

        // Denominator Axes.
        if ($report->getDenominatorAxis1() != null) {
            $stdReport->refDenominatorAxis1 = $report->getDenominatorAxis1()->getRef();
        } else {
            $stdReport->refDenominatorAxis1 = null;
        }
        if ($report->getDenominatorAxis2() != null) {
            $stdReport->refDenominatorAxis2 = $report->getDenominatorAxis2()->getRef();
        } else {
            $stdReport->refDenominatorAxis2 = null;
        }

        // Attributes.
        $stdReport->chartType = $report->getChartType();
        $stdReport->sortType = $report->getSortType();
        $stdReport->withUncertainty = $report->getWithUncertainty();

        // Filters.
        $stdReport->filters = [];
        foreach ($report->getFilters() as $filter) {
            $stdFilter = new stdClass();
            $stdFilter->refAxis = $filter->getAxis()->getRef();
            $stdFilter->refMembers = [];
            foreach ($filter->getMembers() as $filterMember) {
                $stdFilter->refMembers[] = $filterMember->getRef();
            }
            $stdReport->filters[] = $stdFilter;
        }

        return $stdReport;
    }

    /**
     * @param stdClass $stdReport
     * @return string
     */
    public function encodeStdReportAsJson(stdClass $stdReport)
    {
        return json_encode($stdReport);
    }

    /**
     * @param string $jsonReport
     * @return stdClass
     */
    public function decodeJsonAsStdReport($jsonReport)
    {
        return json_decode($jsonReport);
    }

    /**
     * @param stdClass $stdReport
     * @param Cube $cube
     * @return Report
     */
    public function getReportFromStdReport(stdClass $stdReport, Cube $cube = null)
    {
        if ($stdReport->id !== null) {
            $report = Report::load($stdReport->id);
        } else {
            if ($stdReport->idCube != null) {
                $cube = Cube::load($stdReport->idCube);
            }
            $report = new Report($cube);
        }

        // Label.
        $report->setLabel(TranslatedString::fromArray((array) $stdReport->label));

        // Numerator Indicator.
        if ($stdReport->refNumeratorIndicator !== null) {
            $report->setNumeratorIndicator(
                $report->getCube()->getIndicatorByRef($stdReport->refNumeratorIndicator)
            );
        } else {
            $report->setNumeratorIndicator();
        }

        // Numerator axes.
        if ($stdReport->refNumeratorAxis1 != null) {
            $report->setNumeratorAxis1(
                $report->getCube()->getAxisByRef($stdReport->refNumeratorAxis1)
            );
        } else {
            $report->setNumeratorAxis1();
        }
        if ($stdReport->refNumeratorAxis2 != null) {
            $report->setNumeratorAxis2(
                $report->getCube()->getAxisByRef($stdReport->refNumeratorAxis2)
            );
        } else {
            $report->setNumeratorAxis2();
        }

        // Denominator Indicator.
        if ($stdReport->refDenominatorIndicator !== null) {
            $report->setDenominatorIndicator(
                $report->getCube()->getIndicatorByRef($stdReport->refDenominatorIndicator, $report->getCube())
            );
        } else {
            $report->setDenominatorIndicator();
        }

        // Denominator Axes.
        if ($stdReport->refDenominatorAxis1 != null) {
            $report->setDenominatorAxis1(
                $report->getCube()->getAxisByRef($stdReport->refDenominatorAxis1)
            );
        } else {
            $report->setDenominatorAxis1();
        }
        if ($stdReport->refDenominatorAxis2 != null) {
            $report->setDenominatorAxis2(
                $report->getCube()->getAxisByRef($stdReport->refDenominatorAxis2)
            );
        } else {
            $report->setDenominatorAxis2();
        }

        // Attributes.
        if ($stdReport->chartType !== null) {
            $report->setChartType($stdReport->chartType);
        }
        if ($stdReport->sortType !== null) {
            $report->setSortType($stdReport->sortType);
        }
        $report->setWithUncertainty($stdReport->withUncertainty);

        // Filters.
        foreach ($report->getFilters() as $reportFilter) {
            $report->removeFilter($reportFilter);
        }
        foreach ($stdReport->filters as $stdFilter) {
            $axis = $report->getCube()->getAxisByRef($stdFilter->refAxis);
            $filter = new Filter($report, $axis);
            foreach ($stdFilter->refMembers as $filterRefMember) {
                $filter->addMember(
                    $filter->getAxis()->getMemberByRef($filterRefMember)
                );
            }
        }

        return $report;
    }

    /**
     * @param string $jsonReport
     * @return Report
     */
    public function getReportFromJson($jsonReport)
    {
        return $this->getReportFromStdReport($this->decodeJsonAsStdReport($jsonReport));
    }

    /**
     * @param Report $report
     * @return Report
     */
    public function duplicateReport(Report $report)
    {
        $stdReport = $this->getReportAsStdReport($report);
        $stdReport->id = null;
        return $this->getReportFromStdReport($stdReport);
    }

    /**
     * @param Report $report
     * @param Cube   $cube
     * @return Report
     */
    public function copyReportToCube(Report $report, Cube $cube)
    {
        $stdReport = $this->getReportAsStdReport($report);
        $stdReport->id = null;
        $stdReport->idCube = null;
        return $this->getReportFromStdReport(
            $this->decodeJsonAsStdReport($this->encodeStdReportAsJson($stdReport)),
            $cube
        );
    }

    /**
     * @param Report $report
     * @param Report $sourceReport
     * @return Report
     */
    public function updateReportFromAnother(Report $report, Report $sourceReport)
    {
        $stdReport = $this->getReportAsStdReport($sourceReport);
        $stdReport->id = $report->getId();
        $stdReport->idCube = $report->getCube()->getId();
        return $this->getReportFromStdReport(
            $this->decodeJsonAsStdReport($this->encodeStdReportAsJson($stdReport)),
            $report->getCube()
        );
    }

}
