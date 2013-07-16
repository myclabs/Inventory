<?php
/**
 * Classe Orga_Service_Export
 * @author valentin.claras
 * @package    Orga
 * @subpackage Service
 */

use Xport\Spreadsheet\Builder\SpreadsheetModelBuilder;
use Xport\Spreadsheet\Exporter\PHPExcelExporter;
use Xport\MappingReader\YamlMappingReader;

/**
 * Service Orga.
 * @package    Orga
 * @subpackage Service
 */
class Orga_Service_Export
{
    /**
     * Exporte la version de orga.
     *
     * @param string $format
     * @param Orga_Model_Organization $organization
     */
    public function stream($format, Orga_Model_Organization $organization)
    {
        $modelBuilder = new SpreadsheetModelBuilder();
        $export = new PHPExcelExporter();

        // Organization.
        $modelBuilder->bind('organization', $organization);

        // Feuilles de l'Organization.
        $modelBuilder->bind('organizationSheetLabel', __('Orga', 'exports', 'organizationSheetLAbel'));

        $modelBuilder->bind('organizationColumnLabel', __('Orga', 'exports', 'organizationColumnLabel'));
        $modelBuilder->bind('organizationColumnGranularityForInventoryStatus', __('Orga', 'exports', 'organizationColumnGranularityForInventoryStatus'));
        $modelBuilder->bind('organizationInputGranularityColumnInput', __('Orga', 'exports', 'organizationInputGranularityColumnInput'));
        $modelBuilder->bind('organizationInputGranularityColumnInputConfig', __('Orga', 'exports', 'organizationInputGranularityColumnInputConfig'));

        // Feuille des Axis.
        $modelBuilder->bind('axesSheetLabel', __('Orga', 'exports', 'axesSheetLabel'));

        $modelBuilder->bind('axisColumnLabel', __('Orga', 'exports', 'axisColumnLabel'));
        $modelBuilder->bind('axisColumnRef', __('Orga', 'exports', 'axisColumnRef'));
        $modelBuilder->bind('axisColumnNarrower', __('Orga', 'exports', 'axisColumnNarrower'));
        $modelBuilder->bindFunction(
            'displayAxisDirectNarrower',
            function(Orga_Model_Axis $axis) {
                if ($axis->getDirectNarrower() !== null) {
                    return $axis->getDirectNarrower()->getLabel() . ' (' . $axis->getDirectNarrower()->getRef() . ')';
                }
                return '';
            }
        );

        // Feuille des Granularity.
        $modelBuilder->bind('granularitiesSheetLabel', __('Orga', 'exports', 'granularitiesSheetLabel'));

        $modelBuilder->bind('granularityColumnLabel', __('Orga', 'exports', 'granularityColumnLabel'));
        $modelBuilder->bind('granularityColumnNavigable', __('Orga', 'exports', 'granularityColumnNavigable'));
        $modelBuilder->bind('granularityColumnOrgaTab', __('Orga', 'exports', 'granularityColumnOrgaTab'));
        $modelBuilder->bind('granularityColumnACL', __('Orga', 'exports', 'granularityColumnACL'));
        $modelBuilder->bind('granularityColumnAFTab', __('Orga', 'exports', 'granularityColumnAFTab'));
        $modelBuilder->bind('granularityColumnDW', __('Orga', 'exports', 'granularityColumnDW'));
        $modelBuilder->bind('granularityColumnGenericActions', __('Orga', 'exports', 'granularityColumnGenericActions'));
        $modelBuilder->bind('granularityColumnContextActions', __('Orga', 'exports', 'granularityColumnContextActions'));
        $modelBuilder->bind('granularityColumnInputDocuments', __('Orga', 'exports', 'granularityColumnInputDocuments'));

        // Feuille des Member.
        $modelBuilder->bind('membersSheetLabel', __('Orga', 'exports', 'membersSheetLabel'));

        $modelBuilder->bind('memberColumnLabel', __('Orga', 'exports', 'memberColumnLabel'));
        $modelBuilder->bind('memberColumnRef', __('Orga', 'exports', 'memberColumnRef'));
        $modelBuilder->bindFunction(
            'displayParentMemberForAxis',
            function(Orga_Model_member $member, Orga_Model_Axis $broaderAxis) {
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