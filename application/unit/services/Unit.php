<?php
/**
 * Classe Unit_Service_Unit
 * @author valentin.claras
 * @package    Unit
 * @subpackage Service
 */

use Xport\Spreadsheet\Builder\SpreadsheetModelBuilder;
use Xport\Spreadsheet\Exporter\PHPExcelExporter;
use Xport\MappingReader\YamlMappingReader;

/**
 * Service Unit.
 * @package    Unit
 * @subpackage Service
 */
class Unit_Service_Unit
{
    /**
     * Exporte la version de unit.
     *
     * @param string $format
     */
    public function streamExport($format)
    {
        $modelBuilder = new SpreadsheetModelBuilder();
        $export = new PHPExcelExporter();

        $modelBuilder->bind('separators', []);

        // Feuilles des Context, Indicator, ContextIndicator.
        $modelBuilder->bind('contextindicatorSheetLabel', __('Unit', 'exports', 'indicatorSheetLabel'));

        $modelBuilder->bind('contextColumnLabel', __('Unit', 'exports', 'contextColumnLabel'));
        $modelBuilder->bind('contextColumnRef', __('Unit', 'exports', 'contextColumnRef'));
        $modelBuilder->bind('contexts', Unit_Model_Context::loadList());

        $modelBuilder->bind('indicatorColumnLabel', __('Unit', 'exports', 'indicatorColumnLabel'));
        $modelBuilder->bind('indicatorColumnRef', __('Unit', 'exports', 'indicatorColumnRef'));
        $modelBuilder->bind('indicatorColumnUnit', __('Unit', 'exports', 'indicatorColumnUnit'));
        $modelBuilder->bind('indicatorColumnRatioUnit', __('Unit', 'exports', 'indicatorColumnRatioUnit'));
        $modelBuilder->bind('indicators', Unit_Model_Indicator::loadList());

        $modelBuilder->bind('contextindicatorColumnContext', __('Unit', 'exports', 'contextindicatorColumnContext'));
        $modelBuilder->bind('contextindicatorColumnIndicator', __('Unit', 'exports', 'contextindicatorColumnIndicator'));
        $modelBuilder->bind('contextindicatorColumnAxes', __('Unit', 'exports', 'contextindicatorColumnAxes'));
        $modelBuilder->bind('contextindicators', Unit_Model_ContextIndicator::loadList());
        $modelBuilder->bindFunction(
            'displayContextIndicatorAxes',
            function(Unit_Model_ContextIndicator $contextIndicator) {
                $axesLabelRef = [];
                foreach ($contextIndicator->getAxes() as $axis) {
                        $axesLabelRef[] = $axis->getLabel() . ' (' . $axis->getRef() . ')';
                }
                return implode(' - ', $axesLabelRef);
            }
        );

        // Feuille des Axis.
        $modelBuilder->bind('axesSheetLabel', __('Unit', 'exports', 'axesSheetLabel'));

        $modelBuilder->bind('axisColumnLabel', __('Unit', 'exports', 'axisColumnLabel'));
        $modelBuilder->bind('axisColumnRef', __('Unit', 'exports', 'axisColumnRef'));
        $modelBuilder->bind('axisColumnRef', __('Unit', 'exports', 'axisColumnNarrower'));
        $modelBuilder->bind('axes', Unit_Model_Axis::loadList());
        $modelBuilder->bindFunction(
            'displayAxisDirectNarrower',
            function(Unit_Model_Axis $axis) {
                if ($axis->getDirectNarrower() !== null) {
                    return $axis->getDirectNarrower()->getLabel() . ' (' . $axis->getDirectNarrower()->getRef() . ')';
                }
                return '';
            }
        );

        switch ($format) {
            case 'xls':
                $writer = new PHPExcel_Writer_Excel5();
                break;
            case 'xlsx':
            default:
                $writer = new PHPExcel_Writer_Excel2007();
                break;
        }

        $export->export(
            $modelBuilder->build(new YamlMappingReader(__DIR__.'/export.yml')),
            'php://output',
            $writer
        );
    }

}