<?php
/**
 * Classe Classif_Service_Classif
 * @author valentin.claras
 * @package    Classif
 * @subpackage Service
 */

use Xport\SpreadsheetModelBuilder;
use Xport\SpreadsheetExporter\PHPExcelExporter;
use Xport\MappingReader\YamlMappingReader;

/**
 * Service Classif.
 * @package    Classif
 * @subpackage Service
 */
class Classif_Service_Classif
{
    /**
     * Exporte la version de classif.
     *
     * @param string $format
     */
    public function streamExport($format)
    {
        $modelBuilder = new SpreadsheetModelBuilder();
        $export = new PHPExcelExporter();

        $modelBuilder->bind('separators', []);

        // Feuilles des Context, Indicator, ContextIndicator.
        $modelBuilder->bind('contextindicatorSheetLabel', __('Classif', 'exports', 'indicatorSheetLabel'));

        $modelBuilder->bind('contextColumnLabel', __('Classif', 'exports', 'contextColumnLabel'));
        $modelBuilder->bind('contextColumnRef', __('Classif', 'exports', 'contextColumnRef'));
        $modelBuilder->bind('contexts', Classif_Model_Context::loadList());

        $modelBuilder->bind('indicatorColumnLabel', __('Classif', 'exports', 'indicatorColumnLabel'));
        $modelBuilder->bind('indicatorColumnRef', __('Classif', 'exports', 'indicatorColumnRef'));
        $modelBuilder->bind('indicatorColumnUnit', __('Classif', 'exports', 'indicatorColumnUnit'));
        $modelBuilder->bind('indicatorColumnRatioUnit', __('Classif', 'exports', 'indicatorColumnRatioUnit'));
        $modelBuilder->bind('indicators', Classif_Model_Indicator::loadList());

        $modelBuilder->bind('contextindicatorColumnContext', __('Classif', 'exports', 'contextindicatorColumnContext'));
        $modelBuilder->bind('contextindicatorColumnIndicator', __('Classif', 'exports', 'contextindicatorColumnIndicator'));
        $modelBuilder->bind('contextindicatorColumnAxes', __('Classif', 'exports', 'contextindicatorColumnAxes'));
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
        $modelBuilder->bind('axesSheetLabel', __('Classif', 'exports', 'axesSheetLabel'));

        $modelBuilder->bind('axisColumnLabel', __('Classif', 'exports', 'axisColumnLabel'));
        $modelBuilder->bind('axisColumnRef', __('Classif', 'exports', 'axisColumnRef'));
        $modelBuilder->bind('axisColumnRef', __('Classif', 'exports', 'axisColumnNarrower'));
        $modelBuilder->bind('axes', Classif_Model_Axis::loadList());
        $modelBuilder->bindFunction(
            'displayAxisDirectNarrower',
            function(Classif_Model_Axis $axis) {
                if ($axis->getDirectNarrower() !== null) {
                    return $axis->getDirectNarrower()->getLabel() . ' (' . $axis->getDirectNarrower()->getRef() . ')';
                }
                return '';
            }
        );

//        // Feuille des Member.
//        $modelBuilder->bind('membersSheetLabel', __('Classif', 'exports', 'membersSheetLabel'));
//
//        $modelBuilder->bind('memberColumnLabel', __('Classif', 'exports', 'memberColumnLabel'));
//        $modelBuilder->bind('memberColumnRef', __('Classif', 'exports', 'memberColumnRef'));
//        $modelBuilder->bind('memberColumnRef', __('Classif', 'exports', 'memberColumnNarrower'));
//        $modelBuilder->bindFunction(
//            'displayMemberDirectParents',
//            function(Classif_Model_member $member) {
//                $axesLabelRef = [];
//                foreach ($contextIndicator->getAxes() as $axis) {
//                    $axesLabelRef[] = $axis->getDirectNarrower()->getLabel() . ' (' . $axis->getDirectNarrower()->getRef() . ')';
//                }
//                return implode(' - ', $axesLabelRef);
//                return '';
//            }
//        );


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