<?php
/**
 * Classe Orga_OrganizationController
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use Core\Work\ServiceCall\ServiceCallTask;
use DI\Annotation\Inject;
use MyCLabs\Work\Dispatcher\WorkDispatcher;
use Orga\ViewModel\OrganizationViewModel;

/**
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */
class Orga_OrganizationController extends Core_Controller
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
     * @Inject
     * @var WorkDispatcher
     */
    private $workDispatcher;

    /**
     * @Inject("work.waitDelay")
     * @var int
     */
    private $waitDelay;

    /**
     * Redirection sur la liste.
     * @Secure("loggedIn")
     */
    public function indexAction()
    {
        /** @var User_Model_User $connectedUser */
        $connectedUser = $this->_helper->auth();

        $organizationResource = User_Model_Resource_Entity::loadByEntityName('Orga_Model_Organization');
        $isConnectedUserAbleToCreateOrganizations = $this->aclService->isAllowed(
            $connectedUser,
            User_Model_Action_Default::CREATE(),
            $organizationResource
        );

        $aclQuery = new Core_Model_Query();
        $aclQuery->aclFilter->enabled = true;
        $aclQuery->aclFilter->user = $connectedUser;
        $aclQuery->aclFilter->action = User_Model_Action_Default::DELETE();
        $isConnectedUserAbleToDeleteOrganizations = (Orga_Model_Organization::countTotal($aclQuery) > 0);
        $aclQuery->aclFilter->action = User_Model_Action_Default::VIEW();
        $isConnectedUserAbleToSeeManyOrganizations = (Orga_Model_Organization::countTotal($aclQuery) > 1);

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

        if (($isConnectedUserAbleToCreateOrganizations)
            || ($isConnectedUserAbleToDeleteOrganizations)
            || ($isConnectedUserAbleToSeeManyOrganizations)
        ) {
            $this->redirect('orga/organization/manage');
        } elseif ($isConnectedUserAbleToSeeManyCells) {
            $organizationArray = Orga_Model_Organization::loadList($aclQuery);
            $this->redirect('orga/organization/cells/idOrganization/'.array_pop($organizationArray)->getId());
        } elseif (count($listCellResource) == 1) {
            $this->redirect('orga/cell/details/idCell/'.array_pop($listCellResource)->getEntity()->getId());
        } else {
            $this->forward('noaccess', 'organization', 'orga');
        }
    }

    /**
     * Liste des projets.
     * @Secure("viewOrganizations")
     */
    public function manageAction()
    {
        $connectedUser = $this->_helper->auth();

        // Retrouve la liste des organisations
        $query = new Core_Model_Query();
        $query->aclFilter->enabled = true;
        $query->aclFilter->user = $connectedUser;
        $query->aclFilter->action = User_Model_Action_Default::VIEW();
        $organizations = Orga_Model_Organization::loadList($query);

        // Crée les ViewModel
        $createViewModel = function (Orga_Model_Organization $organization) use ($connectedUser) {
            $viewModel = new OrganizationViewModel();
            $viewModel->id = $organization->getId();
            $viewModel->label = $organization->getLabel();
            $viewModel->rootAxesLabels = array_map(
                function (Orga_Model_Axis $axis) {
                    return $axis->getLabel();
                },
                $organization->getRootAxes()
            );
            $viewModel->canBeDeleted = $this->aclService->isAllowed(
                $connectedUser,
                User_Model_Action_Default::DELETE(),
                $organization
            );
            try {
                $viewModel->inventory =  $organization->getGranularityForInventoryStatus()->getLabel();
            } catch (Core_Exception_UndefinedAttribute $e) {
            };
            $canUserSeeManyCells = false;
            foreach ($organization->getGranularities() as $granularity) {
                $aclCellQuery = new Core_Model_Query();
                $aclCellQuery->aclFilter->enabled = true;
                $aclCellQuery->aclFilter->user = $connectedUser;
                $aclCellQuery->aclFilter->action = User_Model_Action_Default::VIEW();
                $aclCellQuery->filter->addCondition(Orga_Model_Cell::QUERY_GRANULARITY, $granularity);
                $numberCellsUserCanSee = Orga_Model_Cell::countTotal($aclCellQuery);
                if ($numberCellsUserCanSee > 1) {
                    $canUserSeeManyCells = true;
                    break;
                } elseif ($numberCellsUserCanSee == 1) {
                    break;
                }
            }
            if ($canUserSeeManyCells) {
                $viewModel->link = 'orga/organization/cells/idOrganization/' . $organization->getId();
            } elseif ($numberCellsUserCanSee == 1) {
                $cellWithAccess = Orga_Model_Cell::loadList($aclCellQuery);
                $viewModel->link = 'orga/cell/details/idCell/' . array_pop($cellWithAccess)->getId();
            }
            return $viewModel;
        };
        $organizationsVM = array_map($createViewModel, $organizations);
        $this->view->assign('organizations', $organizationsVM);

        $organizationResource = User_Model_Resource_Entity::loadByEntityName('Orga_Model_Organization');
        $this->view->assign('canCreateOrganization', $this->aclService->isAllowed(
            $connectedUser,
            User_Model_Action_Default::CREATE(),
            $organizationResource
        ));
    }

    /**
     * @Secure("createOrganization")
     */
    public function addAction()
    {
        $user = $this->_helper->auth();
        $label = $this->getParam('label');

        $success = function() {
            UI_Message::addMessageStatic(__('UI', 'message', 'added'));
        };
        $timeout = function() {
            UI_Message::addMessageStatic(__('UI', 'message', 'addedLater'));
        };
        $error = function() {
            throw new Core_Exception("Error in the background task");
        };

        // Lance la tache en arrière plan
        $task = new ServiceCallTask(
            'Orga_Service_OrganizationService',
            'createOrganization',
            [$user, $label],
            __('Orga', 'backgroundTasks', 'createOrganization', ['LABEL' => $label])
        );
        $this->workDispatcher->runBackground($task, $this->waitDelay, $success, $timeout, $error);

        $this->redirect('orga/organization/manage');
    }

    /**
     * @Secure("deleteOrganization")
     */
    public function deleteAction()
    {
        $organization = Orga_Model_Organization::load($this->_getParam('idOrganization'));

        $success = function() {
            UI_Message::addMessageStatic(__('UI', 'message', 'deleted'));
        };
        $timeout = function() {
            UI_Message::addMessageStatic(__('UI', 'message', 'deletedLater'));
        };
        $error = function() {
            throw new Core_Exception("Error in the background task");
        };

        // Lance la tache en arrière plan
        $task = new ServiceCallTask(
            'Orga_Service_OrganizationService',
            'deleteOrganization',
            [$organization]
        );
        $this->workDispatcher->runBackground($task, $this->waitDelay, $success, $timeout, $error);

        $this->redirect('orga/organization/manage');
    }

    /**
     * Action de détails d'un organization.
     * @Secure("editOrganization")
     */
    public function detailsAction()
    {
        $idOrganization = $this->getParam('idOrganization');
        $organization = Orga_Model_Organization::load($idOrganization);

        $this->view->idOrganization = $idOrganization;
        $this->view->organizationLabel = $organization->getLabel();
        $this->view->granularities = $organization->getGranularities();
        try {
            $this->view->granularityRefForInventoryStatus = $organization->getGranularityForInventoryStatus()->getRef();
        } catch (Core_Exception_UndefinedAttribute $e) {
            $this->view->granularityRefForInventoryStatus = null;
        }
        $this->view->granularitiesWithDWCube = array();
        foreach ($organization->getGranularities() as $granularity) {
            if ($granularity->getCellsGenerateDWCubes()) {
                $this->view->granularitiesWithDWCube[] = $granularity;
            }
        }


        if ($this->hasParam('display') && ($this->getParam('display') === 'render')) {
            $this->_helper->layout()->disableLayout();
            $this->view->display = false;
            $this->view->granularityReportBaseUrl = 'orga/granularity/report/idCell/'.$this->getParam('idCell');
        } else {
            $this->view->display = true;
            $this->view->granularityReportBaseUrl = 'orga/granularity/report/idOrganization/'.$idOrganization;
        }
    }

    /**
     * Action de détails d'un organization.
     * @Secure("editOrganization")
     */
    public function editAction()
    {
        $idOrganization = $this->getParam('idOrganization');
        $organization = Orga_Model_Organization::load($idOrganization);
        $formData = $this->getFormData('organizationDetails');

        $refGranularityForInventoryStatus = $formData->getValue('granularityForInventoryStatus');
        if (!empty($refGranularityForInventoryStatus)) {
            $granularityForInventoryStatus = Orga_Model_Granularity::loadByRefAndOrganization(
                $refGranularityForInventoryStatus,
                $organization
            );
            try {
                $organization->setGranularityForInventoryStatus($granularityForInventoryStatus);
            } catch (Core_Exception_InvalidArgument $e) {
                $this->addFormError(
                    'granularityForInventoryStatus',
                    __('Orga', 'exception', 'broaderInputGranularity')
                );
            }
        }

        $label = (string) $formData->getValue('label');
        if ($organization->getLabel() !== $label) {
            $organization->setLabel($label);
        }

        $this->setFormMessage(__('UI', 'message', 'updated'));

        $this->sendFormResponse();
    }

    /**
     * Affiche le popup propopsant de regénérer les organizations et données de DW.
     * @Secure("editOrganization")
     */
    public function dwcubesstateAction()
    {
        set_time_limit(0);
        $this->sendJsonResponse([
            'organizationDWCubesState' => $this->etlStructureService->areOrganizationDWCubesUpToDate(
                Orga_Model_Organization::load($this->getParam('idOrganization'))
            )
        ]);
    }

    /**
     * Réinitialise les DW Organizations du Organization donné.
     * @Secure("editOrganization")
     */
    public function resetdwcubesAction()
    {
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));

        $success = function () {
            $this->sendJsonResponse(__('UI', 'message', 'updated'));
        };
        $timeout = function () {
            $this->sendJsonResponse(__('UI', 'message', 'operationInProgress'));
        };
        $error = function () {
            throw new Core_Exception_User('DW', 'rebuild', 'analysisDataRebuildFailMessage');
        };

        // Lance la tache en arrière plan
        $task = new ServiceCallTask(
            'Orga_Service_ETLStructure',
            'resetOrganizationDWCubes',
            [$organization],
            __('Orga', 'backgroundTasks', 'resetDWOrga', ['LABEL' => $organization->getLabel()])
        );
        $this->workDispatcher->runBackground($task, $this->waitDelay, $success, $timeout, $error);
    }

    /**
     * Controller de la vue de la cohérence d'un organization.
     * @Secure("viewOrganization")
     */
    public function consistencyAction()
    {
        if ($this->hasParam('idCell')) {
            $this->view->idCell = $this->getParam('idCell');
        } else {
            $this->view->idCell = null;
        }
        $this->view->idOrganization = $this->getParam('idOrganization');

        if ($this->hasParam('display') && ($this->getParam('display') === 'render')) {
            $this->_helper->layout()->disableLayout();
            $this->view->display = false;
        } else {
            $this->view->display = true;
        }
    }

    /**
     * Liste des cellules d'un projet.
     * @Secure("viewOrganization")
     */
    public function cellsAction()
    {
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));

        $this->view->idOrganization = $this->getParam('idOrganization');

        $this->view->listGranularities = array();
        foreach ($organization->getGranularities() as $granularity) {
            if ($granularity->isNavigable()) {
                $this->view->listGranularities[$granularity->getRef()] = $granularity->getLabel();
            }
        }

        $this->view->listAccess = array(
            'cellAdministrator' => __('Orga', 'role', 'cellAdministrator'),
            'cellContributor' => __('Orga', 'role', 'cellContributor'),
            'cellObserver' => __('Orga', 'role', 'cellObserver'),
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
