<?php
/**
 * Classe UnitExport
 * @author valentin.claras
 * @package    Unit
 * @subpackage Service
 */

namespace Unit\Application\Service;

use Unit\Domain\PhysicalQuantity;
use Unit\Domain\Unit\DiscreteUnit;
use Unit\Domain\Unit\ExtendedUnit;
use Unit\Domain\Unit\StandardUnit;
use Xport\Spreadsheet\Builder\SpreadsheetModelBuilder;
use Xport\Spreadsheet\Exporter\PHPExcelExporter;
use Xport\MappingReader\YamlMappingReader;

/**
 * Service Unit.
 * @package    Unit
 * @subpackage Service
 */
class UnitExport
{
    /**
     * Exporte la version de unit.
     *
     * @param string $format
     */
    public function stream($format='xlsx')
    {
        $modelBuilder = new SpreadsheetModelBuilder();
        $export = new PHPExcelExporter();

        // Physical Quantities
        $queryBasePhysicalQuantities = new \Core_Model_Query();
        $queryBasePhysicalQuantities->filter->addCondition(PhysicalQuantity::QUERY_ISBASE, true);
        $modelBuilder->bind('basePhysicalQuantities', PhysicalQuantity::loadList($queryBasePhysicalQuantities));
        $modelBuilder->bind('physicalQuantities', PhysicalQuantity::loadList());
        $modelBuilder->bind('physicalQuantitiesSheetLabel', __('Unit', 'name', 'physicalQuantities'));
        $modelBuilder->bind('physicalQuantityColumnLabel', __('UI', 'name', 'label'));
        $modelBuilder->bind('physicalQuantityColumnRef', __('UI', 'name', 'identifier'));
        $modelBuilder->bindFunction(
            'displayNormalizedExponent',
            function (PhysicalQuantity $physicalQuantity, PhysicalQuantity $basePhysicalQuantity) {
                foreach ($physicalQuantity->getPhysicalQuantityComponents() as $component) {
                    if ($component->getBasePhysicalQuantity()->getRef() === $basePhysicalQuantity->getRef()) {
                        return $component->getExponent();
                    }
                }
                return 0;
            }
        );

        // Standard Units
        $modelBuilder->bind('standardUnits', StandardUnit::loadList());
        $modelBuilder->bind('standardUnitsSheetLabel', __('Unit', 'name', 'standardUnits'));
        $modelBuilder->bind('standardUnitColumnLabel', __('UI', 'name', 'label'));
        $modelBuilder->bind('standardUnitColumnRef', __('UI', 'name', 'identifier'));
        $modelBuilder->bind('standardUnitColumnSymbol', __('Unit', 'name', 'symbol'));
        $modelBuilder->bind('standardUnitColumnPhysicalQuantity', __('Unit', 'name', 'physicalQuantity'));
        $modelBuilder->bind('standardUnitColumnMultiplier', __('Unit', 'name', 'multiplier'));
        $modelBuilder->bind('standardUnitColumnSystem', __('Unit', 'name', 'unitSystem'));

        // Extended Units
        $modelBuilder->bind('extendedUnits', ExtendedUnit::loadList());
        $modelBuilder->bind('extendedUnitsSheetLabel', __('Unit', 'name', 'extendedUnits'));
        $modelBuilder->bind('extendedUnitColumnLabel', __('UI', 'name', 'label'));
        $modelBuilder->bind('extendedUnitColumnRef', __('UI', 'name', 'identifier'));
        $modelBuilder->bind('extendedUnitColumnSymbol', __('Unit', 'name', 'symbol'));
        $modelBuilder->bind('extendedUnitColumnMultiplier', __('Unit', 'name', 'multiplier'));

        // Discrete Units
        $modelBuilder->bind('discreteUnits', DiscreteUnit::loadList());
        $modelBuilder->bind('discreteUnitsSheetLabel', __('Unit', 'name', 'discreteUnits'));
        $modelBuilder->bind('discreteUnitColumnLabel', __('UI', 'name', 'label'));
        $modelBuilder->bind('discreteUnitColumnRef', __('UI', 'name', 'identifier'));


        switch ($format) {
            case 'xls':
                $writer = new \PHPExcel_Writer_Excel5();
                break;
            case 'xlsx':
            default:
                $writer = new \PHPExcel_Writer_Excel2007();
                break;
        }

        $export->export(
            $modelBuilder->build(new YamlMappingReader(__DIR__.'/export.yml')),
            'php://output',
            $writer
        );
    }

}