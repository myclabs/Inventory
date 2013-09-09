<?php
/**
 * Classe Techno_Service_Export
 * @author valentin.claras
 * @package    Techno
 * @subpackage Service
 */

use Xport\Spreadsheet\Builder\SpreadsheetModelBuilder;
use Xport\Spreadsheet\Exporter\PHPExcelExporter;
use Xport\MappingReader\YamlMappingReader;

/**
 * Service Techno.
 * @package    Techno
 * @subpackage Service
 */
class Techno_Service_Export
{
    /**
     * Exporte la version de techno.
     *
     * @param string $format
     */
    public function stream($format)
    {
        $modelBuilder = new SpreadsheetModelBuilder();
        $export = new PHPExcelExporter();

        // Feuilles det Category.
        $modelBuilder->bind('categories', Techno_Model_Category::loadRootCategories());
        $modelBuilder->bind('cellDigitalValue', __('Techno', 'exports', 'digitalValue'));
        $modelBuilder->bind('cellRelativeUncertainty', __('Techno', 'exports', 'relativeUncertainty'));
        $modelBuilder->bindFunction('getAllFamilies', 'getAllFamilies');
        $modelBuilder->bindFunction(
            'getFamilyLabel',
            function (Techno_Model_Family $family) {
                $label = '';

                $category = $family->getCategory();
                while ($category->getParentCategory() !== null) {
                    $label .= $category->getLabel().'/';
                }
                $label .= $family->getLabel();

                $label .= ' ('.$family->getUnit()->getSymbol().')';

                if ($family instanceof Techno_Model_Family_Coeff) {
                    $label .= ' - '.__('Techno', 'exports', 'coeff');
                } else if ($family instanceof Techno_Model_Family_Process) {
                    $label .= ' - '.__('Techno', 'exports', 'process');
                }

                return $label;
            }
        );
        $modelBuilder->bindFunction(
            'displayCellMemberForDimension',
            function(Techno_Model_Family_Cell $cell, Techno_Model_Family_Dimension $dimension) {
                foreach ($cell->getMembers() as $member) {
                    if ($dimension->hasMember($member)) {
                        return $member->getLabel();
                    }
                    return '';
                }
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

function getAllFamilies(Techno_Model_Category $catgory)
{
    $families = [];
    $families = array_merge($families, $catgory->getFamilies()->toArray());
    foreach ($catgory->getChildCategories() as $childCategory) {
        $families = array_merge($families, getAllFamilies($childCategory));
    }
    return $families;
}