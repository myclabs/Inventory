<?php

namespace Orga\ViewModel;

use Core_Exception_UndefinedAttribute;
use Orga_Model_Axis;
use Orga_Model_Organization;
use User_Model_User;
use User_Model_Action_Default;
use User_Service_ACL;

/**
 * Factory de OrganizationViewModel.
 */
class OrganizationViewModelFactory
{
    /**
     * @var User_Service_ACL
     */
    private $aclService;

    public function __construct(User_Service_ACL $aclService)
    {
        $this->aclService = $aclService;
    }

    public function createOrganizationViewModel(Orga_Model_Organization $organization, User_Model_User $connectedUser)
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
            User_Model_Action_Default::EDIT(),
            $organization
        );
        $viewModel->canBeDeleted = $this->aclService->isAllowed(
            $connectedUser,
            User_Model_Action_Default::DELETE(),
            $organization
        );

        try {
            $viewModel->inventory =  $organization->getGranularityForInventoryStatus()->getLabel();
        } catch (Core_Exception_UndefinedAttribute $e) {
        };

        return $viewModel;
    }
}
