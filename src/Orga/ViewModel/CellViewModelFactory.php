<?php

namespace Orga\ViewModel;

use Core_Exception_UndefinedAttribute;
use Doctrine\Common\Collections\Criteria;
use Orga_Model_Cell;
use Orga_Model_Member;
use User\Domain\ACL\Action;
use User\Domain\User;
use User\Domain\ACL\ACLService;
use Orga\Model\ACL\Action\CellAction;
use UI_HTML_Image;
use AF_Model_InputSet_Primary;

/**
 * Factory de CellViewModel.
 */
class CellViewModelFactory
{
    /**
     * @var ACLService
     */
    private $aclService;

    /**
     * @var array
     */
    public $inventoryStatusList;

    /**
     * @var array
     */
    public $inventoryStatusStyles;

    /**
     * @var array
     */
    public $inputStatusList;

    /**
     * @var array
     */
    public $inputStatusStyles;

    /**
     * @param ACLService $aclService
     */
    public function __construct(ACLService $aclService)
    {
        $this->aclService = $aclService;

        $this->inventoryStatusList = [
            Orga_Model_Cell::STATUS_NOTLAUNCHED => __('Orga', 'inventory', 'notLaunched'),
            Orga_Model_Cell::STATUS_ACTIVE => __('UI', 'property', 'open'),
            Orga_Model_Cell::STATUS_CLOSED => __('UI', 'property', 'closed')
        ];
        $this->inventoryStatusStyles = [
            Orga_Model_Cell::STATUS_NOTLAUNCHED => 'inverse',
            Orga_Model_Cell::STATUS_ACTIVE => 'info',
            Orga_Model_Cell::STATUS_CLOSED => 'info'
        ];
        $this->inputStatusList = [
            AF_Model_InputSet_Primary::STATUS_FINISHED => __('AF', 'inputInput', 'statusFinished'),
            AF_Model_InputSet_Primary::STATUS_COMPLETE => __('AF', 'inputInput', 'statusComplete'),
            AF_Model_InputSet_Primary::STATUS_CALCULATION_INCOMPLETE => __('AF', 'inputInput', 'statusCalculationIncomplete'),
            AF_Model_InputSet_Primary::STATUS_INPUT_INCOMPLETE => __('AF', 'inputInput', 'statusInputIncomplete'),
            null => __('AF', 'inputInput', 'statusNotStarted')
        ];
        $this->inputStatusStyles = [
            AF_Model_InputSet_Primary::STATUS_FINISHED => 'success',
            AF_Model_InputSet_Primary::STATUS_COMPLETE => 'warning',
            AF_Model_InputSet_Primary::STATUS_CALCULATION_INCOMPLETE => 'danger',
            AF_Model_InputSet_Primary::STATUS_INPUT_INCOMPLETE => 'danger',
            null => 'danger'
        ];
    }

    /**
     * @param Orga_Model_Cell $cell
     * @param User $user
     * @param bool $withAdministrators
     * @param bool $withACL
     * @param bool $withReports
     * @param bool $withExports
     * @param bool $withInventory
     * @param bool $editInventory
     * @param bool $withInput
     * @return CellViewModel
     */
    public function createCellViewModel(Orga_Model_Cell $cell, User $user,
        $withAdministrators=null, $withACL=null, $withReports=null, $withExports=null,
        $withInventory=null, $editInventory=null, $withInput=null)
    {
        $cellViewModel = new CellViewModel();
        $cellViewModel->id = $cell->getId();
        $cellViewModel->shortLabel = $cell->getLabel();
        $cellViewModel->extendedLabel = $cell->getExtendedLabel();
        $cellViewModel->relevant = $cell->isRelevant();
        $cellViewModel->tag = $cell->getTag();

        // Administrateurs.
        if ($withAdministrators === true) {
            foreach ($cell->getAdminRoles() as $administrator) {
                array_unshift($cellViewModel->administrators, $administrator->getUser()->getEmail());
            }
            foreach (array_reverse($cell->getParentCells()) as $parentCell) {
                foreach ($parentCell->getAdminRoles() as $parentAdministrator) {
                    array_unshift($cellViewModel->administrators, $parentAdministrator->getUser()->getEmail());
                }
            }
            foreach ($cell->getOrganization()->getAdminRoles() as $organizationAdministrator) {
                array_unshift($cellViewModel->administrators, $organizationAdministrator->getUser()->getEmail());
            }
        }

        // Utilisateurs.
        if (($withACL === true)
            || (($withACL !== false)
                && ($cell->getGranularity()->getCellsWithACL())
                && ($this->aclService->isAllowed($user, Action::ALLOW(), $cell)))
        ) {
            $cellViewModel->showsUsers = true;
        }

        // Reports.
        if (($withReports === true)
            || (($withReports !== false)
                && ($cell->getGranularity()->getCellsGenerateDWCubes())
                && ($this->aclService->isAllowed($user, CellAction::VIEW_REPORTS(), $cell)))
        ) {
            $cellViewModel->showReports = true;
        }

        // Exports.
        if (($withExports === true)
            || (($withExports !== false)
                && ($this->aclService->isAllowed($user, CellAction::VIEW_REPORTS(), $cell)))
        ) {
            $cellViewModel->showExports = true;
        }

        // Inventory
        $cellViewModel->inventoryStatus = $cell->getInventoryStatus();
        if (($withInventory === true)
            || (($withInventory !== false)
                && (($this->aclService->isAllowed($user, CellAction::VIEW_REPORTS(), $cell))))
        ) {
            try {
                $granularityForInventoryStatus = $cell->getGranularity()->getOrganization()->getGranularityForInventoryStatus();

                if (($editInventory)
                    || (($cell->getGranularity() === $granularityForInventoryStatus)
                        && ($this->aclService->isAllowed($user, Action::EDIT(), $cell)))) {
                    $cellViewModel->canEditInventory = true;
                }

                if (($cell->getGranularity() === $granularityForInventoryStatus)
                    || ($cell->getGranularity()->isNarrowerThan($granularityForInventoryStatus))
                ) {
                    $cellViewModel->showInventory = true;

                    $cellViewModel->inventoryStatusTitle = $this->inventoryStatusList[$cellViewModel->inventoryStatus];
                    $cellViewModel->inventoryStatusStyle = $this->inventoryStatusStyles[$cellViewModel->inventoryStatus];

                    $cellViewModel->inventoryCompletion = 0;
                    $cellViewModel->inventoryNotStartedInputsNumber = 0;
                    $cellViewModel->inventoryStartedInputsNumber = 0;
                    $cellViewModel->inventoryCompletedInputsNumber = 0;
                    if (($cell->getGranularity()->isNarrowerThan($granularityForInventoryStatus)
                        || ($cell->getGranularity() === $granularityForInventoryStatus))
                        && ($cell->getGranularity()->getInputConfigGranularity() !== null)) {
                        if ($cell->getAFInputSetPrimary() !== null) {
                            $cellViewModel->inventoryCompletion += $cell->getAFInputSetPrimary()->getCompletion();
                            if ($cell->getAFInputSetPrimary()->getCompletion() == 0) {
                                $cellViewModel->inventoryNotStartedInputsNumber ++;
                            } else if ($cell->getAFInputSetPrimary()->getCompletion() < 100) {
                                $cellViewModel->inventoryStartedInputsNumber ++;
                            } else {
                                $cellViewModel->inventoryCompletedInputsNumber ++;
                            }
                        } else {
                            $cellViewModel->inventoryNotStartedInputsNumber ++;
                        }
                    }
                    foreach ($cell->getGranularity()->getNarrowerGranularities() as $narrowerInputGranularity) {
                        if (($narrowerInputGranularity->getInputConfigGranularity() !== null)
                            && ($narrowerInputGranularity->isNarrowerThan($granularityForInventoryStatus))) {
                            $relevantCriteria = new Criteria();
                            $relevantCriteria->where($relevantCriteria->expr()->eq(Orga_Model_Cell::QUERY_ALLPARENTSRELEVANT, true));
                            $relevantCriteria->andWhere($relevantCriteria->expr()->eq(Orga_Model_Cell::QUERY_RELEVANT, true));
                            $relevantChildInputCells = $cell->getChildCellsForGranularity($narrowerInputGranularity)->matching($relevantCriteria);
                            /** @var Orga_Model_Cell $childInputCell */
                            foreach ($relevantChildInputCells as $childInputCell) {
                                $childAFInputSetPrimary = $childInputCell->getAFInputSetPrimary();
                                if ($childAFInputSetPrimary !== null) {
                                    $cellViewModel->inventoryCompletion += $childInputCell->getAFInputSetPrimary()->getCompletion();
                                    if ($childInputCell->getAFInputSetPrimary()->getCompletion() == 0) {
                                        $cellViewModel->inventoryNotStartedInputsNumber ++;
                                    } else if ($childInputCell->getAFInputSetPrimary()->getCompletion() < 100) {
                                        $cellViewModel->inventoryStartedInputsNumber ++;
                                    } else {
                                        $cellViewModel->inventoryCompletedInputsNumber ++;
                                    }
                                } else {
                                    $cellViewModel->inventoryNotStartedInputsNumber ++;
                                }
                            }
                        }
                    }
                    $totalInventoryInputs = $cellViewModel->inventoryNotStartedInputsNumber + $cellViewModel->inventoryStartedInputsNumber + $cellViewModel->inventoryCompletedInputsNumber;
                    if ($totalInventoryInputs > 0) {
                        $cellViewModel->inventoryCompletion /= $totalInventoryInputs;
                    }
                    $cellViewModel->inventoryCompletion = round($cellViewModel->inventoryCompletion);
                }
            } catch (Core_Exception_UndefinedAttribute $e) {
            } catch (\Core_Exception_NotFound $e) {
            }
        }

        // Saisie.
        if (($withInput === true)
            || (($withInput !== false)
                && ($cell->getGranularity()->getInputConfigGranularity() !== null)
                && (($this->aclService->isAllowed($user, CellAction::INPUT(), $cell))))
        ) {
            $cellViewModel->showInput = true;

            $aFInputSetPrimary = $cell->getAFInputSetPrimary();
            if ($aFInputSetPrimary !== null) {
                $cellViewModel->inputStatus = $aFInputSetPrimary->getStatus();
                $cellViewModel->inputStatusTitle = $this->inputStatusList[$cellViewModel->inputStatus];
                $cellViewModel->inputStatusStyle = $this->inputStatusStyles[$cellViewModel->inputStatus];
                $cellViewModel->inputCompletion = $aFInputSetPrimary->getCompletion();
            } else {
                $cellViewModel->inputStatus = null;
                $cellViewModel->inputStatusTitle = $this->inputStatusList[null];
                $cellViewModel->inputStatusStyle = $this->inputStatusStyles[null];
                $cellViewModel->inputCompletion = 0;
            }
        }

        return $cellViewModel;
    }
}
