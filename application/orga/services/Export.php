<?php

use AF\Domain\Component\NumericField;
use AF\Domain\Component\Select\SelectMulti;
use AF\Domain\Component\Select\SelectSingle;
use AF\Domain\Input\Input;
use AF\Domain\Input\TextFieldInput;
use AF\Domain\Input\NumericFieldInput;
use AF\Domain\Input\GroupInput;
use AF\Domain\Input\CheckboxInput;
use AF\Domain\Input\Select\SelectSingleInput;
use AF\Domain\Input\Select\SelectMultiInput;
use AF\Domain\Input\SubAF\RepeatedSubAFInput;
use AF\Domain\Input\SubAF\NotRepeatedSubAFInput;
use AF\Domain\InputSet\PrimaryInputSet;
use AF\Domain\InputSet\SubInputSet;
use AF\Domain\Output\OutputElement;
use Classification\Domain\Axis;
use Classification\Domain\Indicator;
use Mnapoli\Translated\AbstractTranslatedString;
use Mnapoli\Translated\Translator;
use Orga\Model\ACL\AbstractCellRole;
use MyCLabs\UnitAPI\Exception\IncompatibleUnitsException;
use Xport\Spreadsheet\Builder\SpreadsheetModelBuilder;
use Xport\Spreadsheet\Exporter\PHPExcelExporter;
use Xport\MappingReader\YamlMappingReader;

/**
 * @author valentin.claras
 */
class Orga_Service_Export
{
    /**
     * @var Translator
     */
    private $translator;

    /**
     * Constructeur, augmente la limite de mémoire à 2G pour réaliser l'export.
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;

        ini_set('memory_limit', '2G');
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
        $modelBuilder->bind(
            'organizationColumnGranularityForInventoryStatus',
            __('Orga', 'configuration', 'granularityForInventoryStatus')
        );
        $modelBuilder->bind(
            'organizationInputGranularityColumnInput',
            __('Orga', 'inputGranularities', 'inputGranularity')
        );
        $modelBuilder->bind(
            'organizationInputGranularityColumnInputConfig',
            __('Orga', 'inputGranularities', 'inputConfigGranularity')
        );

        // Feuille des Axis.
        $modelBuilder->bind('axesSheetLabel', __('UI', 'name', 'axes'));

        $modelBuilder->bind('axisColumnLabel', __('UI', 'name', 'label'));
        $modelBuilder->bind('axisColumnRef', __('UI', 'name', 'identifier'));
        $modelBuilder->bind('axisColumnNarrower', __('Classification', 'export', 'axisColumnNarrower'));
        $modelBuilder->bindFunction(
            'displayAxisDirectNarrower',
            function (Orga_Model_Axis $axis) {
                if ($axis->getDirectNarrower() !== null) {
                    return $this->translator->get($axis->getDirectNarrower()->getLabel())
                        . ' (' . $axis->getDirectNarrower()->getRef() . ')';
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
                        return $this->translator->get($directParent->getLabel());
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
            'filterRelevanceGranularities',
            function ($granularities) {
                $relevanceGranularities = [];
                /** @var Orga_Model_Granularity $granularity */
                foreach ($granularities as $granularity) {
                    if ($granularity->getCellsControlRelevance()) {
                        $relevanceGranularities[] = $granularity;
                    }
                }
                return $relevanceGranularities;
            }
        );
        $modelBuilder->bindFunction(
            'filterAllParentsRelevantCells',
            function ($cells) {
                $allParentsRelevantCells = [];
                /** @var Orga_Model_Cell $cell */
                foreach ($cells as $cell) {
                    if ($cell->getAllParentsRelevant()) {
                        $allParentsRelevantCells[] = $cell;
                    }
                }
                return $allParentsRelevantCells;
            }
        );
        $modelBuilder->bindFunction(
            'displayCellMemberForAxis',
            function (Orga_Model_Cell $cell, Orga_Model_Axis $axis) {
                foreach ($cell->getMembers() as $member) {
                    if ($member->getAxis() === $axis) {
                        return $this->translator->get($member->getLabel());
                    }
                }
                return '';
            }
        );

        $modelBuilder->bindFunction(
            'translateString',
            function (AbstractTranslatedString $string) {
                return $this->translator->get($string);
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
                foreach ($organization->getLastOrderedAxes() as $organizationAxis) {
                    foreach ($cell->getMembers() as $member) {
                        if ($organizationAxis->isBroaderThan($member->getAxis())) {
                            continue 2;
                        }
                    }
                    if (!$organizationAxis->isTransverse($cell->getGranularity()->getAxes())) {
                        $axes[] = $organizationAxis;
                    }
                }
                return $axes;
            }
        );
        $modelBuilder->bindFunction(
            'getCellNarrowerMembers',
            function (Orga_Model_Cell $cell, Orga_Model_Axis $axis) {
                return $cell->getChildMembersForAxes([$axis])[$axis->getRef()];
            }
        );
        $modelBuilder->bindFunction(
            'displayParentMemberForAxis',
            function (Orga_Model_member $member, Orga_Model_Axis $broaderAxis) {
                foreach ($member->getDirectParents() as $directParent) {
                    if ($directParent->getAxis() === $broaderAxis) {
                        return $this->translator->get($directParent->getLabel());
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
            'filterRelevanceGranularities',
            function ($granularities) {
                $relevanceGranularities = [];
                /** @var Orga_Model_Granularity $granularity */
                foreach ($granularities as $granularity) {
                    if ($granularity->getCellsControlRelevance()) {
                        $relevanceGranularities[] = $granularity;
                    }
                }
                return $relevanceGranularities;
            }
        );
        $modelBuilder->bindFunction(
            'getChildCellsForGranularity',
            function (Orga_Model_Cell $cell, Orga_Model_Granularity $granularity) {
                $allParentsRelevantCells = [];
                foreach ($cell->getChildCellsForGranularity($granularity) as $childCell) {
                    if ($childCell->getAllParentsRelevant()) {
                        $allParentsRelevantCells[] = $childCell;
                    }
                }
                return $allParentsRelevantCells;
            }
        );
        $modelBuilder->bindFunction(
            'displayCellMemberForAxis',
            function (Orga_Model_Cell $cell, Orga_Model_Axis $axis) {
                foreach ($cell->getMembers() as $member) {
                    if ($member->getAxis() === $axis) {
                        return $this->translator->get($member->getLabel());
                    }
                }
                return '';
            }
        );

        $modelBuilder->bindFunction(
            'translateString',
            function (AbstractTranslatedString $string) {
                return $this->translator->get($string);
            }
        );


        $type = 'cell';
        foreach ($cell->getGranularity()->getNarrowerGranularities() as $narrowerGranularity) {
            if ($narrowerGranularity->getCellsControlRelevance()) {
                $type = 'relevance';
                break;
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

        $export->export(
            $modelBuilder->build(new YamlMappingReader(__DIR__.'/exports/'.$type.'.yml')),
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
                    $users[] = ['user' => $role->getSecurityIdentity(), 'role' => $role];
                }
                return $users;
            }
        );
        $modelBuilder->bind('userColumnFirstName', __('User', 'user', 'firstName'));
        $modelBuilder->bind('userColumnLastName', __('User', 'user', 'lastName'));
        $modelBuilder->bind('userColumnEmail', __('User', 'user', 'emailAddress'));
        $modelBuilder->bind('userColumnRole', __('User', 'role', 'role'));
        $modelBuilder->bindFunction(
            'displayCellMemberForAxis',
            function (Orga_Model_Cell $cell, Orga_Model_Axis $axis) {
                foreach ($cell->getMembers() as $member) {
                    if ($member->getAxis() === $axis) {
                        return $this->translator->get($member->getLabel());
                    }
                }
                return '';
            }
        );
        $modelBuilder->bindFunction(
            'displayRoleName',
            function (AbstractCellRole $role) {
                return $role->getLabel();
            }
        );

        $modelBuilder->bindFunction(
            'translateString',
            function (AbstractTranslatedString $string) {
                return $this->translator->get($string);
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
            if (!$input instanceof GroupInput) {
                $inputs = array_merge($inputs, $this->getInputsDetails($input, $this->translator));
            }
        }

        $modelBuilder = new SpreadsheetModelBuilder();
        $export = new PHPExcelExporter();

        $modelBuilder->bind('cell', $cell);
        $modelBuilder->bind('inputs', $inputs);

        $modelBuilder->bind('inputAncestor', __('Orga', 'export', 'subForm'));
        $modelBuilder->bind('inputStatus', __('Orga', 'input', 'inputStatus'));
        $modelBuilder->bind('inputLabel', __('Orga', 'export', 'fieldLabel'));
        $modelBuilder->bind('inputRef', __('Orga', 'export', 'fieldRef'));
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
                        return $this->translator->get($member->getLabel());
                    } elseif ($member->getAxis()->isNarrowerThan($axis)) {
                        return $this->translator->get($member->getParentForAxis($axis)->getLabel());
                    }
                }
                return '';
            }
        );

        $modelBuilder->bindFunction(
            'displayInputStatus',
            function (Orga_Model_Cell $cell) {
                switch ($cell->getAFInputSetPrimary()->getStatus()) {
                    case PrimaryInputSet::STATUS_FINISHED:
                        return __('AF', 'inputInput', 'statusFinished');
                        break;
                    case PrimaryInputSet::STATUS_COMPLETE:
                        return __('AF', 'inputInput', 'statusComplete');
                        break;
                    case PrimaryInputSet::STATUS_CALCULATION_INCOMPLETE:
                        return __('AF', 'inputInput', 'statusCalculationIncomplete');
                        break;
                    case PrimaryInputSet::STATUS_INPUT_INCOMPLETE:
                        return __('AF', 'inputInput', 'statusInputIncomplete');
                        break;
                    default:
                        return '';
                }
            }
        );

        $modelBuilder->bindFunction(
            'translateString',
            function (AbstractTranslatedString $string) {
                return $this->translator->get($string);
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
            $granularitySheet->setTitle(mb_substr($this->translator->get($granularity->getLabel()), 0, 31));

            // Colonnes
            $columns = [];
            foreach ($granularity->getAxes() as $axis) {
                $columns[] = $this->translator->get($axis->getLabel());
                foreach ($axis->getAllBroadersFirstOrdered() as $broaderAxis) {
                    $columns[] = $this->translator->get($broaderAxis->getLabel());
                }
            };
            $columns[] = __('Orga', 'export', 'subForm');
            $columns[] = __('Orga', 'input', 'inputStatus');
            $columns[] = __('Orga', 'export', 'fieldLabel');
            $columns[] = __('Orga', 'export', 'fieldRef');
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
                    $childCells = [$cell];
                } else {
                    $childCells = [];
                }
            } else {
                $criteria = new \Doctrine\Common\Collections\Criteria();
                $criteria->where($criteria->expr()->neq('aFInputSetPrimary', null));
                $criteria->where($criteria->expr()->eq('relevant', true));
                $criteria->where($criteria->expr()->eq('allParentsRelevant', true));
                $criteria->orderBy(['tag' => 'ASC']);
                /** @var Orga_Model_Cell[] $childCells */
                $childCells = $cell->getChildCellsForGranularity($granularity)->matching($criteria);
            }
            foreach ($childCells as $childCell) {
                $childCellFile = $inputsExportsDirectory . $childCell->getId() . '.' . $format;
                if (!file_exists($childCellFile)) {
                    continue;
                }
                $childCellInputsPHPExcel = PHPExcel_IOFactory::load($childCellFile);
                $childCellInputsEndDataRow = $childCellInputsPHPExcel->getActiveSheet()->getHighestRow();
                if ($childCellInputsEndDataRow < 2) {
                    continue;
                }
                $childCellInputsEndData = $childCellInputsPHPExcel->getActiveSheet()->getHighestColumn()
                    . $childCellInputsEndDataRow;
                $childCellInputsData = $childCellInputsPHPExcel->getActiveSheet()
                    ->rangeToArray('A2:' . $childCellInputsEndData);
                $granularitySheet->fromArray($childCellInputsData, null, 'A' . ($granularitySheet->getHighestRow() + 1), true);
                $childCellInputsPHPExcel->disconnectWorksheets();
                unset($childCellInputsPHPExcel);
            }

            foreach (array_values($columns) as $columnIndex => $column) {
                $granularitySheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($columnIndex))
                    ->setAutoSize(true);
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

        $indicators = [];
        foreach ($cell->getOrganization()->getContextIndicators() as $contextIndicator) {
            $indicators[] = $contextIndicator->getIndicator();
        }
        $modelBuilder->bind('indicators', $indicators);

        $queryOrganizationAxes = new Core_Model_Query();
        $queryOrganizationAxes->filter->addCondition(
            Orga_Model_Axis::QUERY_ORGANIZATION,
            $cell->getGranularity()->getOrganization()
        );
        $queryOrganizationAxes->order->addOrder(Orga_Model_Axis::QUERY_NARROWER);
        $queryOrganizationAxes->order->addOrder(Orga_Model_Axis::QUERY_POSITION);
        $orgaAxes = [];
        foreach ($cell->getGranularity()->getOrganization()->getFirstOrderedAxes() as $organizationAxis) {
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

        $modelBuilder->bind('classifAxes', $cell->getOrganization()->getClassificationAxes());

        $modelBuilder->bind('inputStatus', __('Orga', 'input', 'inputStatus'));
        $modelBuilder->bind('resultLabel', __('UI', 'name', 'label'));
        $modelBuilder->bind('resultFreeLabel', __('AF', 'inputInput', 'freeLabel'));
        $modelBuilder->bind('resultValue', __('UI', 'name', 'value'));
        $modelBuilder->bind('resultRoundedValue', __('Orga', 'export', 'roundedValue'));
        $modelBuilder->bind('resultUncertainty', __('UI', 'name', 'uncertainty') . ' (%)');

        $modelBuilder->bindFunction(
            'getOutputsForIndicator',
            function (Orga_Model_Cell $cell, Indicator $indicator) {
                $results = [];
                if (($cell->getAFInputSetPrimary() !== null)
                    && ($cell->getAFInputSetPrimary()->getOutputSet() !== null)
                ) {
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
                        return $this->translator->get($cellMember->getExtendedLabel());
                    } elseif ($cellMember->getAxis()->isNarrowerThan($axis)) {
                        try {
                            return $this->translator->get($cellMember->getParentForAxis($axis)->getExtendedLabel());
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
            function (OutputElement $output, Axis $axis) {
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
                    return $this->translator->get($member->getLabel());
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
                    case PrimaryInputSet::STATUS_FINISHED:
                        return __('AF', 'inputInput', 'statusFinished');
                        break;
                    case PrimaryInputSet::STATUS_COMPLETE:
                        return __('AF', 'inputInput', 'statusComplete');
                        break;
                    case PrimaryInputSet::STATUS_CALCULATION_INCOMPLETE:
                        return __('AF', 'inputInput', 'statusCalculationIncomplete');
                        break;
                    case PrimaryInputSet::STATUS_INPUT_INCOMPLETE:
                        return __('AF', 'inputInput', 'statusInputIncomplete');
                        break;
                    default:
                        return '';
                }
            }
        );

        $modelBuilder->bindFunction(
            'displayFreeLabel',
            function (OutputElement $output) {
                $inputSet = $output->getInputSet();
                if ($inputSet instanceof SubInputSet) {
                    return $inputSet->getFreeLabel();
                }
                return '';
            }
        );

        $modelBuilder->bindFunction(
            'displayValue',
            function ($value) {
                if (preg_match('#\.\d+#', $value, $matches) === 1) {
                    return number_format($value, (strlen($matches[0]) - 1), '.', '');
                }
                return $value;
            }
        );

        $modelBuilder->bindFunction(
            'displayRoundedValue',
            function ($value) {
                return number_format(round($value, floor(3 - log10(abs($value)))), strlen($value), '.', '');
            }
        );

        $modelBuilder->bindFunction(
            'translateString',
            function (AbstractTranslatedString $string) {
                return $this->translator->get($string);
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

    private function getInputsDetails(Input $input, Translator $translator, $path = '')
    {
        if (($input->getComponent() !== null) && (!$input->isHidden())) {
            $componentLabel = $translator->get($input->getComponent()->getLabel());
            $componentRef = $input->getComponent()->getRef();
        } else {
            return [];
        }
        if ($input instanceof NotRepeatedSubAFInput) {
            $subInputs = [];
            foreach ($input->getValue()->getInputs() as $subInput) {
                if (!$subInput instanceof GroupInput) {
                    $subInputs = array_merge(
                        $subInputs,
                        $this->getInputsDetails($subInput, $translator, $path . $componentLabel . '/')
                    );
                }
            }
            return $subInputs;
        } elseif ($input instanceof RepeatedSubAFInput) {
            $subInputs = [];
            foreach ($input->getValue() as $number => $subInputSet) {
                foreach ($subInputSet->getInputs() as $subInput) {
                    if (!$subInput instanceof GroupInput) {
                        $label = ($number + 1) . ' - ' . $subInputSet->getFreeLabel();
                        $subInputs = array_merge(
                            $subInputs,
                            $this->getInputsDetails(
                                $subInput,
                                $path . $componentLabel . '/' . $label . '/'
                            )
                        );
                    }
                }
            }
            return $subInputs;
        } else {
            if (!$input->hasValue()) {
                return [];
            }
            return [
                [
                    'ancestors' => $path,
                    'status' => '',
                    'label' => $componentLabel,
                    'ref' => $componentRef,
                    'type' => $this->getInputType($input),
                    'values' => $this->getInputValues($input, $translator)
                ]
            ];
        }
    }

    /**
     * @param Input $input
     * @return string
     */
    private function getInputType(Input $input)
    {
        switch (get_class($input)) {
            case CheckboxInput::class:
                return __('Orga', 'export', 'checkboxField');
            case SelectSingleInput::class:
                return __('Orga', 'export', 'singleSelectField');
            case SelectMultiInput::class:
                return __('Orga', 'export', 'multiSelectField');
            case TextFieldInput::class:
                return __('Orga', 'export', 'textField');
            case NumericFieldInput::class:
                return __('Orga', 'export', 'numericField');
            default:
                return __('Orga', 'export', 'unknownFieldType');
        }
    }

    private function getInputValues(Input $input, Translator $translator)
    {
        $inputValue = $input->getValue();

        switch (true) {
            case $input instanceof NumericFieldInput:
                /** @var Calc_UnitValue $inputValue */
                $inputDigitalValue = $inputValue->getDigitalValue();
                if (preg_match('{\.\d+}', $inputDigitalValue, $matches)===1) {
                    $inputDigitalValue = number_format($inputDigitalValue, (strlen($matches[0]) - 1), '.', '');
                }
                /** @var NumericField $component */
                $component = $input->getComponent();
                if ($component !== null) {
                    try {
                        $baseConvertedValue = $inputValue->convertTo($component->getUnit());
                        return [
                            $inputDigitalValue,
                            $inputValue->getRelativeUncertainty(),
                            $translator->get($inputValue->getUnit()->getSymbol()),
                            $baseConvertedValue->getDigitalValue(),
                            $translator->get($component->getUnit()->getSymbol()),
                        ];
                    } catch (IncompatibleUnitsException $e) {
                        return [
                            $inputDigitalValue,
                            $inputValue->getRelativeUncertainty(),
                            $translator->get($inputValue->getUnit()->getSymbol()),
                        ];
                    }
                }
                return [
                    $inputDigitalValue,
                    $inputValue->getRelativeUncertainty(),
                    $inputValue->getUnit()->getSymbol(),
                ];

            case $input instanceof SelectMultiInput:
                /** @var SelectMulti $component */
                $component = $input->getComponent();
                if (is_array($inputValue)) {
                    if ($component !== null) {
                        $labels = [];
                        foreach ($inputValue as $value) {
                            if (empty($value)) {
                                $labels[] = '';
                            } else {
                                $labels[] = $translator->get($component->getOptionByRef($value)->getLabel());
                            }
                        }
                        return [implode(', ', $labels)];
                    }
                    return [implode(', ', $inputValue)];
                }
                break;

            case $input instanceof SelectSingleInput:
                /** @var SelectSingle $component */
                $component = $input->getComponent();
                if (empty($inputValue)) {
                    return [''];
                } elseif ($component !== null) {
                    return [$translator->get($component->getOptionByRef($inputValue)->getLabel())];
                }
                return [$inputValue];

            case $input instanceof CheckboxInput:
                return [($inputValue) ? __('UI', 'property', 'checked') : __('UI', 'property', 'unchecked')];
        }

        return [$inputValue];
    }
}
