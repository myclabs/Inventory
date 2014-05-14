<?php

namespace Orga\ViewModel;

use Core_Exception_UndefinedAttribute;
use Doctrine\Common\Collections\Criteria;
use Mnapoli\Translated\TranslationHelper;
use MyCLabs\ACL\ACL;
use User\Domain\ACL\Actions;
use Orga_Model_Cell;
use User\Domain\User;
use AF\Domain\InputSet\PrimaryInputSet;

/**
 * Factory de CellViewModel.
 */
class CellViewModelFactory
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
     * @var TranslationHelper
     */
    private $translator;


    public function __construct(ACL $acl, TranslationHelper $translator)
    {
        $this->acl = $acl;
        $this->translator = $translator;

        $this->inventoryStatusList = [
            Orga_Model_Cell::STATUS_NOTLAUNCHED => __('Orga', 'view', 'inventoryNotLaunched'),
            Orga_Model_Cell::STATUS_ACTIVE => __('Orga', 'view', 'inventoryOpen'),
            Orga_Model_Cell::STATUS_CLOSED => __('Orga', 'view', 'inventoryClosed')
        ];
        $this->inputStatusList = [
            PrimaryInputSet::STATUS_FINISHED => __('AF', 'inputInput', 'statusFinished'),
            PrimaryInputSet::STATUS_COMPLETE => __('AF', 'inputInput', 'statusComplete'),
            PrimaryInputSet::STATUS_CALCULATION_INCOMPLETE => __('AF', 'inputInput', 'statusCalculationIncomplete'),
            PrimaryInputSet::STATUS_INPUT_INCOMPLETE => __('AF', 'inputInput', 'statusInputIncomplete'),
            CellViewModel::AF_STATUS_AF_NOT_CONFIGURED => __('Orga', 'view', 'statusAFNotConfigured'),
            CellViewModel::AF_STATUS_NOT_STARTED => __('Orga', 'view', 'statusNotStarted'),
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
     * @param bool $withInventoryProgress
     * @param bool $editInventory
     * @param bool $withInput
     * @param bool $withInputLink
     * @return CellViewModel
     */
    public function createCellViewModel(
        Orga_Model_Cell $cell,
        User $user,
        $withAdministrators = null,
        $withACL = null,
        $withReports = null,
        $withExports = null,
        $withInventory = null,
        $withInventoryProgress = null,
        $editInventory = null,
        $withInput = null,
        $withInputLink = null
    ) {
        $cellViewModel = new CellViewModel();
        $cellViewModel->id = $cell->getId();
        $cellViewModel->shortLabel = $this->translator->toString($cell->getLabel());
        $cellViewModel->extendedLabel = $this->translator->toString($cell->getExtendedLabel());
        $cellViewModel->relevant = $cell->isRelevant();
        $cellViewModel->tag = $cell->getTag();

        foreach ($cell->getMembers() as $member) {
            $cellViewModel->members[$member->getAxis()->getRef()] = $this->translator->toString($member->getLabel());
        }

        // Administrateurs.
        if ($withAdministrators === true) {
            foreach ($cell->getAdminRoles() as $administrator) {
                array_unshift($cellViewModel->administrators, $administrator->getSecurityIdentity()->getEmail());
            }
            foreach (array_reverse($cell->getParentCells()) as $parentCell) {
                /** @var Orga_Model_Cell $parentCell */
                foreach ($parentCell->getAdminRoles() as $parentAdministrator) {
                    array_unshift($cellViewModel->administrators, $parentAdministrator->getSecurityIdentity()->getEmail());
                }
            }
            foreach ($cell->getOrganization()->getAdminRoles() as $organizationAdministrator) {
                array_unshift($cellViewModel->administrators, $organizationAdministrator->getSecurityIdentity()->getEmail());
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
                $granularityForInventoryStatus = $cell->getGranularity()->getOrganization()->getGranularityForInventoryStatus();

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
                                $relevantCriteria->where($relevantCriteria->expr()->eq(Orga_Model_Cell::QUERY_ALLPARENTSRELEVANT, true));
                                $relevantCriteria->andWhere($relevantCriteria->expr()->eq(Orga_Model_Cell::QUERY_RELEVANT, true));
                                $relevantChildInputCells = $cell->getChildCellsForGranularity($narrowerInputGranularity)->matching($relevantCriteria);
                                /** @var Orga_Model_Cell $childInputCell */
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
            $cellViewModel->showInput = true;
            $cellViewModel->showInputLink = (($withInputLink !== true) && ($withInputLink !== false)) ? true : $withInputLink;
            $inputStatus = ($cell->getInputAFUsed() !== null) ? CellViewModel::AF_STATUS_NOT_STARTED : CellViewModel::AF_STATUS_AF_NOT_CONFIGURED;
            try {
                $granularityForInventoryStatus = $cell->getGranularity()->getOrganization()->getGranularityForInventoryStatus();
                if (($cell->getInventoryStatus() === Orga_Model_Cell::STATUS_NOTLAUNCHED)
                    && (($cell->getGranularity() === $granularityForInventoryStatus)
                        || ($cell->getGranularity()->isNarrowerThan($granularityForInventoryStatus)))) {
                    if ($withInputLink !== false) {
                        $cellViewModel->showInputLink = false;
                    }
                }
            } catch (Core_Exception_UndefinedAttribute $e) {
            } catch (\Core_Exception_NotFound $e) {
            }

            $aFInputSetPrimary = $cell->getAFInputSetPrimary();
            if ($aFInputSetPrimary !== null) {
                $cellViewModel->inputStatus = $aFInputSetPrimary->getStatus();
                $cellViewModel->inputCompletion = $aFInputSetPrimary->getCompletion();
            } else {
                $cellViewModel->inputStatus = $inputStatus;
                $cellViewModel->inputCompletion = 0;
            }
            $cellViewModel->inputStatusTitle = $this->inputStatusList[$cellViewModel->inputStatus];
        }

        return $cellViewModel;
    }
}
