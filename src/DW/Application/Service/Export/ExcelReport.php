<?php
/**
 * @author valentin.claras
 */

namespace DW\Application\Service\Export;

use Core_Locale;
use Core_Tools;
use DW\Domain\Report;
use Export_Excel;
use Mnapoli\Translated\Translator;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Border;

/**
 * @package    DW
 * @subpackage Export
 */
class ExcelReport extends Export_Excel
{
    /**
     * @var Translator
     */
    private $translator;


    public function __construct(Report $report, Translator $translator)
    {
        $this->translator = $translator;

        $numeratorAxis1 = $report->getNumeratorAxis1();
        $numeratorAxis2 = $report->getNumeratorAxis2();
        $denominatorAxis1 = $report->getDenominatorAxis1();
        $denominatorAxis2 = $report->getDenominatorAxis2();

        $this->fileName = date('Y-m-d', time())
            . '-' . Core_Tools::refactor($this->translator->get($report->getCube()->getLabel()))
            . '-' . Core_Tools::refactor($this->translator->get($report->getLabel()));


        $sheets = array();


        // Premier onglet : Configuration.
        $sheetData = array();

        $sheetData[] = array(
            array(
                $this->translator->get($report->getCube()->getLabel()),
                array('font' => array('bold' => true, 'size' => 14))
            )
        );
        $sheetData[] = [
            [
                $this->translator->get($report->getLabel()),
                ['font' => ['bold' => true, 'size' => 14]]
            ]
        ];
        $sheetData[] = array();
        $sheetData[] = array(
            array(__('UI', 'name', 'configuration'), array('font' => array('bold' => true, 'size' => 12)))
        );

        if ($report->getDenominatorIndicator() === null) {
            $sheetData[] = array(
                __('Classification', 'indicator', 'indicator'),
                $this->translator->get($report->getNumeratorIndicator()->getLabel())
                . ' (' . $this->translator->get($report->getValuesUnitSymbol()) . ')'
            );
            if ($numeratorAxis1 !== null) {
                $sheetData[] = array(
                    __('UI', 'name', 'axis') . ' 1',
                    $this->translator->get($numeratorAxis1->getLabel())
                );
            }
            if ($numeratorAxis2 !== null) {
                $sheetData[] = array(
                    __('UI', 'name', 'axis') . ' 2',
                    $this->translator->get($numeratorAxis2->getLabel())
                );
            }
        } else {
            $sheetData[] = array(
                __('DW', 'name', 'numerator'),
                $this->translator->get($report->getNumeratorIndicator()->getLabel())
                . ' ('
                . $this->translator->get($report->getNumeratorIndicator()->getRatioUnit()->getSymbol())
                . ')'
            );
            if ($numeratorAxis1 !== null) {
                $sheetData[] = array(
                    __('UI', 'name', 'axis') . ' 1 ' . __('DW', 'name', 'numeratorMin'),
                    $this->translator->get($numeratorAxis1->getLabel())
                );
            }
            if ($numeratorAxis2 !== null) {
                $sheetData[] = array(
                    __('UI', 'name', 'axis') . ' 2 ' . __('DW', 'name', 'numeratorMin'),
                    $this->translator->get($numeratorAxis2->getLabel())
                );
            }

            $sheetData[] = array(
                __('DW', 'name', 'denominator'),
                $this->translator->get($report->getDenominatorIndicator()->getLabel())
                . ' ('
                . $this->translator->get($report->getDenominatorIndicator()->getRatioUnit()->getSymbol())
                . ')'
            );

            if ($numeratorAxis1 !== null) {
                $sheetData[] = array(
                    __('UI', 'name', 'axis') . ' 1 ' . __('DW', 'name', 'denominatorMin'),
                    ($denominatorAxis1 !== null) ? $this->translator->get($denominatorAxis1->getLabel()) : '--'
                );
            }
            if ($numeratorAxis2 !== null) {
                $sheetData[] = array(
                    __('UI', 'name', 'axis') . ' 2 ' . __('DW', 'name', 'denominatorMin'),
                    ($denominatorAxis2 !== null) ? $this->translator->get($denominatorAxis2->getLabel()) : '--'
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
                $sheetData[] = [$this->translator->get($axis->getLabel())];
                foreach ($filter->getMembers() as $member) {
                    $sheetData[] = ['', $this->translator->get($member->getLabel())];
                }
            }
        }
        if (!$hasFilter) {
            $sheetData[] = array(__('DW', 'export', 'noFilter'));
        }


        $date = date(str_replace('&nbsp;', '', __('DW', 'export', 'dateFormat')));
        $sheetData[] = array();
        $sheetData[] = array(
            array(__('UI', 'name', 'date') . __('UI', 'other', ':') . $date, array('font' => array('italic' => true)))
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
                $this->translator->get($numeratorAxis1->getLabel()),
                array(
                    'font' => array('bold' => true),
                    'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                )
            );
            $y++;
        }
        if ($numeratorAxis2 !== null) {
            $sheetHeader[] = array(
                $this->translator->get($numeratorAxis2->getLabel()),
                array(
                    'font' => array('bold' => true),
                    'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                )
            );
            $y++;
        }

        $sheetHeader[] = array(
            __('UI', 'name', 'value')
            . ' (' . $this->translator->get($report->getValuesUnitSymbol()) . ')',
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

        $coordinate = "A" . ($x);

        $locale = Core_Locale::loadDefault();
        if ($numeratorAxis2 !== null) {
            foreach ($report->getValues() as $value) {
                if ($value['value'] != 0) {
                    $line = array();
                    foreach ($value['members'] as $member) {
                        $line[] = $this->translator->get($member->getLabel());
                    }
                    $line[] = (string)floatval(
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
                    $line[] = $this->translator->get(array_shift($value['members'])->getLabel());
                    $line[] = (string)floatval(
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
