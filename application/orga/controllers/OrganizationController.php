<?php
/**
 * Classe Orga_OrganizationController
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use Core\Work\ServiceCall\ServiceCallTask;
use MyCLabs\Work\Dispatcher\WorkDispatcher;
use Orga\ViewModel\OrganizationViewModelFactory;

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
     * @Inject
     * @var OrganizationViewModelFactory
     */
    private $organizationVMFactory;

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
        $organizationsVM = [];
        foreach ($organizations as $organization) {
            $vm = $this->organizationVMFactory->createOrganizationViewModel($organization, $connectedUser);
            $organizationsVM[] = $vm;
        }
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
        UI_Form::addHeader();
    }

    /**
     * @Secure("createOrganization")
     */
    public function addSubmitAction()
    {
        $user = $this->_helper->auth();
        $formData = json_decode($this->getRequest()->getParam('addOrganization'), true);
        $label = $formData['organization']['elements']['organizationLabel']['value'];

        $success = function () {
            $this->sendJsonResponse('ok');
        };
        $timeout = function () {
            $this->sendJsonResponse('processing');
        };
        $error = function (Exception $e) {
            throw $e;
        };

        // Lance la tache en arrière plan
        $task = new ServiceCallTask(
            'Orga_Service_OrganizationService',
            'createOrganization',
            [$user, $formData],
            __('Orga', 'backgroundTasks', 'createOrganization', ['LABEL' => $label])
        );
        $this->workDispatcher->runBackground($task, $this->waitDelay, $success, $timeout, $error);
    }

    /**
     * @Secure("deleteOrganization")
     */
    public function deleteAction()
    {
        $organization = Orga_Model_Organization::load($this->_getParam('idOrganization'));

        $success = function () {
            UI_Message::addMessageStatic(__('UI', 'message', 'deleted'), UI_Message::TYPE_SUCCESS);
        };
        $timeout = function () {
            UI_Message::addMessageStatic(__('UI', 'message', 'deletedLater'), UI_Message::TYPE_SUCCESS);
        };
        $error = function (Exception $e) {
            throw $e;
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
            $this->sendJsonResponse(__('DW', 'rebuild', 'analysisDataRebuildConfirmationMessage'));
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
