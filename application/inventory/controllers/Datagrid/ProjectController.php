<?php
/**
 * @author valentin.claras
 * @package Inventory
 */

use Core\Annotation\Secure;

/**
 * Controller de projet
 * @package Inventory
 */
class Inventory_Datagrid_ProjectController extends UI_Controller_Datagrid
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

        foreach (Inventory_Model_Project::loadList($this->request) as $project) {
            $data = array();
            $data['index'] = $project->getKey()['id'];
            $data['label'] = $project->getLabel();
            try {
                $data['orgaGranularityForInventoryStatus'] = $this->cellList(
                    $project->getOrgaGranularityForInventoryStatus()->getRef(),
                    $project->getOrgaGranularityForInventoryStatus()->getLabel()
                );
            } catch (Core_Exception_UndefinedAttribute $e) {
                $data['orgaGranularityForInventoryStatus'] = $this->cellList(null, '');
            };
            $data['resetDWCubesAndDatasUpToDate'] = $this->cellPopup(
                'inventory/project/reset/idProject/'.$data['index'],
                __('Inventory', 'projectList', 'statusRebuildColumnLink'),
                'zoom-in'
            );

            $userIsAbleToSeeManyCells = false;
            foreach ($project->getOrgaCube()->getGranularities() as $orgaGranularity) {
                $aclProjectQuery = new Core_Model_Query();
                $aclProjectQuery->aclFilter->enabled = true;
                $aclProjectQuery->aclFilter->user = $this->_helper->auth();
                $aclProjectQuery->aclFilter->action = User_Model_Action_Default::VIEW();
                $aclProjectQuery->filter->addCondition(
                    Orga_Model_Cell::QUERY_GRANULARITY,
                    $orgaGranularity,
                    Core_Model_Filter::OPERATOR_EQUAL,
                    Orga_Model_Cell::getAlias()
                );
                $numberCellUserCanSee = Inventory_Model_CellDataProvider::countTotal($aclProjectQuery);
                if ($numberCellUserCanSee > 1) {
                    $userIsAbleToSeeManyCells = true;
                    break;
                } else if ($numberCellUserCanSee == 1) {
                    break;
                }
            }
            if ($userIsAbleToSeeManyCells) {
                $data['details'] = $this->cellLink('inventory/project/cells/idProject/'.$project->getKey()['id']);
            } else {
                $cellDataProviderWithAccess = Inventory_Model_CellDataProvider::loadList($aclProjectQuery);
                $orgaCell = $cellDataProviderWithAccess[0]->getOrgaCell();
                $data['details'] = $this->cellLink('inventory/cell/details/idCell/'.$orgaCell->getKey()['id']);
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
                'Inventory_Service_ProjectService',
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

        $project = Inventory_Model_Project::load(array('id' => $this->delete));

        $workDispatcher->runBackground(
            new Core_Work_ServiceCall_Task(
                'Inventory_Service_ProjectService',
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
        $project = Inventory_Model_Project::load(array('id' => $this->update['index']));
        switch ($this->update['column']) {
            case 'label':
                $project->setLabel($this->update['value']);
                $this->data = $project->getLabel();
                break;
            case 'orgaGranularityForInventoryStatus':
                $orgaGranularity = Orga_Model_Granularity::loadByRefAndCube(
                    $this->update['value'],
                    $project->getOrgaCube()
                );
                foreach ($project->getAFGranularities() as $aFGranularities) {
                    if (!($aFGranularities->getAFInputOrgaGranularity()->isNarrowerThan($orgaGranularity))) {
                        throw new Core_Exception_User('Inventory', 'projectList', 'InputGranularityNotNarrowerThanNewInventoryGranularity');
                    }
                }
                $project->setOrgaGranularityForInventoryStatus($orgaGranularity);
                $this->data = $this->cellList(
                    $project->getOrgaGranularityForInventoryStatus()->getRef(),
                    $project->getOrgaGranularityForInventoryStatus()->getLabel()
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
        $project = Inventory_Model_Project::load(array('id' => $this->_getParam('index')));
        foreach ($project->getOrgaCube()->getGranularities() as $orgaGranularities) {
            $this->addElementList($orgaGranularities->getRef(), $orgaGranularities->getLabel());
        }
        $this->send();
    }

}
