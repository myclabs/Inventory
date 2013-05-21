<?php
/**
 * @author valentin.claras
 * @package Inventory
 */

use Core\Annotation\Secure;


/**
 * @author valentin.claras
 * @package Inventory
 */
class Inventory_ProjectController extends Core_Controller_Ajax
{
    /**
     * Redirection sur la liste.
     * @Secure("loggedIn")
     */
    public function indexAction()
    {
        $connectedUser = $this->_helper->auth();
        $aclService = User_Service_ACL::getInstance();

        $projectResource = User_Model_Resource_Entity::loadByEntityName('Inventory_Model_Project');
        $isConnectedUserAbleToCreateProjects = $aclService->isAllowed(
            $connectedUser,
            User_Model_Action_Default::CREATE(),
            $projectResource
        );

        $aclQuery = new Core_Model_Query();
        $aclQuery->aclFilter->enabled = true;
        $aclQuery->aclFilter->user = $connectedUser;
        $aclQuery->aclFilter->action = User_Model_Action_Default::EDIT();
        $isConnectedUserAbleToEditProjects = (Inventory_Model_Project::countTotal($aclQuery) > 0);
        $aclQuery->aclFilter->action = User_Model_Action_Default::VIEW();
        $isConnectedUserAbleToSeeManyProjects = (Inventory_Model_Project::countTotal($aclQuery) > 1);

        $listCellDataProviderResource = array();
        foreach ($connectedUser->getLinkedResources() as $cellDataProviderResource) {
            if (($cellDataProviderResource instanceof User_Model_Resource_Entity)
                && ($cellDataProviderResource->getEntity() instanceof Inventory_Model_CellDataProvider)
                && (!in_array($cellDataProviderResource, $listCellDataProviderResource))
            ) {
                $listCellDataProviderResource[] = $cellDataProviderResource;
            }
        }
        foreach ($connectedUser->getRoles() as $userRole) {
            foreach ($userRole->getLinkedResources() as $cellDataProviderResource) {
                if (($cellDataProviderResource instanceof User_Model_Resource_Entity)
                    && ($cellDataProviderResource->getEntity() instanceof Inventory_Model_CellDataProvider)
                    && (!in_array($cellDataProviderResource, $listCellDataProviderResource))
                ) {
                    $listCellDataProviderResource[] = $cellDataProviderResource;
                }
            }
        }
        $isConnectedUserAbleToSeeManyCellDataProviders = (count($listCellDataProviderResource) > 1);

        if (($isConnectedUserAbleToCreateProjects)
            || ($isConnectedUserAbleToEditProjects)
            || ($isConnectedUserAbleToSeeManyProjects)
        ) {
            $this->_redirect('inventory/project/manage');
        } else if ($isConnectedUserAbleToSeeManyCellDataProviders) {
            $projectArray = Inventory_Model_Project::loadList($aclQuery);
            $this->_redirect('inventory/project/cells/idProject/'.$projectArray[0]->getKey()['id']);
        } else {
            $cellDataProviderArray = Inventory_Model_CellDataProvider::loadList($aclQuery);
            $this->_redirect('inventory/cell/details/idCell/'.$cellDataProviderArray[0]->getOrgaCell()->getKey()['id']);
        }
    }

    /**
     * Liste des projets.
     * @Secure("viewProjects")
     */
    public function manageAction()
    {
        $connectedUser = $this->_helper->auth();
        $aclService = User_Service_ACL::getInstance();

        $projectResource = User_Model_Resource_Entity::loadByEntityName('Inventory_Model_Project');
        $this->view->isConnectedUserAbleToCreateProjects = $aclService->isAllowed(
            $connectedUser,
            User_Model_Action_Default::CREATE(),
            $projectResource
        );

        $aclQuery = new Core_Model_Query();
        $aclQuery->aclFilter->enabled = true;
        $aclQuery->aclFilter->user = $connectedUser;
        $aclQuery->aclFilter->action = User_Model_Action_Default::EDIT();
        $this->view->isConnectedUserAbleToEditProjects = (Inventory_Model_Project::countTotal($aclQuery) > 0);
        $aclQuery->aclFilter->action = User_Model_Action_Default::DELETE();
        $this->view->isConnectedUserAbleToDeleteProjects = (Inventory_Model_Project::countTotal($aclQuery) > 0);
    }

    /**
     * Liste des cellules d'un projet.
     * @Secure("viewProject")
     */
    public function cellsAction()
    {
        $project = Inventory_Model_Project::load(array('id' => $this->_getParam('idProject')));

        $this->view->idProject = $this->_getParam('idProject');

        $this->view->listGranularities = array();
        foreach ($project->getOrgaCube()->getGranularities() as $orgaGranularity) {
            if ($orgaGranularity->isNavigable()) {
                $this->view->listGranularities[$orgaGranularity->getRef()] = $orgaGranularity->getLabel();
            }
        }

        $this->view->listAccess = array(
            'cellDataProviderAdministrator' => __('Inventory', 'role', 'cellAdministrator'),
            'cellDataProviderContributor' => __('Inventory', 'role', 'cellContributor'),
            'cellDataProviderObserver' => __('Inventory', 'role', 'cellObserver'),
        );
    }

    /**
     * Affiche le popup prpopsant de regénérer les cubes et données de DW.
     * @Secure("editProject")
     */
    public function resetAction()
    {
        // Désactivation du layout.
        $this->_helper->layout()->disableLayout();
        $this->view->idProject = $this->_getParam('idProject');
        $this->view->areProjetDWCubesUpToDate = Inventory_Service_ETLStructure::getInstance()->areProjetDWCubesUpToDate(
                Inventory_Model_Project::load(array('id' => $this->view->idProject))
        );
    }

    /**
     * Réinitialise les DW Cubes du Project donné.
     * @Secure("editProject")
     */
    public function resetdwcubesAction()
    {
        /** @var Core_Work_Dispatcher $workDispatcher */
        $workDispatcher = Zend_Registry::get('workDispatcher');

        $project = Inventory_Model_Project::load(array('id' => $this->_getParam('idProject')));

        try {
            // Lance la tache en arrière plan
            $workDispatcher->runBackground(
                new Core_Work_ServiceCall_Task(
                    'Inventory_Service_ETLStructure',
                    'resetProjectDWCubes',
                    [$project]
                )
            );
        } catch (Core_Exception_NotFound $e) {
            throw new Core_Exception_User('DW', 'rebuild', 'analysisDataRebuildFailMessage');
        }
        $this->sendJsonResponse(array('message' => __('UI', 'message', 'operationInProgress')));
    }
}