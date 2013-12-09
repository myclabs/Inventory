<?php

use Core\Annotation\Secure;
use Core\Work\ServiceCall\ServiceCallTask;
use MyCLabs\Work\Dispatcher\WorkDispatcher;
use Orga\Model\ACL\Role\AbstractCellRole;
use Orga\ViewModel\OrganizationViewModelFactory;
use User\Domain\ACL\Action;
use User\Domain\ACL\ACLService;
use User\Domain\ACL\Resource\NamedResource;
use User\Domain\User;

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
     * @var ACLService
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
        /** @var User $connectedUser */
        $connectedUser = $this->_helper->auth();

        $organizationResource = NamedResource::loadByName(Orga_Model_Organization::class);
        $isConnectedUserAbleToCreateOrganizations = $this->aclService->isAllowed(
            $connectedUser,
            Action::CREATE(),
            $organizationResource
        );

        $aclQuery = new Core_Model_Query();
        $aclQuery->aclFilter->enabled = true;
        $aclQuery->aclFilter->user = $connectedUser;
        $aclQuery->aclFilter->action = Action::DELETE();
        $isConnectedUserAbleToDeleteOrganizations = (Orga_Model_Organization::countTotal($aclQuery) > 0);
        $aclQuery->aclFilter->action = Action::VIEW();
        $isConnectedUserAbleToSeeManyOrganizations = (Orga_Model_Organization::countTotal($aclQuery) > 1);

        if ($isConnectedUserAbleToCreateOrganizations
            || $isConnectedUserAbleToDeleteOrganizations
            || $isConnectedUserAbleToSeeManyOrganizations
        ) {
            $this->redirect('orga/organization/manage');
            return;
        }

        $cells = [];
        foreach ($connectedUser->getRoles() as $role) {
            if ($role instanceof AbstractCellRole) {
                $cell = $role->getCell();
                if (! in_array($cell, $cells)) {
                    $cells[] = $cell;
                }
            }
        }
        $isConnectedUserAbleToSeeManyCells = (count($cells) > 1);

        if ($isConnectedUserAbleToSeeManyCells) {
            $organizationArray = Orga_Model_Organization::loadList($aclQuery);
            $this->redirect('orga/organization/cells/idOrganization/'.array_pop($organizationArray)->getId());
            return;
        }

        if (count($cells) == 1) {
            $this->redirect('orga/cell/details/idCell/'.array_pop($cells)->getId());
            return;
        }

        $this->forward('noaccess', 'organization', 'orga');
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
        $query->aclFilter->action = Action::VIEW();
        $organizations = Orga_Model_Organization::loadList($query);

        // Crée les ViewModel
        $organizationsVM = [];
        foreach ($organizations as $organization) {
            $vm = $this->organizationVMFactory->createOrganizationViewModel($organization, $connectedUser);
            $organizationsVM[] = $vm;
        }
        $this->view->assign('organizations', $organizationsVM);

        $organizationResource = NamedResource::loadByName(Orga_Model_Organization::class);
        $this->view->assign('canCreateOrganization', $this->aclService->isAllowed(
            $connectedUser,
            Action::CREATE(),
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

        $success = function () {
            UI_Message::addMessageStatic(__('UI', 'message', 'added'), UI_Message::TYPE_SUCCESS);
        };
        $timeout = function () {
            UI_Message::addMessageStatic(__('UI', 'message', 'addedLater'), UI_Message::TYPE_SUCCESS);
        };
        $error = function (Exception $e) {
            throw $e;
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
            $granularityForInventoryStatus = $organization->getGranularityByRef($refGranularityForInventoryStatus);
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
    }

    /**
     * Affiche une vue indiquant l'accès à aucun projet.
     * @Secure("loggedIn")
     */
    public function noaccessAction()
    {
    }
}
