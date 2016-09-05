<?php
/**
 * @author valentin.claras
 */

namespace DW\Application\Service\Export;

use Core_Locale;
use Core_Tools;
use DOMDocument;
use DOMNode;
use DOMText;
use DW\Application\Service\ReportService;
use DW\Domain\Cube;
use DW\Domain\Filter;
use DW\Domain\Report;
use Exception;
use Export_Pdf;
use Mnapoli\Translated\Translator;
use Orga\Domain\Axis;
use Orga\Domain\Cell;
use Orga\Domain\Granularity;
use UI_Chart_Generic;
use Zend_Controller_Action_HelperBroker;

/**
 * @package    DW
 * @subpackage Export
 */
class PdfSpecific extends Export_Pdf
{
    /**
     * @var ReportService
     */
    private $reportService;
    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var Report[]
     */
    private $reports = [];


    /**
     * @param string $xmlPath
     * @param Cell $cell
     * @param Cube $cube
     * @param ReportService $reportService
     * @param Translator $translator
     * @param string|null $exportUrl
     * @throws \Core_Exception_InvalidArgument
     */
    public function __construct(
        $xmlPath,
        Cell $cell,
        Cube $cube,
        ReportService $reportService,
        Translator $translator,
        $exportUrl = null
    ) {
        $this->reportService = $reportService;
        $this->translator = $translator;

        $isPreview = ($exportUrl !== null);

        //    Ajout du html
        $this->html = '<html>';
        $this->html .= '<head>';
        $this->html .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
        $this->html .= '</head>';

        $xmlDocument = new DOMDocument();
        libxml_use_internal_errors(true);
        try {
            $xmlDocument->load($xmlPath);
        } catch (Exception $e) {
            $this->html = __('DW', 'specificReport', 'documentFailToLoad');
        }
        libxml_clear_errors();
        if (!($xmlDocument->schemaValidate(__DIR__ . '/validate.xsd'))) {
            $this->fileName = date('Y-m-d', time()) . 'error';
            $this->html .= '<body>';
            $this->html .= '<div>';
            foreach (libxml_get_errors() as $error) {
                $this->html .= "<br/>\n";
                switch ($error->level) {
                    case LIBXML_ERR_WARNING:
                        $this->html .= "<b>Warning $error->code</b>: ";
                        break;
                    case LIBXML_ERR_ERROR:
                        $this->html .= "<b>Error $error->code</b>: ";
                        break;
                    case LIBXML_ERR_FATAL:
                        $this->html .= "<b>Fatal Error $error->code</b>: ";
                        break;
                }
                $this->html .= trim($error->message);
                $this->html .= " on line <b>$error->line</b>\n";
            }
            libxml_clear_errors();
            $this->html .= '</div>';
            $this->html .= '</body>';
        } else {
            $xmlSpecificExport = $xmlDocument->getElementsByTagName("specificExport")->item(0);
            $this->fileName = date('Y-m-d', time())
                . '-' . Core_Tools::refactor($this->translator->get($cube->getLabel()))
                . '-' . Core_Tools::refactor($xmlSpecificExport->getAttribute('label'));
            if ($isPreview) {
                UI_Chart_Generic::addHeader();
            }
            $this->html .= '<style type="text/css">';

            // Style du pdf
            $this->html .= '
    .pdf {
        width: ' . (($isPreview) ? '800px' : '100%') . ';
        margin-right: auto;
        margin-left: auto;
    	font-family: cambria;
        text-align: center;
    }

    .pdf h2 {
        font-family: calibri;
        font-size:25px;
    }

    .pdf h3 {
        margin: 5px;
        font-family: calibri;
        font-size:23px;
    }

    .pdf h4 {
        margin: 5px;
    }

    .pdf .data {
        width: 100%;
        page-break-inside: avoid;
    }

    .pdf table {
        text-align: left;
        font-size:15px;
    }

    .lineBreak {
        border-bottom: 1px solid #000000;
        margin: 2px 0px;
	}

    .pageBreak{
  		page-break-after: always;
	}

    .pdf div.footer {
        margin: 15px 0px 0px 0px;
        text-align: left;
    }

    .pdf .data table {
		border-collapse: collapse;
		margin-top: 5px;
		width: 100%;
		vertical-align: top;
    }

    .pdf .data table tr td {
        border-top: 1px solid;
    }

    .pdf .data table tr th {
    	text-align: center;
    	font-weight: bold;
        border-bottom: 2px solid;
    }

    .pdf .data table tr:nth-child(2n) {
    	background: #DDDDDD;
    }
        ';

            $this->html .= '</style>';
            $this->html .= '<body>';
            if ($isPreview) {
                $this->html .= '<a id="exportAsPdfTop" href="' . $exportUrl . '" class="btn btn-default">';
                $this->html .= '<img src="images/dw/pdf.png" alt="pdf" /> ';
                $this->html .= __('UI', 'verb', 'exportToPDF');
                $this->html .= '</a>';
            }
            $this->html .= '<div class="pdf">';

            $locale = Core_Locale::loadDefault();
            $this->html .= '<h2>'
                . $xmlSpecificExport->getAttribute("prefix")
                . $this->translator->get($cube->getLabel())
                . $xmlSpecificExport->getAttribute("postfix")
                . '</h2>';

            // Récupération des contenus de données.
            /** @var \DOMNodeList $xmlContent */
            foreach ($xmlSpecificExport->childNodes as $xmlContent) {
                if ($xmlContent instanceof DOMText) {
                    continue;
                }

                switch ($xmlContent->nodeName) {

                    case 'row':
                        $xmlDatas = $xmlContent->getElementsByTagName('data');
                        $isMain = ($xmlContent->hasAttribute('main') && ($xmlContent->getAttribute('main') == 'true'));

                        if (($xmlDatas->length % 2) === 0) {
                            $nbLines = (($xmlDatas->length + 1) / 2);
                        } else {
                            $nbLines = $xmlDatas->length / 2;
                        }

                        if ($isMain) {
                            $this->html .= '<div class="data">';
                        } else {
                            $this->html .= '<table class="data">';
                            $i = 1;
                        }

                        foreach ($xmlDatas as $xmlData) {
                            if ($isMain) {
                                $this->html .= '<h3>';
                            } else {
                                if (($i % 2) !== 0) {
                                    $this->html .= '<tr>';
                                }
                                $this->html .= '<td width="50%">';
                            }

                            $type = $xmlData->getAttribute('type');

                            if ($type === 'AF') {
                                // un AF input = une cell d'orga
                                // cette cell d'orga est reprise en fonction de la
                                // récupérer l'AF input correspondant
                                $AFIndicators = $xmlData->getElementsByTagName('AFIndicator');
                                $AFRef = $AFIndicators->item(0)->getAttribute('refGranularity');
                                $AFGranularity = $cell->getWorkspace()->getGranularityByRef($AFRef);
                                $AFCells = $cell->getChildCellsForGranularity($AFGranularity);
//                                $cell->getSubCellsGroupForInputGranularity();
                                $this->html .= "AF data detected";
                            }
                            else {
                                $report = $this->getReportFromXML($xmlData, $cube, true);

                                $results = $report->getValues();

                                $this->html .= $this->translator->get($report->getLabel())
                                    . ' : ' . $locale->formatNumber(array_pop($results)['value'], 3)
                                    // On n'affiche pas l'incertitude
                                    //' ± '.$locale->formatUncertainty($results[0]['uncertainty']).
                                    . ' ' . $this->translator->get($report->getValuesUnitSymbol());
                            }

                            if ($isMain) {
                                $this->html .= '</h3>';
                            } else {
                                $this->html .= '</td>';
                                if (($i % 2) === 0) {
                                    $this->html .= '</tr>';
                                }
                                $i++;
                            }
                        }

                        if ($isMain) {
                            $this->html .= '</div>';
                        } else {
                            if (($i % 2) === 0) {
                                $this->html .= '<td width="50%"></td></tr>';
                            }
                            $this->html .= '</table>';
                        }

                        break;

                    case 'report':
                        $report = $this->getReportFromXML($xmlContent, $cube, false);

                        $this->html .= '<div class="data">';
                        $this->html .= '<h4>';
                        $this->html .= $this->translator->get($report->getLabel());
                        $this->html .= '</h4>';
                        if ($xmlContent->getAttribute('type') == 'chart' || $xmlContent->getAttribute('type') == 'chart_table') {
                            $reportChartNumber = (isset($reportChartNumber)) ? $reportChartNumber + 1 : 1;
                            if ($isPreview) {
                                $chart = $this->reportService->getChartReport(
                                    $report,
                                    'pdfChartSpecificExport' . $reportChartNumber
                                );
                                $chart->height = 200;
                                $this->html .= $chart->render();
                            } else {
                                $this->html .= '<img src="temp/pdfChartSpecificExport' . $reportChartNumber . '.png">';
                            }

                        }
                        if ($xmlContent->getAttribute('type') == 'table' || $xmlContent->getAttribute('type') == 'chart_table') {

                            // Tableau des valeurs du report

                            $this->html .= '<table>';
                            $this->html .= '<tr>';
                            $numeratorAxis1 = $report->getNumeratorAxis1();
                            $numeratorAxis2 = $report->getNumeratorAxis2();
                            if ($numeratorAxis1 !== null) {
                                $this->html .= '<th>' . $this->translator->get($numeratorAxis1->getLabel()) . '</th>';
                            }
                            if ($numeratorAxis2 !== null) {
                                $this->html .= '<th>' . $this->translator->get($numeratorAxis2->getLabel()) . '</th>';
                            }
                            $this->html .= '<th>' . __('UI', 'name', 'value') . ' ('
                                . $this->translator->get($report->getValuesUnitSymbol()) . ')</th>';
                            $this->html .= '<th>' . __('UI', 'name', 'uncertainty') . ' (%)</th>';
                            $this->html .= '</tr>';


                            // Récupération des unités

                            $locale = Core_Locale::loadDefault();

                            foreach ($report->getValues() as $value) {
                                if ($value['value'] != 0) {
                                    $this->html .= '<tr>';
                                    foreach ($value['members'] as $member) {
                                        $this->html .= '<td>' . $this->translator->get($member->getLabel()) . '</td>';
                                    }
                                    $this->html .= '<td align="right">' . str_replace(
                                            '.',
                                            ',',
                                            $locale->formatNumber($value['value'], 3)
                                        ) . '</td>';
                                    $this->html .= '<td align="right">' . str_replace('.', ',', round($value['uncertainty'])) . '</td>';
                                    $this->html .= '</tr>';
                                }
                            }

                            $this->html .= '</table>';
                        }
                        $this->html .= '</div>';

                        break;

                    case 'pageBreak':
                        $this->html .= '<div class="pageBreak"></div>';
                        break;
                }

                $this->html .= '<div class="lineBreak"></div>';
            }

            $this->html .= '<div class="footer">';
            $this->html .= '<i>';
            $this->html .= __('UI', 'name', 'date') . __('UI', 'other', ':');
            $this->html .= date(str_replace('&nbsp;', '', __('DW', 'export', 'dateFormat')), time());
            $this->html .= '</i>';
            $this->html .= '</div>';

            $this->html .= '</div>';
            if ($isPreview) {
                $this->html .= '<a id="exportAsPdfBottom" href="' . $exportUrl . '" class="btn btn-default">';
                $this->html .= '<img src="images/dw/pdf.png" alt="pdf" /> ';
                $this->html .= __('UI', 'verb', 'exportToPDF');
                $this->html .= '</a>';

                $reportChartNumber = (isset($reportChartNumber)) ? $reportChartNumber : 0;
                if ($reportChartNumber > 0) {
                    $script = '';
                    // Fonction de récupération de l'image.
                    $script .= 'function getImageData(idChart) {';
                    $script .= 'if (window[idChart + "_data"].getNumberOfRows() == 0) {';
                    $script .= 'return "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAyAAAADIAQMAAAAqQRdZAAAABlBMVEX///////9VfPVsAAAFjUlEQVR4nO1aS66kOBCEZkHvuEBL3ORxlTlGL1oFNxvmJhyhlrVA0M6PDaRxVvqp50kzciwoChyO9Jd02lVVUFBQUFBQUFBQUFBQUFBQUFBQUPB/Qrs/+a4Od9X+0ghLHgFe7/tEd/2+87MuPItR7/tW5RDQrt3b4+7YxnE/bJRwOe9zDoE5K9417u7ljeVnNxiDVUYCc6jQrshcEW14FgNy5qxthIrarqXi95ujMds9m+4JnSOMlLWNgIa5d+MCt8PTSZKcszR0IYFhB8MrO8GhharssY4hPcnBdUg05AjPyWobwaGDVy3Ucb0fci6PLtHxR8h/nO0ESASWNKu/kNzm/97gcWW9JzgMYFOFiV7+Uq/+coOfcMHyGwkgMsF1BJuePiUSq+2e8Ffl0xoJnL2TAusWnxLZ/OYemKudsIXydHMo0+KfpVCjiJlAFdm7933gdO9FsOObCSQCRlGaIcjRVaGZCfXrEME7LNPknyUBtWwmnESo3cAcMpHqIAEYLWYCi7Qzj7JTmdp3ImZC/Qwi1M/AHDKx1b5CIGAmnETWYA6Z2GgiYLuZ4EWWrxHh1oGUxK41/2OoMghfKjJ/kcgzpOTEmvsxVBmEY5w0gVPbROyEY8QnOd/p59u/KvKDftrPi/hZeKqaxT/gyubeX31UPokQSRFSIm4abQOnuXL4g3edyIcqTYhBL3qFw9+i6yfpMyIah4tw/YLniQz+dZLDn4mrL/KRJzJxDp7zK3C4xWkGF17VQyHEwMqAHGC2Z85y4ZBT1VznjIdCiIGVATl4ziNwqCa5DOKL9FAIMYIv6N2AR9UKzhaMObBqhAjBF0xzsF8JfydPBB1y8LrTHOwbwnPLE8GlBSw00hwsxHWYwGyVIzIsVbupItgc12GSK9KvuOJTONAtxDBp1J4So93/wSVlmgN9QwyTXJGawx5pDg7V6zCBJDkibs2MVaFwtmiYZIv0FFVQOGM0TCBxlgjHYxSOGyNimPSZIj4K4zkfgdOfshTrQViQKIQI/U6hEoXjGkSsbI81pk1kfFLsReG0T7lGH3WrJGqcVha1ipuXGCb4DcyZhXGC1MdWvcr1jT4PRcCwS/dmUt3EMMHayxChUMymc0YxTBp9sotAA2DXOYOIyqHnkCFCA2CcVE4v4ottpgjHVmaV04lhgk1kF+EB0OsirRgm2EQZIi9vWo4INqRdpAkL0xwRbEi7CL93Wjlt8u7bkBTRHELZu8inVAhXsDmuaTTXdvh7OpPqi8h7X/gksgRO5KSLEU/TpUYQImziIXK33BBzF02XGuFeZNU4chYmSbtIbxGR3xOqvD8sIr+MNKlmiFQhZeBEy3L5jadJ1b6OP4mkAwzSWyFFe0TCJCL8Lq67fJFfGkd4kM0R6MotCaeEn1VwhC/cHoGuBOGdyE0gTnr1/XJ+b4jcHV04zZHrE9rX+cMicqU1Tp8WSYd55ZrRz/vmuLBFRKx+a69nFjmmeh/fX0J833PEOj40T5LwXmSONhFERCLkZd51YBGwLrkdImIr4c68f8LqUJ2pjR0ZJQplMu8EsQhkkdqikvGu0DrmPa3D70putsnIXehn5t25JniQyW1DEYMMPdi+z3j4wskNUBFNPfaRs3dM5/RWrogLt6Fx7Hu/NEfAdJTalBYR7i50M/su9rF9j5VNdsLfUC0iVn/0oRQhxrBUvATGlHRQAAoWDgqIXYdT8yYIMfBVv/pC8ZGM5XTkQeyfnJo3QYiBBzfwcAUd3kA78fDGfJu+Pjn4JgKgwXMraI5bIPAaodunLnEMBWJwO8fhTAREoNgO1JxFzCdw3BrdpzQdDTqL2M8SZZ6KOovYT0Vlnu+6iJjPd2HTe73F342qXYeBuYSCgoKCgoKCgoKCgoKCgoKCgoL/KH4D7MpzFoOVH5sAAAAASUVORK5CYII=";';
                    $script .= "} else {";
                    $script .= 'return window[idChart].getImageURI();';
                    $script .= '}';
                    $script .= '}';
                    // Fonction de récupération de l'image.
                    $script .= 'function saveImageData(e) {';
                    $script .= 'e.preventDefault();';
                    $script .= '$.when(';
                    for ($i = 1; $i <= $reportChartNumber; $i++) {
                        $chartId = 'pdfChartSpecificExport' . $i;
                        if ($i > 1) {
                            $script .= ',';
                        }
                        $script .= 'function() {';
                        $script .= 'return $.post(';
                        $script .= '\'dw/report/saveimagedata\',';
                        $script .= '{name: \'' . $chartId . '\', image: getImageData(\'' . $chartId . '\')}';
                        $script .= ');';
                        $script .= '}()';
                    }
                    $script .= ').done(';
                    $script .= 'function() {';
                    $script .= 'window.location = $(e.target).prop(\'href\');';
                    $script .= '}';
                    $script .= ');';
                    $script .= '}';
                    // Changement du fonctionnement initial du lien d'export Pdf.
                    $script .= '$(\'#exportAsPdfTop\').click(saveImageData);';
                    $script .= '$(\'#exportAsPdfBottom\').click(saveImageData);';
                }

                $this->html .= '<script type="text/javascript">' . $script . '</script>';
            }
            $this->html .= '</body>';
        }

        $this->html .= '</html>';

        $this->clearReports();
    }

    /**
     * @param DOMNode $xmlReport
     * @param Cube $cube
     * @param bool $isSum
     * @return Report
     */
    protected function getReportFromXML(DOMNode $xmlReport, $cube, $isSum)
    {
        $report = new Report($cube);

        if ($isSum) {
            $xmlIndicators = $xmlReport->getElementsByTagName('sumIndicator');

            $xmlNumerator = $xmlIndicators->item(0);
            $numeratorIndicator = $cube->getIndicatorByRef(
                $xmlNumerator->getAttribute('ref')
            );
            $report->setNumeratorIndicator($numeratorIndicator);

            if (($xmlIndicators->length > 1)) {
                $xmlDenominator = $xmlIndicators->item(1);
                $denominatorIndicator = $cube->getIndicatorByRef(
                    $xmlDenominator->getAttribute('ref')
                );
                $report->setDenominatorIndicator($denominatorIndicator);
            }
        } else {
            $xmlIndicators = $xmlReport->getElementsByTagName('indicator');

            $xmlNumerator = $xmlIndicators->item(0);
            $numeratorIndicator = $cube->getIndicatorByRef(
                $xmlNumerator->getAttribute('ref')
            );
            $report->setNumeratorIndicator($numeratorIndicator);

            $xmlNumeratorAxis = $xmlNumerator->getElementsByTagName('refAxis');

            $xmlNumeratorAxis1 = $xmlNumeratorAxis->item(0);
            $prefix = substr($xmlNumeratorAxis1->getAttribute('source'), 0, 1) . '_';
            $report->setNumeratorAxis1(
                $report->getCube()->getAxisByRef(
                    $prefix . $xmlNumeratorAxis1->firstChild->nodeValue
                )
            );

            if (($xmlNumeratorAxis->length > 1)) {
                $xmlNumeratorAxis2 = $xmlNumeratorAxis->item(1);
                $prefix = substr($xmlNumeratorAxis2->getAttribute('source'), 0, 1) . '_';
                $report->setNumeratorAxis2(
                    $report->getCube()->getAxisByRef(
                        $prefix . $xmlNumeratorAxis2->firstChild->nodeValue
                    )
                );
            }

            if (($xmlIndicators->length > 1)) {
                $xmlDenominator = $xmlIndicators->item(1);
                $denominatorIndicator = $cube->getIndicatorByRef(
                    $xmlDenominator->getAttribute('ref')
                );
                $report->setDenominatorIndicator($denominatorIndicator);

                $xmlDenominatorAxis = $xmlDenominator->getElementsByTagName('refAxis');

                $xmlDenominatorAxis1 = $xmlDenominatorAxis->item(0);
                $prefix = substr($xmlDenominatorAxis1->getAttribute('source'), 0, 1) . '_';
                $report->setDenominatorAxis1(
                    $report->getCube()->getAxisByRef(
                        $prefix . $xmlDenominatorAxis1->firstChild->nodeValue
                    )
                );

                if (($xmlDenominatorAxis->length > 1)) {
                    $xmlDenominatorAxis2 = $xmlDenominatorAxis->item(1);
                    $prefix = substr($xmlDenominatorAxis2->getAttribute('source'), 0, 1) . '_';
                    $report->setDenominatorAxis2(
                        $report->getCube()->getAxisByRef(
                            $prefix . $xmlDenominatorAxis2->firstChild->nodeValue
                        )
                    );
                }
            }
        }

        $label = $xmlReport->getAttribute('label');
        if ($label == '') {
            $label = $this->translator->get($report->getNumeratorIndicator()->getLabel());
            if (($report->getDenominatorIndicator() !== null)) {
                $label .= ' / ' . $this->translator->get($report->getDenominatorIndicator()->getLabel());
            }
        }
        $this->translator->set($report->getLabel(), $label);
        if ($xmlReport->hasAttribute('format')) {
            $report->setChartType($xmlReport->getAttribute('format'));
        }
        if ($xmlReport->hasAttribute('withUncertainty')) {
            $report->setWithUncertainty(($xmlReport->getAttribute('withUncertainty') == 'true'));
        }
        if ($xmlReport->hasAttribute('sortType')) {
            $report->setSortType($xmlReport->getAttribute('sortType'));
        }

        foreach ($xmlReport->getElementsByTagName('filter') as $xmlFilter) {
            $prefix = substr($xmlFilter->getAttribute('source'), 0, 1) . '_';

            /* @var DOMNode $xmlFilter */
            $axis = $report->getCube()->getAxisByRef(
                $prefix . $xmlFilter->getElementsByTagName('refAxis')->item(0)->firstChild->nodeValue
            );
            $filter = new Filter($report, $axis);
            foreach ($xmlFilter->getElementsByTagName('refMember') as $xmlMember) {
                /* @var DOMNode $xmlMember */
                $filter->addMember(
                    $filter->getAxis()->getMemberByRef(
                        $xmlMember->firstChild->nodeValue
                    )
                );
            }
        }

        $this->reports[] = $report;
        return $report;
    }

    /**
     * Supprime tous ler reports créés durant l'export.
     */
    protected function clearReports()
    {
        foreach ($this->reports as $report) {
            $report->getCube()->removeReport($report);
        }
    }

    /**
     * @param string $xmlPath
     * @return bool
     */
    public static function isValid($xmlPath)
    {
        $xmlDocument = new DOMDocument();
        libxml_use_internal_errors(true);
        $xmlDocument->load($xmlPath);
        libxml_clear_errors();
        return $xmlDocument->schemaValidate(__DIR__ . '/validate.xsd');
    }

}