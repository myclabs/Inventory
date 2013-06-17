<?php
/**
 * @author valentin.claras
 * @package Orga
 */

use Core\Annotation\Secure;
use DI\Annotation\Inject;

/**
 * Controller de projet
 * @package Orga
 */
class Orga_Datagrid_ProjectController extends UI_Controller_Datagrid
{
    /**
     * @Inject
     * @var User_Service_ACL
     */
    private $aclService;

    /**
     * @Inject
     * @var Core_Work_Dispatcher
     */
    private $workDispatcher;

    /**
     * Methode appelee pour remplir le tableau.
     * @Secure("viewProjects")
     */
    public function getelementsAction()
    {
        $this->request->aclFilter->enabled = true;
        $this->request->aclFilter->user = $this->_helper->auth();
        $this->request->aclFilter->action = User_Model_Action_Default::VIEW();

        foreach (Orga_Model_Project::loadList($this->request) as $project) {
            /** @var Orga_Model_Project $project */
            $data = array();
            $data['index'] = $project->getId();
            $data['label'] = $project->getLabel();
            $rootAxesLabel = array();
            foreach ($project->getRootAxes() as $rootAxis) {
                $rootAxesLabel[] = $rootAxis->getLabel();
            }
            $data['rootAxes'] = implode(', ', $rootAxesLabel);
            try {
                $data['granularityForInventoryStatus'] = $project->getGranularityForInventoryStatus()->getLabel();
            } catch (Core_Exception_UndefinedAttribute $e) {
                $data['granularityForInventoryStatus'] = '';
            };

            $isConnectedUserAbleToSeeManyCells = false;
            foreach ($project->getGranularities() as $granularity) {
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
                $data['details'] = $this->cellLink('orga/project/cells/idProject/'.$project->getId());
            } else {
                $cellWithAccess = Orga_Model_Cell::loadList($aclCellQuery);
                $data['details'] = $this->cellLink('orga/cell/details/idCell/'.array_pop($cellWithAccess)->getId());
            }

            $isConnectedUserAbleToDeleteProject = $this->aclService->isAllowed(
                $this->_helper->auth(),
                User_Model_Action_Default::DELETE(),
                $project
            );
            if (!$isConnectedUserAbleToDeleteProject) {
                $data['delete'] = false;
            }

            $this->addLine($data);
        }

        $this->send();
    }

    /**
     * Ajoute un nouvel element.
     * @Secure("createProject")
     */
    public function addelementAction()
    {
        $administrator = $this->_helper->auth();
        $label = $this->getAddElementValue('label');

        $this->workDispatcher->runBackground(
            new Core_Work_ServiceCall_Task(
                'Orga_Service_ProjectService',
                'createProject',
                [$administrator, $label]
            )
        );

        $this->message = __('UI', 'message', 'addedLater');
        $this->send();
    }

    /**
     * Supprime un element.
     * @Secure("deleteProject")
     */
    public function deleteelementAction()
    {
        $project = Orga_Model_Project::load($this->delete);

        $this->workDispatcher->runBackground(
            new Core_Work_ServiceCall_Task(
                'Orga_Service_ProjectService',
                'deleteProject',
                [$project]
            )
        );

        $this->message = __('UI', 'message', 'deletedLater');
        $this->send();
    }

}
