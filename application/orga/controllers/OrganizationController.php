<?php

use Core\Annotation\Secure;
use Core\Work\ServiceCall\ServiceCallTask;
use Doctrine\Common\Collections\Criteria;
use MyCLabs\Work\Dispatcher\WorkDispatcher;
use Orga\Model\ACL\Role\CellAdminRole;
use Orga\ViewModel\OrganizationViewModelFactory;
use Orga\ViewModel\CellViewModelFactory;
use User\Domain\ACL\Action;
use User\Domain\ACL\ACLService;
use User\Domain\ACL\Resource\NamedResource;
use User\Domain\User;

/**
 * @author valentin.claras
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
     * @var Orga_Service_ACLManager
     */
    private $aclManager;

    /**
     * @Inject
     * @var OrganizationViewModelFactory
     */
    private $organizationVMFactory;

    /**
     * @Inject
     * @var CellViewModelFactory
     */
    private $cellVMFactory;

    /**
     * @Inject
     * @var Orga_Service_OrganizationService
     */
    private $organizationService;

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
            if (count($organizationsWithAccess) === 1) {
                $idOrganization = array_pop($organizationsWithAccess)->getId();
                $this->redirect('orga/organization/view/idOrganization/'.$idOrganization);
            }
        }

        $this->forward('noaccess', 'organization', 'orga');
    }

    /**
     * @Secure("viewOrganizations")
     */
    public function manageAction()
    {
        /** @var User $connectedUser */
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
        $this->view->assign('templates', $this->organizationService->getOrganizationTemplates());
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
            $this->sendJsonResponse(
                [
                    'message' => __('UI', 'message', 'added'),
                    'typeMessage' => 'success',
                    'info' => __('Orga', 'add', 'complementaryInfo')
                ]
            );
        };
        $timeout = function () {
            $this->sendJsonResponse(
                [
                    [
                        'message' => __('UI', 'message', 'addedLater'),
                        'typeMessage' => 'info',
                        'info' => __('Orga', 'add', 'addOrganizationComplementaryInfo')
                    ]
                ]
            );
        };
        $error = function (Exception $e) {
            throw $e;
        };

        // Lance la tache en arrière plan
        $task = new ServiceCallTask(
            'Orga_Service_OrganizationService',
            'createOrganizationFromTemplatesForm',
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
     * @Secure("viewOrganization")
     */
    public function viewAction()
    {
        /** @var User $connectedUser */
        $connectedUser = $this->_helper->auth();

        $idOrganization = $this->getParam('idOrganization');
        /** @var Orga_Model_Organization $organization */
        $organization = Orga_Model_Organization::load($idOrganization);

        $cellsWithAccess = $this->aclManager->getTopCellsWithAccessForOrganization($connectedUser, $organization);
        if (count($cellsWithAccess['cells']) === 1) {
            $this->redirect('orga/cell/view/idCell/'.array_pop($cellsWithAccess['cells'])->getId());
        }
        
        $organizationViewModel = $this->organizationVMFactory->createOrganizationViewModel($organization, $connectedUser);
        $this->view->assign('organization', $organizationViewModel);
        $cellViewModels = [];
        foreach ($cellsWithAccess['cells'] as $cellWithAccess) {
            $cellViewModels[] = $this->cellVMFactory->createCellViewModel($cellWithAccess, $connectedUser);
        }
        $this->view->assign('cells', $cellViewModels);
        $this->view->assign('cellsAccess', $cellsWithAccess['accesses']);
    }

    /**
     * @Secure("editOrganizationAndCells")
     */
    public function editAction()
    {
        /** @var User $connectedUser */
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
            Action::EDIT(),
            $organization
        );
        if (!$isUserAllowedToEditOrganization) {
            $numberCellsUserCanEdit = 0;
            foreach ($organization->getOrderedGranularities() as $granularity) {
                $aclCellQuery = new Core_Model_Query();
                $aclCellQuery->aclFilter->enabled = true;
                $aclCellQuery->aclFilter->user = $connectedUser;
                $aclCellQuery->aclFilter->action = Action::EDIT();
                $aclCellQuery->filter->addCondition(Orga_Model_Cell::QUERY_GRANULARITY, $granularity);

                $numberCellsUserCanEdit = Orga_Model_Cell::countTotal($aclCellQuery);
                if ($numberCellsUserCanEdit > 0) {
                    break;
                }
            }
            $isUserAllowedToEditCells = ($numberCellsUserCanEdit > 0);
        } else {
            $isUserAllowedToEditCells = true;
        }

        $tabView = new UI_Tab_View('orga');
        $parameters = '/idOrganization/'.$idOrganization.'/display/render/';

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
        $canUserEditMembers = $isUserAllowedToEditCells;
        if (!$isUserAllowedToEditOrganization) {
            $axesCanEdit = $this->aclManager->getAxesCanEdit($connectedUser, $organization);
            if (count($axesCanEdit) === 0) {
                $canUserEditMembers = false;
            }
        }
        if ($canUserEditMembers) {
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
        $canUserEditRelevance = $isUserAllowedToEditCells;
        if (!$isUserAllowedToEditOrganization) {
            $relevanceGranularities = [];
            foreach ($this->aclManager->getGranularitiesCanEdit($connectedUser, $organization) as $granularity) {
                if ($granularity->getCellsControlRelevance()) {
                    break;
                }
                $canUserEditRelevance = false;
            }
        }
        if ($canUserEditRelevance) {
            $relevanceTab = new UI_Tab('relevance');
            $relevanceTab->label = __('Orga', 'cellRelevance', 'relevance');
            $relevanceTab->dataSource = 'orga/organization/edit-relevance'.$parameters;
            $relevanceTab->useCache = false;
            $tabView->addTab($relevanceTab);
        }

        // Tab AFConfiguration.
        if ($isUserAllowedToEditOrganization) {
            $afTab = new UI_Tab('afs');
            $afTab->label = __('UI', 'name', 'forms');
            $afTab->dataSource = 'orga/organization/edit-afs'.$parameters;
            $afTab->useCache = false;
            $tabView->addTab($afTab);
        }

        // Tab Consistency.
        if ($isUserAllowedToEditOrganization) {
            $consistencyTab = new UI_Tab('consistency');
            $consistencyTab->label = __('UI', 'name', 'control');
            $consistencyTab->dataSource = 'orga/organization/consistency'.$parameters;
            $consistencyTab->useCache = true;
            $tabView->addTab($consistencyTab);
        }

        // Tab DW
        if ($isUserAllowedToEditOrganization) {
            $dwTab = new UI_Tab('reports');
            $dwTab->label = __('DW', 'name', 'analysesConfig');
            $dwTab->dataSource = 'orga/organization/edit-reports'.$parameters;
            $dwTab->useCache = false;
            $tabView->addTab($dwTab);
        }

        // Tab Rebuild
        $canUserRebuildCells = $isUserAllowedToEditCells;
        if (!$isUserAllowedToEditOrganization) {
            $cellsCanEdit = $this->aclManager->getTopCellsWithAccessForOrganization(
                $connectedUser,
                $organization,
                [CellAdminRole::class]
            )['cells'];
            /** @var Orga_Model_Cell $cell */
            foreach ($cellsCanEdit as $cell) {
                if ($cell->getGranularity()->getDWCube()) {
                    break;
                }
                $canUserRebuildCells = false;
            }
            if (!$canUserRebuildCells) {
                foreach ($this->aclManager->getGranularitiesCanEdit($connectedUser, $organization) as $granularity) {
                    if ($granularity->getCellsGenerateDWCubes()) {
                        $canUserRebuildCells = true;
                        break;
                    }
                }
            }
        }
        if ($canUserRebuildCells) {
            $rebuildTab = new UI_Tab('rebuild');
            $rebuildTab->label = __('DW', 'rebuild', 'dataRebuildTab');
            $rebuildTab->dataSource = 'orga/organization/rebuild'.$parameters;
            $rebuildTab->useCache = true;
            $tabView->addTab($rebuildTab);
        }

        $activeTab = $this->hasParam('tab') ? $this->getParam('tab') : 'organization';
        $editOrganizationTabs = ['organization', 'axes', 'granularities', 'consistency'];
        if (!$isUserAllowedToEditOrganization && in_array($activeTab, $editOrganizationTabs)) {
            $activeTab = 'default';
            if ($canUserRebuildCells) {
                $activeTab = 'rebuild';
            }
            if ($canUserEditRelevance) {
                $activeTab = 'relevance';
            }
            if ($canUserEditMembers) {
                $activeTab = 'members';
            }
        }
        switch ($activeTab) {
            case 'organization':
                $organizationTab->active = true;
                break;
            case 'axes':
                $axisTab->active = true;
                break;
            case 'members':
                $membersTab->active = true;
                break;
            case 'granularities':
                $granularityTab->active = true;
                break;
            case 'relevance':
                $relevanceTab->active = true;
                break;
            case 'afs':
                $afTab->active = true;
                break;
            case 'consistency':
                $consistencyTab->active = true;
                break;
            case 'reports':
                $dwTab->active = true;
                break;
            case 'rebuild':
                $rebuildTab->active = true;
                break;
        }

        $this->view->assign('tabView', $tabView);
        UI_Datagrid::addHeader();
        UI_Tree::addHeader();
        UI_Form::addHeader();
        UI_Popup_Ajax::addHeader();
    }

    /**
     * @Secure("editOrganization")
     */
    public function editOrganizationAction()
    {
        $idOrganization = $this->getParam('idOrganization');
        /** @var Orga_Model_Organization $organization */
        $organization = Orga_Model_Organization::load($idOrganization);

        $this->view->assign('idOrganization', $idOrganization);
        $this->view->assign('organizationLabel', $organization->getLabel());
        $this->view->assign('granularities', $organization->getOrderedGranularities());
        try {
            $this->view->granularityRefForInventoryStatus = $organization->getGranularityForInventoryStatus()->getRef();
        } catch (Core_Exception_UndefinedAttribute $e) {
            $this->view->granularityRefForInventoryStatus = null;
        }
        $this->view->granularitiesWithDWCube = array();
        foreach ($organization->getOrderedGranularities() as $granularity) {
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
     * @param Orga_Model_Organization $organization
     *
     * @throws Core_Exception_User
     *
     * @return Orga_Model_Axis
     */
    protected function getAxesAddGranularity(Orga_Model_Organization $organization)
    {
        $refAxes = explode(',', $this->getParam('axes'));

        /** @var Orga_Model_Axis $axes */
        $axes = [];
        if (!empty($this->getParam('axes'))) {
            foreach ($refAxes as $refAxis) {
                $axis = $organization->getAxisByRef($refAxis);
                // On regarde si les axes précédement ajouter ne sont pas lié hierachiquement à l'axe actuel.
                if (!$axis->isTransverse($axes)) {
                    throw new Core_Exception_User('Orga', 'granularity', 'hierarchicallyLinkedAxes');
                    break;
                } else {
                    $axes[] = $axis;
                }
            }
        }

        return $axes;
    }

    /**
     * @Secure("editOrganizationAndCells")
     */
    public function editRelevanceAction()
    {
        /** @var User $connectedUser */
        $connectedUser = $this->_helper->auth();

        $idOrganization = $this->getParam('idOrganization');
        /** @var Orga_Model_Organization $organization */
        $organization = Orga_Model_Organization::load($idOrganization);

        $this->view->assign('organization', $organization);

        $isUserAllowedToEditOrganization = $this->aclService->isAllowed(
            $connectedUser,
            Action::EDIT(),
            $organization
        );
        $this->view->assign('isUserAllowedToEditOrganization', $isUserAllowedToEditOrganization);
        $relevanceGranularities = [];
        foreach ($this->aclManager->getGranularitiesCanEdit($connectedUser, $organization) as $granularity) {
            if ($granularity->getCellsControlRelevance()) {
                $relevanceGranularities[] = $granularity;
            }
        }
        $this->view->assign('granularities', $relevanceGranularities);

        if ($this->hasParam('display') && ($this->getParam('display') === 'render')) {
            $this->_helper->layout()->disableLayout();
            $this->view->assign('display', false);
        } else {
            $this->view->assign('display', true);
        }
    }

    /**
     * @Secure("editOrganization")
     */
    public function addGranularityRelevanceAction()
    {
        $idOrganization = $this->getParam('idOrganization');
        /** @var Orga_Model_Organization $organization */
        $organization = Orga_Model_Organization::load($idOrganization);

        $axes = $this->getAxesAddGranularity($organization);

        try {
            $granularity = $organization->getGranularityByRef(Orga_Model_Granularity::buildRefFromAxes($axes));
            if ($granularity->getCellsControlRelevance()) {
                throw new Core_Exception_User('Orga', 'edit', 'granularityAlreadyConfigured');
            }
            $granularity->setCellsControlRelevance(true);
            $this->sendJsonResponse(['message' => __('UI', 'message', 'added')]);
        } catch (Core_Exception_NotFound $e) {
            $success = function () {
                $this->sendJsonResponse(['message' => __('UI', 'message', 'added')]);
            };
            $timeout = function () {
                $this->sendJsonResponse(['message' => __('UI', 'message', 'addedLater')]);
            };
            $error = function (Exception $e) {
                throw $e;
            };

            // Lance la tache en arrière plan
            $task = new ServiceCallTask(
                'Orga_Service_OrganizationService',
                'addGranularity',
                [
                    $organization,
                    $axes,
                    ['relevance' => true]
                ],
                __('Orga', 'backgroundTasks', 'addGranularity', ['LABEL' => implode(', ', $axes)])
            );
            $this->workDispatcher->runBackground($task, $this->waitDelay, $success, $timeout, $error);
        }
    }

    /**
     * @Secure("editOrganization")
     */
    public function editAfsAction()
    {
        $idOrganization = $this->getParam('idOrganization');
        /** @var Orga_Model_Organization $organization */
        $organization = Orga_Model_Organization::load($idOrganization);

        $this->view->assign('organization', $organization);
        $this->view->assign('granularities', $organization->getInputGranularities());

        $afs = [];
        /** @var AF_Model_AF $af */
        foreach (AF_Model_AF::loadList() as $af) {
            $afs[$af->getRef()] = $af->getLabel();
        }
        $this->view->assign('afs', $afs);

        if ($this->hasParam('display') && ($this->getParam('display') === 'render')) {
            $this->_helper->layout()->disableLayout();
            $this->view->assign('display', false);
        } else {
            $this->view->assign('display', true);
        }
    }

    /**
     * @Secure("editOrganization")
     */
    public function addGranularityAfsAction()
    {
        $idOrganization = $this->getParam('idOrganization');
        /** @var Orga_Model_Organization $organization */
        $organization = Orga_Model_Organization::load($idOrganization);

        $inputAxes = $this->getAxesAddGranularity($organization);

        $refConfigAxes = explode(',', $this->getParam('inputConfigAxes'));
        /** @var Orga_Model_Axis[] $configAxes */
        $configAxes = [];
        if (!empty($this->getParam('inputConfigAxes')))
            foreach ($refConfigAxes as $refConfigAxis) {
            $configAxis = $organization->getAxisByRef($refConfigAxis);
            // On regarde si les axes précédement ajouter ne sont pas lié hierachiquement à l'axe actuel.
            if (!$configAxis->isTransverse($configAxes)) {
                throw new Core_Exception_User('Orga', 'granularity', 'hierarchicallyLinkedAxes');
                break;
            } else {
                $configAxes[] = $configAxis;
            }
        }

        foreach ($configAxes as $configAxis) {
            foreach ($inputAxes as $inputAxis) {
                if ($inputAxis->isBroaderThan($configAxis)) {
                    throw new Core_Exception_User('Orga', 'configuration', 'inputGranularityNeedsToBeNarrowerThanFormChoiceGranularity');
                }
            }
            if ($configAxis->isTransverse($inputAxes)) {
                throw new Core_Exception_User('Orga', 'configuration', 'inputGranularityNeedsToBeNarrowerThanFormChoiceGranularity');
            }
        }

        try {
            $inputGranularity = $organization->getGranularityByRef(Orga_Model_Granularity::buildRefFromAxes($inputAxes));

            $configGranularity = $organization->getGranularityByRef(Orga_Model_Granularity::buildRefFromAxes($configAxes));
            $inputGranularity->setInputConfigGranularity($configGranularity);
            $this->sendJsonResponse(['message' => __('UI', 'message', 'added')]);
        } catch (Core_Exception_NotFound $e) {
            $success = function () {
                $this->sendJsonResponse(['message' => __('UI', 'message', 'added')]);
            };
            $timeout = function () {
                $this->sendJsonResponse(['message' => __('UI', 'message', 'addedLater')]);
            };
            $error = function (Exception $e) {
                throw $e;
            };

            // Lance la tache en arrière plan
            $task = new ServiceCallTask(
                'Orga_Service_OrganizationService',
                'addGranularity',
                [
                    $organization,
                    $inputAxes,
                    ['afs' => $configAxes]
                ],
                __('Orga', 'backgroundTasks', 'addGranularity', ['LABEL' => implode(', ', $inputAxes)])
            );
            $this->workDispatcher->runBackground($task, $this->waitDelay, $success, $timeout, $error);
        }
    }

    /**
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
     * @Secure("editOrganization")
     */
    public function editReportsAction()
    {
        $idOrganization = $this->getParam('idOrganization');
        /** @var Orga_Model_Organization $organization */
        $organization = Orga_Model_Organization::load($idOrganization);

        $this->view->assign('organization', $organization);
        $criteriaReports = new Criteria();
        $criteriaReports->where($criteriaReports->expr()->eq('cellsGenerateDWCubes', true));
        $reportsGranularities = $organization->getOrderedGranularities()->matching($criteriaReports)->toArray();
        $this->view->assign('granularities', $reportsGranularities);

        if ($this->hasParam('display') && ($this->getParam('display') === 'render')) {
            $this->_helper->layout()->disableLayout();
            $this->view->assign('display', false);
        } else {
            $this->view->assign('display', true);
        }
    }

    /**
     * @Secure("editOrganization")
     */
    public function addGranularityReportsAction()
    {
        $idOrganization = $this->getParam('idOrganization');
        /** @var Orga_Model_Organization $organization */
        $organization = Orga_Model_Organization::load($idOrganization);

        $axes = $this->getAxesAddGranularity($organization);

        try {
            $granularity = $organization->getGranularityByRef(Orga_Model_Granularity::buildRefFromAxes($axes));
            if ($granularity->getCellsGenerateDWCubes()) {
                throw new Core_Exception_User('Orga', 'edit', 'granularityAlreadyConfigured');
            }
            $granularity->setCellsGenerateDWCubes(true);
            $this->sendJsonResponse(['message' => __('UI', 'message', 'added')]);
        } catch (Core_Exception_NotFound $e) {
            $success = function () {
                $this->sendJsonResponse(['message' => __('UI', 'message', 'added')]);
            };
            $timeout = function () {
                $this->sendJsonResponse(['message' => __('UI', 'message', 'addedLater')]);
            };
            $error = function (Exception $e) {
                throw $e;
            };

            // Lance la tache en arrière plan
            $task = new ServiceCallTask(
                'Orga_Service_OrganizationService',
                'addGranularity',
                [
                    $organization,
                    $axes,
                    ['reports' => true]
                ],
                __('Orga', 'backgroundTasks', 'addGranularity', ['LABEL' => implode(', ', $axes)])
            );
            $this->workDispatcher->runBackground($task, $this->waitDelay, $success, $timeout, $error);
        }
    }

    /**
     * @Secure("editOrganizationAndCells")
     */
    public function rebuildAction()
    {
        /** @var User $connectedUser */
        $connectedUser = $this->_helper->auth();

        $idOrganization = $this->getParam('idOrganization');
        /** @var Orga_Model_Organization $organization */
        $organization = Orga_Model_Organization::load($idOrganization);

        $this->view->assign('organization', $organization);

        $userCanEditOrganization = false;
        foreach ($organization->getAdminRoles() as $role) {
            if ($role->getUser() === $connectedUser) {
                $userCanEditOrganization = true;
                break;
            }
        }
        if ($userCanEditOrganization) {
            $this->view->assign('cellData', $organization);
            $this->view->assign('cellResults', $organization->getGranularityByRef('global')->getCellByMembers([]));
        } else {
            $cellsCanEdit = $this->aclManager->getTopCellsWithAccessForOrganization(
                $connectedUser,
                $organization,
                [CellAdminRole::class]
            )['cells'];
            $this->view->assign('cells', $cellsCanEdit);
            if (count($cellsCanEdit) === 1) {
                $cell = array_pop(array_values($cellsCanEdit));
                $this->view->assign('cellData', $cell);
                $this->view->assign('cellResults', $cell);
            }
        }


        if ($this->hasParam('display') && ($this->getParam('display') === 'render')) {
            $this->_helper->layout()->disableLayout();
            $this->view->assign('display', false);
        } else {
            $this->view->assign('display', true);
        }
    }

    /**
     * @Secure("editOrganizationAndCells")
     */
    public function rebuildDataAction()
    {
        /** @var User $connectedUser */
        $connectedUser = $this->_helper->auth();

        $idOrganization = $this->getParam('idOrganization');
        /** @var Orga_Model_Organization $organization */
        $organization = Orga_Model_Organization::load($idOrganization);

        $userCanEditOrganization = false;
        foreach ($organization->getAdminRoles() as $role) {
            if ($role->getUser() === $connectedUser) {
                $userCanEditOrganization = true;
                $break;
            }
        }
        if ($userCanEditOrganization) {
            $taskName = 'resetOrganizationDWCubes';
            $taskParameters = [$organization];
            $organizationalUnit = __('Orga', 'organization', 'forWorkspace', ['LABEL' => $organization->getLabel()]);
        } else {
            $taskName = 'resetCellAndChildrenDWCubes';

            $cellsCanEdit = $this->aclManager->getTopCellsWithAccessForOrganization(
                $connectedUser,
                $organization,
                [CellAdminRole::class]
            )['cells'];
            if (count($cellsCanEdit) > 1) {
                $cell = Orga_Model_Cell::load($this->getParam('cell'));
            } else {
                $cell = array_pop($cellsCanEdit);
            }
            $taskParameters = [$cell];
            $organizationalUnit = __('Orga', 'organization', 'forOrganizationalUnit', ['LABEL' => $cell->getLabel()]);
        }

        $success = function () {
            $this->sendJsonResponse(['message' => __('DW', 'rebuild', 'analysisDataRebuildConfirmationMessage')]);
        };
        $timeout = function () {
            $this->sendJsonResponse(['message' => __('UI', 'message', 'operationInProgress')]);
        };
        $error = function () {
            throw new Core_Exception_User('DW', 'rebuild', 'analysisDataRebuildFailMessage');
        };

        // Lance la tache en arrière plan
        $task = new ServiceCallTask(
            'Orga_Service_ETLStructure',
            $taskName,
            $taskParameters,
            __('Orga', 'backgroundTasks', 'resetDWOrga', ['FOR_ORGANIZATIONAL_UNIT' => $organizationalUnit])
        );
        $this->workDispatcher->runBackground($task, $this->waitDelay, $success, $timeout, $error);
    }

    /**
     * @Secure("editOrganizationAndCells")
     */
    public function rebuildResultsAction()
    {
        /** @var User $connectedUser */
        $connectedUser = $this->_helper->auth();

        $idOrganization = $this->getParam('idOrganization');
        /** @var Orga_Model_Organization $organization */
        $organization = Orga_Model_Organization::load($idOrganization);


        $cellsCanEdit = $this->aclManager->getTopCellsWithAccessForOrganization(
            $connectedUser,
            $organization,
            [CellAdminRole::class]
        )['cells'];
        if (count($cellsCanEdit) > 1) {
            $cell = Orga_Model_Cell::load($this->getParam('cell'));
        } else {
            $cell = array_pop($cellsCanEdit);
        }

        $success = function () {
            $this->sendJsonResponse(['message' => __('DW', 'rebuild', 'analysisDataRebuildConfirmationMessage')]);
        };
        $timeout = function () {
            $this->sendJsonResponse(['message' => __('UI', 'message', 'operationInProgress')]);
        };
        $error = function () {
            throw new Core_Exception_User('DW', 'rebuild', 'analysisDataRebuildFailMessage');
        };

        // Lance la tache en arrière plan
        $task = new ServiceCallTask(
            'Orga_Service_ETLStructure',
            'resetCellAndChildrenDWCubes',
            [$cell],
            __('Orga', 'backgroundTasks', 'resetDWCellAndResults', ['LABEL' => $cell->getLabel()])
        );
        $this->workDispatcher->runBackground($task, $this->waitDelay, $success, $timeout, $error);
    }

    /**
     * @Secure("loggedIn")
     */
    public function noaccessAction()
    {
    }
}
