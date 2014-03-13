<?php

use Core\Annotation\Secure;
use Orga\Model\ACL\CellAdminRole;
use User\Domain\ACL\ACLService;
use User\Domain\ACL\Action;
use User\Domain\User;

/**
 * @author valentin.claras
 */
class Orga_MemberController extends Core_Controller
{
    /**
     * @Inject
     * @var ACLService
     */
    private $aclService;

    /**
     * @Inject
     * @var Orga_Service_ACLManager
     */
    private $aclManager;

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

        $isUserAllowedToEditOrganization = $this->aclService->isAllowed(
            $connectedUser,
            Action::EDIT(),
            $organization
        );
        $isUserAllowToEditAllMembers = $isUserAllowedToEditOrganization || $this->aclService->isAllowed(
            $connectedUser,
            Action::EDIT(),
            $organization->getGranularityByRef('global')->getCellByMembers([])
        );
        $this->view->assign('isUserAllowToEditAllMembers', $isUserAllowToEditAllMembers);
        $axes = $this->aclManager->getAxesCanEdit($connectedUser, $organization);
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
