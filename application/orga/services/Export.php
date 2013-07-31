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
     * Exporte la structure d'une Organization.
     *
     * @param string $format
     * @param Orga_Model_Cell $cell
     */
    public function streamOrganization($format, Orga_Model_Cell $cell)
    {
        $modelBuilder = new SpreadsheetModelBuilder();
        $export = new PHPExcelExporter();

        // Organization.
        $modelBuilder->bind('organization', $cell->getGranularity()->getOrganization());

        // Feuilles de l'Organization.
        $modelBuilder->bind('organizationSheetLabel', __('Orga', 'exports', 'organizationSheetLabel'));

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

        // Feuille de la pertinence des Cell.
        $modelBuilder->bind('cellsRelevanceSheetLabel', __('Orga', 'exports', 'cellsRelevanceSheetLabel'));

        $modelBuilder->bind('cellColumnRelevant', __('Orga', 'exports', 'cellColumnRelevant'));
        $modelBuilder->bind('cellColumnAllParentsRelevant', __('Orga', 'exports', 'cellColumnAllParentsRelevant'));
        $modelBuilder->bindFunction(
            'displayCellMemberForAxis',
            function(Orga_Model_Cell $cell, Orga_Model_Axis $axis) {
                foreach ($cell->getMembers() as $member) {
                    if ($member->getAxis() === $axis) {
                        return $member->getLabel();
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
            $modelBuilder->build(new YamlMappingReader(__DIR__.'/exports/organization.yml')),
            'php://output',
            $writer
        );
    }
    /**
     * Exporte la structure d'une Cell es de ses enfants.
     *
     * @param string $format
     * @param Orga_Model_Cell $cell
     */
    public function streamCell($format, Orga_Model_Cell $cell)
    {
        $modelBuilder = new SpreadsheetModelBuilder();
        $export = new PHPExcelExporter();

        // Cell.
        $modelBuilder->bind('cell', $cell);
        // Organization.
        $modelBuilder->bind('organization', $cell->getGranularity()->getOrganization());

        // Feuille des Member.
        $modelBuilder->bind('membersSheetLabel', __('Orga', 'exports', 'membersSheetLabel'));

        $modelBuilder->bind('memberColumnLabel', __('Orga', 'exports', 'memberColumnLabel'));
        $modelBuilder->bind('memberColumnRef', __('Orga', 'exports', 'memberColumnRef'));
        $modelBuilder->bindFunction(
            'getCellNarrowerAxes',
            function(Orga_Model_Cell $cell, Orga_Model_Organization $organization=null) {
                $organization = $cell->getGranularity()->getOrganization();
                $axes = [];
                foreach ($organization->getAxes() as $organizationAxis) {
                    foreach ($cell->getMembers() as $member) {
                        if ($organizationAxis->isNarrowerThan($member->getAxis())) {
                            continue;
                        } elseif (!$organizationAxis->isTransverse($member->getAxis())) {
                            continue 2;
                        }
                    }
                    $axes[] = $organizationAxis;
                }
                return $axes;
            }
        );
        $modelBuilder->bindFunction(
            'getCellNarrowerMembers',
            function(Orga_Model_Cell $cell, Orga_Model_Axis $axis) {
                $members = [];
                foreach ($axis->getMembers() as $axisMember) {
                    foreach ($cell->getMembers() as $member) {
                        if (($axis->isNarrowerThan($member->getAxis())) && in_array($member, $axisMember->getAllParents())) {
                            continue;
                        } elseif (!$axis->isTransverse($member->getAxis())) {
                            continue 2;
                        }
                    }
                    $members[] = $axisMember;
                }
                return $members;
            }
        );
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

        // Feuille de la pertinence des Cell.
        $modelBuilder->bind('cellsRelevanceSheetLabel', __('Orga', 'exports', 'cellsRelevanceSheetLabel'));

        $modelBuilder->bind('cellColumnRelevant', __('Orga', 'exports', 'cellColumnRelevant'));
        $modelBuilder->bind('cellColumnAllParentsRelevant', __('Orga', 'exports', 'cellColumnAllParentsRelevant'));
        $modelBuilder->bindFunction(
            'displayCellMemberForAxis',
            function(Orga_Model_Cell $cell, Orga_Model_Axis $axis) {
                foreach ($cell->getMembers() as $member) {
                    if ($member->getAxis() === $axis) {
                        return $member->getLabel();
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
            $modelBuilder->build(new YamlMappingReader(__DIR__.'/exports/cell.yml')),
            'php://output',
            $writer
        );
    }

    /**
     * Exporte les utilisateurs de la version de orga.
     *
     * @param string $format
     * @param Orga_Model_Cell $cell
     */
    public function streamUsers($format, Orga_Model_Cell $cell)
    {
        $modelBuilder = new SpreadsheetModelBuilder();
        $export = new PHPExcelExporter();

        // Cell.
        $modelBuilder->bind('cell', $cell);
        // Organization.
        $granularities = [];
        foreach ($cell->getGranularity()->getNarrowerGranularities() as $narrowerGranularity) {
            if ($narrowerGranularity->getCellsWithACL()) {
                $granularities[] = $narrowerGranularity;
            }
        }
        $modelBuilder->bind('granularities', $granularities);

        $modelBuilder->bindFunction(
            'getUsersForCell',
            function(Orga_Model_Cell $cell) {
                $users = [];

                $cellResource = User_Model_Resource_Entity::loadByEntity($cell);
                $linkedIndentities = $cellResource->getLinkedSecurityIdentities();
                foreach ($linkedIndentities as $linkedIndentity) {
                    if ($linkedIndentity instanceof User_Model_Role) {
                        foreach ($linkedIndentity->getUsers() as $user) {
                            $users[] = ['user' => $user, 'role' => $linkedIndentity];
                        }
                    }
                }

                return $users;
            }
        );
        $modelBuilder->bind('userColumnFirstName', __('User', 'name', 'firstName'));
        $modelBuilder->bind('userColumnLastName', __('User', 'name', 'lastName'));
        $modelBuilder->bind('userColumnEmail', __('User', 'name', 'email'));
        $modelBuilder->bind('userColumnRole', __('User', 'name', 'role'));
        $modelBuilder->bindFunction(
            'displayCellMemberForAxis',
            function(Orga_Model_Cell $cell, Orga_Model_Axis $axis) {
                foreach ($cell->getMembers() as $member) {
                    if ($member->getAxis() === $axis) {
                        return $member->getLabel();
                    }
                }
                return '';
            }
        );
        $modelBuilder->bindFunction(
            'displayRoleName',
            function(User_Model_Role $role) {
                return __('Orga', 'role', $role->getName());
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
            $modelBuilder->build(new YamlMappingReader(__DIR__.'/exports/users.yml')),
            'php://output',
            $writer
        );
    }

    /**
     * Exporte les Inputs de la version de orga.
     *
     * @param string $format
     * @param Orga_Model_Organization $organization
     */
    public function streamInputs($format, Orga_Model_Organization $organization)
    {
        $modelBuilder = new SpreadsheetModelBuilder();
        $export = new PHPExcelExporter();

        $modelBuilder->bind('organization', $organization);

        $modelBuilder->bindFunction(
            'displayCellMemberForAxis',
            function(Orga_Model_Cell $cell, Orga_Model_Axis $axis) {
                foreach ($cell->getMembers() as $member) {
                    if ($member->getAxis() === $member) {
                        return $member->getLabel();
                    }
                }
                return '';
            }
        );

        $modelBuilder->bindFunction(
            'getInputCellFields',
            function(Orga_Model_Cell $cell) {
                try {
                    $aFInputSetPrimary = $cell->getAFInputSetPrimary();
                } catch (Core_Exception_UndefinedAttribute $e) {
                    return [__('Orga', 'exports', 'noInputHeader')];
                }

                $inputFields = [];
                foreach ($aFInputSetPrimary->getInputs() as $input) {
                    $inputFields = array_merge($inputFields, getInputComponentLabel($input));
                }
                return $inputFields;
            }
        );

        $modelBuilder->bindFunction(
            'getInputCellValues',
            function(Orga_Model_Cell $cell) {
                try {
                    $aFInputSetPrimary = $cell->getAFInputSetPrimary();
                } catch (Core_Exception_UndefinedAttribute $e) {
                    return [__('Orga', 'exports', 'noInput')];
                }

                $inputValues = [];
                foreach ($aFInputSetPrimary->getInputs() as $input) {
                    $inputValues = array_merge($inputValues, getInputComponentValue($input));
                }
                return $inputValues;
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
            $modelBuilder->build(new YamlMappingReader(__DIR__.'/exports/inputs.yml')),
            'php://output',
            $writer
        );
    }

}

function getInputComponentLabel(AF_Model_Input $input, $prefix='')
{
    if ($input instanceof AF_Model_Input_SubAF_NotRepeated) {
        $prefix = $prefix.$input->getComponent()->getLabel().'/';
        $subInputLabels = [];
        foreach ($input->getValue()->getInputs() as $subInput) {
            $subInputLabels = array_merge(
                $subInputLabels,
                getInputComponentLabel($subInput, $prefix)
            );
        }
        return $subInputLabels;
    } else if ($input instanceof AF_Model_Input_SubAF_Repeated) {
        $prefix = $prefix.$input->getComponent()->getLabel().'/';
        $subInputLabels = [];
        foreach ($input->getValue() as $number => $subInputSet) {
            foreach ($subInputSet->getInputs() as $subInput) {
                $subInputLabels = array_merge(
                    $subInputLabels,
                    getInputComponentLabel($subInput, $prefix.$number.'/')
                );
            }
        }
        return $subInputLabels;
    } else {
        return [$prefix.$input->getComponent()->getLabel()];
    }
}

function getInputComponentValue(AF_Model_Input $input)
{
    if ($input instanceof AF_Model_Input_SubAF_NotRepeated) {
        $subInputLabels = [];
        foreach ($input->getValue()->getInputs() as $subInput) {
            $subInputLabels = array_merge(
                $subInputLabels,
                getInputComponentValue($subInput)
            );
        }
        return $subInputLabels;
    } else if ($input instanceof AF_Model_Input_SubAF_Repeated) {
        $subInputLabels = [];
        foreach ($input->getValue() as $number => $subInputSet) {
            foreach ($subInputSet->getInputs() as $subInput) {
                $subInputLabels = array_merge(
                    $subInputLabels,
                    getInputComponentValue($subInput)
                );
            }
        }
        return $subInputLabels;
    } else {
        return [$input->getValue()];
    }
}