<?php

namespace Account\Application\Service;

use Account\Application\ViewModel\WorkspaceView;
use Mnapoli\Translated\Translator;
use MyCLabs\ACL\ACL;
use Orga\Application\Service\OrgaUserAccessManager;
use User\Domain\ACL\Actions;
use Orga\Domain\Workspace;
use Orga\Domain\Cell;
use User\Domain\User;
use Core_Model_Query;

/**
 * Crée des représentations simplifiées de la vue d'une organisation pour un utilisateur.
 *
 * @author matthieu.napoli
 */
class WorkspaceViewFactory
{
    /**
     * @var ACL
     */
    private $acl;

    /**
     * @var OrgaUserAccessManager
     */
    private $orgaUserAccessManager;

    /**
     * @var Translator
     */
    private $translator;

    public function __construct(ACL $acl, OrgaUserAccessManager $orgaUserAccessManager, Translator $translator)
    {
        $this->acl = $acl;
        $this->orgaUserAccessManager = $orgaUserAccessManager;
        $this->translator = $translator;
    }

    public function createWorkspaceView(Workspace $workspace, User $connectedUser)
    {
        $viewModel = new WorkspaceView();
        $viewModel->id = $workspace->getId();
        $viewModel->label = $this->translator->get($workspace->getLabel());
        if ($viewModel->label == '') {
            $viewModel->label = __('Orga', 'navigation', 'defaultWorkspaceLabel');
        }

        // Vérification d'accèr à l'édition.
        $viewModel->canBeEdited = $this->acl->isAllowed(
            $connectedUser,
            Actions::EDIT,
            $workspace
        );
        if (!$viewModel->canBeEdited) {
            // Edition de la cellule globale ?
            $query = new Core_Model_Query();
            $query->filter->addCondition(Cell::QUERY_GRANULARITY, $workspace->getGranularityByRef('global'));
            $query->aclFilter->enabled = true;
            $query->aclFilter->user = $connectedUser;
            $query->aclFilter->action = Actions::EDIT;
            if (Cell::countTotal($query) > 0) {
                $viewModel->canBeEdited = true;
            }
        }
        if (!$viewModel->canBeEdited) {
            // Edition d'au moins un axe ?
            $axesCanEdit = $this->orgaUserAccessManager->getAxesCanEdit($connectedUser, $workspace);
            if (count($axesCanEdit) > 0) {
                $viewModel->canBeEdited = true;
            }
        }
        if (!$viewModel->canBeEdited) {
            // Edition d'au moins une granularité de pertinence ou de DW ?
            foreach ($this->orgaUserAccessManager->getGranularitiesCanEdit($connectedUser, $workspace) as $granularity) {
                if ($granularity->getCellsControlRelevance() || $granularity->getCellsGenerateDWCubes()) {
                    $viewModel->canBeEdited = true;
                    break;
                }
            }
        }

        $viewModel->canBeDeleted = $this->acl->isAllowed(
            $connectedUser,
            Actions::DELETE,
            $workspace
        );

        return $viewModel;
    }
}
