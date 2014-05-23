<?php

use Core\Annotation\Secure;
use MyCLabs\ACL\ACL;
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
     * @var Orga_Service_ACLManager
     */
    private $orgaACLManager;

    /**
     * Controller de la vue des Member d'un organization.
     * @Secure("viewOrganization")
     */
    public function manageAction()
    {
        /** @var User $connectedUser */
        $connectedUser = $this->_helper->auth();

        $idOrganization = $this->getParam('idOrganization');
        /** @var Orga_Model_Organization $organization */
        $organization = Orga_Model_Organization::load($idOrganization);
        $this->view->assign('idOrganization', $organization->getId());

        $isUserAllowedToEditOrganization = $this->acl->isAllowed(
            $connectedUser,
            Actions::EDIT,
            $organization
        );
        $isUserAllowToEditAllMembers = $isUserAllowedToEditOrganization || $this->acl->isAllowed(
            $connectedUser,
            Actions::EDIT,
            $organization->getGranularityByRef('global')->getCellByMembers([])
        );
        $this->view->assign('isUserAllowToEditAllMembers', $isUserAllowToEditAllMembers);
        $axes = $this->orgaACLManager->getAxesCanEdit($connectedUser, $organization);
        usort($axes, [Orga_Model_Axis::class, 'lastOrderAxes']);
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
