<?php

namespace Orga\Application\ViewModel;

use Mnapoli\Translated\Translator;
use MyCLabs\ACL\ACL;
use Orga\Application\Service\OrgaUserAccessManager;
use Orga\Application\ViewModel\WorkspaceView;
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
        $workspaceView = new WorkspaceView();
        $workspaceView->id = $workspace->getId();
        $workspaceView->label = $this->translator->get($workspace->getLabel());
        if ($workspaceView->label == '') {
            $workspaceView->label = __('Orga', 'navigation', 'defaultWorkspaceLabel');
        }

        // Vérification d'accèr à l'édition.
        $workspaceView->canBeEdited = $this->acl->isAllowed(
            $connectedUser,
            Actions::EDIT,
            $workspace
        );

        if (!$workspaceView->canBeEdited) {
            $workspaceView->canBeEdited = $this->acl->isAllowed(
                $connectedUser,
                Actions::EDIT,
                $workspace->getGranularityByRef('global')->getCellByMembers([])
            );
        }
        if (!$workspaceView->canBeEdited) {
            // Edition d'au moins un axe ?
            $axesCanEdit = $this->orgaUserAccessManager->getAxesCanEdit($connectedUser, $workspace);
            if (count($axesCanEdit) > 0) {
                $workspaceView->canBeEdited = true;
            }
        }
        if (!$workspaceView->canBeEdited) {
            // Edition d'au moins une granularité de pertinence ou de DW ?
            foreach ($this->orgaUserAccessManager->getGranularitiesCanEdit($connectedUser, $workspace) as $granularity) {
                if ($granularity->getCellsControlRelevance() || $granularity->getCellsGenerateDWCubes()) {
                    $workspaceView->canBeEdited = true;
                    break;
                }
            }
        }

        return $workspaceView;
    }
}
