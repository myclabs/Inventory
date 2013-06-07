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
class Orga_Datagrid_ProjectController extends UI_Controller_Datagrid
{
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
            $data = array();
            $data['index'] = $project->getKey()['id'];
            $data['label'] = $project->getLabel();
            try {
                $data['granularityForInventoryStatus'] = $this->cellList(
                    $project->getGranularityForInventoryStatus()->getRef(),
                    $project->getGranularityForInventoryStatus()->getLabel()
                );
            } catch (Core_Exception_UndefinedAttribute $e) {
                $data['granularityForInventoryStatus'] = $this->cellList(null, '');
            };
            $data['resetDWCubesAndDatasUpToDate'] = $this->cellPopup(
                'orga/project/dwcubesstate/idProject/'.$data['index'],
                __('Orga', 'projectList', 'statusRebuildColumnLink'),
                'zoom-in'
            );

            $userIsAbleToSeeManyCells = false;
            foreach ($project->getGranularities() as $granularity) {
                $aclProjectQuery = new Core_Model_Query();
                $aclProjectQuery->aclFilter->enabled = true;
                $aclProjectQuery->aclFilter->user = $this->_helper->auth();
                $aclProjectQuery->aclFilter->action = User_Model_Action_Default::VIEW();
                $aclProjectQuery->filter->addCondition(
                    Orga_Model_Cell::QUERY_GRANULARITY,
                    $granularity,
                    Core_Model_Filter::OPERATOR_EQUAL,
                    Orga_Model_Cell::getAlias()
                );
                $numberCellUserCanSee = Orga_Model_Cell::countTotal($aclProjectQuery);
                if ($numberCellUserCanSee > 1) {
                    $userIsAbleToSeeManyCells = true;
                    break;
                } else if ($numberCellUserCanSee == 1) {
                    break;
                }
            }
            if ($userIsAbleToSeeManyCells) {
                $data['details'] = $this->cellLink('orga/project/cells/idProject/'.$project->getKey()['id']);
            } else {
                $cellWithAccess = Orga_Model_Cell::loadList($aclProjectQuery);
                $data['details'] = $this->cellLink('orga/cell/details/idCell/'.$cellWithAccess[0]->getKey()['id']);
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
        /** @var Core_Work_Dispatcher $workDispatcher */
        $workDispatcher = Zend_Registry::get('workDispatcher');

        $administrator = $this->_helper->auth();
        $label = $this->getAddElementValue('label');

        $workDispatcher->runBackground(
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
        /** @var Core_Work_Dispatcher $workDispatcher */
        $workDispatcher = Zend_Registry::get('workDispatcher');

        $project = Orga_Model_Project::load(array('id' => $this->delete));

        $workDispatcher->runBackground(
            new Core_Work_ServiceCall_Task(
                'Orga_Service_ProjectService',
                'deleteProject',
                [$project]
            )
        );

        $this->message = __('UI', 'message', 'deletedLater');
        $this->send();
    }

    /**
     * Met à jour un element.
     * @Secure("editProject")
     */
    public function updateelementAction()
    {
        $project = Orga_Model_Project::load(array('id' => $this->update['index']));
        switch ($this->update['column']) {
            case 'label':
                $project->setLabel($this->update['value']);
                $this->data = $project->getLabel();
                break;
            case 'granularityForInventoryStatus':
                $granularity = Orga_Model_Granularity::loadByRefAndProject(
                    $this->update['value'],
                    $project
                );
                foreach ($project->getInputGranularities() as $inputGranularity) {
                    if ($inputGranularity->isNarrowerThan($granularity)) {
                        throw new Core_Exception_User('Orga', 'projectList', 'InputGranularityNotNarrowerThanNewGranularity');
                    }
                }
                $project->setGranularityForInventoryStatus($granularity);
                $this->data = $this->cellList(
                    $project->getGranularityForInventoryStatus()->getRef(),
                    $project->getGranularityForInventoryStatus()->getLabel()
                );
                break;
            default:
                parent::updateelementAction();

        }
        $this->message = __('UI', 'message', 'updated');
        $this->send();
    }

    /**
     * List des granularité d'un projet.
     * @Secure("editProject")
     */
    public function getlistgranularitiesAction()
    {
        $project = Orga_Model_Project::load(array('id' => $this->getParam('index')));
        foreach ($project->getGranularities() as $granularity) {
            $this->addElementList($granularity->getRef(), $granularity->getLabel());
        }
        $this->send();
    }

}
