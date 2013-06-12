<?php
/**
 * Classe Orga_ProjectController
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use DI\Annotation\Inject;


/**
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */
class Orga_ProjectController extends Core_Controller
{
    use UI_Controller_Helper_Form;

    /**
     * @Inject
     * @var User_Service_ACL
     */
    private $aclService;

    /**
     * @Inject
     * @var Orga_Service_ETLStructure
     */
    private $etlStructureService;

    /**
     * Redirection sur la liste.
     * @Secure("loggedIn")
     */
    public function indexAction()
    {
        /** @var User_Model_User $connectedUser */
        $connectedUser = $this->_helper->auth();

        $projectResource = User_Model_Resource_Entity::loadByEntityName('Orga_Model_Project');
        $isConnectedUserAbleToCreateProjects = $this->aclService->isAllowed(
            $connectedUser,
            User_Model_Action_Default::CREATE(),
            $projectResource
        );

        $aclQuery = new Core_Model_Query();
        $aclQuery->aclFilter->enabled = true;
        $aclQuery->aclFilter->user = $connectedUser;
        $aclQuery->aclFilter->action = User_Model_Action_Default::DELETE();
        $isConnectedUserAbleToDeleteProjects = (Orga_Model_Project::countTotal($aclQuery) > 0);
        $aclQuery->aclFilter->action = User_Model_Action_Default::VIEW();
        $isConnectedUserAbleToSeeManyProjects = (Orga_Model_Project::countTotal($aclQuery) > 1);

        $listCellResource = array();
        foreach ($connectedUser->getLinkedResources() as $cellResource) {
            if (($cellResource instanceof User_Model_Resource_Entity)
                && ($cellResource->getEntity() instanceof Orga_Model_Cell)
                && (!in_array($cellResource, $listCellResource))
            ) {
                $listCellResource[] = $cellResource;
            }
        }
        foreach ($connectedUser->getRoles() as $userRole) {
            foreach ($userRole->getLinkedResources() as $cellResource) {
                if (($cellResource instanceof User_Model_Resource_Entity)
                    && ($cellResource->getEntity() instanceof Orga_Model_Cell)
                    && (!in_array($cellResource, $listCellResource))
                ) {
                    $listCellResource[] = $cellResource;
                }
            }
        }
        $isConnectedUserAbleToSeeManyCells = (count($listCellResource) > 1);

        if (($isConnectedUserAbleToCreateProjects)
            || ($isConnectedUserAbleToDeleteProjects)
            || ($isConnectedUserAbleToSeeManyProjects)
        ) {
            $this->redirect('orga/project/manage');
        } else if ($isConnectedUserAbleToSeeManyCells) {
            $projectArray = Orga_Model_Project::loadList($aclQuery);
            $this->redirect('orga/project/cells/idProject/'.array_pop($projectArray)->getId());
        } else if (count($listCellResource) == 1) {
            $this->redirect('orga/cell/details/idCell/'.array_pop($listCellResource)->getEntity()->getId());
        } else {
            $this->forward('noaccess', 'project', 'orga');
        }
    }

    /**
     * Liste des projets.
     * @Secure("viewProjects")
     */
    public function manageAction()
    {
        $connectedUser = $this->_helper->auth();

        $projectResource = User_Model_Resource_Entity::loadByEntityName('Orga_Model_Project');
        $this->view->isConnectedUserAbleToCreateProjects = $this->aclService->isAllowed(
            $connectedUser,
            User_Model_Action_Default::CREATE(),
            $projectResource
        );

        $aclQuery = new Core_Model_Query();
        $aclQuery->aclFilter->enabled = true;
        $aclQuery->aclFilter->user = $connectedUser;
        $aclQuery->aclFilter->action = User_Model_Action_Default::EDIT();
        $this->view->isConnectedUserAbleToEditProjects = (Orga_Model_Project::countTotal($aclQuery) > 0);
        $aclQuery->aclFilter->action = User_Model_Action_Default::DELETE();
        $this->view->isConnectedUserAbleToDeleteProjects = (Orga_Model_Project::countTotal($aclQuery) > 0);
    }

    /**
     * Action de détails d'un project.
     * @Secure("editProject")
     */
    public function detailsAction()
    {
        $idProject = $this->getParam('idProject');
        $project = Orga_Model_Project::load($idProject);

        $this->view->idProject = $idProject;
        $this->view->projectLabel = $project->getLabel();
        $this->view->granularities = $project->getGranularities();
        try {
            $this->view->granularityRefForInventoryStatus = $project->getGranularityForInventoryStatus()->getRef();
        } catch (Core_Exception_UndefinedAttribute $e) {
            $this->view->granularityRefForInventoryStatus = null;
        }
        $this->view->granularitiesWithDWCube = array();
        foreach ($project->getGranularities() as $granularity) {
            if ($granularity->getCellsGenerateDWCubes()) {
                $this->view->granularitiesWithDWCube[] = $granularity;
            }
        }


        if ($this->hasParam('display') && ($this->getParam('display') === 'render')) {
            $this->_helper->layout()->disableLayout();
            $this->view->display = false;
        } else {
            $this->view->display = true;
        }
    }

    /**
     * Action de détails d'un project.
     * @Secure("editProject")
     */
    public function editAction()
    {
        $idProject = $this->getParam('idProject');
        $project = Orga_Model_Project::load($idProject);
        $formData = $this->getFormData('projectDetails');

        $refGranularityForInventoryStatus = $formData->getValue('granularityForInventoryStatus');
        if (!empty($refGranularityForInventoryStatus)) {
            $granularityForInventoryStatus = Orga_Model_Granularity::loadByRefAndProject(
                $refGranularityForInventoryStatus,
                $project
            );
            try {
                $project->setGranularityForInventoryStatus($granularityForInventoryStatus);
            } catch (Core_Exception_InvalidArgument $e) {
                $this->addFormError('granularityForInventoryStatus', __('Orga', 'exception', 'broaderInputGranularity'));
            }
        }

        $label = $formData->getValue('label');
        if ($project->getLabel() !== $label) {
            $project->setLabel($label);
        }

        $this->setFormMessage(__('Orga', 'project', 'updated'));

        $this->sendFormResponse();
    }

    /**
     * Affiche le popup propopsant de regénérer les projects et données de DW.
     * @Secure("editProject")
     */
    public function dwcubesstateAction()
    {
        // Désactivation du layout.
        $this->_helper->layout()->disableLayout();
        $this->view->idProject = $this->getParam('idProject');
        $this->view->areProjectDWCubesUpToDate = $this->etlStructureService->areProjectDWCubesUpToDate(
            Orga_Model_Project::load($this->view->idProject)
        );
    }

    /**
     * Réinitialise les DW Projects du Project donné.
     * @Secure("editProject")
     */
    public function resetdwcubesAction()
    {
        /** @var Core_Work_Dispatcher $workDispatcher */
        $workDispatcher = Zend_Registry::get('workDispatcher');

        $project = Orga_Model_Project::load($this->getParam('idProject'));

        try {
            // Lance la tache en arrière plan
            $workDispatcher->runBackground(
                new Core_Work_ServiceCall_Task(
                    'Orga_Service_ETLStructure',
                    'resetProjectDWCubes',
                    [$project]
                )
            );
        } catch (Core_Exception_NotFound $e) {
            throw new Core_Exception_User('DW', 'rebuild', 'analysisDataRebuildFailMessage');
        }
        $this->sendJsonResponse(array('message' => __('UI', 'message', 'operationInProgress')));
    }

    /**
     * Controller de la vue de la cohérence d'un project.
     * @Secure("viewProject")
     */
    public function consistencyAction()
    {
        if ($this->hasParam('idCell')) {
            $this->view->idCell = $this->getParam('idCell');
        } else {
            $this->view->idCell = null;
        }
        $this->view->idProject = $this->getParam('idProject');

        if ($this->hasParam('display') && ($this->getParam('display') === 'render')) {
            $this->_helper->layout()->disableLayout();
            $this->view->display = false;
        } else {
            $this->view->display = true;
        }
    }

    /**
     * Liste des cellules d'un projet.
     * @Secure("viewProject")
     */
    public function cellsAction()
    {
        $project = Orga_Model_Project::load($this->getParam('idProject'));

        $this->view->idProject = $this->getParam('idProject');

        $this->view->listGranularities = array();
        foreach ($project->getGranularities() as $granularity) {
            if ($granularity->isNavigable()) {
                $this->view->listGranularities[$granularity->getRef()] = $granularity->getLabel();
            }
        }

        $this->view->listAccess = array(
            'cellAdministrator' => __('Orga', 'role', 'cellAdministrator'),
            'cellContributor' => __('Orga', 'role', 'cellContributor'),
            'cellDataProviderObserver' => __('Orga', 'role', 'cellObserver'),
        );
    }

    /**
     * Affiche une vue indiquant l'accès à aucun projet.
     * @Secure("loggedIn")
     */
    public function noaccessAction()
    {

    }
}