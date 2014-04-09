<?php

use AF\Domain\AF;
use Core\Annotation\Secure;
use Core\Work\ServiceCall\ServiceCallTask;
use Doctrine\Common\Collections\Criteria;
use MyCLabs\ACL\ACL;
use MyCLabs\MUIH\Tab;
use MyCLabs\MUIH\Tabs;
use Orga\OrganizationViewFactory;
use User\Domain\ACL\Actions;
use MyCLabs\ACL\Model\ClassResource;
use MyCLabs\Work\Dispatcher\WorkDispatcher;
use Orga\Model\ACL\CellAdminRole;
use Orga\ViewModel\CellViewModelFactory;
use User\Domain\User;

/**
 * @author valentin.claras
 */
class Orga_OrganizationController extends Core_Controller
{
    use UI_Controller_Helper_Form;

    /**
     * @Inject
     * @var ACL
     */
    private $acl;

    /**
     * @Inject
     * @var Orga_Service_ACLManager
     */
    private $orgaACLManager;

    /**
     * @Inject
     * @var OrganizationViewFactory
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

        $isConnectedUserAbleToCreateOrganizations = $this->acl->isAllowed(
            $connectedUser,
            Actions::CREATE,
            new ClassResource(Orga_Model_Organization::class)
        );

        if (!$isConnectedUserAbleToCreateOrganizations) {
            $aclQuery = new Core_Model_Query();
            $aclQuery->aclFilter->enabled = true;
            $aclQuery->aclFilter->user = $connectedUser;
            $aclQuery->aclFilter->action = Actions::DELETE;
            $isConnectedUserAbleToDeleteOrganizations = (Orga_Model_Organization::countTotal($aclQuery) > 0);
            if (!$isConnectedUserAbleToDeleteOrganizations) {
                $aclQuery->aclFilter->action = Actions::VIEW;
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
        $query->aclFilter->action = Actions::VIEW;
        $organizations = Orga_Model_Organization::loadList($query);

        // Crée les ViewModel
        $organizationsViewModel = [];
        foreach ($organizations as $organization) {
            $organizationsViewModel[] = $this->organizationVMFactory->createOrganizationView($organization, $connectedUser);
        }
        $this->view->assign('organizations', $organizationsViewModel);

        $this->view->assign('canCreateOrganization', $this->acl->isAllowed(
            $connectedUser,
            Actions::CREATE,
            new ClassResource(Orga_Model_Organization::class)
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
                    'message' => __('UI', 'message', 'addedLater'),
                    'typeMessage' => 'info',
                    'info' => __('Orga', 'add', 'complementaryInfo')
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

        $cellsWithAccess = $this->orgaACLManager->getTopCellsWithAccessForOrganization($connectedUser, $organization);
        if (count($cellsWithAccess['cells']) === 1) {
            $this->redirect('orga/cell/view/idCell/'.array_pop($cellsWithAccess['cells'])->getId());
        }

        $organizationViewModel = $this->organizationVMFactory->createOrganizationView($organization, $connectedUser);
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
            $this->organizationVMFactory->createOrganizationView($organization, $connectedUser)
        );
        $isUserAllowedToEditOrganization = $this->acl->isAllowed(
            $connectedUser,
            Actions::EDIT,
            $organization
        );
        if (!$isUserAllowedToEditOrganization) {
            $numberCellsUserCanEdit = 0;
            foreach ($organization->getOrderedGranularities() as $granularity) {
                $aclCellQuery = new Core_Model_Query();
                $aclCellQuery->aclFilter->enabled = true;
                $aclCellQuery->aclFilter->user = $connectedUser;
                $aclCellQuery->aclFilter->action = Actions::EDIT;
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

        $tabView = new Tabs('orga');
        $parameters = '/idOrganization/'.$idOrganization.'/display/render/';

        // Tab Organization & Axes.
        if ($isUserAllowedToEditOrganization) {
            $organizationTab = new Tab('organization');
            $organizationTab->setTitle(__('Orga', 'configuration', 'generalInfoTab'));
            $organizationTab->setContent('orga/organization/edit-organization'.$parameters);
            $organizationTab->setAjax(true, false);
            $tabView->addTab($organizationTab);

            $axisTab = new Tab('axes');
            $axisTab->setTitle(__('UI', 'name', 'axes'));
            $axisTab->setContent('orga/axis/manage'.$parameters);
            $axisTab->setAjax(true, true);
            $tabView->addTab($axisTab);
        }

        // Tab Members.
        $canUserEditMembers = $isUserAllowedToEditCells;
        if (!$isUserAllowedToEditOrganization) {
            $axesCanEdit = $this->orgaACLManager->getAxesCanEdit($connectedUser, $organization);
            if (count($axesCanEdit) === 0) {
                $canUserEditMembers = false;
            }
        }
        if ($canUserEditMembers) {
            $membersTab = new Tab('members');
            $membersTab->setTitle(__('UI', 'name', 'elements'));
            $membersTab->setContent('orga/member/manage'.$parameters);
            $membersTab->setAjax(true, false);
            $tabView->addTab($membersTab);
        }

        // Tab Relevant.
        $canUserEditRelevance = $isUserAllowedToEditCells;
        if (!$isUserAllowedToEditOrganization) {
            $canUserEditRelevance = false;
            foreach ($this->orgaACLManager->getGranularitiesCanEdit($connectedUser, $organization) as $granularity) {
                if ($granularity->getCellsControlRelevance()) {
                    $canUserEditRelevance = true;
                    break;
                }
            }
        }
        if ($canUserEditRelevance) {
            $relevanceTab = new Tab('relevance');
            $relevanceTab->setTitle(__('Orga', 'cellRelevance', 'relevance'));
            $relevanceTab->setContent('orga/organization/edit-relevance'.$parameters);
            $relevanceTab->setAjax(true, false);
            $tabView->addTab($relevanceTab);
        }

        // Tab AFConfiguration.
        if ($isUserAllowedToEditOrganization) {
            $afTab = new Tab('afs');
            $afTab->setTitle(__('UI', 'name', 'forms'));
            $afTab->setContent('orga/organization/edit-afs'.$parameters);
            $afTab->setAjax(true, false);
            $tabView->addTab($afTab);
        }

        // Tab ACL.
        if ($isUserAllowedToEditOrganization) {
            $aclTab = new Tab('acl');
            $aclTab->setTitle(__('User', 'role', 'roles'));
            $aclTab->setContent('orga/organization/edit-acl'.$parameters);
            $aclTab->setAjax(true, false);
            $tabView->addTab($aclTab);
        }

        // Tab Granularities.
        if ($isUserAllowedToEditOrganization) {
            $granularityTab = new Tab('granularities');
            $granularityTab->setTitle(__('Orga', 'granularity', 'granularities'));
            $granularityTab->setContent('orga/granularity/manage'.$parameters);
            $granularityTab->setAjax(true, false);
            $tabView->addTab($granularityTab);
        }

        // Tab Consistency.
        if ($isUserAllowedToEditOrganization) {
            $consistencyTab = new Tab('consistency');
            $consistencyTab->setTitle(__('UI', 'name', 'control'));
            $consistencyTab->setContent('orga/organization/consistency'.$parameters);
            $consistencyTab->setAjax(true, true);
            $tabView->addTab($consistencyTab);
        }

        // Tab DW
        if ($isUserAllowedToEditOrganization) {
            $dwTab = new Tab('reports');
            $dwTab->setTitle(__('DW', 'name', 'analysesConfig'));
            $dwTab->setContent('orga/organization/edit-reports'.$parameters);
            $dwTab->setAjax(true, false);
            $tabView->addTab($dwTab);
        }

        // Tab Rebuild
        if ($canUserEditMembers) {
            $rebuildTab = new Tab('rebuild');
            $rebuildTab->setTitle(__('DW', 'rebuild', 'dataRebuildTab'));
            $rebuildTab->setContent('orga/organization/rebuild'.$parameters);
            $rebuildTab->setAjax(true, !$canUserEditMembers);
            $tabView->addTab($rebuildTab);
        }

        $activeTab = $this->hasParam('tab') ? $this->getParam('tab') : 'organization';
        $editOrganizationTabs = ['organization', 'axes', 'granularities', 'consistency'];
        if (!$isUserAllowedToEditOrganization && in_array($activeTab, $editOrganizationTabs)) {
            $activeTab = 'default';
            if ($canUserEditRelevance) {
                $activeTab = 'relevance';
            }
            if ($canUserEditMembers) {
                $activeTab = 'members';
            }
        }
        switch ($activeTab) {
            case 'organization':
                $tabView->activeTab($organizationTab);
                break;
            case 'axes':
                $tabView->activeTab($axisTab);
                break;
            case 'members':
                $tabView->activeTab($membersTab);
                break;
            case 'relevance':
                $tabView->activeTab($relevanceTab);
                break;
            case 'afs':
                $tabView->activeTab($afTab);
                break;
            case 'acl':
                $tabView->activeTab($aclTab);
                break;
            case 'granularities':
                $tabView->activeTab($granularityTab);
                break;
            case 'consistency':
                $tabView->activeTab($consistencyTab);
                break;
            case 'reports':
                $tabView->activeTab($dwTab);
                break;
            case 'rebuild':
                $tabView->activeTab($rebuildTab);
                break;
        }

        $this->view->assign('tabView', $tabView);
        UI_Datagrid::addHeader();
        UI_Tree::addHeader();
        UI_Form::addHeader();
        $this->view->headScript()->appendFile('scripts/ui/refRefactor.js', 'text/javascript');
        $this->setActiveMenuItemOrganization($organization->getId());
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

        $updated = false;

        $label = (string) $this->getParam('label');
        if (empty($label)) {
            $this->addFormError(
                'label',
                __('UI', 'formValidation', 'emptyRequiredField')
            );
        } elseif ($organization->getLabel() !== $label) {
            $organization->setLabel($label);
            $updated = true;
        }

        //@todo Faire une action à part ?
//        $refGranularityForInventoryStatus = $formData->getValue('granularityForInventoryStatus');
//        if (!empty($refGranularityForInventoryStatus)) {
//            $newGranularityForInventoryStatus = $organization->getGranularityByRef($refGranularityForInventoryStatus);
//            try {
//                $currentGranularityForInventoryStatus = $organization->getGranularityForInventoryStatus();
//            } catch (Core_Exception_UndefinedAttribute $e) {
//                $currentGranularityForInventoryStatus = null;
//            }
//            if ($currentGranularityForInventoryStatus !== $newGranularityForInventoryStatus) {
//                try {
//                    $organization->setGranularityForInventoryStatus($newGranularityForInventoryStatus);
//                    $updated = true;
//                } catch (Core_Exception_InvalidArgument $e) {
//                    $this->addFormError(
//                        'granularityForInventoryStatus',
//                        __('Orga', 'exception', 'broaderInputGranularity')
//                    );
//                }
//            }
//        }

        if ($this->hasFormError() || !$updated) {
            $this->setFormMessage(__('UI', 'message', 'nullUpdated'));
        } else {
            $this->setFormMessage(__('UI', 'message', 'updated'));
        }

        $this->sendFormResponse();
    }

    /**
     * @Secure("editOrganization")
     */
    public function editBannerAction()
    {
        $idOrganization = $this->getParam('idOrganization');
        /** @var Orga_Model_Organization $organization */
        $organization = Orga_Model_Organization::load($idOrganization);
        $this->view->assign('idOrganization', $idOrganization);

        $basePath = APPLICATION_PATH . '/../public/workspaceBanners/';

        $message = [];

        $transferAdapter = new Zend_File_Transfer_Adapter_Http();
        $mimeType = [
            'image/bmp',
            'image/gif',
            'image/x-png',
            'image/png',
            'image/jpeg',
            'image/pjpeg',
            'image/tiff',
            'image/vnd.microsoft.icon',
            'image/svg+xml'
        ];
        $transferAdapter->addValidators(['MimeType' => array_merge(['headerCheck' => true], $mimeType)]);

        if (empty($messages) && ($transferAdapter->hasErrors())) {
            $messages = $transferAdapter->getErrors();
        }
        if (empty($messages) && (!$transferAdapter->isUploaded())) {
            $messages = [__('Doc', 'library', 'noDocumentGiven')];
        }
        if (empty($messages) && (!$transferAdapter->isValid())) {
            $messages = [__('Doc', 'library', 'invalidMIMEType')];
        }
        if (empty($messages) && ($transferAdapter->getFileName() == null)) {
            $messages = [__('Doc', 'messages', 'uploadError')];
        }

        if (empty($messages)) {
            foreach (glob(APPLICATION_PATH . '/../public/workspaceBanners/' . $idOrganization . '.*') as $file) {
                unlink($file);
            }
            $transferAdapter->addFilter('Rename', ['target' => $basePath.$idOrganization.'.'.pathinfo($transferAdapter->getFileName(), PATHINFO_EXTENSION)]);
            if (!$transferAdapter->receive()) {
                $messages = [__('Core', 'exception', 'applicationError')];
            }
        }

        $this->view->assign('success', empty($messages));
        if (!empty($messages)) {
            $this->view->assign('message', implode("\n", $messages));
        } else {
            $this->view->assign('message', $transferAdapter->getFileName(null, false));
        }

        $this->_helper->layout->disableLayout();
    }

    /**
     * @Secure("editOrganization")
     */
    public function removeBannerAction()
    {
        $idOrganization = $this->getParam('idOrganization');
        /** @var Orga_Model_Organization $organization */
        $organization = Orga_Model_Organization::load($idOrganization);

        foreach (glob(APPLICATION_PATH . '/../public/workspaceBanners/' . $idOrganization . '.*') as $file) {
            unlink($file);
        }

        $this->sendJsonResponse(['message' => __('UI', 'message', 'updated')]);
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
        $refAxes = $this->getParam('axes');

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

        $isUserAllowedToEditOrganization = $this->acl->isAllowed(
            $connectedUser,
            Actions::EDIT,
            $organization
        );
        $this->view->assign('isUserAllowedToEditOrganization', $isUserAllowedToEditOrganization);
        $relevanceGranularities = [];
        foreach ($this->orgaACLManager->getGranularitiesCanEdit($connectedUser, $organization) as $granularity) {
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
                throw new Core_Exception_User('Orga', 'granularity', 'granularityAlreadyConfigured');
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
        /** @var \AF\Domain\AF $af */
        foreach (AF::loadList() as $af) {
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

        $refConfigAxes = $this->getParam('inputConfigAxes');
        /** @var Orga_Model_Axis[] $configAxes */
        $configAxes = [];
        if (!empty($this->getParam('inputConfigAxes'))) {
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
        }

        foreach ($configAxes as $configAxis) {
            foreach ($inputAxes as $inputAxis) {
                if ($inputAxis->isBroaderThan($configAxis)) {
                    throw new Core_Exception_User(
                        'Orga',
                        'configuration',
                        'inputGranularityNeedsToBeNarrowerThanFormChoiceGranularity'
                    );
                }
            }
            if ($configAxis->isTransverse($inputAxes)) {
                throw new Core_Exception_User(
                    'Orga',
                    'configuration',
                    'inputGranularityNeedsToBeNarrowerThanFormChoiceGranularity'
                );
            }
        }

        try {
            $inputGranularity = $organization->getGranularityByRef(
                Orga_Model_Granularity::buildRefFromAxes($inputAxes)
            );

            $configGranularity = $organization->getGranularityByRef(
                Orga_Model_Granularity::buildRefFromAxes($configAxes)
            );
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
    public function editAclAction()
    {
        $idOrganization = $this->getParam('idOrganization');
        /** @var Orga_Model_Organization $organization */
        $organization = Orga_Model_Organization::load($idOrganization);

        $this->view->assign('organization', $organization);
        $criteria = Doctrine\Common\Collections\Criteria::create();
        $criteria->where(Doctrine\Common\Collections\Criteria::expr()->eq('cellsWithACL', true));
        $criteria->orderBy(['position' => 'ASC']);
        $aclGranularities = $organization->getGranularities()->matching($criteria)->toArray();
        $this->view->assign('aclGranularities', $aclGranularities);

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
    public function addGranularityAclAction()
    {
        $idOrganization = $this->getParam('idOrganization');
        /** @var Orga_Model_Organization $organization */
        $organization = Orga_Model_Organization::load($idOrganization);

        $axes = $this->getAxesAddGranularity($organization);

        try {
            $granularity = $organization->getGranularityByRef(Orga_Model_Granularity::buildRefFromAxes($axes));
            if ($granularity->getCellsWithACL()) {
                throw new Core_Exception_User('Orga', 'granularity', 'granularityAlreadyConfigured');
            }
            $granularity->setCellsWithACL(true);
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
                    ['acl' => true]
                ],
                __('Orga', 'backgroundTasks', 'addGranularity', ['LABEL' => implode(', ', $axes)])
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
                throw new Core_Exception_User('Orga', 'granularity', 'granularityAlreadyConfigured');
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

        $cellsData = [];

        $userCanEditOrganization = $this->aclService->isAllowed($connectedUser, Action::EDIT(), $organization);
        $this->view->assign('canEditOrganization', $userCanEditOrganization);
        if ($userCanEditOrganization) {
            $cellsData[] = $organization;
            $globalCell = $organization->getGranularityByRef('global')->getCellByMembers([]);
            $cellsResults = [$globalCell];
            foreach ($globalCell->getGranularity()->getNarrowerGranularities() as $narrowerGranularity) {
                if ($narrowerGranularity->getCellsGenerateDWCubes()) {
                    foreach ($narrowerGranularity->getCells() as $childCell) {
                        $cellsResults[] = $childCell;
                    }
                }
            }
            $this->view->assign('cellsResults', $cellsResults);

            $topCells = [$globalCell];
        } else {
            /** @var Orga_Model_Cell[] $topCells */
            $topCells = $this->acl->getTopCellsWithAccessForOrganization(
                $connectedUser,
                $organization,
                [CellAdminRole::class]
            )['cells'];

        }
        foreach ($topCells as $topCell) {
            $cellsData[$topCell->getId()] = $topCell;
            foreach ($topCell->getGranularity()->getNarrowerGranularities() as $narrowerGranularity) {
                if ($narrowerGranularity->getCellsGenerateDWCubes()) {
                    foreach ($topCell->getChildCellsForGranularity($narrowerGranularity) as $childCell) {
                        $cellsData[$childCell->getId()] = $childCell;
                    }
                }
            }
        }
        $this->view->assign('cellsData', $cellsData);

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

        $idCell = $this->getParam('cell');
        if (empty($idCell)) {
            $taskName = 'resetOrganizationDWCubes';
            $taskParameters = [$organization];
            $organizationalUnit = __('Orga', 'organization', 'forWorkspace', ['LABEL' => $organization->getLabel()]);
        } else {
            $taskName = 'resetCellAndChildrenDWCubes';
            $cell = Orga_Model_Cell::load($this->getParam('cell'));
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

        $cell = Orga_Model_Cell::load($this->getParam('cell'));

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
            'resetCellAndChildrenCalculationsAndDWCubes',
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
