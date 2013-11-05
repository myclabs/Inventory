<?php

namespace Orga\ViewModel;

use Core_Exception_UndefinedAttribute;
use Orga_Model_Cell;
use Orga_Model_Granularity;
use Orga_Model_Member;
use User_Model_User;
use User_Model_Action_Default;
use Orga_Action_Cell;
use User_Service_ACL;
use Core_Model_Query;
use Core_Model_Filter;

/**
 * Factory de CellViewModel.
 */
class CellViewModelFactory
{
    /**
     * @var User_Service_ACL
     */
    private $aclService;

    /**
     * @param User_Service_ACL $aclService
     */
    public function __construct(User_Service_ACL $aclService)
    {
        $this->aclService = $aclService;
    }

    /**
     * @param Orga_Model_Cell $cell
     * @param User_Model_User $user
     * @return CellViewModel
     */
    public function createCellViewModel(Orga_Model_Cell $cell, User_Model_User $user)
    {
        $cellViewModel = new CellViewModel();
        $cellViewModel->shortLabel = $cell->getLabel();
        $cellViewModel->extendedLabel = $cell->getLabelExtended();
        $cellViewModel->path = implode(' ', array_map(function (Orga_Model_Member $member) { return $member->getCompleteRef(); }, $cell->getMembers()));

        $cellViewModel->administrators = ['test@mail.com', 'admin@myc-sense.com', 'subadmin@organization.com'];

        // DW
        $cellViewModel->canBeAnalyzed = $cell->getGranularity()->getCellsGenerateDWCubes();

        // Inventory
        try {
            $granularityForInventoryStatus = $cell->getGranularity()->getOrganization()->getGranularityForInventoryStatus();

            if (($cell->getGranularity() === $granularityForInventoryStatus)
                || ($granularityForInventoryStatus->isBroaderThan($cell->getGranularity()))) {
                $cellViewModel->inventoryStatus = $cell->getInventoryStatus();
            }
        } catch (Core_Exception_UndefinedAttribute $e) {
        }

        // Saisie.
        if ($cell->getGranularity()->getInputConfigGranularity() !== null) {
            $cellViewModel->canBeInputted = $this->aclService->isAllowed($user, Orga_Action_Cell::INPUT(), $cell);

            try {
                $cellViewModel->inputStatus = $cell->getAFInputSetPrimary()->getStatus();
            } catch (Core_Exception_UndefinedAttribute $e) {
            }
        } else {
            $cellViewModel->canBeInputted = false;
        }

        return $cellViewModel;
    }

    public function createChildCellsViewModel(Orga_Model_Cell $cell, User_Model_User $user)
    {
        $granularityFilter = new Core_Model_Filter();
        $granularityFilter->condition = Core_Model_Filter::CONDITION_OR;
        foreach ($cell->getGranularity()->getNarrowerGranularities() as $narrowerGranularity) {
            $granularityFilter->addCondition(Orga_Model_Cell::QUERY_GRANULARITY, $narrowerGranularity);
        }
        if (count($granularityFilter->getConditions()) === 0) {
            return [];
        }
        $cells = [];

        foreach ($cell->getGranularity()->getNarrowerGranularities() as $narrowerGranularity) {
            $granularityWithInput = ($narrowerGranularity->getInputConfigGranularity() !== null);
            $granularityWithDWCubes = $narrowerGranularity->getCellsGenerateDWCubes();
            if (($narrowerGranularity->getInputConfigGranularity() === null) && (!$narrowerGranularity->getCellsGenerateDWCubes())) {
                continue;
            }

            $granularityCells = [];
            foreach ($cell->loadChildCellsForGranularity($narrowerGranularity) as $childCell) {
                $granularityCells[] = $this->createCellViewModel($childCell, $user);
            }
            $cells[] = [
                'granularity' => $narrowerGranularity,
                'cells' => $granularityCells
            ];
        }

        return $cells;
    }
}
