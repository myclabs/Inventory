<?php

namespace Orga\ViewModel;

use Core_Exception_UndefinedAttribute;
use Orga\Model\ACL\Role\CellAdminRole;
use Orga_Model_Organization;
use Orga_Model_Axis;
use Orga_Model_Cell;
use Orga_Service_ACLManager;
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
    /**
     * @var Orga_Service_ACLManager
     */
    private $aclManager;

    public function __construct(ACLService $aclService, Orga_Service_ACLManager $aclManager)
    {
        $this->aclService = $aclService;
        $this->aclManager = $aclManager;
    }

    public function createOrganizationViewModel(Orga_Model_Organization $organization, User $connectedUser)
    {
        $viewModel = new OrganizationViewModel();
        $viewModel->id = $organization->getId();
        $viewModel->label = $organization->getLabel();
        if ($viewModel->label == '') {
            $viewModel->label = __('Orga', 'navigation', 'defaultOrganizationLabel');
        }

        // Vérification d'accèr à l'édition.
        $viewModel->canBeEdited = $this->aclService->isAllowed(
            $connectedUser,
            Action::EDIT(),
            $organization
        );
        if (!$viewModel->canBeEdited) {
            // Edition de la cellule globale ?
            $query = new Core_Model_Query();
            $query->filter->addCondition(Orga_Model_Cell::QUERY_GRANULARITY, $organization->getGranularityByRef('global'));
            $query->aclFilter->enabled = true;
            $query->aclFilter->user = $connectedUser;
            $query->aclFilter->action = Action::EDIT();
            if (Orga_Model_Cell::countTotal($query) > 0) {
                $viewModel->canBeEdited = true;
            }
        }
        if (!$viewModel->canBeEdited) {
            // Edition d'au moins un axe ?
            $axesCanEdit = $this->aclManager->getAxesCanEdit($connectedUser, $organization);
            if (count($axesCanEdit) > 0) {
                $viewModel->canBeEdited = true;
            }
        }
        if (!$viewModel->canBeEdited) {
            // Edition d'au moins une granularité de pertinence ou de DW ?
            $relevanceOnRebuildGranularities = [];
            foreach ($this->aclManager->getGranularitiesCanEdit($connectedUser, $organization) as $granularity) {
                if ($granularity->getCellsControlRelevance() || $granularity->getCellsGenerateDWCubes()) {
                    $viewModel->canBeEdited = true;
                    break;
                }
            }
        }

        $viewModel->canBeDeleted = $this->aclService->isAllowed(
            $connectedUser,
            Action::DELETE(),
            $organization
        );

        return $viewModel;
    }
}
