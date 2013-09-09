<?php
/**
 * Classe Classif_Service_Export
 * @author valentin.claras
 * @package    Classif
 * @subpackage Service
 */

use Xport\Spreadsheet\Builder\SpreadsheetModelBuilder;
use Xport\Spreadsheet\Exporter\PHPExcelExporter;
use Xport\MappingReader\YamlMappingReader;

/**
 * Service Classif.
 * @package    Classif
 * @subpackage Service
 */
class Classif_Service_Export
{
    /**
     * Exporte la version de classif.
     *
     * @param string $format
     */
    public function stream($format)
    {
        $modelBuilder = new SpreadsheetModelBuilder();
        $export = new PHPExcelExporter();

        // Feuilles des Context, Indicator, ContextIndicator.
        $modelBuilder->bind('contextindicatorsSheetLabel', __('Classif', 'indicator', 'indicators'));

        $modelBuilder->bind('contextColumnLabel', __('UI', 'name', 'label'));
        $modelBuilder->bind('contextColumnRef', __('UI', 'name', 'identifier'));
        $modelBuilder->bind('contexts', Classif_Model_Context::loadList());

        $modelBuilder->bind('indicatorColumnLabel', __('UI', 'name', 'label'));
        $modelBuilder->bind('indicatorColumnRef', __('UI', 'name', 'identifier'));
        $modelBuilder->bind('indicatorColumnUnit', __('Unit', 'name', 'unit'));
        $modelBuilder->bind('indicatorColumnRatioUnit', __('Unit', 'name', 'ratioUnit'));
        $modelBuilder->bind('indicators', Classif_Model_Indicator::loadList());

        $modelBuilder->bind('contextindicatorColumnContext', __('Classif', 'context', 'context'));
        $modelBuilder->bind('contextindicatorColumnIndicator', __('Classif', 'indicator', 'indicator'));
        $modelBuilder->bind('contextindicatorColumnAxes', __('UI', 'name', 'axes'));
        $modelBuilder->bind('contextindicators', Classif_Model_ContextIndicator::loadList());
        $modelBuilder->bindFunction(
            'displayContextIndicatorAxes',
            function(Classif_Model_ContextIndicator $contextIndicator) {
                $axesLabelRef = [];
                foreach ($contextIndicator->getAxes() as $axis) {
                        $axesLabelRef[] = $axis->getLabel() . ' (' . $axis->getRef() . ')';
                }
                return implode(' - ', $axesLabelRef);
            }
        );

        // Feuille des Axis.
        $modelBuilder->bind('axesSheetLabel', __('UI', 'name', 'axes'));

        $modelBuilder->bind('axisColumnLabel', __('UI', 'name', 'label'));
        $modelBuilder->bind('axisColumnRef', __('UI', 'name', 'identifier'));
        $modelBuilder->bind('axisColumnNarrower', __('Classif', 'export', 'axisColumnNarrower'));
        $modelBuilder->bind('axes', Classif_Model_Axis::loadListOrderedAsAscendantTree());
        $modelBuilder->bindFunction(
            'displayAxisDirectNarrower',
            function(Classif_Model_Axis $axis) {
                if ($axis->getDirectNarrower() !== null) {
                    return $axis->getDirectNarrower()->getLabel() . ' (' . $axis->getDirectNarrower()->getRef() . ')';
                }
                return '';
            }
        );

        // Feuille des Member.
        $modelBuilder->bind('membersSheetLabel', __('UI', 'name', 'members'));

        $modelBuilder->bind('memberColumnLabel', __('UI', 'name', 'label'));
        $modelBuilder->bind('memberColumnRef', __('UI', 'name', 'identifier'));
        $modelBuilder->bindFunction(
            'displayParentMemberForAxis',
            function(Classif_Model_member $member, Classif_Model_Axis $broaderAxis) {
                foreach ($member->getDirectParents() as $directParent) {
                    if ($directParent->getAxis() === $broaderAxis) {
                        return $directParent->getLabel();
                    }
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