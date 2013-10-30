<?php

namespace Orga\ViewModel;

use Core_Exception_UndefinedAttribute;
use Core_Model_Query;
use Orga_Model_Axis;
use Orga_Model_Cell;
use Orga_Model_Organization;
use User\Domain\ACL\ACLService;
use User\Domain\ACL\Action;

/**
 * Factory de OrganizationViewModel.
 */
class OrganizationViewModelFactory
{
    /**
     * @var ACLService
     */
    private $aclService;

    public function __construct(ACLService $aclService)
    {
        $this->aclService = $aclService;
    }

    public function createOrganizationViewModel(Orga_Model_Organization $organization, $connectedUser)
    {
        $viewModel = new OrganizationViewModel();
        $viewModel->id = $organization->getId();
        $viewModel->label = $organization->getLabel();
        if ($viewModel->label == '') {
            $viewModel->label = __('Orga', 'navigation', 'defaultOrganizationLabel');
        }

        $viewModel->rootAxesLabels = array_map(
            function (Orga_Model_Axis $axis) {
                return $axis->getLabel();
            },
            $organization->getRootAxes()
        );
        $viewModel->canBeDeleted = $this->aclService->isAllowed(
            $connectedUser,
            Action::DELETE(),
            $organization
        );

        try {
            $viewModel->inventory =  $organization->getGranularityForInventoryStatus()->getLabel();
        } catch (Core_Exception_UndefinedAttribute $e) {
        };
        $canUserSeeManyCells = false;
        foreach ($organization->getGranularities() as $granularity) {
            $aclCellQuery = new Core_Model_Query();
            $aclCellQuery->aclFilter->enabled = true;
            $aclCellQuery->aclFilter->user = $connectedUser;
            $aclCellQuery->aclFilter->action = Action::VIEW();
            $aclCellQuery->filter->addCondition(Orga_Model_Cell::QUERY_GRANULARITY, $granularity);
            $numberCellsUserCanSee = Orga_Model_Cell::countTotal($aclCellQuery);
            if ($numberCellsUserCanSee > 1) {
                $canUserSeeManyCells = true;
                break;
            } elseif ($numberCellsUserCanSee == 1) {
                break;
            }
        }

        if ($canUserSeeManyCells) {
            $viewModel->link = 'orga/organization/cells/idOrganization/' . $organization->getId();
        } elseif ($numberCellsUserCanSee == 1) {
            $cellWithAccess = Orga_Model_Cell::loadList($aclCellQuery);
            $viewModel->link = 'orga/cell/details/idCell/' . array_pop($cellWithAccess)->getId();
        }

        return $viewModel;
    }
}
