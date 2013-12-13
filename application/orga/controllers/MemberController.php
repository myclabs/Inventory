<?php

use Core\Annotation\Secure;
use Orga\Model\ACL\Role\CellAdminRole;
use User\Domain\ACL\ACLService;
use User\Domain\ACL\Action;

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
        $isUserAllowedToEditGlobalCell = $isUserAllowedToEditOrganization || $this->aclService->isAllowed(
            $connectedUser,
            Action::EDIT(),
            $organization->getGranularityByRef('global')->getCellByMembers([])
        );
        $isUserAllowToEditAllMembers = $isUserAllowedToEditOrganization || $isUserAllowedToEditGlobalCell;
        $this->view->assign('isUserAllowToEditAllMembers', $isUserAllowToEditAllMembers);

        if ($isUserAllowToEditAllMembers) {
            $axes = [];
            foreach ($organization->getOrderedGranularities() as $granularity) {
                $aclCellQuery = new Core_Model_Query();
                $aclCellQuery->aclFilter->enabled = true;
                $aclCellQuery->aclFilter->user = $connectedUser;
                $aclCellQuery->aclFilter->action = Action::EDIT();
                $aclCellQuery->filter->addCondition(Orga_Model_Cell::QUERY_GRANULARITY, $granularity);

                $numberCellsUserCanEdit = Orga_Model_Cell::countTotal($aclCellQuery);
                if ($numberCellsUserCanEdit > 0) {
                    foreach ($organization->getLastOrderedAxes() as $axis) {
                        if (!in_array($axis, $axes)
                            && (!$granularity->hasAxes() || !$axis->isTransverse($granularity->getAxes()))
                        ) {
                            foreach ($granularity->getAxes() as $granularityAxis) {
                                if ($axis->isBroaderThan($granularityAxis) || ($axis === $granularityAxis)) {
                                    continue 2;
                                }
                            }
                            $axes[] = $axis;
                            foreach ($axis->getAllNarrowers() as $narrowerAxis) {
                                if (!in_array($narrowerAxis, $axes)) {
                                    $axes[] = $narrowerAxis;
                                }
                            }
                        }
                    }
                }
            }
            usort($axes, [Orga_Model_Axis::class, 'lastOrderAxes']);
        } else {
            $axes = $organization->getLastOrderedAxes();
        }
        $this->view->assign('axes', $axes);

        if ($this->hasParam('display') && ($this->getParam('display') === 'render')) {
            $this->_helper->layout()->disableLayout();
            $this->view->assign('display', false);
        } else {
            $this->view->assign('display', true);
        }
    }

}