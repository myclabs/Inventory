<?php

namespace Orga\ViewModel;

use Core_Exception_UndefinedAttribute;
use Doctrine\Common\Collections\Criteria;
use Orga_Model_Cell;
use Orga_Model_Member;
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
    private $inventoryStatusList;

    /**
     * @var array
     */
    private $inputStatusList;

    /**
     * @var array
     */
    private $inputStatusStyleList;

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
        $imageFinished = new UI_HTML_Image('images/af/bullet_green.png', 'finish');
        $imageComplete = new UI_HTML_Image('images/af/bullet_orange.png', 'complet');
        $imageCalculationIncomplete = new UI_HTML_Image('images/af/bullet_red.png', 'incomplet');
        $imageInputIncomplete = new UI_HTML_Image('images/af/bullet_red.png', 'incomplet');
        $imageInputNotStarted = new UI_HTML_Image('images/af/bullet_red.png', 'incomplet');
        $this->inputStatusList = [
            AF_Model_InputSet_Primary::STATUS_FINISHED => $imageFinished->render() . ' ' . __('AF', 'inputInput', 'statusFinished'),
            AF_Model_InputSet_Primary::STATUS_COMPLETE => $imageComplete->render() . ' ' . __('AF', 'inputInput', 'statusComplete'),
            AF_Model_InputSet_Primary::STATUS_CALCULATION_INCOMPLETE => $imageCalculationIncomplete->render() . ' ' . __('AF', 'inputInput', 'statusCalculationIncomplete'),
            AF_Model_InputSet_Primary::STATUS_INPUT_INCOMPLETE => $imageInputIncomplete->render() . ' ' . __('AF', 'inputInput', 'statusInputIncomplete'),
            null => $imageInputNotStarted->render() . ' ' . __('AF', 'inputInput', 'statusNotStarted')
        ];
        $this->inputStatusStyleList = [
            AF_Model_InputSet_Primary::STATUS_FINISHED => 'progress-success',
            AF_Model_InputSet_Primary::STATUS_COMPLETE => 'progress-warning',
            AF_Model_InputSet_Primary::STATUS_CALCULATION_INCOMPLETE => 'progress-danger',
            AF_Model_InputSet_Primary::STATUS_INPUT_INCOMPLETE => 'progress-danger',
            null => 'progress-danger'
        ];
    }

    /**
     * @param Orga_Model_Cell $cell
     * @param User $user
     * @param bool $withAdministrators
     * @param bool $withDW
     * @param bool $withInventory
     * @param bool $withInput
     * @return CellViewModel
     */
    public function createCellViewModel(Orga_Model_Cell $cell, User $user,
        $withAdministrators=null, $withDW=null, $withInventory=null, $withInput=null)
    {
        $cellViewModel = new CellViewModel();
        $cellViewModel->id = $cell->getId();
        $cellViewModel->shortLabel = $cell->getLabel();
        $cellViewModel->extendedLabel = $cell->getExtendedLabel();
        $cellViewModel->path = implode(' ', array_map(function (Orga_Model_Member $member) { return $member->getCompleteRef(); }, $cell->getMembers()));

        // Administrateurs
        if ($withAdministrators === true) {
            foreach ($cell->getAdminRoles() as $administrator) {
                $cellViewModel->administrators[] = $administrator->getUser()->getEmail();
            }
            foreach ($cell->getParentCells() as $parentCell) {
                foreach ($parentCell->getAdminRoles() as $parentAdministrator) {
                    $cellViewModel->administrators[] = $parentAdministrator->getUser()->getEmail();
                }
            }
        }

        // DW
        if (($withDW === true)
            || (($withDW !== false) && ($cell->getGranularity()->getCellsGenerateDWCubes()))) {
            $cellViewModel->canBeAnalyzed = true;
        }

        // Inventory
        if ($withInventory !== false) {
            try {
                $granularityForInventoryStatus = $cell->getGranularity()->getOrganization()->getGranularityForInventoryStatus();

                if (($cell->getGranularity() === $granularityForInventoryStatus)
                    || ($cell->getGranularity()->isNarrowerThan($granularityForInventoryStatus))
                ) {
                    $cellViewModel->inventoryStatus = $cell->getInventoryStatus();
                    $cellViewModel->inventoryStatusTitle = $this->inventoryStatusList[$cellViewModel->inventoryStatus];

                    $cellViewModel->inventoryCompletion = 0;
                    $cellViewModel->inventoryNotStartedInputsNumber = 0;
                    $cellViewModel->inventoryStartedInputsNumber = 0;
                    $cellViewModel->inventoryCompletedInputsNumber = 0;
                    if (($cell->getGranularity()->isNarrowerThan($granularityForInventoryStatus)
                        || ($cell->getGranularity() === $granularityForInventoryStatus))
                        && ($cell->getGranularity()->getInputConfigGranularity() !== null)) {
                        try {
                            $cellViewModel->inventoryCompletion += $cell->getAFInputSetPrimary()->getCompletion();
                            if ($cell->getAFInputSetPrimary()->getCompletion() == 0) {
                                $cellViewModel->inventoryNotStartedInputsNumber ++;
                            } else if ($cell->getAFInputSetPrimary()->getCompletion() < 100) {
                                $cellViewModel->inventoryStartedInputsNumber ++;
                            } else {
                                $cellViewModel->inventoryCompletedInputsNumber ++;
                            }
                        } catch (Core_Exception_UndefinedAttribute $e) {
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
            || (($withInput !== false) && ($cell->getGranularity()->getInputConfigGranularity() !== null))) {
            $cellViewModel->canBeInputted = $this->aclService->isAllowed($user, CellAction::INPUT(), $cell);

            $aFInputSetPrimary = $cell->getAFInputSetPrimary();
            if ($aFInputSetPrimary !== null) {
                $cellViewModel->inputStatus = $aFInputSetPrimary->getStatus();
                $cellViewModel->inputStatusTitle = $this->inputStatusList[$cellViewModel->inputStatus];
                $cellViewModel->inputStatusStyle = $this->inputStatusStyleList[$cellViewModel->inputStatus];
                $cellViewModel->inputCompletion = $aFInputSetPrimary->getCompletion();
            } else {
                $cellViewModel->inputStatus = null;
                $cellViewModel->inputStatusTitle = $this->inputStatusList[null];
                $cellViewModel->inputStatusStyle = $this->inputStatusStyleList[null];
                $cellViewModel->inputCompletion = 0;
            }
        }

        return $cellViewModel;
    }
}
