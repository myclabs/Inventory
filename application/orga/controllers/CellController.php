<?php
/**
 * Classe Orga_CellController
 * @author valentin.claras
 * @author sidoine.tardieu
 * @package    Orga
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Classe controleur de cell.
 * @package    Orga
 * @subpackage Controller
 */
class Orga_CellController extends Core_Controller
{
    /**
     * Affiche le détail d'une cellule.
     * @Secure("viewCell")
     */
    public function detailsAction()
    {
        $this->view->headLink()->appendStylesheet('css/orga/navigation.css');
        UI_Datagrid::addHeader();
        UI_Tree::addHeader();

        $idCell = $this->getParam('idCell');
        $this->view->idCell = $idCell;
        $cell = Orga_Model_Cell::load(array('id' => $this->getParam('idCell')));
        $granularity = $cell->getGranularity();
        $project = $granularity->getProject();
        $idProject = $project->getKey()['id'];

        $this->view->cell = $cell;

        $connectedUser = $this->_helper->auth();
        /** @var User_Service_ACL $aclService */
        $aclService = $this->get('User_Service_ACL');

        if ($this->hasParam('tab')) {
            $tab = $this->getParam('tab');
        } else {
            $tab = 'inputs';
        }


        $this->view->tabView = new UI_Tab_View('container');
        $viewConfiguration = new Orga_ViewConfiguration();
        $this->view->outputURL = 'orga/cell/details?';
        $this->view->pageTitle = $cell->getLabelExtended().' <small>'.$project->getLabel().'</small>';
        $this->view->isParentCellReachable = array();
        foreach ($cell->getParentCells() as $parentCell) {
            $isUserAllowedToViewParentCell = $aclService->isAllowed(
                $connectedUser,
                User_Model_Action_Default::VIEW(),
                $parentCell
            );
            if (!$isUserAllowedToViewParentCell) {
                $this->view->isParentCellReachable[$parentCell->getMembersHashKey()] = false;
            }
        }


        // TAB PROJECT.
        $isUserAllowedToEditProject = $aclService->isAllowed(
            $connectedUser,
            User_Model_Action_Default::EDIT(),
            $project
        );
        if (($granularity->getRef() === 'global') && ($isUserAllowedToEditProject)) {
            $projectTab = new UI_Tab('project');
            if ($tab === 'project') {
                $projectTab->active = true;
            }
            $projectTab->label = __('UI', 'name', 'project');
            $projectTab->dataSource = 'orga/tab_celldetails/project/idProject/'.$idProject.'/idCell/'.$idCell;
            $projectTab->useCache = false;
            $this->view->tabView->addTab($projectTab);
        }


        // TAB STRUCTURE.
        $isUserAllowedToEditCell = $aclService->isAllowed(
            $connectedUser,
            User_Model_Action_Default::EDIT(),
            $cell
        );
        if (($granularity->getCellsWithOrgaTab() === true) && ($isUserAllowedToEditCell === true)) {
            $structureTab = new UI_Tab('structure');
            $structureTab->label = __('Orga', 'name', 'structure');
            $structureTab->cacheData  = true;
            $structureTab->dataSource = 'orga/cell/organisation?'.
                'idCell='.$idCell.'&'.
                'tab='.$tab.'&'.
                'outputUrl='.urlencode($this->view->outputURL).'&'.
                'display=render';
            $structureSubTabs = array('axes', 'granularities', 'members', 'childCells', 'relevant', 'consistency');
            if (in_array($tab, $structureSubTabs)) {
                $structureTab->active = true;
            }
            $this->view->tabView->addTab($structureTab);
        }


        // TAB ACL
        $isUserAllowedToAllowAuthorizations = $aclService->isAllowed(
            $connectedUser,
            User_Model_Action_Default::ALLOW(),
            $cell
        );
        if (($isUserAllowedToAllowAuthorizations === true) && ($granularity->getCellsWithACL() === false)) {
            foreach ($granularity->getNarrowerGranularities() as $narrowerGranularity) {
                if ($narrowerGranularity->getCellsWithACL()) {
                    $isUserAllowedToAllowAuthorizations = ($isUserAllowedToAllowAuthorizations && true);
                    break;
                }
            }
        }
        if ($isUserAllowedToAllowAuthorizations) {
            $aclsTab = new UI_Tab('acls');
            if ($tab === 'acls') {
                $aclsTab->active = true;
            }
            $aclsTab->label = __('User', 'name', 'roles');
            $aclsTab->dataSource = 'orga/tab_celldetails/acls/idCell/'.$idCell;
            $aclsTab->useCache = !$isUserAllowedToEditProject;
            $viewConfiguration->addTab($aclsTab);
        }


        // TAB AF CONFIGURATION
        if (($isUserAllowedToEditCell) && ($granularity->getCellsWithAFConfigTab() === true)) {
            $aFConfigurationTab = new UI_Tab('aFConfiguration');
            if ($tab === 'aFConfig') {
                $aFConfigurationTab->active = true;
            }
            $aFConfigurationTab->label = __('UI', 'name', 'forms');
            $aFConfigurationTab->dataSource = 'orga/tab_celldetails/afsconfig/idCell/'.$idCell;
            $aFConfigurationTab->useCache = !$isUserAllowedToEditProject;
            $viewConfiguration->addTab($aFConfigurationTab);
        }


        // TAB INVENTORIES
        $inventoriesTab = new UI_Tab('inventories');
        try {
            $granularityForInventoryStatus = $project->getGranularityForInventoryStatus();
        } catch (Core_Exception_UndefinedAttribute $e) {
            $granularityForInventoryStatus = null;
        }
        if ($granularityForInventoryStatus=== null) {
            $inventoriesTab->disabled = true;
        } else if ($tab === 'inventories') {
            $inventoriesTab->active = true;
        }
        $inventoriesTab->label = __('Orga', 'name', 'inventories');
        $inventoriesTab->dataSource = 'orga/tab_celldetails/inventories/idCell/'.$idCell;
        $viewConfiguration->addTab($inventoriesTab);


        // TAB INPUTS
        $inputsTab = new UI_Tab('inputs');
        if ($tab === 'inputs') {
            $inputsTab->active = true;
        }
        $inputsTab->label = __('UI', 'name', 'inputs');
        $inputsTab->dataSource = 'orga/tab_celldetails/afsinputs/idCell/'.$idCell;
        $inputsTab->useCache = !$isUserAllowedToEditProject;
        $viewConfiguration->addTab($inputsTab);


        // TAB ANALYSIS
        if ($granularity->getCellsGenerateDWCubes() === true) {
            $analysisTab = new UI_Tab('analysis');
            if ($tab === 'analysis') {
                $analysisTab->active = true;
            }
            $analysisTab->label = __('DW', 'name', 'analysis');
            $analysisTab->dataSource = 'orga/tab_celldetails/analysis/idCell/'.$idCell;
            $analysisTab->useCache = true;
            $viewConfiguration->addTab($analysisTab);
        }


        // TAB GENERIC ACTIONS
        if ($granularity->getCellsWithSocialGenericActions() === true) {
            $genericActionsTab = new UI_Tab('genericActions');
            if ($tab === 'genericActions') {
                $genericActionsTab->active = true;
            }
            $genericActionsTab->label = __('Social', 'name', 'actionTemplates');
            $genericActionsTab->dataSource = 'orga/tab_celldetails/genericactions?idCell='.$idCell;
            $viewConfiguration->addTab($genericActionsTab);
        }


        // TAB CONTEXT ACTIONS
        if ($granularity->getCellsWithSocialContextActions() === true) {
            $contextActionsTab = new UI_Tab('contextActions');
            if ($tab === 'contextActions') {
                $contextActionsTab->active = true;
            }
            $contextActionsTab->label = __('Social', 'name', 'actions');
            $contextActionsTab->dataSource = 'orga/tab_celldetails/contextactions?idCell='.$idCell;
            $viewConfiguration->addTab($contextActionsTab);
        }


        // TAB DOCUMENTS
        $isUserAllowedToInputCell = $aclService->isAllowed(
            $connectedUser,
            Orga_Action_Cell::INPUT(),
            User_Model_Resource_Entity::loadByEntity($cell)
        );
        if (($isUserAllowedToInputCell)
            && (($granularity->getCellsWithSocialContextActions() === true)
                || ($granularity->getCellsWithSocialGenericActions() === true)
                || ($granularity->getCellsWithInputDocs() === true)
            )
        ) {
            $documentsTab = new UI_Tab('documents');
            if ($tab === 'documents') {
                $documentsTab->active = true;
            }
            $documentsTab->label = __('Doc', 'name', 'documents');
            $documentsTab->dataSource = 'orga/tab_celldetails/documents?idCell='.$idCell;
            $viewConfiguration->addTab($documentsTab);
        }


        // TAB ADMINISTRATION
        if ($isUserAllowedToEditProject) {
            $administrationTab = new UI_Tab('administration');
            if ($tab === 'administration') {
                $administrationTab->active = true;
            }
            $administrationTab->label = __('DW', 'rebuild', 'dataRebuildTab');
            $administrationTab->dataSource = 'orga/tab_celldetails/administration?idCell='.$idCell;
            $viewConfiguration->addTab($administrationTab);
        }
    }

    /**
     * Action pour l'organisation général de la cellule.
     * @Secure("viewProject")
     */
    public function organisationAction()
    {
        $this->view->idCell = $this->getParam('idCell');
        $cell = Orga_Model_Cell::load(array('id' => $this->getParam('idCell')));
        $this->view->isGlobal = $cell->getGranularity()->getRef() === 'global';
        $this->view->activatedTab = $this->getParam('tab');
        if (empty($this->view->activatedTab)) {
            $this->view->activatedTab = 'childCells';
        }
        if ($this->hasParam('outputUrl')) {
            $outputUrl = $this->getParam('outputUrl');
        } else {
            $outputUrl = urlencode('orga/cell/organisation?tab=childCells&');
        }
        $this->view->outputUrl = $outputUrl;
        $this->view->hasChildCells = count($cell->getGranularity()->getNarrowerGranularities()) > 0;

        if ($this->hasParam('display') && ($this->getParam('display') === 'render')) {
            $this->_helper->layout()->disableLayout();
            $this->view->display = false;
        } else {
            $this->view->display = true;
            UI_Datagrid::addHeader();
            if ($this->view->isGlobal) {
                UI_Tree::addHeader();
            }
        }
    }

    /**
     * Action pour les cellules enfants.
     * @Secure("viewCell")
     */
    public function childAction()
    {
        $this->view->idCell = $this->getParam('idCell');
        $cell = Orga_Model_Cell::load(array('id' => $this->getParam('idCell')));
        $this->view->granularities = $cell->getGranularity()->getNarrowerGranularities();

        if (($this->hasParam('minimize')) && ($this->getParam('minimize') === false)) {
            $this->view->minimize = false;
        } else {
            $this->view->minimize = true;
        }

        if ($this->hasParam('datagridConfiguration')) {
            $datagridConfiguration = $this->getParam('datagridConfiguration');
            if (is_array($datagridConfiguration)) {
                $this->view->listDatagrids = $datagridConfiguration;
            } else {
                $this->view->listDatagrids = array($datagridConfiguration);
            }
        } else {
            $this->view->listDatagrids = array();
            foreach ($cell->getGranularity()->getNarrowerGranularities() as $narrowerGranularity) {
                $datagridConfiguration = new Orga_DatagridConfiguration(
                    'child_c'.$cell->getKey()['id'].'_g'.$narrowerGranularity->getKey()['id'],
                    'datagrid_childcells',
                    'orga',
                    $cell,
                    $narrowerGranularity
                );
                $datagridConfiguration->datagrid->addParam('idCell', $cell->getKey()['id']);
                if ($this->hasParam('outputUrl')) {
                    $outputUrl = urldecode($this->getParam('outputUrl'));
                    if (preg_match('#[^?&]$#', $outputUrl)) {
                        if (strpos($outputUrl, '?')) {
                            $outputUrl .= '&';
                        }
                    } else {
                        $outputUrl .= '?';
                    }
                } else {
                    $outputUrl = 'orga/cell/organisation?tab=childCells&';
                }
                $datagridConfiguration->datagrid->addParam('outputUrl', urlencode($outputUrl));
                if ($narrowerGranularity->isNavigable()) {
                    $columnLink = new UI_Datagrid_Col_Link('link');
                    $columnLink->label = __('UI', 'name', 'browsing');
                    $datagridConfiguration->datagrid->addCol($columnLink);
                }
                $this->view->listDatagrids[$narrowerGranularity->getLabel()] = $datagridConfiguration;
            }
        }

        if ($this->hasParam('display') && ($this->getParam('display') === 'render')) {
            $this->view->display = false;
        } else {
            $this->view->display = true;
        }
    }

    /**
     * Action pour la pertinence des cellules enfants.
     * @Secure("viewProject")
     */
    public function relevantAction()
    {
        $cell = Orga_Model_Cell::load(array('id' => $this->getParam('idCell')));
        $this->view->granularities = $cell->getGranularity()->getNarrowerGranularities();

        $listDatagridConfiguration = array();
        foreach ($cell->getGranularity()->getNarrowerGranularities() as $narrowerGranularity) {
            $datagridConfiguration = new Orga_DatagridConfiguration(
                'relevant_c'.$cell->getKey()['id'].'_g'.$narrowerGranularity->getKey()['id'],
                'datagrid_relevant',
                'orga',
                $cell,
                $narrowerGranularity
            );
            $datagridConfiguration->datagrid->addParam('idCell', $cell->getKey()['id']);
            $columnRelevant = new UI_Datagrid_Col_Bool('relevant');
            $columnRelevant->label = __('Orga', 'name', 'relevance');
            $columnRelevant->editable = true;
            $columnRelevant->textTrue = __('Orga', 'property', 'relevantFem');
            $columnRelevant->textFalse = __('Orga', 'property', 'irrelevantFem');
            $columnRelevant->valueTrue = '<i class="icon-ok"></i> '.__('Orga', 'property', 'relevantFem');
            $columnRelevant->valueFalse = '<i class="icon-remove"></i> '.__('Orga', 'property', 'irrelevantFem');
            $datagridConfiguration->datagrid->addCol($columnRelevant);

            $columnAllParentsRelevant = new UI_Datagrid_Col_Bool('allParentsRelevant');
            $columnAllParentsRelevant->label = __('Orga', 'relevance', 'parentCellsRelevanceHeader');
            $columnAllParentsRelevant->editable = false;
            $columnAllParentsRelevant->valueTrue = '<i class="icon-ok"></i> '.__('Orga', 'relevance', 'allParentCellsRelevantProperty');
            $columnAllParentsRelevant->valueFalse = '<i class="icon-remove"></i> '.__('Orga', 'relevance', 'notAllParentCellsRelevantProperty');
            $datagridConfiguration->datagrid->addCol($columnAllParentsRelevant);
            $listDatagridConfiguration[$narrowerGranularity->getLabel()] = $datagridConfiguration;
        }

        $this->forward('child', 'cell', 'orga', array('datagridConfiguration' => $listDatagridConfiguration));
    }

    /**
     * Action redirigeant vers AF.
     * @Secure("viewCell")
     */
    public function inputAction()
    {
        $idCell = $this->getParam('idCell');
        $cell = Orga_Model_Cell::load($idCell);
        $aFGranularities = Orga_Model_AFGranularities::loadByAFInputOrgaGranularity($cell->getGranularity());
        $cellsGroupDataProvider = $aFGranularities->getCellsGroupDataProviderForContainerCell(
            $cell->getParentCellForGranularity($aFGranularities->getAFConfigOrgaGranularity())
        );

        /** @var User_Service_ACL $aclService */
        $aclService = $this->get('User_Service_ACL');
        $isUserAllowedToInputCell = $aclService->isAllowed(
            $this->_helper->auth(),
            Orga_Action_Cell::INPUT(),
            $cell
        );

        $viewConfiguration = new AF_ViewConfiguration();
        if ($isUserAllowedToInputCell) {
            $viewConfiguration->setMode(AF_ViewConfiguration::MODE_WRITE);
        } else {
            $viewConfiguration->setMode(AF_ViewConfiguration::MODE_READ);
        }
        $viewConfiguration->setPageTitle(__('UI', 'name', 'input').' <small>'.$cell->getLabel().'</small>');
        $viewConfiguration->addToActionStack('inputsave', 'cell', 'orga', array('idCell' => $idCell));
        $viewConfiguration->setExitUrl('orga/cell/details?idCell='.$this->getParam('fromIdCell'));
        $viewConfiguration->addUrlParam('idCell', $idCell);
        $viewConfiguration->setDisplayConfigurationLink(false);
        $viewConfiguration->addBaseTabs();
        try {
            $viewConfiguration->setIdInputSet($cell->getAFInputSetPrimary()->getKey()['id']);
        } catch (Core_Exception_UndefinedAttribute $e) {
            // Pas d'inputSetPrimary : nouvelle saisie.
        }

        $tabComments = new UI_Tab('inputComments');
        $tabComments->label = __('Social', 'name', 'comments');
        $tabComments->dataSource = 'orga/tab_input/comments/idCell/'.$idCell;
        $tabComments->cacheData = true;
        $viewConfiguration->addTab($tabComments);

        $tabDocs = new UI_Tab('inputDocs');
        $tabDocs->label = __('Doc', 'name', 'documents');
        $tabDocs->dataSource = 'orga/tab_input/docs/idCell/'.$idCell;
        $tabDocs->cacheData = true;
        $viewConfiguration->addTab($tabDocs);

        $this->forward('display', 'af', 'af', array(
                'id' => $cellsGroupDataProvider->getAF()->getKey()['id'],
                'viewConfiguration' => $viewConfiguration
            ));
    }

    /**
     * Fonction de sauvegarde de l'AF.
     * @Secure("inputCell")
     */
    public function inputsaveAction()
    {
        /** @var Orga_Service_ETLData $etlDataService */
        $etlDataService = $this->get('Orga_Service_ETLData');

        $cell = Orga_Model_Cell::load($this->getParam('idCell'));
        $inputSet = $this->getParam('inputSet');

        $cell->setAFInputSetPrimary($inputSet);

        if ($inputSet->isInputComplete()) {
            $etlDataService->clearDWResultsFromCell($cell);
            $etlDataService->populateDWResultsFromCell($cell);
        }

        $this->_helper->viewRenderer->setNoRender(true);
    }

    /**
     * Réinitialise le DW du Cell donné et ceux des cellules enfants.
     * @Secure("editCell")
     */
    public function resetdwsAction()
    {
        /** @var Core_Work_Dispatcher $workDispatcher */
        $workDispatcher = Zend_Registry::get('workDispatcher');

        $cell = Orga_Model_Cell::load(array('id' => $this->getParam('idCell')));

        try {
            // Lance la tache en arrière plan
            $workDispatcher->runBackground(
                new Core_Work_ServiceCall_Task(
                    'Orga_Service_ETLStructure',
                    'resetCellAndChildrenDWCubes',
                    [$cell]
                )
            );
        } catch (Core_Exception_NotFound $e) {
            throw new Core_Exception_User('DW', 'rebuild', 'analysisDataRebuildFailMessage');
        }
        $this->sendJsonResponse(array('message' => __('UI', 'message', 'operationInProgress')));
    }

    /**
     * Re-calcule l'input du Cell donné et ceux des cellules enfants.
     * @Secure("editCell")
     */
    public function calculateinputsAction()
    {
        /** @var Core_Work_Dispatcher $workDispatcher */
        $workDispatcher = Zend_Registry::get('workDispatcher');

        $cell = Orga_Model_Cell::load(array('id' => $this->getParam('idCell')));

        try {
            // Lance la tache en arrière plan
            $workDispatcher->runBackground(
                new Core_Work_ServiceCall_Task(
                    'Orga_Service_ETLStructure',
                    'resetCellAndChildrenCalculationsAndDWCubes',
                    [$cell]
                )
            );
        } catch (Core_Exception_NotFound $e) {
            throw new Core_Exception_User('DW', 'rebuild', 'outputDataRebuildFailMessage');
        }
        $this->sendJsonResponse(array('message' => __('UI', 'message', 'operationInProgress')));
    }

    /**
     * Action fournissant le détails d'une action générique.
     * @Secure("problemToSolve")
     */
    public function genericactiondetailsAction()
    {
        $idCell = $this->getParam('idCell');
        $this->view->idCell = $idCell;
        $cell = Orga_Model_Cell::load($idCell);
        $this->view->documentLibrary = $cell->getDocLibraryForSocialGenericAction();

        $this->forward('generic-action-details', 'action', 'social', array(
                'title' => __('Social', 'actionTemplate', 'actionTemplateDetails').
                ' <small>'.$cell->getLabel().'</small>',
                'returnUrl' => 'orga/cell/details/idCell/'.$idCell.'/tab/genericActions',
            ));
    }

    /**
     * Action fournissant le détails d'une action générique.
     * @Secure("problemToSolve")
     */
    public function contextactiondetailsAction()
    {
        $idCell = $this->getParam('idCell');
        $this->view->idCell = $idCell;
        $cell = Orga_Model_Cell::load($idCell);
        $this->view->documentLibrary = $cell->getDocLibraryForSocialContextAction();

        $this->forward('context-action-details', 'action', 'social', array(
                'title' => __('Social', 'action', 'actionDetails').
                ' <small>'.$cell->getLabel().'</small>',
                'returnUrl' => 'orga/cell/details/idCell/'.$idCell.'/tab/contextActions',
            ));
    }

    /**
     * Action fournissant un export spécifique.
     * @Secure("viewCell")
     */
    public function specificexportAction()
    {
        $idCell = $this->getParam('idCell');
        $cell = Orga_Model_Cell::load($idCell);

        if (!($this->hasParam('display') && ($this->getParam('display') == true))) {
            $exportUrl = 'orga/cell/specificexport/'.
                'idCell/'.$idCell.'/export/'.$this->getParam('export').'/display/true';
        } else {
            $exportUrl = null;
        }

        $specificReportsDirectoryPath = PACKAGE_PATH.'/data/specificExports/'.
            $cell->getProject()->getKey()['id'].'/'.
            str_replace('|', '_', $cell->getGranularity()->getRef()).'/';
        $specificReports = new DW_Export_Specific_Pdf(
            $specificReportsDirectoryPath.$this->getParam('export').'.xml',
            $cell->getDWCube(),
            $exportUrl
        );

        if ($exportUrl !== null) {
            $this->view->html = $specificReports->html;
        } else {
            Zend_Layout::getMvcInstance()->disableLayout();
            $specificReports->display();
        }
    }

}