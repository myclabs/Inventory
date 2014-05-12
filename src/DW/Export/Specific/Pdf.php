<?php
/**
 * @author valentin.claras
 * @author cyril.perraud
 * @package DW
 * @subpackage Library
 */

use Mnapoli\Translated\TranslationHelper;

/**
 * Classe permettant de gérer en export spécifique au format pdf.
 * @package DW
 */
class DW_Export_Specific_Pdf extends Export_Pdf
{
    /**
     * @var TranslationHelper
     */
    private $translationHelper;

    /**
     * @var DW_Model_Report[]
     */
    private $reports = [];

    /**
     * @param string            $xmlPath
     * @param DW_Model_Cube     $cube
     * @param string|null       $exportUrl
     * @param TranslationHelper $translationHelper
     */
    public function __construct($xmlPath, DW_Model_Cube $cube, $exportUrl = null, TranslationHelper $translationHelper)
    {
        $this->translationHelper = $translationHelper;

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
        if (!($xmlDocument->schemaValidate(__DIR__.'/validate.xsd'))) {
            $this->fileName = date('Y-m-d', time()).'error';
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
                .'-'.Core_Tools::refactor($this->translationHelper->toString($cube->getLabel()))
                .'-'.Core_Tools::refactor($xmlSpecificExport->getAttribute('label'));
            if ($isPreview) {
                UI_Chart_Generic::addHeader();
                $broker = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
                $broker->view->headScript()->appendFile('http://canvg.googlecode.com/svn/trunk/rgbcolor.js', 'text/javascript');
                $broker->view->headScript()->appendFile('http://canvg.googlecode.com/svn/trunk/canvg.js', 'text/javascript');
            }
            $this->html .= '<style type="text/css">';

            // Style du pdf
            $this->html .= '
    .pdf {
        width: '.(($isPreview) ? '800px' : '100%').';
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
        ';

            $this->html .= '</style>';
            $this->html .= '<body>';
            if ($isPreview) {
                $this->html .= '<a id="exportAsPdfTop" href="'.$exportUrl.'" class="btn btn-default">';
                $this->html .= '<img src="images/dw/pdf.png" alt="pdf" /> ';
                $this->html .= __('UI', 'verb','exportToPDF');
                $this->html .= '</a>';
            }
            $this->html .= '<div class="pdf">';

            $locale = Core_Locale::loadDefault();
            $this->html .= '<h2>'
                .$xmlSpecificExport->getAttribute("prefix")
                .$this->translationHelper->toString($cube->getLabel())
                .$xmlSpecificExport->getAttribute("postfix")
                .'</h2>';

            // Récupération des contenus de données.
            foreach ($xmlSpecificExport->childNodes as $xmlContent) {
                if ($xmlContent instanceof DOMText) {
                    continue;
                }

                /* @var DOMNode $xmlContent */
                switch ($xmlContent->nodeName) {

                    case 'row':
                        $xmlDatas = $xmlContent->getElementsByTagName('data');
                        $isMain = ($xmlContent->hasAttribute('main') && ($xmlContent->getAttribute('main') == 'true'));

                        if (($xmlDatas->length % 2) === 0) {
                            $nbLines = (($xmlDatas->length+1) / 2);
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

                            $report = $this->getReportFromXML($xmlData, $cube, true);

                            $results = $report->getValues();

                            $this->html .= $report->getLabel().' : '.
                            $locale->formatNumber(array_pop($results)['value'], 3).
                            // On n'affiche pas l'incertitude
                            //' ± '.$locale->formatUncertainty($results[0]['uncertainty']).
                            ' '.$report->getValuesUnitSymbol();

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

                    case 'chart':
                        $report = $this->getReportFromXML($xmlContent, $cube, false);
                        $reportNumber = (isset($reportNumber)) ? $reportNumber + 1 : 1;

                        $this->html .= '<div class="data">';
                        $this->html .= '<h4>';
                        $this->html .= $report->getLabel();
                        $this->html .= '</h4>';

                        if ($isPreview) {
                            $chart = $report->getChart('pdfChartSpecificExport'.$reportNumber);
                            $chart->height = 200;
                            $this->html .= $chart->render();
                        } else {
                            $this->html .= '<img src="temp/pdfChartSpecificExport'.$reportNumber.'.png">';
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
                $this->html .= '<a id="exportAsPdfBottom" href="'.$exportUrl.'" class="btn btn-default">';
                $this->html .= '<img src="images/dw/pdf.png" alt="pdf" /> ';
                $this->html .= __('UI', 'verb','exportToPDF');
                $this->html .= '</a>';

                $reportNumber = (isset($reportNumber)) ? $reportNumber : 0;
                if ($reportNumber > 0) {
                    $script = '';
                    // Fonction de récupération de l'image.
                    $script .= 'function getImageData(idChart) {';
                    $script .= 'var chartArea = $(\'#\' + idChart + \' div:first-child div:first-child\')[0];';
                    $script .= 'var svg = chartArea.innerHTML.replace(/&nbsp;/g, " ");';
                    $script .= 'var doc = chartArea.ownerDocument;';
                    $script .= 'var canvas = doc.createElement( \'canvas\');';
                    $script .= 'canvas.setAttribute(\'width\', chartArea.offsetWidth);';
                    $script .= 'canvas.setAttribute(\'height\', chartArea.offsetHeight);';
                    $script .= 'canvas.setAttribute(\'style\', ';
                    $script .= '\'position: absolute; \'';
                    $script .= ' +\'top: \' + (-chartArea.offsetHeight * 2) + \'px; \'';
                    $script .= ' +\'left: \' + (-chartArea.offsetWidth * 2) + \'px;\'';
                    $script .= ');';
                    $script .= 'doc.body.appendChild(canvas);';
                    $script .= 'canvg(canvas, svg);';
                    $script .= 'var imgData = canvas.toDataURL(\'image/png\');';
                    $script .= 'canvas.parentNode.removeChild(canvas);';
                    $script .= 'return imgData;';
                    $script .= '}';
                    // Fonction de récupération de l'image.
                    $script .= 'function saveImageData(e) {';
                    $script .= 'e.preventDefault();';
                    $script .= '$.when(';
                    for ($i = 1; $i <= $reportNumber; $i++) {
                        $chartId = 'pdfChartSpecificExport'.$i;
                        if ($i > 1) {
                            $script .= ',';
                        }
                        $script .= 'function() {';
                        $script .= 'return $.post(';
                        $script .= '\'dw/report/saveimagedata\',';
                        $script .= '{name: \''.$chartId.'\', image: getImageData(\''.$chartId.'\')}';
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

                $this->html .= '<script type="text/javascript">'.$script.'</script>';
            }
            $this->html .= '</body>';
        }

        $this->html .= '</html>';

        $this->clearReports();
    }

    /**
     * Créer un DW_Model_Report à partir d'un node XML.
     *
     * @param DOMNode $xmlReport
     * @param DW_Model_Cube $cube
     * @param bool $isSum
     *
     * @return DW_Model_Report
     */
    protected function getReportFromXML($xmlReport, $cube, $isSum)
    {
        $report = new DW_Model_Report($cube);

        if ($isSum) {
            $xmlIndicators = $xmlReport->getElementsByTagName('sumIndicator');

            $xmlNumerator = $xmlIndicators->item(0);
            $numeratorIndicator = DW_Model_Indicator::loadByRefAndCube(
                $xmlNumerator->getAttribute('ref'),
                $cube
            );
            $report->setNumerator($numeratorIndicator);

            if (($xmlIndicators->length > 1)) {
                $xmlDenominator = $xmlIndicators->item(1);
                $denominatorIndicator = DW_Model_Indicator::loadByRefAndCube(
                    $xmlDenominator->getAttribute('ref'),
                    $cube
                );
                $report->setDenominator($denominatorIndicator);
            }
        } else {
            $xmlIndicators = $xmlReport->getElementsByTagName('indicator');

            $xmlNumerator = $xmlIndicators->item(0);
            $numeratorIndicator = DW_Model_Indicator::loadByRefAndCube(
                $xmlNumerator->getAttribute('ref'),
                $cube
            );
            $report->setNumerator($numeratorIndicator);

            $xmlNumeratorAxis = $xmlNumerator->getElementsByTagName('refAxis');

            $xmlNumeratorAxis1 = $xmlNumeratorAxis->item(0);
            $prefix = substr($xmlNumeratorAxis1->getAttribute('source'), 0, 1).'_';
            $report->setNumeratorAxis1(
                DW_Model_Axis::loadByRefAndCube(
                    $prefix.$xmlNumeratorAxis1->firstChild->nodeValue,
                    $report->getCube()
                )
            );

            if (($xmlNumeratorAxis->length > 1)) {
                $xmlNumeratorAxis2 = $xmlNumeratorAxis->item(1);
                $prefix = substr($xmlNumeratorAxis2->getAttribute('source'), 0, 1).'_';
                $report->setNumeratorAxis2(
                    DW_Model_Axis::loadByRefAndCube(
                        $prefix.$xmlNumeratorAxis2->firstChild->nodeValue,
                        $report->getCube()
                    )
                );
            }

            if (($xmlIndicators->length > 1)) {
                $xmlDenominator = $xmlIndicators->item(1);
                $denominatorIndicator = DW_Model_Indicator::loadByRefAndCube(
                    $xmlDenominator->getAttribute('ref'),
                    $cube
                );
                $report->setDenominator($denominatorIndicator);

                $xmlDenominatorAxis = $xmlDenominator->getElementsByTagName('refAxis');

                $xmlDenominatorAxis1 = $xmlDenominatorAxis->item(0);
                $prefix = substr($xmlDenominatorAxis1->getAttribute('source'), 0, 1).'_';
                $report->setDenominatorAxis1(
                    DW_Model_Axis::loadByRefAndCube(
                        $prefix.$xmlDenominatorAxis1->firstChild->nodeValue,
                        $report->getCube()
                    )
                );

                if (($xmlDenominatorAxis->length > 1)) {
                    $xmlDenominatorAxis2 = $xmlDenominatorAxis->item(1);
                    $prefix = substr($xmlDenominatorAxis2->getAttribute('source'), 0, 1).'_';
                    $report->setDenominatorAxis2(
                        DW_Model_Axis::loadByRefAndCube(
                            $prefix.$xmlDenominatorAxis2->firstChild->nodeValue,
                            $report->getCube()
                        )
                    );
                }
            }
        }

        $label = $xmlReport->getAttribute('label');
        if ($label == '') {
            $label = $this->translationHelper->toString($report->getNumerator()->getLabel());
            if (($report->getDenominator() !== null)) {
                $label .= ' / '.$this->translationHelper->toString($report->getDenominator()->getLabel());
            }
        }
        $report->setLabel($label);
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
            $prefix = substr($xmlFilter->getAttribute('source'), 0, 1).'_';

            /* @var DOMNode $xmlFilter */
            $axis = DW_Model_Axis::loadByRefAndCube(
                $prefix.$xmlFilter->getElementsByTagName('refAxis')->item(0)->firstChild->nodeValue,
                $report->getCube()
            );
            $filter = new DW_Model_Filter($report, $axis);
            foreach ($xmlFilter->getElementsByTagName('refMember') as $xmlMember) {
                /* @var DOMNode $xmlMember */
                $filter->addMember(
                    DW_Model_Member::loadByRefAndAxis(
                        $xmlMember->firstChild->nodeValue,
                        $filter->getAxis()
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
     * Indique si le document est valide.
     *
     * @param string $xmlPath
     *
     * @return bool
     */
    public static function isValid($xmlPath)
    {
        $xmlDocument = new DOMDocument();
        libxml_use_internal_errors(true);
        $xmlDocument->load($xmlPath);
        libxml_clear_errors();
        return $xmlDocument->schemaValidate(__DIR__.'/validate.xsd');
    }

}
