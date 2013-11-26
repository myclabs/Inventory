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
        /** @var User $connectedUser */
        $connectedUser = $this->_helper->auth();

        $organizationResource = NamedResource::loadByName(Orga_Model_Organization::class);
        $isConnectedUserAbleToCreateOrganizations = $this->aclService->isAllowed(
            $connectedUser,
            Action::CREATE(),
            $organizationResource
        );

        if (!$isConnectedUserAbleToCreateOrganizations) {
            $aclQuery = new Core_Model_Query();
            $aclQuery->aclFilter->enabled = true;
            $aclQuery->aclFilter->user = $connectedUser;
            $aclQuery->aclFilter->action = Action::DELETE();

            $isConnectedUserAbleToDeleteOrganizations = (Orga_Model_Organization::countTotal($aclQuery) > 0);

            if (!$isConnectedUserAbleToDeleteOrganizations) {
                $aclQuery->aclFilter->action = Action::VIEW();
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
            if (count($organizationsWithAccess) > 0) {
                $idOrganization = array_pop($organizationsWithAccess)->getId();
                $this->redirect('orga/organization/view/idOrganization/'.$idOrganization);
            }
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
        $organizationsViewModel = [];
        foreach ($organizations as $organization) {
            $organizationsViewModel[] = $this->organizationVMFactory->createOrganizationViewModel($organization, $connectedUser);
        }
        $this->view->assign('organizations', $organizationsViewModel);

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
                $cellsWithViewAccess = Orga_Model_Cell::loadList($aclCellQuery);
                $idCell = array_pop($cellsWithViewAccess)->getId();
                $this->redirect('orga/cell/view/idCell/'.$idCell);
                break;
            } else if ($numberCellsUserCanSee > 1) {
                //@todo Organization view : Faire une nouvelle vue.
                $this->view->assign(
                    'organization',
                    $this->organizationVMFactory->createOrganizationViewModel($organization, $connectedUser)
                );
                break;
            }
        }
    }

    /**
     * Action de détails d'un organization.
     * @Secure("editOrganizationAndCells")
     */
    public function editAction()
    {
        /** @var User_Model_User $connectedUser */
        $connectedUser = $this->_helper->auth();

        $idOrganization = $this->getParam('idOrganization');
        /** @var Orga_Model_Organization $organization */
        $organization = Orga_Model_Organization::load($idOrganization);

        $this->view->assign(
            'organization',
            $this->organizationVMFactory->createOrganizationViewModel($organization, $connectedUser)
        );
        $isUserAllowedToEditOrganization = $this->aclService->isAllowed(
            $connectedUser,
            User_Model_Action_Default::EDIT(),
            $organization
        );
        if (!$isUserAllowedToEditOrganization) {
            foreach ($organization->getGranularities() as $granularity) {
                $aclCellQuery = new Core_Model_Query();
                $aclCellQuery->aclFilter->enabled = true;
                $aclCellQuery->aclFilter->user = $connectedUser;
                $aclCellQuery->aclFilter->action = User_Model_Action_Default::EDIT();
                $aclCellQuery->filter->addCondition(Orga_Model_Cell::QUERY_GRANULARITY, $granularity);

                $numberCellsUserCanEdit = Orga_Model_Cell::countTotal($aclCellQuery);
                if ($numberCellsUserCanEdit > 0) {
                    break;
                }
            }
            $isUserAllowedToEditCells = ($numberCellsUserCanEdit > 0);
            foreach ($organization->getGranularities() as $granularity) {
                $aclCellQuery = new Core_Model_Query();
                $aclCellQuery->aclFilter->enabled = true;
                $aclCellQuery->aclFilter->user = $connectedUser;
                $aclCellQuery->aclFilter->action = User_Model_Action_Default::ALLOW();
                $aclCellQuery->filter->addCondition(Orga_Model_Cell::QUERY_GRANULARITY, $granularity);

                $numberCellsUserCanAllow = Orga_Model_Cell::countTotal($aclCellQuery);
                if ($numberCellsUserCanAllow > 0) {
                    break;
                }
            }
            $isUserAllowedToAllowCells = ($numberCellsUserCanAllow > 0);
        } else {
            $isUserAllowedToEditCells = true;
            $isUserAllowedToAllowCells = true;
        }

        $tabView = new UI_Tab_View('orga');
        $parameters = '/idOrganization/'.$idOrganization.'/display/render';

        // Tab Organization & Axes.
        if ($isUserAllowedToEditOrganization) {
            $organizationTab = new UI_Tab('organization');
            $organizationTab->label = __('Orga', 'configuration', 'generalInfoTab');
            $organizationTab->dataSource = 'orga/organization/edit-organization'.$parameters;
            $organizationTab->useCache = false;
            $tabView->addTab($organizationTab);

            $axisTab = new UI_Tab('axes');
            $axisTab->label = __('UI', 'name', 'axes');
            $axisTab->dataSource = 'orga/axis/manage'.$parameters;
            $axisTab->useCache = true;
            $tabView->addTab($axisTab);
        }

        // Tab Members.
        if ($isUserAllowedToEditCells) {
            $membersTab = new UI_Tab('members');
            $membersTab->label = __('UI', 'name', 'elements');
            $membersTab->dataSource = 'orga/member/manage'.$parameters;
            $membersTab->useCache = false;
            $tabView->addTab($membersTab);
        }

        // Tab Granularities.
        if ($isUserAllowedToEditOrganization) {
            $granularityTab = new UI_Tab('granularities');
            $granularityTab->label = __('Orga', 'granularity', 'granularities');
            $granularityTab->dataSource = 'orga/granularity/manage'.$parameters;
            $granularityTab->useCache = false;
            $tabView->addTab($granularityTab);
        }

        // Tab Relevant.
        if ($isUserAllowedToEditCells) {
            $relevanceTab = new UI_Tab('relevance');
            $relevanceTab->label = __('Orga', 'cellRelevance', 'relevance');
            $relevanceTab->dataSource = 'orga/organization/edit-relevance'.$parameters;
            $relevanceTab->useCache = false;
            $tabView->addTab($relevanceTab);
        }

        // Tab Consistency.
        if ($isUserAllowedToEditOrganization) {
            $consistencyTab = new UI_Tab('consistency');
            $consistencyTab->label = __('UI', 'name', 'control');
            $consistencyTab->dataSource = 'orga/organization/consistency'.$parameters;
            $consistencyTab->useCache = true;
            $tabView->addTab($consistencyTab);
        }

        // Tab ACLs.
        if ($isUserAllowedToAllowCells) {
            $aclsTab = new UI_Tab('acls');
            $aclsTab->label = __('User', 'role', 'roles');
            $aclsTab->dataSource = 'orga/organization/edit-acls'.$parameters;
            $aclsTab->useCache = !$isUserAllowedToEditOrganization;
            $tabView->addTab($aclsTab);
        }

        // Tab AFConfiguration.
        if ($isUserAllowedToEditOrganization) {
            $afTab = new UI_Tab('afs');
            $afTab->label = __('UI', 'name', 'forms');
            $afTab->dataSource = 'orga/organization/edit-afs'.$parameters;
            $afTab->useCache = !$isUserAllowedToEditOrganization;
            $tabView->addTab($afTab);
        }

        $activeTab = $this->hasParam('tab') ? $this->getParam('tab') : 'organization';
        $editOrganizationTabs = ['organization', 'axes', 'granularities', 'consistency', 'afs'];
        $editCellsTabs = ['members', 'relevant'];
        $allowCellsTabs = ['acls'];
        if (!$isUserAllowedToEditOrganization && in_array($activeTab, $editOrganizationTabs)) {
            $activeTab = 'members';
        }
        if (!$isUserAllowedToEditCells && in_array($activeTab, $editCellsTabs)) {
            $activeTab = 'acl';
        }
        if (!$isUserAllowedToAllowCells && in_array($activeTab, $allowCellsTabs)) {
            $activeTab = 'members';
        }
        switch ($activeTab) {
            case 'organization':
                $organizationTab->active = true;
                break;
            case 'axes':
                $axisTab->active = true;
                break;
            case 'granularities':
                $granularityTab->active = true;
                break;
            case 'consistency':
                $consistencyTab->active = true;
                break;
            case 'afs':
                $afTab->active = true;
                break;
            case 'members':
                $membersTab->active = true;
                break;
            case 'relevance':
                $relevanceTab->active = true;
                break;
            case 'acls':
                $aclsTab->active = true;
                break;
        }

        $this->view->assign('tabView', $tabView);
        UI_Datagrid::addHeader();
        UI_Tree::addHeader();
        UI_Form::addHeader();
        UI_Popup_Ajax::addHeader();
    }

    /**
     * Action de détails d'un organization.
     * @Secure("editOrganization")
     */
    public function editOrganizationAction()
    {
        $idOrganization = $this->getParam('idOrganization');
        /** @var Orga_Model_Organization $organization */
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
            $this->view->assign('display', false);
        } else {
            $this->view->assign('display', true);
        }
        $this->view->granularityReportBaseUrl = 'orga/granularity/report/idOrganization/'.$idOrganization;
    }

    /**
     * Action de détails d'un organization.
     * @Secure("editOrganization")
     */
    public function editOrganizationSubmitAction()
    {
        $idOrganization = $this->getParam('idOrganization');
        /** @var Orga_Model_Organization $organization */
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
     * Action pour la pertinence des cellules enfants.
     * @Secure("editOrganizationAndCells")
     */
    public function editRelevanceAction()
    {
        /** @var User_Model_User $connectedUser */
        $connectedUser = $this->_helper->auth();

        $idOrganization = $this->getParam('idOrganization');
        /** @var Orga_Model_Organization $organization */
        $organization = Orga_Model_Organization::load($idOrganization);
        $isUserAllowedToEditOrganization = $this->aclService->isAllowed(
            $connectedUser,
            User_Model_Action_Default::EDIT(),
            $organization
        );
        if (!$isUserAllowedToEditOrganization) {
            $granularities = [];
            foreach ($organization->getGranularities() as $granularity) {
                $aclCellQuery = new Core_Model_Query();
                $aclCellQuery->aclFilter->enabled = true;
                $aclCellQuery->aclFilter->user = $connectedUser;
                $aclCellQuery->aclFilter->action = User_Model_Action_Default::EDIT();
                $aclCellQuery->filter->addCondition(Orga_Model_Cell::QUERY_GRANULARITY, $granularity);

                $numberCellsUserCanEdit = Orga_Model_Cell::countTotal($aclCellQuery);
                if ($numberCellsUserCanEdit > 0) {
                    $granularities[] = $granularity;
                }
            }
        } else {
            $granularities = $organization->getGranularities();
            uasort($granularities, function(Orga_Model_Granularity $a, Orga_Model_Granularity $b) { return $b->getPosition() - $a->getPosition(); });
            // Pas de reglage de la pertinence de la cellule globale.
            array_pop($granularities);
        }
        $this->view->assign('granularities', $granularities);

        $globalGranularity = $organization->getGranularityByRef('global');
        $globalCell = $globalGranularity->getCellByMembers([]);

        $listDatagridConfiguration = array();
        foreach ($granularities as $granularity) {
            $datagridConfiguration = new Orga_DatagridConfiguration(
                'relevance_granularity'.$granularity->getId(),
                'datagrid_organization_relevance',
                'orga',
                $globalCell,
                $granularity
            );
            $datagridConfiguration->datagrid->addParam('idOrganization', $idOrganization);
            $columnRelevant = new UI_Datagrid_Col_Bool('relevant');
            $columnRelevant->label = __('Orga', 'cellRelevance', 'relevance');
            $columnRelevant->editable = true;
            $columnRelevant->textTrue = __('Orga', 'cellRelevance', 'relevantFem');
            $columnRelevant->textFalse = __('Orga', 'cellRelevance', 'irrelevantFem');
            $columnRelevant->valueTrue = '<i class="icon-ok"></i> '.__('Orga', 'cellRelevance', 'relevantFem');
            $columnRelevant->valueFalse = '<i class="icon-remove"></i> '.__('Orga', 'cellRelevance', 'irrelevantFem');
            $datagridConfiguration->datagrid->addCol($columnRelevant);
            $listDatagridConfiguration[$granularity->getLabel()] = $datagridConfiguration;
        }

        $this->forward('child', 'cell', 'orga', ['datagridConfiguration' => $listDatagridConfiguration, 'idCell' => $globalCell->getId()]);
    }

    /**
     * Controller de la vue de la cohérence d'un organization.
     * @Secure("editOrganization")
     */
    public function consistencyAction()
    {
        $this->view->assign('idOrganization', $this->getParam('idOrganization'));

        if ($this->hasParam('display') && ($this->getParam('display') === 'render')) {
            $this->_helper->layout()->disableLayout();
            $this->view->assign('display', false);
        } else {
            $this->view->assign('display', true);
        }
    }

    /**
     * Action renvoyant le tab.
     * @Secure("allowOrganizationAndCells")
     */
    public function editAclsAction()
    {
        /** @var User_Model_User $connectedUser */
        $connectedUser = $this->_helper->auth();

        $idOrganization = $this->getParam('idOrganization');
        /** @var Orga_Model_Organization $organization */
        $organization = Orga_Model_Organization::load($idOrganization);
        //@todo Changer par un droit ALLOW
        $isUserAllowedToAllowOrganization = $this->aclService->isAllowed(
            $connectedUser,
            User_Model_Action_Default::EDIT(),
            $organization
        );
        if (!$isUserAllowedToAllowOrganization) {
            $granularities = [];
            foreach ($organization->getGranularities() as $granularity) {
                $aclCellQuery = new Core_Model_Query();
                $aclCellQuery->aclFilter->enabled = true;
                $aclCellQuery->aclFilter->user = $connectedUser;
                $aclCellQuery->aclFilter->action = User_Model_Action_Default::ALLOW();
                $aclCellQuery->filter->addCondition(Orga_Model_Cell::QUERY_GRANULARITY, $granularity);

                $numberCellsUserCanEdit = Orga_Model_Cell::countTotal($aclCellQuery);
                if ($numberCellsUserCanEdit > 0) {
                    $granularities[] = $granularity;
                }
            }
        } else {
            $granularities = $organization->getGranularities();
            uasort($granularities, function(Orga_Model_Granularity $a, Orga_Model_Granularity $b) { return $b->getPosition() - $a->getPosition(); });
            // La granularité globale subit un traitement a part.
            $globalGranularity = array_pop($granularities);
        }
        $this->view->assign('granularities', $granularities);

        $globalGranularity = $organization->getGranularityByRef('global');
        $globalCell = $globalGranularity->getCellByMembers([]);

        $listDatagridConfiguration = array();
        if ($isUserAllowedToAllowOrganization) {
            // Utilisateurs de l'organization.
            $datagridConfiguration = new Orga_DatagridConfiguration(
                'organizationACL'.$organization->getId(),
                'datagrid_organization_acls_organization',
                'orga',
                $globalCell,
                $globalGranularity
            );
            $datagridConfiguration->datagrid->addParam('idOrganization', $idOrganization);

            $columnUserFirstName = new UI_Datagrid_Col_Text('userFirstName', __('User', 'user', 'firstName'));
            $columnUserFirstName->addable = false;
            $datagridConfiguration->datagrid->addCol($columnUserFirstName);

            $columnUserLastName = new UI_Datagrid_Col_Text('userLastName', __('User', 'user', 'lastName'));
            $columnUserLastName->addable = false;
            $datagridConfiguration->datagrid->addCol($columnUserLastName);

            $columnUserEmail = new UI_Datagrid_Col_Text('userEmail', __('UI', 'name', 'emailAddress'));
            $datagridConfiguration->datagrid->addCol($columnUserEmail);

            $datagridConfiguration->datagrid->pagination = false;
            $datagridConfiguration->datagrid->addElements = true;
            $datagridConfiguration->datagrid->addPanelTitle = __('Orga', 'role', 'addAdministratorPanelTitle');
            $datagridConfiguration->datagrid->deleteElements = true;

            $labelDatagrid = __('Orga', 'role', 'organizationAdministrators');
            $listDatagridConfiguration[$labelDatagrid] = $datagridConfiguration;

            // Utilisateurs de la cellule globale.
            $datagridConfiguration = new Orga_DatagridConfiguration(
                'granularityACL'.$globalGranularity->getId(),
                'datagrid_organization_acls_cell',
                'orga',
                $globalCell,
                $globalGranularity
            );
            $datagridConfiguration->datagrid->addParam('idOrganization', $idOrganization);
            $datagridConfiguration->datagrid->addParam('idCell', $globalCell->getId());

            $columnUserFirstName = new UI_Datagrid_Col_Text('userFirstName', __('User', 'user', 'firstName'));
            $columnUserFirstName->addable = false;
            $datagridConfiguration->datagrid->addCol($columnUserFirstName);

            $columnUserLastName = new UI_Datagrid_Col_Text('userLastName', __('User', 'user', 'lastName'));
            $columnUserLastName->addable = false;
            $datagridConfiguration->datagrid->addCol($columnUserLastName);

            $columnUserEmail = new UI_Datagrid_Col_Text('userEmail', __('UI', 'name', 'emailAddress'));
            $datagridConfiguration->datagrid->addCol($columnUserEmail);

            $columnRole = new UI_Datagrid_Col_List('userRole', __('User', 'role', 'role'));
            $columnRole->list = array();
            $columnRole->list[] = 'En attente du refactoring des ACLs';
//            foreach ($cellACLResource->getLinkedSecurityIdentities() as $role) {
//                if ($role instanceof User_Model_Role) {
//                    $columnRole->list[$role->getRef()] = __('Orga', 'role', $role->getName());
//                }
//            }
            $datagridConfiguration->datagrid->addCol($columnRole);

            $datagridConfiguration->datagrid->pagination = false;
            $datagridConfiguration->datagrid->addElements = true;
            $datagridConfiguration->datagrid->addPanelTitle = __('Orga', 'role', 'addPanelTitle');
            $datagridConfiguration->datagrid->deleteElements = true;

            $labelDatagrid = $globalCell->getLabel();
            $listDatagridConfiguration[$labelDatagrid] = $datagridConfiguration;
        }

        foreach ($granularities as $narrowerGranularity) {
            if ($narrowerGranularity->getCellsWithACL()) {
                // Datagrid des utilisateurs des cellules enfants.
                $datagridConfiguration = new Orga_DatagridConfiguration(
                    'granularityUserACL'.$narrowerGranularity->getId(),
                    'datagrid_organization_acls_childusers',
                    'orga',
                    $globalCell,
                    $narrowerGranularity
                );
                $datagridConfiguration->datagrid->addParam('idOrganization', $idOrganization);

                $columnUserFirstName = new UI_Datagrid_Col_Text('userFirstName', __('User', 'user', 'firstName'));
                $columnUserFirstName->addable = false;
                $datagridConfiguration->datagrid->addCol($columnUserFirstName);

                $columnUserLastName = new UI_Datagrid_Col_Text('userLastName', __('User', 'user', 'lastName'));
                $columnUserLastName->addable = false;
                $datagridConfiguration->datagrid->addCol($columnUserLastName);

                $columnUserEmail = new UI_Datagrid_Col_Text('userEmail', __('UI', 'name', 'emailAddress'));
                $datagridConfiguration->datagrid->addCol($columnUserEmail);

                $columnRole = new UI_Datagrid_Col_List('userRole', __('User', 'role', 'role'));
                $columnRole->list = array();
                $columnRole->list[] = 'En attente du refactoring des ACLs';
//                foreach ($cellACLResource->getLinkedSecurityIdentities() as $role) {
//                    if ($role instanceof User_Model_Role) {
//                        $columnRole->list[$role->getName()] = __('Orga', 'role', $role->getName());
//                    }
//                }
                $datagridConfiguration->datagrid->addCol($columnRole);

                $datagridConfiguration->datagrid->pagination = true;
                $datagridConfiguration->datagrid->addElements = true;
                $datagridConfiguration->datagrid->addPanelTitle = __('Orga', 'role', 'addPanelTitle');
                $datagridConfiguration->datagrid->deleteElements = true;

                $labelDatagrid = $narrowerGranularity->getLabel() . ' — ' . __('Orga', 'role', 'userDetails');
                $listDatagridConfiguration[$labelDatagrid] = $datagridConfiguration;

                // Datagrid des cellules enfants avec le nombre d'utilisteur pour chacune.
                $datagridConfiguration = new Orga_DatagridConfiguration(
                    'granularityCellACL'.$narrowerGranularity->getId(),
                    'datagrid_organization_acls_childcells',
                    'orga',
                    $globalCell,
                    $narrowerGranularity
                );
                $datagridConfiguration->datagrid->addParam('idOrganization', $idOrganization);

                $columnAdministrators = new UI_Datagrid_Col_Text('administrators', __('Orga', 'role', 'cellGenericAdministrators'));
                $datagridConfiguration->datagrid->addCol($columnAdministrators);

                $columnDetails = new UI_Datagrid_Col_Popup('details', __('Orga', 'role', 'detailCellRolesHeader'));
                $columnDetails->popup->addAttribute('class', 'large');
                $columnDetails->popup->title = '';
                $datagridConfiguration->datagrid->addCol($columnDetails);

                $labelDatagrid = $narrowerGranularity->getLabel() . ' — ' . __('Orga', 'role', 'cellDetails');
                $listDatagridConfiguration[$labelDatagrid] = $datagridConfiguration;
            }
        }

        $this->forward('child', 'cell', 'orga', array(
            'idCell' => $globalCell->getId(),
            'datagridConfiguration' => $listDatagridConfiguration,
            'display' => 'render',
            'minimize' => false,
        ));
    }

    /**
     * Action renvoyant le tab.
     * @Secure("editOrganization")
     */
    public function editAfsAction()
    {
        $idOrganization = $this->getParam('idOrganization');
        /** @var Orga_Model_Organization $organization */
        $organization = Orga_Model_Organization::load($idOrganization);

        $globalGranularity = $organization->getGranularityByRef('global');
        $globalCell = $globalGranularity->getCellByMembers([]);

        $listAFs = array();
        /** @var AF_Model_AF $aF */
        foreach (AF_Model_AF::loadList() as $aF) {
            $listAFs[$aF->getRef()] = $aF->getLabel();
        }

        $listDatagridConfiguration = array();
        foreach ($organization->getInputGranularities() as $inputGranularity) {
            $datagridConfiguration = new Orga_DatagridConfiguration(
                'aFGranularityConfig'.$inputGranularity->getId(),
                'datagrid_organization_afs',
                'orga',
                $globalCell,
                $inputGranularity->getInputConfigGranularity()
            );
            $datagridConfiguration->datagrid->addParam('idCell', $globalCell->getId());
            $idInputGranularity = $inputGranularity->getId();
            $datagridConfiguration->datagrid->addParam('idInputGranularity', $idInputGranularity);

            $columnAF = new UI_Datagrid_Col_List('aF', __('AF', 'name', 'accountingForm'));
            $columnAF->list = $listAFs;
            $columnAF->editable = true;
            $columnAF->fieldType = UI_Datagrid_Col_List::FIELD_AUTOCOMPLETE;
            $datagridConfiguration->datagrid->addCol($columnAF);

            $labelDatagrid = $inputGranularity->getInputConfigGranularity()->getLabel()
                . ' <small>' . $inputGranularity->getLabel() . '</small>';
            $listDatagridConfiguration[$labelDatagrid] = $datagridConfiguration;
        }

        $this->forward('child', 'cell', 'orga', array(
            'idCell' => $globalCell->getId(),
            'datagridConfiguration' => $listDatagridConfiguration,
            'display' => 'render',
            'minimize' => false,
        ));
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
     * Affiche une vue indiquant l'accès à aucun projet.
     * @Secure("loggedIn")
     */
    public function noaccessAction()
    {
    }
}
