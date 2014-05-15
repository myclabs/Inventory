<?php

namespace Account\Application\Service;

use Account\Application\ViewModel\OrganizationView;
use Mnapoli\Translated\Translator;
use MyCLabs\ACL\ACL;
use User\Domain\ACL\Actions;
use Orga_Model_Organization;
use Orga_Model_Cell;
use Orga_Service_ACLManager;
use User\Domain\User;
use Core_Model_Query;

/**
 * Crée des représentations simplifiées de la vue d'une organisation pour un utilisateur.
 *
 * @author matthieu.napoli
 */
class OrganizationViewFactory
{
    /**
     * @var ACL
     */
    private $acl;

    /**
     * @var Orga_Service_ACLManager
     */
    private $orgaACLManager;

    /**
     * @var Translator
     */
    private $translator;

    public function __construct(ACL $acl, Orga_Service_ACLManager $orgaACLManager, Translator $translator)
    {
        $this->acl = $acl;
        $this->orgaACLManager = $orgaACLManager;
        $this->translator = $translator;
    }

    public function createOrganizationView(Orga_Model_Organization $organization, User $connectedUser)
    {
        $viewModel = new OrganizationView();
        $viewModel->id = $organization->getId();
        $viewModel->label = $this->translator->get($organization->getLabel());
        if ($viewModel->label == '') {
            $viewModel->label = __('Orga', 'navigation', 'defaultOrganizationLabel');
        }

        // Vérification d'accèr à l'édition.
        $viewModel->canBeEdited = $this->acl->isAllowed(
            $connectedUser,
            Actions::EDIT,
            $organization
        );
        if (!$viewModel->canBeEdited) {
            // Edition de la cellule globale ?
            $query = new Core_Model_Query();
            $query->filter->addCondition(Orga_Model_Cell::QUERY_GRANULARITY, $organization->getGranularityByRef('global'));
            $query->aclFilter->enabled = true;
            $query->aclFilter->user = $connectedUser;
            $query->aclFilter->action = Actions::EDIT;
            if (Orga_Model_Cell::countTotal($query) > 0) {
                $viewModel->canBeEdited = true;
            }
        }
        if (!$viewModel->canBeEdited) {
            // Edition d'au moins un axe ?
            $axesCanEdit = $this->orgaACLManager->getAxesCanEdit($connectedUser, $organization);
            if (count($axesCanEdit) > 0) {
                $viewModel->canBeEdited = true;
            }
        }
        if (!$viewModel->canBeEdited) {
            // Edition d'au moins une granularité de pertinence ou de DW ?
            foreach ($this->orgaACLManager->getGranularitiesCanEdit($connectedUser, $organization) as $granularity) {
                if ($granularity->getCellsControlRelevance() || $granularity->getCellsGenerateDWCubes()) {
                    $viewModel->canBeEdited = true;
                    break;
                }
            }
        }

        $viewModel->canBeDeleted = $this->acl->isAllowed(
            $connectedUser,
            Actions::DELETE,
            $organization
        );

        return $viewModel;
    }
}
