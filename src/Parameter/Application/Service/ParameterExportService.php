<?php

namespace Parameter\Application\Service;

use Mnapoli\Translated\AbstractTranslatedString;
use Mnapoli\Translated\Translator;
use Parameter\Domain\ParameterLibrary;
use PHPExcel_Writer_Excel2007;
use PHPExcel_Writer_Excel5;
use Parameter\Domain\Category;
use Parameter\Domain\Family\Family;
use Parameter\Domain\Family\Cell;
use Parameter\Domain\Family\Dimension;
use Xport\Spreadsheet\Builder\SpreadsheetModelBuilder;
use Xport\Spreadsheet\Exporter\PHPExcelExporter;
use Xport\MappingReader\YamlMappingReader;

/**
 * @author valentin.claras
 * @author matthieu.napoli
 */
class ParameterExportService
{
    /**
     * @var Translator
     */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Exporte les paramètres.
     *
     * @param ParameterLibrary $library
     * @param string           $format
     */
    public function stream(ParameterLibrary $library, $format)
    {
        $modelBuilder = new SpreadsheetModelBuilder();
        $export = new PHPExcelExporter();

        $getAllFamilies = function (Category $category) use (&$getAllFamilies) {
            $families = $category->getFamilies()->toArray();
            foreach ($category->getChildCategories() as $childCategory) {
                $families = array_merge($families, $getAllFamilies($childCategory));
            }
            return $families;
        };
        $modelBuilder->bindFunction('getAllFamilies', $getAllFamilies);

        // Skip les catégories vides sinon onglet vide et -> https://github.com/PHPOffice/PHPExcel/issues/193
        $categories = array_filter(
            $library->getCategories()->toArray(),
                function (Category $category) use ($getAllFamilies) {
                return !empty($getAllFamilies($category));
            }
        );

        $modelBuilder->bind('categories', $categories);
        $modelBuilder->bind('cellDigitalValue', __('UI', 'name', 'value'));
        $modelBuilder->bind('cellRelativeUncertainty', '+/- (%)');

        $modelBuilder->bindFunction(
            'getFamilyLabel',
            function (Family $family) {
                $label = '';

                $category = $family->getCategory();
                while ($category->getParentCategory() !== null) {
                    $label .= $this->translator->get($category->getLabel()) . ' / ';
                    $category = $category->getParentCategory();
                }
                $label .= $this->translator->get($family->getLabel());

                $label .= ' (' . $this->translator->get($family->getUnit()->getSymbol()) . ')';

                return $label;
            }
        );

        $modelBuilder->bindFunction(
            'displayCellMemberForDimension',
            function (Cell $cell, Dimension $dimension) {
                foreach ($cell->getMembers() as $member) {
                    if ($dimension->hasMember($member)) {
                        return $this->translator->get($member->getLabel());
                    }
                }
                return '';
            }
        );

        $modelBuilder->bindFunction(
            'translateLabel',
            function (AbstractTranslatedString $label) {
                return $this->translator->get($label);
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
