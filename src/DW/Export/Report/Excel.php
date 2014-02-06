<?php
/**
 * @author valentin.claras
 * @author cyril.perraud
 * @package DW
 * @subpackage Library
 */

/**
 * Classe permettant de gérer l'export détaillé d'une analyse au format excel.
 * @package DW
 */
class DW_Export_Report_Excel extends Export_Excel
{
    /**
     * Constructeur
     *
     * @param DW_Model_Report $report
     */
    public function __construct($report)
    {
        $numeratorAxis1 = $report->getNumeratorAxis1();
        $numeratorAxis2 = $report->getNumeratorAxis2();
        $denominatorAxis1 = $report->getDenominatorAxis1();
        $denominatorAxis2 = $report->getDenominatorAxis2();

        $this->fileName = date('Y-m-d', time())
            .'-'.Core_Tools::refactor($report->getCube()->getLabel())
            .'-'.Core_Tools::refactor($report->getLabel());


        $sheets = array();


        // Premier onglet : Configuration.
        $sheetData = array();

        $sheetData[] = array(
            array($report->getCube()->getLabel(), array('font' => array('bold' => true, 'size' => 14)))
        );
        $sheetData[] = array(
            array($report->getLabel(), array('font' => array('bold' => true, 'size' => 14)))
        );
        $sheetData[] = array();
        $sheetData[] = array(
            array(__('UI', 'name', 'configuration'), array('font' => array('bold' => true, 'size' => 12)))
        );

        if ($report->getDenominator() === null) {
            $sheetData[] = array(
                __('Classif', 'indicator', 'indicator'),
                $report->getNumerator()->getLabel() . ' (' . $report->getValuesUnitSymbol() . ')'
            );
            if ($numeratorAxis1 !== null) {
                $sheetData[] = array(
                    __('UI', 'name', 'axis').' 1',
                    $numeratorAxis1->getLabel()
                );
            }
            if ($numeratorAxis2 !== null) {
                $sheetData[] = array(
                    __('UI', 'name', 'axis').' 2',
                    $numeratorAxis2->getLabel()
                );
            }
        } else {
            $sheetData[] = array(
                __('DW', 'name', 'numerator'),
                $report->getNumerator()->getLabel() . ' (' . $report->getNumerator()->getRatioUnit()->getSymbol() . ')'
            );
            if ($numeratorAxis1 !== null) {
                $sheetData[] = array(
                    __('UI', 'name', 'axis') . ' 1 ' . __('DW', 'name', 'numeratorMin'),
                    $numeratorAxis1->getLabel()
                );
            }
            if ($numeratorAxis2 !== null) {
                $sheetData[] = array(
                    __('UI', 'name', 'axis') . ' 2 ' . __('DW', 'name', 'numeratorMin'),
                    $numeratorAxis2->getLabel()
                );
            }

            $sheetData[] = array(
                __('DW', 'name', 'denominator'),
                $report->getDenominator()->getLabel(). ' (' .  $report->getDenominator()->getRatioUnit()->getSymbol() . ')'
            );

            if ($numeratorAxis1 !== null) {
                $sheetData[] = array(
                    __('UI', 'name', 'axis').' 1 '.__('DW', 'name', 'denominatorMin'),
                    ($denominatorAxis1 !== null) ? $denominatorAxis1->getLabel() : '--'
                );
            }
            if ($numeratorAxis2 !== null) {
                $sheetData[] = array(
                    __('UI', 'name', 'axis').' 2 '.__('DW', 'name', 'denominatorMin'),
                    ($denominatorAxis2 !== null) ? $denominatorAxis2->getLabel() : '--'
                );
            }
        }

        $sheetData[] = array();
        $sheetData[] = array(
            array(__('UI', 'name', 'filters'), array('font' => array('bold' => true, 'size' => 11)))
        );
        $hasFilter = false;
        foreach ($report->getCube()->getFirstOrderedAxes() as $axis) {
            $filter = $report->getFilterForAxis($axis);
            if ($filter !== null) {
                $hasFilter = true;
                $sheetData[] = array($axis->getLabel());
                foreach ($filter->getMembers() as $member) {
                    $sheetData[] = array('', $member->getLabel());
                }
            }
        }
        if (!$hasFilter) {
            $sheetData[] = array(__('DW', 'export', 'noFilter'));
        }


        $date = date(str_replace('&nbsp;', '', __('DW', 'export', 'dateFormat')));
        $sheetData[] = array();
        $sheetData[] = array(
            array(__('UI', 'name', 'date'). __('UI', 'other', ':') . $date, array('font' => array('italic' => true)))
        );
        $sheetData[] = array();

        // Fin première onglet.
        $sheets[__('UI', 'name', 'configuration')] = $sheetData;


        // Second onglet : Résultat.
        $sheetData = array();

        $x = 0;
        $y = 0;
        if ($numeratorAxis1 !== null) {
            $sheetHeader[] = array(
                $numeratorAxis1->getLabel(),
                array(
                    'font' => array('bold' => true),
                    'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                )
            );
            $y++;
        }
        if ($numeratorAxis2 !== null) {
            $sheetHeader[] = array(
                $numeratorAxis2->getLabel(),
                array(
                    'font' => array('bold' => true),
                    'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                )
            );
            $y++;
        }

        $sheetHeader[] = array(
            __('UI', 'name', 'value') . ' (' . $report->getValuesUnitSymbol() . ')',
            array(
                'font' => array('bold' => true),
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                )
            )
        );
        $y++;

        $sheetHeader[] = array(
            __('UI', 'name', 'uncertainty') . ' (%)',
            array(
                'font' => array('bold' => true),
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                )
            )
        );
        $y++;
        $sheetData[] = $sheetHeader;
        $x++;

        $coordinate = "A". ($x);

        $locale = Core_Locale::loadDefault();
        if ($numeratorAxis2 !== null) {
            foreach ($report->getValues() as $value) {
                if ($value['value'] != 0) {
                    $line = array();
                    foreach ($value['members'] as $member) {
                        $line[] = $member->getLabel();
                    }
                    $line[] = floatval(
                        str_replace(
                            ['&Acirc;', '&nbsp;'],
                            '',
                            htmlentities(str_replace(',', '.', $locale->formatNumber($value['value'], 3)))
                        )
                    );
                    $line[] = round($value['uncertainty']);
                    $sheetData[] = $line;
                    $x++;
                }
            }
        } else {
            foreach ($report->getValues() as $value) {
                if ($value['value'] != 0) {
                    $line = array();
                    $line[] = array_shift($value['members'])->getLabel();
                    $line[] = floatval(
                        str_replace(
                            ['&Acirc;', '&nbsp;'],
                            '',
                            htmlentities(str_replace(',', '.', $locale->formatNumber($value['value'], 3)))
                        )
                    );
                    $line[] = round($value['uncertainty']);
                    $sheetData[] = $line;
                    $x++;
                }
            }
        }

        $coordinate .= ":" . $this->convertColumnNumber($y) . $x;

        $this->setStyleForCoordinate(
            $coordinate,
            array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THICK,
                        'color' => array('argb' => '000000')
                    ),
                    'inside' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => '000000')
                    )
                )
            ),
            1
        );

        // Fin second onglet.
        $sheets[__('UI', 'name', 'values')] = $sheetData;


        $this->body = $sheets;
        $this->isMultiSheet = true;
    }
}
