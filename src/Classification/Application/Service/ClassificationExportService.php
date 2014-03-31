<?php

namespace Classification\Application\Service;

use Classification\Domain\AxisMember;
use Classification\Domain\ContextIndicator;
use Classification\Domain\Axis;
use Classification\Domain\Context;
use Classification\Domain\Indicator;
use PHPExcel_Writer_Excel2007;
use PHPExcel_Writer_Excel5;
use Xport\Spreadsheet\Builder\SpreadsheetModelBuilder;
use Xport\Spreadsheet\Exporter\PHPExcelExporter;
use Xport\MappingReader\YamlMappingReader;

/**
 * Export des données de classification.
 *
 * @author valentin.claras
 */
class ClassificationExportService
{
    /**
     * @param string $format
     */
    public function stream($format)
    {
        $modelBuilder = new SpreadsheetModelBuilder();
        $export = new PHPExcelExporter();

        // Feuilles des Context, Indicator, ContextIndicator.
        $modelBuilder->bind('contextindicatorsSheetLabel', __('Classification', 'indicator', 'indicators'));

        $modelBuilder->bind('contextTableLabel', __('Classification', 'context', 'contexts'));
        $modelBuilder->bind('contextColumnLabel', __('UI', 'name', 'label'));
        $modelBuilder->bind('contextColumnRef', __('UI', 'name', 'identifier'));
        $modelBuilder->bind('contexts', Context::loadList());

        $modelBuilder->bind('indicatorTableLabel', __('Classification', 'indicator', 'indicators'));
        $modelBuilder->bind('indicatorColumnLabel', __('UI', 'name', 'label'));
        $modelBuilder->bind('indicatorColumnRef', __('UI', 'name', 'identifier'));
        $modelBuilder->bind('indicatorColumnUnit', __('Unit', 'name', 'unit'));
        $modelBuilder->bind('indicatorColumnRatioUnit', __('Unit', 'name', 'ratioUnit'));
        $modelBuilder->bind('indicators', Indicator::loadList());

        $modelBuilder->bind('contextindicatorTableLabel', __('Classification', 'contextIndicator', 'contextIndicators'));
        $modelBuilder->bind('contextindicatorColumnContext', __('Classification', 'context', 'context'));
        $modelBuilder->bind('contextindicatorColumnIndicator', __('Classification', 'indicator', 'indicator'));
        $modelBuilder->bind('contextindicatorColumnAxes', __('UI', 'name', 'axes'));
        $modelBuilder->bind('contextindicators', ContextIndicator::loadList());
        $modelBuilder->bindFunction(
            'displayContextIndicatorAxes',
            function (ContextIndicator $contextIndicator) {
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
        $modelBuilder->bind('axisColumnNarrower', __('Classification', 'export', 'axisColumnNarrower'));
        $modelBuilder->bind('axes', Axis::loadListOrderedAsAscendantTree());
        $modelBuilder->bindFunction(
            'displayAxisDirectNarrower',
            function (Axis $axis) {
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
            function (AxisMember $member, Axis $broaderAxis) {
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
            $modelBuilder->build(new YamlMappingReader(__DIR__ . '/export.yml')),
            'php://output',
            $writer
        );
    }
}
