<?php

use Core\Annotation\Secure;
use MyCLabs\ACL\ACL;
use Orga\Application\Service\OrgaUserAccessManager;
use Orga\Domain\Axis;
use Orga\Domain\Workspace;
use User\Domain\ACL\Actions;
use User\Domain\User;

/**
 * @author valentin.claras
 */
class Orga_MemberController extends Core_Controller
{
    /**
     * @Inject
     * @var ACL
     */
    private $acl;

    /**
     * @Inject
     * @var OrgaUserAccessManager
     */
    private $orgaUserAccessManager;

    /**
     * @Secure("viewWorkspace")
     */
    public function manageAction()
    {
        /** @var User $connectedUser */
        $connectedUser = $this->_helper->auth();

        $workspaceId = $this->getParam('workspace');
        /** @var Workspace $workspace */
        $workspace = Workspace::load($workspaceId);
        $this->view->assign('workspaceId', $workspace->getId());

        $isUserAllowedToEditWorkspace = $this->acl->isAllowed(
            $connectedUser,
            Actions::EDIT,
            $workspace
        );
        $isUserAllowToEditAllMembers = $isUserAllowedToEditWorkspace || $this->acl->isAllowed(
            $connectedUser,
            Actions::EDIT,
            $workspace->getGranularityByRef('global')->getCellByMembers([])
        );
        $this->view->assign('isUserAllowToEditAllMembers', $isUserAllowToEditAllMembers);

        if ($isUserAllowToEditAllMembers) {
            $axes = $workspace->getLastOrderedAxes();
        } else {
            $axes = $this->orgaUserAccessManager->getAxesCanEdit($connectedUser, $workspace);
            usort($axes, [Axis::class, 'lastOrderAxes']);
        }
        $this->view->assign('axes', $axes);

        if ($this->hasParam('display') && ($this->getParam('display') === 'render')) {
            $this->_helper->layout()->disableLayout();
            $this->view->assign('display', false);
        } else {
            $this->view->headScript()->appendFile('scripts/ui/refRefactor.js', 'text/javascript');
            $this->view->assign('display', true);
        }
    }

}
