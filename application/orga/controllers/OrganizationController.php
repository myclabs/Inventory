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
     * @var OrganizationViewModelFactory
     */
    private $organizationVMFactory;

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

        if (!$isConnectedUserAbleToCreateOrganizations) {
            $aclQuery = new Core_Model_Query();
            $aclQuery->aclFilter->enabled = true;
            $aclQuery->aclFilter->user = $connectedUser;
            $aclQuery->aclFilter->action = User_Model_Action_Default::DELETE();

            $isConnectedUserAbleToDeleteOrganizations = (Orga_Model_Organization::countTotal($aclQuery) > 0);

            if (!$isConnectedUserAbleToDeleteOrganizations) {
                $aclQuery->aclFilter->action = User_Model_Action_Default::VIEW();
                $isConnectedUserAbleToSeeManyOrganizations = (Orga_Model_Organization::countTotal($aclQuery) > 1);
            }
        }

        if (($isConnectedUserAbleToCreateOrganizations)
            || ($isConnectedUserAbleToDeleteOrganizations)
            || ($isConnectedUserAbleToSeeManyOrganizations)
        ) {
            $this->redirect('orga/organization/manage');
        } else {
            $organizationsWithAccess = Orga_Model_Organization::loadList($aclQuery);
            $idOrganization = array_pop($organizationsWithAccess)->getId();
            $this->redirect('orga/organization/view/idOrganization/'.$idOrganization);
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
        $organizationsViewModel = [];
        foreach ($organizations as $organization) {
            $organizationsViewModel[] = $this->organizationVMFactory->createOrganizationViewModel($organization, $connectedUser);
        }
        $this->view->assign('organizations', $organizationsViewModel);

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
            UI_Message::addMessageStatic(__('UI', 'message', 'deletedLater'), UI_Message::TYPE_INFO);
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
     * @Secure("viewOrganization")
     */
    public function viewAction()
    {
        /** @var User_Model_User $connectedUser */
        $connectedUser = $this->_helper->auth();

        $idOrganization = $this->getParam('idOrganization');
        /** @var Orga_Model_Organization $organization */
        $organization = Orga_Model_Organization::load($idOrganization);

        foreach ($organization->getGranularities() as $granularity) {
            $aclCellQuery = new Core_Model_Query();
            $aclCellQuery->aclFilter->enabled = true;
            $aclCellQuery->aclFilter->user = $connectedUser;
            $aclCellQuery->aclFilter->action = User_Model_Action_Default::VIEW();
            $aclCellQuery->filter->addCondition(Orga_Model_Cell::QUERY_GRANULARITY, $granularity);

            $numberCellsUserCanSee = Orga_Model_Cell::countTotal($aclCellQuery);
            if ($numberCellsUserCanSee == 1) {
                $cellsWithAccess = Orga_Model_Cell::loadList($aclCellQuery);
                $idCell = array_pop($cellsWithAccess)->getId();
                $this->redirect('orga/cell/view/idCell/'.$idCell);
                break;
            } else if ($numberCellsUserCanSee > 1) {
                $this->view->assign('organization', $this->organizationVMFactory->createOrganizationViewModel($organization, $connectedUser));

                $listGranularities = array();
                foreach ($organization->getGranularities() as $granularity) {
                    if ($granularity->isNavigable()) {
                        $listGranularities[$granularity->getRef()] = $granularity->getLabel();
                    }
                }
                $this->view->assign('listGranularities', $listGranularities);
                break;
            }
        }
    }

    /**
     * Action de détails d'un organization.
     * @Secure("editOrganization")
     * todo organization/details utilité/utilisé ?
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
     *@todo remplacer detailsAction par editAction et ajouter editsubmitAction
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
     * Affiche une vue indiquant l'accès à aucun projet.
     * @Secure("loggedIn")
     */
    public function noaccessAction()
    {
    }
}
