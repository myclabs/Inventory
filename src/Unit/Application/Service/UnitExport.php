<?php
/**
 * Classe Unit_Service_Unit
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
    public function stream($format)
    {
        $modelBuilder = new SpreadsheetModelBuilder();
        $export = new PHPExcelExporter();

        $modelBuilder->bind('separators', []);

        // Physical Quantities
        $queryBasePhysicalQuantities = new \Core_Model_Query();
        $queryBasePhysicalQuantities->filter->addCondition(PhysicalQuantity::QUERY_ISBASE, true);
        $modelBuilder->bind('basePhysicalQuantities', PhysicalQuantity::loadList($queryBasePhysicalQuantities));
        $modelBuilder->bind('physicalQuantities', PhysicalQuantity::loadList());
        $modelBuilder->bind('physicalQuantitiesSheetLabel', __('Unit', 'exports', 'physicalQuantitiesSheetLabel'));
        $modelBuilder->bind('physicalQuantityColumnLabel', __('Unit', 'exports', 'physicalQuantityColumnLabel'));
        $modelBuilder->bind('physicalQuantityColumnRef', __('Unit', 'exports', 'physicalQuantityColumnRef'));
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
        $modelBuilder->bind('standardUnitsSheetLabel', __('Unit', 'exports', 'standardUnitsSheetLabel'));
        $modelBuilder->bind('standardUnitColumnLabel', __('Unit', 'exports', 'standardUnitColumnLabel'));
        $modelBuilder->bind('standardUnitColumnRef', __('Unit', 'exports', 'standardUnitColumnRef'));
        $modelBuilder->bind('standardUnitColumnSymbol', __('Unit', 'exports', 'standardUnitColumnSymbol'));
        $modelBuilder->bind('standardUnitColumnPhysicalQuantity', __('Unit', 'exports', 'standardUnitColumnPhysicalQuantity'));
        $modelBuilder->bind('standardUnitColumnMultiplier', __('Unit', 'exports', 'standardUnitColumnMultiplier'));
        $modelBuilder->bind('standardUnitColumnSystem', __('Unit', 'exports', 'standardUnitColumnUnitSystem'));

        // Extended Units
        $modelBuilder->bind('extendedUnits', ExtendedUnit::loadList());
        $modelBuilder->bind('extendedUnitsSheetLabel', __('Unit', 'exports', 'extendedUnitsSheetLabel'));
        $modelBuilder->bind('extendedUnitColumnLabel', __('Unit', 'exports', 'extendedUnitColumnLabel'));
        $modelBuilder->bind('extendedUnitColumnRef', __('Unit', 'exports', 'extendedUnitColumnRef'));
        $modelBuilder->bind('extendedUnitColumnSymbol', __('Unit', 'exports', 'extendedUnitColumnSymbol'));
        $modelBuilder->bind('extendedUnitColumnMultiplier', __('Unit', 'exports', 'extendedUnitColumnMultiplier'));

        // Discrete Units
        $modelBuilder->bind('discreteUnits', DiscreteUnit::loadList());
        $modelBuilder->bind('discreteUnitsSheetLabel', __('Unit', 'exports', 'discreteUnitsSheetLabel'));
        $modelBuilder->bind('discreteUnitColumnLabel', __('Unit', 'exports', 'discreteUnitColumnLabel'));
        $modelBuilder->bind('discreteUnitColumnRef', __('Unit', 'exports', 'discreteUnitColumnRef'));


        switch ($format) {
            case 'xls':
                $writer = new \PHPExcel_Writer_Excel5();
                break;
            case 'xlsx':
            default:
                $writer = new \PHPExcel_Writer_Excel2007();
                break;
        }

        \Core_Tools::dump($modelBuilder->build(new YamlMappingReader(__DIR__.'/export.yml')));
        $export->export(
            $modelBuilder->build(new YamlMappingReader(__DIR__.'/export.yml')),
            'php://output',
            $writer
        );
    }

}