<?php
/**
 * Classe Orga_Service_Export
 * @author valentin.claras
 * @package    Orga
 * @subpackage Service
 */

use User\Domain\ACL\Role\Role;
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
     * Constructeur, augmente la limite de mémoire à 2G pour réaliser l'export.
     */
    public function __construct()
    {
        ini_set('memory_limit','2G');
    }

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
        $modelBuilder->bind('organizationSheetLabel', __('Orga', 'configuration', 'generalInfoTab'));

        $modelBuilder->bind('organizationColumnLabel', __('UI', 'name', 'label'));
        $modelBuilder->bind('organizationColumnGranularityForInventoryStatus', __('Orga', 'configuration', 'granularityForInventoryStatus'));
        $modelBuilder->bind('organizationInputGranularityColumnInput', __('Orga', 'inputGranularities', 'inputGranularity'));
        $modelBuilder->bind('organizationInputGranularityColumnInputConfig', __('Orga', 'inputGranularities', 'inputConfigGranularity'));

        // Feuille des Axis.
        $modelBuilder->bind('axesSheetLabel', __('UI', 'name', 'axes'));

        $modelBuilder->bind('axisColumnLabel', __('UI', 'name', 'label'));
        $modelBuilder->bind('axisColumnRef', __('UI', 'name', 'identifier'));
        $modelBuilder->bind('axisColumnNarrower', __('Classif', 'export', 'axisColumnNarrower'));
        $modelBuilder->bindFunction(
            'displayAxisDirectNarrower',
            function (Orga_Model_Axis $axis) {
                if ($axis->getDirectNarrower() !== null) {
                    return $axis->getDirectNarrower()->getLabel() . ' (' . $axis->getDirectNarrower()->getRef() . ')';
                }
                return '';
            }
        );

        // Feuille des Granularity.
        $modelBuilder->bind('granularitiesSheetLabel', __('Orga', 'granularity', 'granularities'));

        $modelBuilder->bind('granularityColumnLabel', __('UI', 'name', 'label'));
        $modelBuilder->bind('granularityColumnNavigable', __('Orga', 'granularity', 'navigableHeader'));
        $modelBuilder->bind('granularityColumnOrgaTab', __('Orga', 'cell', 'configurationTab'));
        $modelBuilder->bind('granularityColumnACL', __('User', 'role', 'roles'));
        $modelBuilder->bind('granularityColumnAFTab', __('UI', 'name', 'forms'));
        $modelBuilder->bind('granularityColumnDW', __('DW', 'name', 'analyses'));
        $modelBuilder->bind('granularityColumnGenericActions', __('Social', 'actionTemplate', 'actionTemplates'));
        $modelBuilder->bind('granularityColumnContextActions', __('Social', 'action', 'actions'));
        $modelBuilder->bind('granularityColumnInputDocuments', __('Doc', 'name', 'documents'));

        // Feuille des Member.
        $modelBuilder->bind('membersSheetLabel', __('UI', 'name', 'elements'));

        $modelBuilder->bind('memberColumnLabel', __('UI', 'name', 'label'));
        $modelBuilder->bind('memberColumnRef', __('UI', 'name', 'identifier'));
        $modelBuilder->bindFunction(
            'displayParentMemberForAxis',
            function (Orga_Model_member $member, Orga_Model_Axis $broaderAxis) {
                foreach ($member->getDirectParents() as $directParent) {
                    if ($directParent->getAxis() === $broaderAxis) {
                        return $directParent->getLabel();
                    }
                }
                return '';
            }
        );

        // Feuille de la pertinence des Cell.
        $modelBuilder->bind('cellsRelevanceSheetLabel', __('Orga', 'cellRelevance', 'relevance'));

        $modelBuilder->bind('cellColumnRelevant', __('Orga', 'cellRelevance', 'relevance'));
        $modelBuilder->bind('cellColumnAllParentsRelevant', __('Orga', 'cellRelevance', 'parentCellsRelevanceHeader'));
        $modelBuilder->bindFunction(
            'displayCellMemberForAxis',
            function (Orga_Model_Cell $cell, Orga_Model_Axis $axis) {
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
        $modelBuilder->bind('membersSheetLabel', __('UI', 'name', 'elements'));

        $modelBuilder->bind('memberColumnLabel', __('UI', 'name', 'label'));
        $modelBuilder->bind('memberColumnRef', __('UI', 'name', 'identifier'));
        $modelBuilder->bindFunction(
            'getCellNarrowerAxes',
            function (Orga_Model_Cell $cell) {
                $organization = $cell->getGranularity()->getOrganization();
                $axes = [];
                foreach ($organization->getAxes() as $organizationAxis) {
                    foreach ($cell->getMembers() as $member) {
                        if ($organizationAxis->isNarrowerThan($member->getAxis())) {
                            continue;
                        } elseif (!($organizationAxis->isTransverse([$member->getAxis()]))) {
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
            function (Orga_Model_Cell $cell, Orga_Model_Axis $axis) {
                $members = [];
                foreach ($axis->getMembers() as $axisMember) {
                    foreach ($cell->getMembers() as $member) {
                        if (($axis->isNarrowerThan($member->getAxis())) && in_array($member, $axisMember->getAllParents())) {
                            continue;
                        } elseif (!($axis->isTransverse([$member->getAxis()]))) {
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
            function (Orga_Model_member $member, Orga_Model_Axis $broaderAxis) {
                foreach ($member->getDirectParents() as $directParent) {
                    if ($directParent->getAxis() === $broaderAxis) {
                        return $directParent->getLabel();
                    }
                }
                return '';
            }
        );

        // Feuille de la pertinence des Cell.
        $modelBuilder->bind('cellsRelevanceSheetLabel', __('Orga', 'cellRelevance', 'relevance'));

        $modelBuilder->bind('cellColumnRelevant', __('Orga', 'cellRelevance', 'relevance'));
        $modelBuilder->bind('cellColumnAllParentsRelevant', __('Orga', 'cellRelevance', 'parentCellsRelevanceHeader'));
        $modelBuilder->bindFunction(
            'getChildCellsForGranularity',
            function (Orga_Model_Cell $cell, Orga_Model_Granularity $granularity) {
                return $cell->getChildCellsForGranularity($granularity);
            }
        );
        $modelBuilder->bindFunction(
            'displayCellMemberForAxis',
            function (Orga_Model_Cell $cell, Orga_Model_Axis $axis) {
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

        $granularities = [];
        if ($cell->getGranularity()->getCellsWithACL()) {
            $granularities[] = $cell->getGranularity();
        }
        foreach ($cell->getGranularity()->getNarrowerGranularities() as $narrowerGranularity) {
            if ($narrowerGranularity->getCellsWithACL()) {
                $granularities[] = $narrowerGranularity;
            }
        }
        $modelBuilder->bind('granularities', $granularities);

        $modelBuilder->bindFunction(
            'getChildCellsForGranularity',
            function (Orga_Model_Cell $cell, Orga_Model_Granularity $granularity) {
                if ($cell->getGranularity() === $granularity) {
                    return [$cell];
                } else {
                    return $cell->getChildCellsForGranularity($granularity);
                }
            }
        );

        $modelBuilder->bindFunction(
            'getUsersForCell',
            function (Orga_Model_Cell $cell) {
                $users = [];
                foreach ($cell->getAllRoles() as $role) {
                    $users[] = ['user' => $role->getUser(), 'role' => $role];
                }
                return $users;
            }
        );
        $modelBuilder->bind('userColumnFirstName', __('User', 'user', 'firstName'));
        $modelBuilder->bind('userColumnLastName', __('User', 'user', 'lastName'));
        $modelBuilder->bind('userColumnEmail', __('UI', 'name', 'emailAddress'));
        $modelBuilder->bind('userColumnRole', __('User', 'role', 'role'));
        $modelBuilder->bindFunction(
            'displayCellMemberForAxis',
            function (Orga_Model_Cell $cell, Orga_Model_Axis $axis) {
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
            function (Role $role) {
                return $role->getLabel();
            }
        );


        switch ($format) {
            case 'xlsx':
                $writer = new PHPExcel_Writer_Excel2007();
                break;
            case 'xls':
            default:
                $writer = new PHPExcel_Writer_Excel5();
                break;
        }

        $export->export(
            $modelBuilder->build(new YamlMappingReader(__DIR__.'/exports/users.yml')),
            'php://output',
            $writer
        );
    }

    /**
     * Enregistre (dans data/exports/inputs) l'exports de la saisie de la cellule.
     *
     * @param Orga_Model_Cell $cell
     */
    public function saveCellInput(Orga_Model_Cell $cell)
    {
        $inputsExportsDirectory = APPLICATION_PATH . '/../data/exports/inputs/';
        $writers = [
            'xls' => new PHPExcel_Writer_Excel5(),
//            'xlsx' => new PHPExcel_Writer_Excel2007(),
        ];

        $aFInputSetPrimary = $cell->getAFInputSetPrimary();
        if ($aFInputSetPrimary === null) {
            foreach ($writers as $extension => $writer) {
                $file = $inputsExportsDirectory . $cell->getId() . '.' . $extension;
                if (file_exists($file)) {
                    unlink($file);
                }
            }
            return;
        }
        $inputs = [];
        foreach ($aFInputSetPrimary->getInputs() as $input) {
            if (!$input instanceof AF_Model_Input_Group) {
                $inputs = array_merge($inputs, getInputsDetails($input));
            }
        }

        $modelBuilder = new SpreadsheetModelBuilder();
        $export = new PHPExcelExporter();

        $modelBuilder->bind('cell', $cell);
        $modelBuilder->bind('inputs', $inputs);

        $modelBuilder->bind('inputAncestor', __('Orga', 'export', 'subForm'));
        $modelBuilder->bind('inputLabel', __('UI', 'name', 'fieldLabel'));
        $modelBuilder->bind('inputRef', __('UI', 'name', 'fieldRef'));
        $modelBuilder->bind('inputType', __('Orga', 'export', 'fieldType'));
        $modelBuilder->bind('inputValue', __('Orga', 'export', 'typedInValue'));
        $modelBuilder->bind('inputUncertainty', __('UI', 'name', 'uncertainty') . ' (%)');
        $modelBuilder->bind('inputUnit', __('Orga', 'export', 'choosedUnit'));
        $modelBuilder->bind('inputReferenceValue', __('Orga', 'export', 'valueExpressedInDefaultUnit'));
        $modelBuilder->bind('inputReferenceUnit', __('Orga', 'export', 'defaultUnit'));

        $modelBuilder->bindFunction(
            'displayCellMemberForAxis',
            function (Orga_Model_Cell $cell, Orga_Model_Axis $axis) {
                foreach ($cell->getMembers() as $member) {
                    if ($member->getAxis() === $axis) {
                        return $member->getLabel();
                    }
                }
                return '';
            }
        );

        $document = $modelBuilder->build(new YamlMappingReader(__DIR__.'/exports/inputs.yml'));
        foreach ($writers as $extension => $writer) {
            $export->export(
                $document,
                $inputsExportsDirectory . $cell->getId() . '.' . $extension,
                $writer
            );
        }
    }

    /**
     * Exporte les Inputs de la version de orga.
     *
     * @param string $format
     * @param Orga_Model_Cell $cell
     */
    public function streamInputs($format, Orga_Model_Cell $cell)
    {
        $inputsExportsDirectory = APPLICATION_PATH . '/../data/exports/inputs/';

        $phpExcelModel = new PHPExcel();

        // Détermination des granularitées concernées.
        $granularities = [];
        if ($cell->getGranularity()->isInput()) {
            $granularities[] = $cell->getGranularity();
        }
        foreach ($cell->getGranularity()->getNarrowerGranularities() as $narrowerGranularity) {
            if ($narrowerGranularity->isInput()) {
                $granularities[] = $narrowerGranularity;
            }
        }

        // Création d'une sheet par granularité.
        foreach ($granularities as $indexGranularity => $granularity) {
            /** @var Orga_Model_Granularity $granularity */
            if ($indexGranularity > $phpExcelModel->getSheetCount() - 1) {
                $phpExcelModel->createSheet();
            }

            $granularitySheet = $phpExcelModel->getSheet($indexGranularity);
            $granularitySheet->setTitle(mb_substr($granularity->getLabel(), 0, 31));

            // Colonnes
            $columns = array_map(function(Orga_Model_Axis $axis) { return $axis->getLabel(); }, $granularity->getAxes());
            $columns[] = __('Orga', 'export', 'subForm');
            $columns[] = __('UI', 'name', 'fieldLabel');
            $columns[] = __('UI', 'name', 'fieldRef');
            $columns[] = __('Orga', 'export', 'fieldType');
            $columns[] = __('Orga', 'export', 'typedInValue');
            $columns[] = __('UI', 'name', 'uncertainty') . ' (%)';
            $columns[] = __('Orga', 'export', 'choosedUnit');
            $columns[] = __('Orga', 'export', 'valueExpressedInDefaultUnit');
            $columns[] = __('Orga', 'export', 'defaultUnit');
            foreach (array_values($columns) as $columnIndex => $column) {
                $granularitySheet->setCellValueByColumnAndRow($columnIndex, 1, $column);
            }
            $granularitySheetHighestColumn = $granularitySheet->getHighestColumn();
            $cellCoordinates = 'A1:' . $granularitySheetHighestColumn . '1';
            $granularitySheet->getStyle($cellCoordinates)->applyFromArray(
                [
                    'alignment' => [
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ],
                    'font' => [
                        'bold' => true,
                    ],
                ]
            );

            // Ajout des exports de chaque cellules.
            if ($cell->getGranularity() === $granularity) {
                if ($cell->getAFInputSetPrimary() !== null) {
                    $cells = [$cell];
                } else {
                    $cells = [];
                }
            } else {
                $criteria = new \Doctrine\Common\Collections\Criteria();
                $criteria->where($criteria->expr()->neq('aFInputSetPrimary', null));
                $criteria->orderBy(['tag' => 'ASC']);
                $cells = $cell->getChildCellsForGranularity($granularity)->matching($criteria)->toArray();
            }
            foreach ($cells as $cell) {
                $cellFile = $inputsExportsDirectory . $cell->getId() . '.' . $format;
                if (!file_exists($cellFile)) {
                    continue;
                }
                $cellInputsPHPExcel = PHPExcel_IOFactory::load($cellFile);
                $cellInputsEndDataRow = $cellInputsPHPExcel->getActiveSheet()->getHighestRow();
                if ($cellInputsEndDataRow < 2) {
                    continue;
                }
                $cellInputsEndData = $cellInputsPHPExcel->getActiveSheet()->getHighestColumn() . $cellInputsEndDataRow;
                $cellInputsData = $cellInputsPHPExcel->getActiveSheet()->rangeToArray('A2:' . $cellInputsEndData);
                $granularitySheet->fromArray($cellInputsData, null, 'A' . ($granularitySheet->getHighestRow() + 1));
                $cellInputsPHPExcel->disconnectWorksheets();
                unset($cellInputsPHPExcel);
            }

            foreach (array_values($columns) as $columnIndex => $column) {
                $granularitySheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($columnIndex))->setAutoSize(true);
            }
        }

        switch ($format) {
            case 'xls':
                $writer = new PHPExcel_Writer_Excel5();
                break;
            case 'xlsx':
            default:
                $writer = new PHPExcel_Writer_Excel2007();
                break;
        }

        $writer->setPHPExcel($phpExcelModel);
        $writer->save('php://output');
    }

    /**
     * Exporte les Outputs de la version de orga.
     *
     * @param string $format
     * @param Orga_Model_Cell $cell
     */
    public function streamOutputs($format, Orga_Model_Cell $cell)
    {
        $modelBuilder = new SpreadsheetModelBuilder();
        $export = new PHPExcelExporter();

        $modelBuilder->bind('cell', $cell);
        $modelBuilder->bind('populatingCells', $cell->getPopulatingCells());

        $modelBuilder->bind('indicators', Classif_Model_Indicator::loadList());

        $queryOrganizationAxes = new Core_Model_Query();
        $queryOrganizationAxes->filter->addCondition(Orga_Model_Axis::QUERY_ORGANIZATION, $cell->getGranularity()->getOrganization());
        $queryOrganizationAxes->order->addOrder(Orga_Model_Axis::QUERY_NARROWER);
        $queryOrganizationAxes->order->addOrder(Orga_Model_Axis::QUERY_POSITION);
        $orgaAxes = [];
        foreach ($cell->getGranularity()->getOrganization()->getAxes() as $organizationAxis) {
            foreach ($cell->getGranularity()->getAxes() as $granularityAxis) {
                if ($organizationAxis->isNarrowerThan($granularityAxis)) {
                    continue;
                } elseif (!($organizationAxis->isTransverse([$granularityAxis]))) {
                    continue 2;
                }
            }
            $orgaAxes[] = $organizationAxis;
        }
        $modelBuilder->bind('orgaAxes', $orgaAxes);

        $modelBuilder->bind('classifAxes', Classif_Model_Axis::loadListOrderedAsAscendantTree());

        $modelBuilder->bind('inputStatus', __('Orga', 'input', 'inputStatus'));
        $modelBuilder->bind('resultLabel', __('UI', 'name', 'label'));
        $modelBuilder->bind('resultFreeLabel', __('AF', 'inputInput', 'freeLabel'));
        $modelBuilder->bind('resultValue', __('UI', 'name', 'value'));
        $modelBuilder->bind('resultRoundedValue', __('Orga', 'export', 'roundedValue'));
        $modelBuilder->bind('resultUncertainty', __('UI', 'name', 'uncertainty') . ' (%)');

        $modelBuilder->bindFunction(
            'getOutputsForIndicator',
            function (Orga_Model_Cell $cell, Classif_Model_Indicator $indicator) {
                $results = [];
                if (($cell->getAFInputSetPrimary() !== null) && ($cell->getAFInputSetPrimary()->getOutputSet() !== null)) {
                    foreach ($cell->getAFInputSetPrimary()->getOutputSet()->getElements() as $result) {
                        if ($result->getContextIndicator()->getIndicator() === $indicator) {
                            $results[] = $result;
                        }
                    }
                }
                return $results;
            }
        );

        $modelBuilder->bindFunction(
            'displayMemberForOrgaAxis',
            function (Orga_Model_Cell $cell, Orga_Model_Axis $axis) {
                foreach ($cell->getMembers() as $cellMember) {
                    if ($cellMember->getAxis() === $axis) {
                        return $cellMember->getExtendedLabel();
                    } else if ($cellMember->getAxis()->isNarrowerThan($axis)) {
                        try {
                            return $cellMember->getParentForAxis($axis)->getExtendedLabel();
                        } catch (Core_Exception_NotFound $e) {
                            // Pas de parent pour cet axe.
                        }
                    }
                }
                return '';
            }
        );

        $modelBuilder->bindFunction(
            'displayMemberForClassifAxis',
            function (AF_Model_Output_Element $output, Classif_Model_Axis $axis) {
                try {
                    $member = $output->getIndexForAxis($axis)->getMember();
                    if ($member->getAxis() !== $axis) {
                        foreach ($member->getAllParents() as $parentMember) {
                            if ($parentMember->getAxis() === $axis) {
                                $member = $parentMember;
                                break;
                            }
                        }
                    }
                    return $member->getLabel();
                } catch (Core_Exception_NotFound $e) {
                    // Pas d'indexation suivant cet axe.
                }
                return '';
            }
        );

        $modelBuilder->bindFunction(
            'displayInputStatus',
            function (Orga_Model_Cell $cell) {
                switch ($cell->getAFInputSetPrimary()->getStatus()) {
                    case AF_Model_InputSet_Primary::STATUS_FINISHED:
                        return __('AF', 'inputInput', 'statusFinished');
                        break;
                    case AF_Model_InputSet_Primary::STATUS_COMPLETE:
                        return __('AF', 'inputInput', 'statusComplete');
                        break;
                    case AF_Model_InputSet_Primary::STATUS_CALCULATION_INCOMPLETE:
                        return __('AF', 'inputInput', 'statusCalculationIncomplete');
                        break;
                    case AF_Model_InputSet_Primary::STATUS_INPUT_INCOMPLETE;
                        return __('AF', 'inputInput', 'statusInputIncomplete');
                        break;
                    default:
                        return '';
                }
            }
        );

        $modelBuilder->bindFunction(
            'displayFreeLabel',
            function (AF_Model_Output_Element $output) {
                if ($output->getInputSet() instanceof AF_Model_InputSet_Sub) {
                    return $output->getInputSet()->getFreeLabel();
                }
                return '';
            }
        );

        $modelBuilder->bindFunction(
            'displayRoundedValue',
            function ($value) {
                return round($value, floor(3 - log10(abs($value))));
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
            $modelBuilder->build(new YamlMappingReader(__DIR__.'/exports/outputs.yml')),
            'php://output',
            $writer
        );
    }

}

function getInputsDetails(AF_Model_Input $input, $path = '')
{
    if ($input->getComponent() !== null) {
        $componentLabel = $input->getComponent()->getLabel();
        $componentRef = $input->getComponent()->getRef();
    } else {
        $componentLabel = __('Orga', 'export', 'unknowComponent', ['COMPONENT' => $input->getRefComponent()]);
        $componentRef = __('Orga', 'export', 'unknowComponent', ['COMPONENT' => $input->getRefComponent()]);
    }
    if ($input instanceof AF_Model_Input_SubAF_NotRepeated) {
        $subInputs = [];
        foreach ($input->getValue()->getInputs() as $subInput) {
            if (!$subInput instanceof AF_Model_Input_Group) {
                $subInputs = array_merge(
                    $subInputs,
                    getInputsDetails($subInput, $path . $componentLabel . '/')
                );
            }
        }
        return $subInputs;
    } elseif ($input instanceof AF_Model_Input_SubAF_Repeated) {
        $subInputs = [];
        foreach ($input->getValue() as $number => $subInputSet) {
            foreach ($subInputSet->getInputs() as $subInput) {
                if (!$subInput instanceof AF_Model_Input_Group) {
                    $subInputs = array_merge(
                        $subInputs,
                        getInputsDetails($subInput, $path . $componentLabel . '/' . ($number + 1) . ' / ')
                    );
                }
            }
        }
        return $subInputs;
    } else {
        $a = [
            'ancestors' => $path,
            'label' => $componentLabel,
            'ref' => $componentRef,
            'type' => getInputType($input),
            'values' => getInputValues($input)
        ];
        return [$a];
    }
}

function getInputType(AF_Model_Input $input) {
    switch (get_class($input)) {
        case 'AF_Model_Input_Checkbox':
            return __('Orga', 'export', 'checkboxField');
        case 'AF_Model_Input_Select_Single':
            return __('Orga', 'export', 'singleSelectField');
        case 'AF_Model_Input_Select_Multi':
            return __('Orga', 'export', 'multiSelectField');
        case 'AF_Model_Input_Text':
            return __('Orga', 'export', 'textField');
        case 'AF_Model_Input_Numeric':
            return __('Orga', 'export', 'numericField');
        default:
            return __('Orga', 'export', 'unknownFieldType');
    }
}

function getInputValues(AF_Model_Input $input)
{
    $inputValue = $input->getValue();
    switch (get_class($input)) {
        case 'AF_Model_Input_Numeric':
            /** @var AF_Model_Input_Numeric $input */
            if ($input->getComponent() !== null) {
                try {
                    $conversionFactor = $input->getComponent()->getUnit()->getConversionFactor($inputValue->getUnit()->getRef());
                    $baseConvertedValue = $inputValue->copyWithNewValue($inputValue->getDigitalValue() * $conversionFactor);
                    return [
                        $inputValue->getDigitalValue(),
                        $inputValue->getRelativeUncertainty(),
                        $inputValue->getUnit()->getSymbol(),
                        $baseConvertedValue->getDigitalValue(),
                        $baseConvertedValue->getUnit()->getSymbol(),
                    ];
                } catch (\Unit\IncompatibleUnitsException $e) {
                    return [
                        $inputValue->getDigitalValue(),
                        $inputValue->getRelativeUncertainty(),
                        $inputValue->getUnit()->getSymbol(),
                    ];
                }
            }
            return [
                $inputValue->getDigitalValue(),
                $inputValue->getRelativeUncertainty(),
                $inputValue->getUnit()->getSymbol(),
            ];
        case 'AF_Model_Input_Select_Multi':
            /** @var AF_Model_Input_Select_Multi $input */
            if (is_array($inputValue)) {
                if ($input->getComponent() !== null) {
                    $labels = [];
                    foreach ($inputValue as $value) {
                        if (empty($value)) {
                            $labels[] = '';
                        } else {
                            $labels[] = $input->getComponent()->getOptionByRef($value)->getLabel();
                        }
                    }
                    return [implode(', ', $labels)];
                }
                return [implode(', ', $inputValue)];
            }
        case 'AF_Model_Input_Select_Single':
            /** @var AF_Model_Input_Select_Single $input */
            if (empty($value)) {
                return [''];
            } elseif ($input->getComponent() !== null) {
                return [$input->getComponent()->getOptionByRef($value)->getLabel()];
            }
            return [$value];
        case 'AF_Model_Input_Checkbox':
            /** @var AF_Model_Input_Checkbox $input */
            return [($inputValue) ? __('UI', 'property', 'checked') : __('UI', 'property', 'unchecked')];
        default:
            return [$inputValue];
    }
}
