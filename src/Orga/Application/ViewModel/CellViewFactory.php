<?php

namespace Orga\Application\ViewModel;

use Core_Exception_UndefinedAttribute;
use Doctrine\Common\Collections\Criteria;
use Mnapoli\Translated\Translator;
use MyCLabs\ACL\ACL;
use Orga\Application\ViewModel\CellView;
use User\Domain\ACL\Actions;
use Orga\Domain\Cell;
use User\Domain\User;
use AF\Domain\InputSet\PrimaryInputSet;

/**
 * Factory de CellView.
 */
class CellViewFactory
{
    /**
     * @var ACL
     */
    private $acl;

    /**
     * @var array
     */
    public $inventoryStatusList;

    /**
     * @var array
     */
    public $inputStatusList;

    /**
     * @var Translator
     */
    private $translator;


    public function __construct(ACL $acl, Translator $translator)
    {
        $this->acl = $acl;
        $this->translator = $translator;

        $this->inventoryStatusList = [
            Cell::INVENTORY_STATUS_NOTLAUNCHED => __('Orga', 'view', 'inventoryNotLaunched'),
            Cell::INVENTORY_STATUS_ACTIVE => __('Orga', 'view', 'inventoryOpen'),
            Cell::INVENTORY_STATUS_CLOSED => __('Orga', 'view', 'inventoryClosed')
        ];
        $this->inputStatusList = [
            Cell::INPUT_STATUS_NOT_STARTED => __('Orga', 'view', 'statusNotStarted'),
            Cell::INPUT_STATUS_AF_NOT_CONFIGURED => __('Orga', 'view', 'statusAFNotConfigured'),
            Cell::INPUT_STATUS_INPUT_INCOMPLETE => __('AF', 'inputInput', 'statusInputIncomplete'),
            Cell::INPUT_STATUS_CALCULATION_INCOMPLETE => __('AF', 'inputInput', 'statusCalculationIncomplete'),
            Cell::INPUT_STATUS_COMPLETE => __('AF', 'inputInput', 'statusComplete'),
            Cell::INPUT_STATUS_FINISHED => __('AF', 'inputInput', 'statusFinished'),
        ];
    }

    /**
     * @param Cell $cell
     * @param User $user
     * @param bool $withAdministrators
     * @param bool $withACL
     * @param bool $withReports
     * @param bool $withExports
     * @param bool $withInventory
     * @param bool $withInventoryProgress
     * @param bool $editInventory
     * @param bool $withInput
     * @return CellView
     */
    public function createCellView(
        Cell $cell,
        User $user,
        $withAdministrators = null,
        $withACL = null,
        $withReports = null,
        $withExports = null,
        $withInventory = null,
        $withInventoryProgress = null,
        $editInventory = null,
        $withInput = null
    ) {
        $cellViewModel = new CellView();
        $cellViewModel->id = $cell->getId();
        $cellViewModel->shortLabel = $this->translator->get($cell->getLabel());
        $cellViewModel->extendedLabel = $this->translator->get($cell->getExtendedLabel());
        $cellViewModel->relevant = $cell->isRelevant();
        $cellViewModel->tag = $cell->getTag();

        foreach ($cell->getMembers() as $member) {
            $cellViewModel->members[$member->getAxis()->getRef()] = $this->translator->get($member->getLabel());
        }

        // Administrateurs.
        if ($withAdministrators === true) {
            foreach ($cell->getAdminRoles() as $administrator) {
                array_unshift($cellViewModel->administrators, $administrator->getSecurityIdentity()->getEmail());
            }
            foreach (array_reverse($cell->getParentCells()) as $parentCell) {
                /** @var Cell $parentCell */
                foreach ($parentCell->getAdminRoles() as $parentAdministrator) {
                    array_unshift($cellViewModel->administrators, $parentAdministrator->getSecurityIdentity()->getEmail());
                }
            }
            foreach ($cell->getWorkspace()->getAdminRoles() as $workspaceAdministrator) {
                array_unshift($cellViewModel->administrators, $workspaceAdministrator->getSecurityIdentity()->getEmail());
            }
        }

        // Utilisateurs.
        if (($withACL === true)
            || (($withACL !== false)
                && ($cell->getGranularity()->getCellsWithACL())
                && ($this->acl->isAllowed($user, Actions::ALLOW, $cell)))
        ) {
            $cellViewModel->showUsers = true;
            $cellViewModel->numberUsers = $cell->getAdminRoles()->count() + $cell->getManagerRoles()->count()
                + $cell->getContributorRoles()->count() + $cell->getObserverRoles()->count();
        }

        // Reports.
        if (($withReports === true)
            || (($withReports !== false)
                && ($cell->getGranularity()->getCellsGenerateDWCubes())
                && ($this->acl->isAllowed($user, Actions::ANALYZE, $cell)))
        ) {
            $cellViewModel->showReports = true;
        }

        // Exports.
        if (($withExports === true)
            || (($withExports !== false)
                && ($this->acl->isAllowed($user, Actions::ANALYZE, $cell)))
        ) {
            $cellViewModel->showExports = true;
        }

        // Inventory
        if (($withInventory === true)
            || ($withInventory !== false)
        ) {
            try {
                $granularityForInventoryStatus = $cell->getGranularity()->getWorkspace()->getGranularityForInventoryStatus();

                if (($withInventory === true)
                    || (($withInventory !== false)
                        && (($cell->getGranularity() === $granularityForInventoryStatus)
                            || ($cell->getGranularity()->isNarrowerThan($granularityForInventoryStatus))))
                ) {
                    $cellViewModel->showInventory = true;

                    $cellViewModel->inventoryStatus = $cell->getInventoryStatus();
                    $cellViewModel->inventoryStatusTitle = $this->inventoryStatusList[$cellViewModel->inventoryStatus];

                    if (($editInventory === true)
                        || (($editInventory !== false)
                            || (($cell->getGranularity() === $granularityForInventoryStatus)
                                && ($this->acl->isAllowed($user, Actions::EDIT, $cell))))
                    ) {
                        $cellViewModel->canEditInventory = true;
                    }

                    if (($withInventoryProgress !== true) && ($withInventoryProgress !== false)) {
                        $withInventoryProgress = true;
                        if ($withInventoryProgress) {
                            $narrowerGranularityHasACLParent = $cell->getGranularity()->getCellsWithACL();
                            if (!$narrowerGranularityHasACLParent) {
                                foreach ($cell->getGranularity()->getBroaderGranularities() as $broaderInventoryGranularity) {
                                    if ($broaderInventoryGranularity->getCellsWithACL()) {
                                        foreach ($cell->getGranularity()->getAxes() as $narrowerGranularityAxis) {
                                            if (!$granularityForInventoryStatus->hasAxis($narrowerGranularityAxis)
                                                && !$broaderInventoryGranularity->hasAxis($narrowerGranularityAxis)) {
                                                continue 2;
                                            }
                                        }
                                        $narrowerGranularityHasACLParent = true;
                                        break;
                                    }
                                }
                            }
                            $withInventoryProgress = $withInventoryProgress && $narrowerGranularityHasACLParent;
                        }
                        if ($withInventoryProgress) {
                            $narrowerGranularityHasSubInputGranlarities = false;
                            foreach ($cell->getGranularity()->getNarrowerGranularities() as $narrowerInventoryGranularity) {
                                if ($narrowerInventoryGranularity->getInputConfigGranularity() !== null) {
                                    $narrowerGranularityHasSubInputGranlarities = true;
                                    break;
                                }
                            }
                            $withInventoryProgress = $withInventoryProgress && $narrowerGranularityHasSubInputGranlarities;
                        }
                    }
                    if ($withInventoryProgress === true) {
                        $cellViewModel->showInventoryProgress = true;

                        $cellViewModel->inventoryNotStartedInputsNumber = 0;
                        $cellViewModel->inventoryStartedInputsNumber = 0;
                        $cellViewModel->inventoryFinishedInputsNumber = 0;
                        if (($cell->getGranularity()->isNarrowerThan($granularityForInventoryStatus)
                            || ($cell->getGranularity() === $granularityForInventoryStatus))
                            && ($cell->getGranularity()->getInputConfigGranularity() !== null)) {
                            if ($cell->getAFInputSetPrimary() !== null) {
                                if ($cell->getAFInputSetPrimary()->isFinished()) {
                                    $cellViewModel->inventoryFinishedInputsNumber ++;
                                } else if ($cell->getAFInputSetPrimary()->getCompletion() == 0) {
                                    $cellViewModel->inventoryNotStartedInputsNumber ++;
                                } else {
                                    $cellViewModel->inventoryStartedInputsNumber ++;
                                }
                            } else {
                                $cellViewModel->inventoryNotStartedInputsNumber ++;
                            }
                        }
                        foreach ($cell->getGranularity()->getNarrowerGranularities() as $narrowerInputGranularity) {
                            if (($narrowerInputGranularity->getInputConfigGranularity() !== null)
                                && ($narrowerInputGranularity->isNarrowerThan($granularityForInventoryStatus))) {
                                $relevantCriteria = new Criteria();
                                $relevantCriteria->where($relevantCriteria->expr()->eq(Cell::QUERY_ALLPARENTSRELEVANT, true));
                                $relevantCriteria->andWhere($relevantCriteria->expr()->eq(Cell::QUERY_RELEVANT, true));
                                $relevantChildInputCells = $cell->getChildCellsForGranularity($narrowerInputGranularity)->matching($relevantCriteria);
                                /** @var \Orga\Domain\Cell $childInputCell */
                                foreach ($relevantChildInputCells as $childInputCell) {
                                    $childAFInputSetPrimary = $childInputCell->getAFInputSetPrimary();
                                    if ($childAFInputSetPrimary !== null) {
                                        if ($childInputCell->getAFInputSetPrimary()->isFinished()) {
                                            $cellViewModel->inventoryFinishedInputsNumber ++;
                                        } else if ($childInputCell->getAFInputSetPrimary()->getCompletion() == 0) {
                                            $cellViewModel->inventoryNotStartedInputsNumber ++;
                                        } else {
                                            $cellViewModel->inventoryStartedInputsNumber ++;
                                        }
                                    } else {
                                        $cellViewModel->inventoryNotStartedInputsNumber ++;
                                    }
                                }
                            }
                        }
                        $totalInventoryInputs = $cellViewModel->inventoryNotStartedInputsNumber + $cellViewModel->inventoryStartedInputsNumber + $cellViewModel->inventoryFinishedInputsNumber;
                        if ($totalInventoryInputs > 0) {
                            $cellViewModel->inventoryCompletion = $cellViewModel->inventoryFinishedInputsNumber / $totalInventoryInputs * 100;
                        } else {
                            $cellViewModel->inventoryCompletion = 0;
                        }
                        $cellViewModel->inventoryCompletion = round($cellViewModel->inventoryCompletion);
                    }
                }
            } catch (Core_Exception_UndefinedAttribute $e) {
            } catch (\Core_Exception_NotFound $e) {
            }
        }

        // Saisie.
        if (($withInput === true)
            || (($withInput !== false)
                && ($cell->getGranularity()->getInputConfigGranularity() !== null)
                && (($this->acl->isAllowed($user, Actions::INPUT, $cell))))
        ) {
            $cellViewModel->inputStatus = $cell->getInputStatus();
            $aFInputSetPrimary = $cell->getAFInputSetPrimary();
            if ($aFInputSetPrimary !== null) {
                $cellViewModel->inputCompletion = $aFInputSetPrimary->getCompletion();
            } else {
                $cellViewModel->inputCompletion = 0;
            }
            $cellViewModel->inputStatusTitle = $this->inputStatusList[$cellViewModel->inputStatus];
            $cellViewModel->inputInconsistencies = $cell->getNumberOfInconsistenciesInInputSet();

            $cellViewModel->showInput = true;
        }

        return $cellViewModel;
    }
}
