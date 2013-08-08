<?php
/**
 * @author valentin.claras
 * @package Orga
 */

use Core\Annotation\Secure;

/**
 * Controller de projet
 * @package Orga
 */
class Orga_Datagrid_OrganizationController extends UI_Controller_Datagrid
{
    /**
     * Methode appelee pour remplir le tableau.
     * @Secure("viewOrganizations")
     */
    public function getelementsAction()
    {
        $this->request->aclFilter->enabled = true;
        $this->request->aclFilter->user = $this->_helper->auth();
        $this->request->aclFilter->action = User_Model_Action_Default::VIEW();

        foreach (Orga_Model_Organization::loadList($this->request) as $organization) {
            $data = array();
            $data['index'] = $organization->getId();
            $data['label'] = $organization->getLabel();
            $rootAxesLabel = array();
            foreach ($organization->getRootAxes() as $rootAxis) {
                $rootAxesLabel[] = $rootAxis->getLabel();
            }
            $data['rootAxes'] = implode(', ', $rootAxesLabel);
            try {
                $data['granularityForInventoryStatus'] = $organization->getGranularityForInventoryStatus()->getLabel();
            } catch (Core_Exception_UndefinedAttribute $e) {
                $data['granularityForInventoryStatus'] = '';
            };

            $isConnectedUserAbleToSeeManyCells = false;
            foreach ($organization->getGranularities() as $granularity) {
                $aclCellQuery = new Core_Model_Query();
                $aclCellQuery->aclFilter->enabled = true;
                $aclCellQuery->aclFilter->user = $this->_helper->auth();
                $aclCellQuery->aclFilter->action = User_Model_Action_Default::VIEW();
                $aclCellQuery->filter->addCondition(
                    Orga_Model_Cell::QUERY_GRANULARITY,
                    $granularity,
                    Core_Model_Filter::OPERATOR_EQUAL
                );
                $numberCellUserCanSee = Orga_Model_Cell::countTotal($aclCellQuery);
                if ($numberCellUserCanSee > 1) {
                    $isConnectedUserAbleToSeeManyCells = true;
                    break;
                } else if ($numberCellUserCanSee == 1) {
                    break;
                }
            }
            if ($isConnectedUserAbleToSeeManyCells) {
                $data['details'] = $this->cellLink('orga/organization/cells/idOrganization/'.$organization->getId());
            } else if ($numberCellUserCanSee == 1) {
                $cellWithAccess = Orga_Model_Cell::loadList($aclCellQuery);
                $data['details'] = $this->cellLink('orga/cell/details/idCell/'.array_pop($cellWithAccess)->getId());
            }

            $isConnectedUserAbleToDeleteOrganization = User_Service_ACL::getInstance()->isAllowed(
                $this->_helper->auth(),
                User_Model_Action_Default::DELETE(),
                $organization
            );
            if (!$isConnectedUserAbleToDeleteOrganization) {
                $data['delete'] = false;
            }

            $this->addLine($data);
        }

        $this->send();
    }

    /**
     * Ajoute un nouvel element.
     * @Secure("createOrganization")
     */
    public function addelementAction()
    {
        /** @var Core_Work_Dispatcher $workDispatcher */
        $workDispatcher = Zend_Registry::get('workDispatcher');

        $administrator = $this->_helper->auth();
        $label = $this->getAddElementValue('label');

        $workDispatcher->runBackground(
            new Core_Work_ServiceCall_Task(
                'Orga_Service_OrganizationService',
                'createOrganization',
                [$administrator, $label]
            )
        );

        $this->message = __('UI', 'message', 'addedLater');
        $this->send();
    }

    /**
     * Supprime un element.
     * @Secure("deleteOrganization")
     */
    public function deleteelementAction()
    {
        /** @var Core_Work_Dispatcher $workDispatcher */
        $workDispatcher = Zend_Registry::get('workDispatcher');

        $organization = Orga_Model_Organization::load($this->delete);

        $workDispatcher->runBackground(
            new Core_Work_ServiceCall_Task(
                'Orga_Service_OrganizationService',
                'deleteOrganization',
                [$organization]
            )
        );

        $this->message = __('UI', 'message', 'deletedLater');
        $this->send();
    }

}
