<?php
/**
 * @author valentin.claras
 * @author cyril.perraud
 * @package DW
 * @subpackage Library
 */

use Mnapoli\Translated\TranslationHelper;

/**
 * Classe permettant de gérer l'export détaillé d'une analyse au format pdf.
 * @package DW
 */
class DW_Export_Report_Pdf extends Export_Pdf
{
    /**
     * @var TranslationHelper
     */
    private $translationHelper;

    public function __construct(DW_Model_Report $report, TranslationHelper $translationHelper)
    {
        $this->translationHelper = $translationHelper;

        $numeratorAxis1 = $report->getNumeratorAxis1();
        $numeratorAxis2 = $report->getNumeratorAxis2();
        $denominatorAxis1 = $report->getDenominatorAxis1();
        $denominatorAxis2 = $report->getDenominatorAxis2();

        $this->fileName = date('Y-m-d', time())
            .'-'.Core_Tools::refactor($this->translationHelper->toString($report->getCube()->getLabel()))
            .'-'.Core_Tools::refactor($this->translationHelper->toString($report->getLabel()));

        //    Ajout du html
        $this->html = '<html>';
        $this->html .= '<head>';
        $this->html .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
        $this->html .= '</head>';
        $this->html .= '<style type="text/css">';
        $this->html .= '
    .pdf {
        margin-right: auto;
        margin-left: auto;
    	font-family: cambria;
    }

    .pdf h2 {
        text-align: left;
        font-family: calibri;
    }

    .pdf h3 {
    	width: 100%;
        border-bottom: 1px solid #000000;
    	margin-top: 40px;
    	margin-bottom: 5px;
    }

    .pdf h4 {
    	width: 100%;
    	margin-bottom: 5px;
    }

    .pdf .data {
        width: 100%;
        page-break-inside: avoid;
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
        $this->html .= '<div class="pdf">';
        $this->html .= '<h2>'.$this->translationHelper->toString($report->getCube()->getLabel()).'</h2>';
        $this->html .= '<h2>'.$report->getLabel().'</h2>';
        $this->html .= '<h3>'.__('UI', 'name', 'configuration').'</h3>';

        // Parcourt des configs
        $this->html .= '<table>';
        //Récupération des axes numérateurs
        if ($report->getDenominator() === null) {
            $indicator = $report->getNumerator();

            $this->html .= '<tr>';
            $this->html .= '<td>'.__('Classification', 'indicator', 'indicator').' : </td>';
            $this->html .= '<td>'.$this->translationHelper->toString($indicator->getLabel())
                .' ('.$indicator->getUnit()->getSymbol() .')'.'</td>';
            $this->html .= '</tr>';

            if ($numeratorAxis1 !== null) {
                $this->html .= '<tr>';
                $this->html .= '<td>'.__('UI', 'name', 'axis').' 1 : </td><td>'
                    .$this->translationHelper->toString($numeratorAxis1->getLabel()).'</td>';
                $this->html .= '</tr>';
            }
            if ($numeratorAxis2 !== null) {
                $this->html .= '<tr>';
                $this->html .= '<td>'.__('UI', 'name', 'axis').' 2 : </td><td>'
                    .$this->translationHelper->toString($numeratorAxis2->getLabel()).'</td>';
                $this->html .= '</tr>';
            }
        } else {
            $numerator = $report->getNumerator();
            $denominator = $report->getDenominator();

            $this->html .= '<tr>';
            $this->html .= '<td>'.__('DW', 'name', 'numerator').' : </td>';
            $this->html .= '<td>'.$this->translationHelper->toString($numerator->getLabel())
                .' ('.$numerator->getRatioUnit()->getSymbol().')'.'</td>';
            $this->html .= '</tr>';

            $this->html .= '<tr>';
            $this->html .= '<td>'.__('DW', 'name', 'denominator').' : </td>';
            $this->html .= '<td>'.$this->translationHelper->toString($denominator->getLabel())
                .' ('.$denominator->getRatioUnit()->getSymbol().')'.'</td>';
            $this->html .= '</tr>';

            if ($numeratorAxis1 !== null) {
                $this->html .= '<tr>';
                $this->html .= '<td>'.__('UI', 'name', 'axis').' 1 '.__('DW', 'name', 'numeratorMin').' : </td>';
                $this->html .= '<td>'.$this->translationHelper->toString($numeratorAxis1->getLabel()).'</td>';
                $this->html .= '</tr>';
            }
            if ($numeratorAxis2 !== null) {
                $this->html .= '<tr>';
                $this->html .= '<td>'.__('UI', 'name', 'axis').' 2 '.__('DW', 'name', 'numeratorMin').' : </td>';
                $this->html .= '<td>'.$this->translationHelper->toString($numeratorAxis2->getLabel()).'</td>';
                $this->html .= '</tr>';
            }

            if ($denominatorAxis1 !== null) {
                $this->html .= '<tr>';
                $this->html .= '<td>'.__('UI', 'name', 'axis').' 1 '.__('DW', 'name', 'denominatorMin').' :'.'</td>';
                $this->html .= '<td>'.($denominatorAxis1 !== null) ? $this->translationHelper->toString($denominatorAxis1->getLabel()) : '--'.'</td>';
                $this->html .= '</tr>';
            }
            if ($denominatorAxis2 !== null) {
                $this->html .= '<tr>';
                $this->html .= '<td>'.__('UI', 'name', 'axis').' 2 '.__('DW', 'name', 'denominatorMin').' :'.'</td>';
                $this->html .= '<td>'.($denominatorAxis2 !== null) ? $this->translationHelper->toString($denominatorAxis2->getLabel()) : '--'.'</td>';
                $this->html .= '</tr>';
            }
        }
        $this->html .= '</table>';

        $this->html .= '<h4>'.__('UI', 'name', 'filters').'</h4>';

        $this->html .= '<table>';
        $hasFilter = false;
        foreach ($report->getCube()->getFirstOrderedAxes() as $axis) {
            $filteredAxis = $report->getFilterForAxis($axis);
            if ($filteredAxis !== null) {
                $hasFilter = true;
                $this->html .= '<tr><td>' .$this->translationHelper->toString($axis->getLabel()).': </td><td>';
                foreach ($filteredAxis->getMembers() as $member) {
                    $this->html .= $this->translationHelper->toString($member->getLabel()).', ';
                }
                $this->html = substr($this->html, 0, -2);
                $this->html .= '</td></tr>';
            }
        }
        if (!$hasFilter) {
            $this->html .= '<tr><td>'.__('DW', 'export', 'noFilter').'</td></tr>';
        }
        $this->html .= '</table>';

        $this->html .= '<br/><br/>';

        $date = date(str_replace('&nbsp;', '', __('DW', 'export', 'dateFormat')));
        $this->html .= '<i>' . __('UI', 'name', 'date') . __('UI', 'other', ':') . $date . '</i>';


        $this->html .= '<div class="data">';
        $this->html .= '<h3>'.__('DW', 'name', 'chart').'</h3>';
        $this->html .= '<img src="temp/pdfChart.png">';
        $this->html .= '</div>';


        $this->html .= '<div class="data">';
        $this->html .= '<h3>'.__('UI', 'name', 'values').'</h3>';

        $this->html .= '<table>';
        $this->html .= '<tr>';
        if ($numeratorAxis1 !== null) {
            $this->html .= '<th>'.$this->translationHelper->toString($numeratorAxis1->getLabel()).'</th>';
        }
        if ($numeratorAxis2 !== null) {
            $this->html .= '<th>'.$this->translationHelper->toString($numeratorAxis2->getLabel()).'</th>';
        }
        $this->html .= '<th>'.__('UI', 'name', 'value').' ('. $report->getValuesUnitSymbol() .')</th>';
        $this->html .= '<th>'.__('UI', 'name', 'uncertainty').' (%)</th>';
        $this->html .= '</tr>';


        // Récupération des unités

        $locale = Core_Locale::loadDefault();

        foreach ($report->getValues() as $value) {
            if ($value['value'] != 0) {
                $this->html .= '<tr>';
                foreach ($value['members'] as $member) {
                    $this->html .= '<td>'.$this->translationHelper->toString($member->getLabel()).'</td>';
                }
                $this->html .= '<td align="right">'.str_replace('.',',', $locale->formatNumber($value['value'], 3)).'</td>';
                $this->html .= '<td align="right">'.str_replace('.',',', round($value['uncertainty'])).'</td>';
                $this->html .= '</tr>';
            }
        }

        $this->html .= '</table>';
        $this->html .= '</div>';


        $this->html .= '</div>';
        $this->html .= '</body>';
        $this->html .= '</html>';
    }

}
