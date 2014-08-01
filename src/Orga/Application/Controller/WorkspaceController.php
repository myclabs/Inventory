<?php

use Account\Domain\AccountRepository;
use AF\Domain\AFLibrary;
use Classification\Domain\ClassificationLibrary;
use Classification\Domain\ContextIndicator;
use MyCLabs\Work\Dispatcher\SynchronousWorkDispatcher;
use Core\Annotation\Secure;
use Core\Work\ServiceCall\ServiceCallTask;
use Doctrine\Common\Collections\Criteria;
use MyCLabs\ACL\ACL;
use MyCLabs\MUIH\Tab;
use MyCLabs\MUIH\Tabs;
use Orga\Application\Service\Workspace\ETLService;
use Orga\Domain\Axis;
use Orga\Domain\Cell;
use Orga\Domain\Granularity;
use Orga\Domain\Service\ETL\ETLStructureService;
use Orga\Domain\Workspace;
use Orga\Application\Service\Workspace\WorkspaceService;
use Orga\Application\Service\OrgaUserAccessManager;
use Orga\Application\ViewModel\WorkspaceViewFactory;
use User\Domain\ACL\Actions;
use MyCLabs\ACL\Model\ClassResource;
use Orga\Domain\ACL\CellAdminRole;
use Orga\Application\ViewModel\CellViewFactory;
use User\Domain\User;

/**
 * @author valentin.claras
 */
class Orga_WorkspaceController extends Core_Controller
{
    use UI_Controller_Helper_Form;

    /**
     * @Inject
     * @var ACL
     */
    private $acl;

    /**
     * @Inject
     * @var OrgaUserAccessManager
     */
    private $orgaUserAccessManager;

    /**
     * @Inject
     * @var WorkspaceViewFactory
     */
    private $workspaceVMFactory;

    /**
     * @Inject
     * @var CellViewFactory
     */
    private $cellVMFactory;

    /**
     * @Inject
     * @var WorkspaceService
     */
    private $workspaceService;

    /**
     * @Inject
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * @Inject
     * @var ETLService
     */
    private $etlService;

    /**
     * @Inject
     * @var SynchronousWorkDispatcher
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
        $this->redirect('/');
    }

    /**
     * @Secure("createWorkspace")
     */
    public function addAction()
    {
        $this->view->assign('account', $this->getParam('account'));
        $this->view->assign('templates', $this->workspaceService->getWorkspaceTemplates());

        $this->view->headScript()->appendFile('scripts/ui/form-ajax.js', 'text/javascript');
    }

    /**
     * @Secure("createWorkspace")
     */
    public function addSubmitAction()
    {
        $user = $this->_helper->auth();
        $label = $this->getParam('oranizationLabel');

        $success = function () {
            $this->sendJsonResponse(
                [
                    'message' => __('UI', 'message', 'added'),
                    'type' => 'success',
                    'info' => __('Orga', 'add', 'complementaryInfo')
                ]
            );
        };
        $timeout = function () {
            $this->sendJsonResponse(
                [
                    'message' => __('UI', 'message', 'addedLater'),
                    'type' => 'info',
                    'info' => __('Orga', 'add', 'complementaryInfo')
                ]
            );
        };
        $error = function (Exception $e) {
            throw $e;
        };

        // Lance la tache en arrière plan
        $task = new ServiceCallTask(
            WorkspaceService::class,
            'createFromTemplatesForm',
            [$user, $this->accountRepository->get($this->getParam('account')), $this->getRequest()->getPost()],
            __('Orga', 'backgroundTasks', 'createWorkspace', ['LABEL' => $label])
        );
        $this->workDispatcher->runAndWait($task, $this->waitDelay, $success, $timeout, $error);
    }

    /**
     * @Secure("deleteWorkspace")
     */
    public function deleteAction()
    {
        $workspace = Workspace::load($this->getParam('workspace'));

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
            WorkspaceService::class,
            'delete',
            [$workspace],
            ___('Orga', 'backgroundTasks', 'removeWorkspace',
                ['LABEL' => $this->translator->get($workspace->getLabel())]
            )
        );
        $this->workDispatcher->runAndWait($task, $this->waitDelay, $success, $timeout, $error);

        $this->redirect('account/dashboard/');
    }

    /**
     * @Secure("viewWorkspace")
     */
    public function viewAction()
    {
        /** @var User $connectedUser */
        $connectedUser = $this->_helper->auth();

        $workspaceId = $this->getParam('workspace');
        /** @var Workspace $workspace */
        $workspace = Workspace::load($workspaceId);

        $cellsWithAccess = $this->orgaUserAccessManager->getTopCellsWithAccessForWorkspace($connectedUser, $workspace);
        if (count($cellsWithAccess['cells']) === 1) {
            $this->redirect('orga/cell/view/cell/'.array_pop($cellsWithAccess['cells'])->getId());
        }

        $workspaceViewModel = $this->workspaceVMFactory->createWorkspaceView($workspace, $connectedUser);
        $this->view->assign('workspace', $workspaceViewModel);
        $cellViewModels = [];
        foreach ($cellsWithAccess['cells'] as $cellWithAccess) {
            $cellViewModels[] = $this->cellVMFactory->createCellView($cellWithAccess, $connectedUser);
        }
        $this->view->assign('cells', $cellViewModels);
        $this->view->assign('cellsAccess', $cellsWithAccess['accesses']);
    }

    /**
     * @Secure("editWorkspaceAndCells")
     */
    public function editAction()
    {
        /** @var User $connectedUser */
        $connectedUser = $this->_helper->auth();

        $workspaceId = $this->getParam('workspace');
        /** @var Workspace $workspace */
        $workspace = Workspace::load($workspaceId);

        $this->view->assign(
            'workspace',
            $this->workspaceVMFactory->createWorkspaceView($workspace, $connectedUser)
        );
        $isUserAllowedToEditWorkspace = $this->acl->isAllowed(
            $connectedUser,
            Actions::EDIT,
            $workspace
        );
        if (!$isUserAllowedToEditWorkspace) {
            $numberCellsUserCanEdit = 0;
            foreach ($workspace->getOrderedGranularities() as $granularity) {
                $aclCellQuery = new Core_Model_Query();
                $aclCellQuery->aclFilter->enabled = true;
                $aclCellQuery->aclFilter->user = $connectedUser;
                $aclCellQuery->aclFilter->action = Actions::EDIT;
                $aclCellQuery->filter->addCondition(Cell::QUERY_GRANULARITY, $granularity);

                $numberCellsUserCanEdit = Cell::countTotal($aclCellQuery);
                if ($numberCellsUserCanEdit > 0) {
                    break;
                }
            }
            $isUserAllowedToEditCells = ($numberCellsUserCanEdit > 0);
        } else {
            $isUserAllowedToEditCells = true;
        }

        $tabView = new Tabs('orga');
        $parameters = '/workspace/'.$workspaceId.'/display/render/';

        // Tab Workspace & Axes.
        if ($isUserAllowedToEditWorkspace) {
            $workspaceTab = new Tab('workspace');
            $workspaceTab->setTitle(__('Orga', 'configuration', 'generalInfoTab'));
            $workspaceTab->setContent('orga/workspace/edit-workspace'.$parameters);
            $workspaceTab->setAjax(true, false);
            $tabView->addTab($workspaceTab);

            $axisTab = new Tab('axes');
            $axisTab->setTitle(__('UI', 'name', 'axes'));
            $axisTab->setContent('orga/axis/manage'.$parameters);
            $axisTab->setAjax(true, true);
            $tabView->addTab($axisTab);
        }

        // Tab Members.
        $canUserEditMembers = $isUserAllowedToEditCells;
        if (!$isUserAllowedToEditWorkspace) {
            $axesCanEdit = $this->orgaUserAccessManager->getAxesCanEdit($connectedUser, $workspace);
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

        // Tab Consistency.
        if ($isUserAllowedToEditWorkspace) {
            $consistencyTab = new Tab('consistency');
            $consistencyTab->setTitle(__('UI', 'name', 'control'));
            $consistencyTab->setContent('orga/workspace/consistency'.$parameters);
            $consistencyTab->setAjax(true, true);
            $tabView->addTab($consistencyTab);
        }

        // Tab Relevant.
        $canUserEditRelevance = $isUserAllowedToEditCells;
        if (!$isUserAllowedToEditWorkspace) {
            $canUserEditRelevance = false;
            foreach ($this->orgaUserAccessManager->getGranularitiesCanEdit($connectedUser, $workspace) as $granularity) {
                if ($granularity->getCellsControlRelevance()) {
                    $canUserEditRelevance = true;
                    break;
                }
            }
        }
        if ($canUserEditRelevance) {
            $relevanceTab = new Tab('relevance');
            $relevanceTab->setTitle(__('Orga', 'cellRelevance', 'relevance'));
            $relevanceTab->setContent('orga/workspace/edit-relevance'.$parameters);
            $relevanceTab->setAjax(true, false);
            $tabView->addTab($relevanceTab);
        }

        // Tab Inventory.
        if ($isUserAllowedToEditWorkspace) {
            $inventoryTab = new Tab('inventory');
            $inventoryTab->setTitle(__('Orga', 'inventory', 'inventory'));
            $inventoryTab->setContent('orga/workspace/edit-inventory'.$parameters);
            $inventoryTab->setAjax(true, false);
            $tabView->addTab($inventoryTab);
        }

        // Tab AFConfiguration.
        if ($isUserAllowedToEditWorkspace) {
            $afTab = new Tab('afs');
            $afTab->setTitle(__('UI', 'name', 'forms'));
            $afTab->setContent('orga/workspace/edit-afs'.$parameters);
            $afTab->setAjax(true, false);
            $tabView->addTab($afTab);
        }

        // Tab ACL.
        if ($isUserAllowedToEditWorkspace) {
            $aclTab = new Tab('acl');
            $aclTab->setTitle(__('User', 'role', 'roles'));
            $aclTab->setContent('orga/workspace/edit-acl'.$parameters);
            $aclTab->setAjax(true, false);
            $tabView->addTab($aclTab);
        }

        // Tab DW
        if ($isUserAllowedToEditWorkspace) {
            $dwTab = new Tab('reports');
            $dwTab->setTitle(__('DW', 'name', 'analysesConfig'));
            $dwTab->setContent('orga/workspace/edit-reports'.$parameters);
            $dwTab->setAjax(true, false);
            $tabView->addTab($dwTab);
        }

        // Tab Granularities.
        if ($isUserAllowedToEditWorkspace) {
            $granularityTab = new Tab('granularities');
            $granularityTab->setTitle(__('Orga', 'granularity', 'granularitiesSynthesis'));
            $granularityTab->setContent('orga/granularity/manage'.$parameters);
            $granularityTab->setAjax(true, false);
            $tabView->addTab($granularityTab);
        }

        // Tab Rebuild
        if ($canUserEditMembers) {
            $rebuildTab = new Tab('rebuild');
            $rebuildTab->setTitle(__('DW', 'rebuild', 'dataRebuildTab'));
            $rebuildTab->setContent('orga/workspace/rebuild'.$parameters);
            $rebuildTab->setAjax(true, !$canUserEditMembers);
            $tabView->addTab($rebuildTab);
        }

        // Tab Translate
        if ($isUserAllowedToEditWorkspace) {
            $translateTab = new Tab('translate');
            $translateTab->setTitle(__('UI', 'name', 'translations'));
            $translateTab->setContent('orga/workspace/translate'.$parameters);
            $translateTab->setAjax(true);
            $tabView->addTab($translateTab);
        }

        $activeTab = $this->hasParam('tab') ? $this->getParam('tab') : 'workspace';
        $editWorkspaceTabs = ['workspace', 'axes', 'granularities', 'consistency'];
        if (!$isUserAllowedToEditWorkspace && in_array($activeTab, $editWorkspaceTabs)) {
            $activeTab = 'default';
            if ($canUserEditRelevance) {
                $activeTab = 'relevance';
            }
            if ($canUserEditMembers) {
                $activeTab = 'members';
            }
        }
        switch ($activeTab) {
            case 'workspace':
                $tabView->activeTab($workspaceTab);
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
            case 'inventory':
                $tabView->activeTab($inventoryTab);
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
            case 'translate':
                $tabView->activeTab($translateTab);
                break;
        }

        $this->view->assign('tabView', $tabView);
        UI_Datagrid::addHeader();
        UI_Tree::addHeader();
        $this->view->headScript()->appendFile('scripts/ui/refRefactor.js', 'text/javascript');
        $this->setActiveMenuItemWorkspace($workspace->getId());
    }

    /**
     * @Secure("editWorkspace")
     */
    public function editWorkspaceAction()
    {
        $workspaceId = $this->getParam('workspace');
        /** @var Workspace $workspace */
        $workspace = Workspace::load($workspaceId);

        $this->view->assign('workspaceId', $workspaceId);
        $this->view->assign('workspaceLabel', $this->translator->get($workspace->getLabel()));

        $potentialAxes = [];
        foreach ($workspace->getAxes() as $axis) {
            if ($axis->isMemberPositioning()) {
                $potentialAxes[] = $axis;
            }
        }
        $this->view->assign('potentialAxes', $potentialAxes);
        $this->view->assign('selectedAxis', $workspace->getTimeAxis());

        $potentialContextIndicators = [];
        foreach (ClassificationLibrary::loadUsableInAccount($workspace->getAccount()) as $classificationLibrary) {
            foreach ($classificationLibrary->getContextIndicators() as $contextIndicator) {
                $potentialContextIndicators[] = $contextIndicator;
            }
        }
        $this->view->assign('potentialContextIndicators', $potentialContextIndicators);
        $this->view->assign('selectedContextIndicators', $workspace->getContextIndicators()->toArray());

        if ($this->hasParam('display') && ($this->getParam('display') === 'render')) {
            $this->_helper->layout()->disableLayout();
            $this->view->assign('display', false);
        } else {
            $this->view->assign('display', true);
        }
    }

    /**
     * @Secure("editWorkspace")
     */
    public function editWorkspaceSubmitAction()
    {
        $workspaceId = $this->getParam('workspace');
        /** @var Workspace $workspace */
        $workspace = Workspace::load($workspaceId);

        $updated = false;

        $label = (string) $this->getParam('label');
        if (empty($label)) {
            $this->addFormError(
                'label',
                __('UI', 'formValidation', 'emptyRequiredField')
            );
        } elseif ($this->translator->get($workspace->getLabel()) !== $label) {
            $this->translator->set($workspace->getLabel(), $label);
            $updated = true;
        }

        if ($this->hasFormError() || !$updated) {
            $this->setFormMessage(__('UI', 'message', 'nullUpdated'));
        } else {
            $this->setFormMessage(__('UI', 'message', 'updated'));
        }

        $this->sendFormResponse();
    }

    /**
     * @Secure("editWorkspace")
     */
    public function editTimeAxisAction()
    {
        $workspaceId = $this->getParam('workspace');
        /** @var Workspace $workspace */
        $workspace = Workspace::load($workspaceId);

        $timeAxis = null;
        $axisId = (string) $this->getParam('timeAxis');
        if (!empty($axisId)) {
            /** @var Axis $timeAxis */
            $timeAxis = Axis::load($axisId);
        }

        $success = function () {
            $this->setFormMessage(__('UI', 'message', 'updated'));
            $this->sendFormResponse();
        };
        $timeout = function () {
            $this->setFormMessage(__('UI', 'message', 'updatedLater'));
            $this->sendFormResponse();
        };
        $error = function (Exception $e) {
            throw $e;
        };

        // Lance la tache en arrière plan
        $task = new ServiceCallTask(
            WorkspaceService::class,
            'edit',
            [
                $workspace,
                ['timeAxis' => $timeAxis]
            ]
        );
        $this->workDispatcher->runAndWait($task, $this->waitDelay, $success, $timeout, $error);
    }

    /**
     * @Secure("editWorkspace")
     */
    public function editContextIndicatorsAction()
    {
        $workspaceId = $this->getParam('workspace');
        /** @var Workspace $workspace */
        $workspace = Workspace::load($workspaceId);

        $updated = false;

        $contextIndicators = [];
        foreach ($this->getParam('contextIndicators') as $contextIndicatorId) {
            $contextIndicator = ContextIndicator::load($contextIndicatorId);
            $contextIndicators[] = $contextIndicator;
            if (!$workspace->hasContextIndicator($contextIndicator)) {
                $updated = true;
                $workspace->addContextIndicator($contextIndicator);
            }
        }

        foreach ($workspace->getContextIndicators() as $oldContextIndicator) {
            if (!in_array($oldContextIndicator, $contextIndicators)) {
                $updated = true;
                $workspace->removeContextIndicator($oldContextIndicator);
            }
        }

        if (!$updated) {
            $this->setFormMessage(__('UI', 'message', 'nullUpdated'));
        } else {
            $this->setFormMessage(__('UI', 'message', 'updated'));
        }

        $this->sendFormResponse();
    }

    /**
     * @Secure("editWorkspace")
     */
    public function editBannerAction()
    {
        $workspaceId = $this->getParam('workspace');
        $this->view->assign('workspaceId', $workspaceId);

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
            foreach (glob(APPLICATION_PATH . '/../public/workspaceBanners/' . $workspaceId . '.*') as $file) {
                unlink($file);
            }
            $transferAdapter->addFilter('Rename', ['target' => $basePath.$workspaceId.'.'.pathinfo($transferAdapter->getFileName(), PATHINFO_EXTENSION)]);
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
     * @Secure("editWorkspace")
     */
    public function removeBannerAction()
    {
        $workspaceId = $this->getParam('workspace');

        foreach (glob(APPLICATION_PATH . '/../public/workspaceBanners/' . $workspaceId . '.*') as $file) {
            unlink($file);
        }

        $this->sendJsonResponse(['message' => __('UI', 'message', 'updated')]);
    }

    /**
     * @param Workspace $workspace
     *
     * @throws Core_Exception_User
     *
     * @return Axis[]
     */
    protected function getAxesAddGranularity(Workspace $workspace)
    {
        $axesRefs = $this->getParam('axes');

        /** @var Axis[] $axes */
        $axes = [];
        if (!empty($this->getParam('axes'))) {
            foreach ($axesRefs as $axisRef) {
                $axis = $workspace->getAxisByRef($axisRef);
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
     * @Secure("editWorkspaceAndCells")
     */
    public function editRelevanceAction()
    {
        /** @var User $connectedUser */
        $connectedUser = $this->_helper->auth();

        $workspaceId = $this->getParam('workspace');
        /** @var Workspace $workspace */
        $workspace = Workspace::load($workspaceId);

        $this->view->assign('workspace', $workspace);
        $this->view->assign(
            'topCellsWithAccess',
            $this->orgaUserAccessManager->getTopCellsWithAccessForWorkspace($connectedUser, $workspace)['cells']
        );

        $isUserAllowedToEditWorkspace = $this->acl->isAllowed(
            $connectedUser,
            Actions::EDIT,
            $workspace
        );
        $this->view->assign('isUserAllowedToEditWorkspace', $isUserAllowedToEditWorkspace);
        $relevanceGranularities = [];
        foreach ($this->orgaUserAccessManager->getGranularitiesCanEdit($connectedUser, $workspace) as $granularity) {
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
     * @Secure("editWorkspace")
     */
    public function addGranularityRelevanceAction()
    {
        $workspaceId = $this->getParam('workspace');
        /** @var Workspace $workspace */
        $workspace = Workspace::load($workspaceId);

        $axes = $this->getAxesAddGranularity($workspace);

        try {
            $granularity = $workspace->getGranularityByRef(Granularity::buildRefFromAxes($axes));
            if ($granularity->getCellsControlRelevance()) {
                throw new Core_Exception_User('Orga', 'granularity', 'granularityAlreadyConfigured');
            }
            $action = 'editGranularity';
            $parameters = [
                $granularity,
                ['relevance' => true]
            ];
        } catch (Core_Exception_NotFound $e) {
            $action = 'addGranularity';
            $parameters = [
                $workspace,
                $axes,
                ['relevance' => true]
            ];
        }

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
            WorkspaceService::class,
            $action,
            $parameters,
            __('Orga', 'backgroundTasks', 'addGranularity', ['LABEL' => implode(', ', $axes)])
        );
        $this->workDispatcher->runAndWait($task, $this->waitDelay, $success, $timeout, $error);
    }

    /**
     * @Secure("editWorkspace")
     */
    public function editInventoryAction()
    {
        $workspaceId = $this->getParam('workspace');
        /** @var Workspace $workspace */
        $workspace = Workspace::load($workspaceId);

        $this->view->assign('workspace', $workspace);

        $granularityForInventoryStatus = $workspace->getGranularityForInventoryStatus();
        $this->view->assign('granularityForInventoryStatus', $granularityForInventoryStatus);

        $criteria = Doctrine\Common\Collections\Criteria::create();
        $criteria->where(Doctrine\Common\Collections\Criteria::expr()->eq('cellsMonitorInventory', true));
        $criteria->orderBy(['position' => 'ASC']);
        $inventoryGranularities = $workspace->getGranularities()->matching($criteria)->toArray();
        $this->view->assign('inventoryGranularities', $inventoryGranularities);

        if ($this->hasParam('display') && ($this->getParam('display') === 'render')) {
            $this->_helper->layout()->disableLayout();
            $this->view->assign('display', false);
        } else {
            $this->view->assign('display', true);
        }
    }

    /**
     * @Secure("editWorkspace")
     */
    public function editInventorySubmitAction()
    {
        $workspaceId = $this->getParam('workspace');
        /** @var Workspace $workspace */
        $workspace = Workspace::load($workspaceId);

        $updated = false;

        try {
            $newGranularityForInventoryStatus = Granularity::load($this->getParam('granularityForInventoryStatus'));
        } catch (Core_Exception_NotFound $e) {
            $newGranularityForInventoryStatus = null;
        }

        $currentGranularityForInventoryStatus = $workspace->getGranularityForInventoryStatus();

        if ($currentGranularityForInventoryStatus !== $newGranularityForInventoryStatus) {
            try {
                $workspace->setGranularityForInventoryStatus($newGranularityForInventoryStatus);
                $updated = true;
            } catch (Core_Exception_InvalidArgument $e) {
                $this->addFormError(
                    'granularityForInventoryStatus',
                    __('Orga', 'exception', 'broaderInputGranularity')
                );
            }
        }

        if (!$updated) {
            $this->setFormMessage(__('UI', 'message', 'nullUpdated'));
        } else {
            $this->setFormMessage(__('UI', 'message', 'updated'));
        }

        $this->sendFormResponse(['updated' => $updated]);
    }

    /**
     * @Secure("editWorkspace")
     */
    public function addGranularityInventoryAction()
    {
        $workspaceId = $this->getParam('workspace');
        /** @var Workspace $workspace */
        $workspace = Workspace::load($workspaceId);

        $axes = $this->getAxesAddGranularity($workspace);

        try {
            $granularity = $workspace->getGranularityByRef(Granularity::buildRefFromAxes($axes));
            if ($granularity->getCellsMonitorInventory()) {
                throw new Core_Exception_User('Orga', 'granularity', 'granularityAlreadyConfigured');
            }
            $action = 'editGranularity';
            $parameters = [
                $granularity,
                ['inventory' => true]
            ];
        } catch (Core_Exception_NotFound $e) {
            $action = 'addGranularity';
            $parameters = [
                $workspace,
                $axes,
                ['inventory' => true]
            ];
        }

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
            WorkspaceService::class,
            $action,
            $parameters,
            __('Orga', 'backgroundTasks', 'addGranularity', ['LABEL' => implode(', ', $axes)])
        );
        $this->workDispatcher->runAndWait($task, $this->waitDelay, $success, $timeout, $error);
    }

    /**
     * @Secure("editWorkspace")
     */
    public function editAfsAction()
    {
        $workspaceId = $this->getParam('workspace');
        /** @var Workspace $workspace */
        $workspace = Workspace::load($workspaceId);

        $this->view->assign('workspace', $workspace);
        $this->view->assign('granularities', $workspace->getInputGranularities());

        $afs = [];
        /** @var \AF\Domain\AF $af */
        foreach (AFLibrary::loadUsableInAccount($workspace->getAccount()) as $afLibrary) {
            foreach ($afLibrary->getAFList() as $af) {
                $afs[$af->getId()] = $this->translator->get($af->getLabel())
                    . ' (' . $this->translator->get($afLibrary->getLabel()) . ')';
            }
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
     * @Secure("editWorkspace")
     */
    public function addGranularityAfsAction()
    {
        $workspaceId = $this->getParam('workspace');
        /** @var Workspace $workspace */
        $workspace = Workspace::load($workspaceId);

        $inputAxes = $this->getAxesAddGranularity($workspace);

        $configAxesRefs = $this->getParam('inputConfigAxes');
        /** @var Axis[] $configAxes */
        $configAxes = [];
        if (!empty($this->getParam('inputConfigAxes'))) {
            foreach ($configAxesRefs as $configAxisRef) {
                $configAxis = $workspace->getAxisByRef($configAxisRef);
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
            $inputGranularity = $workspace->getGranularityByRef(
                Granularity::buildRefFromAxes($inputAxes)
            );
            $action = 'editGranularity';
            $parameters = [
                $inputGranularity,
                ['afs' => $configAxes]
            ];
        } catch (Core_Exception_NotFound $e) {
            $action = 'addGranularity';
            $parameters = [
                $workspace,
                $inputAxes,
                ['afs' => $configAxes]
            ];
        }

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
            WorkspaceService::class,
            $action,
            $parameters,
            __('Orga', 'backgroundTasks', 'addGranularity', ['LABEL' => implode(', ', $inputAxes)])
        );
        $this->workDispatcher->runAndWait($task, $this->waitDelay, $success, $timeout, $error);
    }

    /**
     * @Secure("editWorkspace")
     */
    public function editAclAction()
    {
        $workspaceId = $this->getParam('workspace');
        /** @var Workspace $workspace */
        $workspace = Workspace::load($workspaceId);

        $this->view->assign('workspace', $workspace);
        $this->view->assign('aclGranularities', $workspace->getACLGranularities());

        if ($this->hasParam('display') && ($this->getParam('display') === 'render')) {
            $this->_helper->layout()->disableLayout();
            $this->view->assign('display', false);
        } else {
            $this->view->assign('display', true);
        }
    }

    /**
     * @Secure("editWorkspace")
     */
    public function addGranularityAclAction()
    {
        $workspaceId = $this->getParam('workspace');
        /** @var Workspace $workspace */
        $workspace = Workspace::load($workspaceId);

        $axes = $this->getAxesAddGranularity($workspace);

        try {
            $granularity = $workspace->getGranularityByRef(Granularity::buildRefFromAxes($axes));
            if ($granularity->getCellsWithACL()) {
                throw new Core_Exception_User('Orga', 'granularity', 'granularityAlreadyConfigured');
            }
            $action = 'editGranularity';
            $parameters = [
                $granularity,
                ['acl' => true]
            ];
        } catch (Core_Exception_NotFound $e) {
            $action = 'addGranularity';
            $parameters = [
                $workspace,
                $axes,
                ['acl' => true]
            ];
        }

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
            WorkspaceService::class,
            $action,
            $parameters,
            __('Orga', 'backgroundTasks', 'addGranularity', ['LABEL' => implode(', ', $axes)])
        );
        $this->workDispatcher->runAndWait($task, $this->waitDelay, $success, $timeout, $error);
    }

    /**
     * @Secure("editWorkspace")
     */
    public function consistencyAction()
    {
        $this->view->assign('workspaceId', $this->getParam('workspace'));

        if ($this->hasParam('display') && ($this->getParam('display') === 'render')) {
            $this->_helper->layout()->disableLayout();
            $this->view->assign('display', false);
        } else {
            $this->view->assign('display', true);
        }
    }

    /**
     * @Secure("editWorkspace")
     */
    public function editReportsAction()
    {
        $workspaceId = $this->getParam('workspace');
        /** @var Workspace $workspace */
        $workspace = Workspace::load($workspaceId);

        $this->view->assign('workspace', $workspace);
        $criteriaReports = new Criteria();
        $criteriaReports->where($criteriaReports->expr()->eq('cellsGenerateDWCubes', true));
        $reportsGranularities = $workspace->getOrderedGranularities()->matching($criteriaReports)->toArray();
        $this->view->assign('granularities', $reportsGranularities);

        if ($this->hasParam('display') && ($this->getParam('display') === 'render')) {
            $this->_helper->layout()->disableLayout();
            $this->view->assign('display', false);
        } else {
            $this->view->assign('display', true);
        }
    }

    /**
     * @Secure("editWorkspace")
     */
    public function addGranularityReportsAction()
    {
        $workspaceId = $this->getParam('workspace');
        /** @var Workspace $workspace */
        $workspace = Workspace::load($workspaceId);

        $axes = $this->getAxesAddGranularity($workspace);

        try {
            $granularity = $workspace->getGranularityByRef(Granularity::buildRefFromAxes($axes));
            if ($granularity->getCellsGenerateDWCubes()) {
                throw new Core_Exception_User('Orga', 'granularity', 'granularityAlreadyConfigured');
            }
            $action = 'editGranularity';
            $parameters = [
                $granularity,
                ['reports' => true]
            ];
        } catch (Core_Exception_NotFound $e) {
            $action = 'addGranularity';
            $parameters = [
                $workspace,
                $axes,
                ['reports' => true]
            ];
        }

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
            WorkspaceService::class,
            $action,
            $parameters,
            __('Orga', 'backgroundTasks', 'addGranularity', ['LABEL' => implode(', ', $axes)])
        );
        $this->workDispatcher->runAndWait($task, $this->waitDelay, $success, $timeout, $error);
    }

    /**
     * @Secure("editWorkspaceAndCells")
     */
    public function rebuildAction()
    {
        /** @var User $connectedUser */
        $connectedUser = $this->_helper->auth();

        $workspaceId = $this->getParam('workspace');
        /** @var Workspace $workspace */
        $workspace = Workspace::load($workspaceId);

        $this->view->assign('workspace', $workspace);

        $granularities = [];

        $usercanEditWorkspace = $this->acl->isAllowed($connectedUser, Actions::EDIT, $workspace);
        $this->view->assign('canEditWorkspace', $usercanEditWorkspace);
        if ($usercanEditWorkspace) {
            $globalCell = $workspace->getGranularityByRef('global')->getCellByMembers([]);
            $granularitesResults = [$globalCell->getGranularity()];
            foreach ($globalCell->getGranularity()->getNarrowerGranularities() as $narrowerGranularity) {
                $granularitesResults[] = $narrowerGranularity;
            }
            $this->view->assign('granularitiesResults', $granularitesResults);

            $topCells = [$globalCell];
        } else {
            /** @var Cell[] $topCells */
            $topCells = $this->orgaUserAccessManager->getTopCellsWithAccessForWorkspace(
                $connectedUser,
                $workspace,
                [CellAdminRole::class]
            )['cells'];

        }

        foreach ($topCells as $topCell) {
            $granularity = $topCell->getGranularity();
            if ($granularity->getCellsGenerateDWCubes()) {
                $granularities[$granularity->getId()] = $granularity;
            }
            foreach ($granularity->getNarrowerGranularities() as $narrowerGranularity) {
                if ($narrowerGranularity->getCellsGenerateDWCubes()) {
                    $granularities[$narrowerGranularity->getId()] = $narrowerGranularity;
                }
            }
        }

        $granularitiesData = [];
        foreach ($granularities as $granularityId => $granularity) {
            $granularitiesData[$granularityId] = $granularity;
            foreach ($granularity->getBroaderGranularities() as $broaderGranularity) {
                $granularitiesData[$broaderGranularity->getId()] = $broaderGranularity;
            }
        }
        @uasort(
            $granularitiesData,
            function (Granularity $a, Granularity $b) {
                return $a->getPosition() - $b->getPosition();
            }
        );
        if ($usercanEditWorkspace) {
            array_unshift($granularities, $workspace);
        }
        $this->view->assign('granularitiesData', $granularities);

        if ($this->hasParam('display') && ($this->getParam('display') === 'render')) {
            $this->_helper->layout()->disableLayout();
            $this->view->assign('display', false);
        } else {
            $this->view->assign('display', true);
        }
    }

    /**
     * @Secure("editWorkspaceAndCells")
     */
    public function rebuildListCellAction()
    {
        /** @var User $connectedUser */
        $connectedUser = $this->_helper->auth();

        $workspaceId = $this->getParam('workspace');
        /** @var Workspace $workspace */
        $workspace = Workspace::load($workspaceId);

        $granularityRef = $this->getParam('granularity');
        /** @var Granularity $granularity */
        $granularity = $workspace->getGranularityByRef($granularityRef);

        $searches = array_map('strtolower', explode(' ', $this->getParam('q')));
        $querySearch = new Core_Model_Query();
        $querySearch->filter->addCondition('granularity', $granularity);
        $querySearch->aclFilter->enable($connectedUser, Actions::VIEW);

        $cells = [];
        foreach (Cell::loadList($querySearch) as $cell) {
            $cellLabel = $this->translator->get($cell->getLabel());
            foreach ($searches as $search) {
                if (!empty($search) && (strpos(strtolower($cellLabel), $search) === false)) {
                    continue 2;
                }
            }
            $cells[] = ['id' => $cell->getId(), 'text' => $cellLabel];
        }

        $this->sendJsonResponse($cells);
    }

    /**
     * @Secure("editWorkspaceAndCells")
     */
    public function rebuildDataAction()
    {
        $workspaceId = $this->getParam('workspace');
        /** @var Workspace $workspace */
        $workspace = Workspace::load($workspaceId);

        $granularityRef = $this->getParam('granularity');
        if (empty($granularityRef)) {
            // Appel au service qui lancera les tâches de fond.
            $this->etlService->resetWorkspaceDWCubes($workspace);
        } else {
            $cellId = $this->getParam('cell');
            if (empty($cellId) && ($granularityRef !== 'global')) {
                throw new Core_Exception_User('DW', 'rebuild', 'noCubeSelected');
            }
            if ($granularityRef === 'global') {
                /** @var Granularity $granularity */
                $granularity = $workspace->getGranularityByRef($granularityRef);
                $cell = $granularity->getCellByMembers([]);
            } else {
                $cell = Cell::load($cellId);
            }
            // Appel au service qui lancera les tâches de fond.
            $this->etlService->resetCellAndChildrenDWCubes($cell);
        }

        $this->sendJsonResponse(['message' => __('UI', 'message', 'operationInProgress')]);
    }

    /**
     * @Secure("editWorkspaceAndCells")
     */
    public function rebuildResultsAction()
    {
        $workspaceId = $this->getParam('workspace');
        /** @var Workspace $workspace */
        $workspace = Workspace::load($workspaceId);

        $granularityRef = $this->getParam('granularity');
        $cellId = $this->getParam('cell');
        if (empty($cellId) && ($granularityRef !== 'global')) {
            throw new Core_Exception_User('DW', 'rebuild', 'noCubeSelected');
        }

        if ($granularityRef === 'global') {
            /** @var Granularity $granularity */
            $granularity = $workspace->getGranularityByRef($granularityRef);
            $cell = $granularity->getCellByMembers([]);
        } else {
            $cell = Cell::load($cellId);
        }

        $this->etlService->resetCellAndChildrenCalculationsAndDWCubes($cell);

        $this->sendJsonResponse(['message' => __('UI', 'message', 'operationInProgress')]);
    }

    /**
     * @Secure("editWorkspace")
     */
    public function translateAction()
    {
        $workspaceId = $this->getParam('workspace');

        $this->view->assign('workspaceId', $workspaceId);

        if ($this->hasParam('display') && ($this->getParam('display') === 'render')) {
            $this->_helper->layout()->disableLayout();
            $this->view->assign('display', false);
        } else {
            $this->view->assign('display', true);
        }
    }

}
