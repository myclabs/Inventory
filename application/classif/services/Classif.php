<?php
/**
 * Classe Classif_Service_Classif
 * @author valentin.claras
 * @package    Classif
 * @subpackage Service
 */

use Xport\SpreadsheetModelBuilder;
use Xport\SpreadsheetExporter;
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
    public function export($format)
    {
        $modelBuilder = new SpreadsheetModelBuilder();
        $export = new SpreadsheetExporter();

        $modelBuilder->bind('indicatorSheetLabel', __('Classif', 'exports', 'indicatorSheetLabel'));
        $modelBuilder->bind('indicatorColumnLabel', __('Classif', 'exports', 'indicatorColumnLabel'));
        $modelBuilder->bind('indicatorColumnRef', __('Classif', 'exports', 'indicatorColumnRef'));
        $modelBuilder->bind('indicatorColumnUnit', __('Classif', 'exports', 'indicatorColumnUnit'));
        $modelBuilder->bind('indicatorColumnRatioUnit', __('Classif', 'exports', 'indicatorColumnRatioUnit'));
        $modelBuilder->bind('indicators', Classif_Model_Indicator::loadList());

        $export->export($modelBuilder->build(new YamlMappingReader(__DIR__.'/export.yml')), 'php://output');
    }

}