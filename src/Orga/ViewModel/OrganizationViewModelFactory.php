<?php

namespace Orga\ViewModel;

use Core_Exception_UndefinedAttribute;
use Orga_Model_Organization;
use Orga_Model_Axis;
use Orga_Model_Cell;
use User\Domain\User;
use User\Domain\ACL\ACLService;
use User\Domain\ACL\Action;
use Core_Model_Query;

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

    public function createOrganizationViewModel(Orga_Model_Organization $organization, User $connectedUser)
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
        $viewModel->canBeEdited = $this->aclService->isAllowed(
            $connectedUser,
            Action::EDIT(),
            $organization
        );
        if (!$viewModel->canBeEdited) {
            foreach ($organization->getGranularities() as $granularity) {
                $query = new Core_Model_Query();
                $query->filter->addCondition(Orga_Model_Cell::QUERY_GRANULARITY, $granularity);
                $query->aclFilter->enabled = true;
                $query->aclFilter->user = $connectedUser;
                $query->aclFilter->action = Action::EDIT();
                if (Orga_Model_Cell::countTotal($query) > 0) {
                    $viewModel->canBeEdited = true;
                }
            }
        }
        $viewModel->canBeDeleted = $this->aclService->isAllowed(
            $connectedUser,
            Action::DELETE(),
            $organization
        );

        try {
            $viewModel->inventory =  $organization->getGranularityForInventoryStatus()->getLabel();
        } catch (Core_Exception_UndefinedAttribute $e) {
        };

        return $viewModel;
    }
}
