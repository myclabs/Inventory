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
                    'datagrid_cell',
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
     *
     * @Secure("viewCell")
     */
    public function detailsAction()
    {
        $this->view->headLink()->appendStylesheet('css/orga/navigation.css');
        $this->view->idCell = $this->getParam('idCell');
        $cell = Orga_Model_Cell::load(array('id' => $this->getParam('idCell')));
        $this->view->cell = $cell;
        $this->view->activatedTab = $this->getParam('tab');

        if ($this->hasParam('viewConfiguration')) {
            $viewConfiguration = $this->getParam('viewConfiguration');
            if (!($viewConfiguration instanceof Orga_ViewConfiguration)) {
                throw new Core_Exception_InvalidArgument('The view configuration must be an Orga_ViewConfiguration.');
            }
        } else {
            $viewConfiguration = new Orga_ViewConfiguration();
            $viewConfiguration->setOutputURL('orga/cell/details?tab='.$this->view->activatedTab.'&');
            $viewConfiguration->setPageTitle($cell->getLabelExtended());
            $viewConfiguration->addBaseTabs();
        }
        $this->view->configuration = $viewConfiguration;

        UI_Datagrid::addHeader();
        if ($cell->getGranularity()->getRef() === 'global') {
            UI_Tree::addHeader();
        }
    }
    /**
     * Affiche le détail d'une cellule.
     * @Secure("viewCell")
     */
    public function detailsAction()
    {
        $idCell = $this->getParam('idCell');
        /** @var Orga_Model_Cell $orgaCell */
        $orgaCell = Orga_Model_Cell::load(array('id' => $idCell));
        $granularity = $orgaCell->getGranularity();
        $cell = Orga_Model_Cell::loadByOrgaCell($orgaCell);
        $granularity = Orga_Model_Granularity::loadByOrgaGranularity($granularity);
        $project = Orga_Model_Project::loadByOrgaProject($granularity->getProject());
        $idProject = $project->getKey()['id'];
        $connectedUser = $this->_helper->auth();
        $aclService = User_Service_ACL::getInstance();

        if ($this->hasParam('tab')) {
            $tab = $this->getParam('tab');
        } else {
            $tab = 'inputs';
        }


        $viewConfiguration = new Orga_ViewConfiguration();
        $viewConfiguration->setOutputURL('orga/cell/details?');
        $viewConfiguration->setPageTitle(
            $orgaCell->getLabelExtended().' <small>'.$cell->getProject()->getLabel().'</small>'
        );
        foreach ($orgaCell->getParentCells() as $parentOrgaCell) {
            $viewConfiguration->setParentCellIsALink(
                $parentOrgaCell,
                $aclService->isAllowed(
                    $connectedUser,
                    User_Model_Action_Default::VIEW(),
                    Orga_Model_Cell::loadByOrgaCell($parentOrgaCell)
                )
            );
        }


        $isUserAllowedToEditCell = $aclService->isAllowed(
            $connectedUser,
            User_Model_Action_Default::EDIT(),
            $cell
        );
        if (($granularity->getCellsWithOrgaTab() === true) && ($isUserAllowedToEditCell === true)) {
            $viewConfiguration->addBaseTabs();
        }


        $isUserAllowedToConfigureProject = $aclService->isAllowed(
            $connectedUser,
            User_Model_Action_Default::EDIT(),
            $project
        );
        if (($granularity->getRef() === 'global') && ($isUserAllowedToConfigureProject)) {
            $tabConfiguration = new UI_Tab('configuration');
            if ($tab === 'configuration') {
                $tabConfiguration->active = true;
            }
            $tabConfiguration->label = __('UI', 'name', 'configuration');
            $tabConfiguration->dataSource = 'orga/tab_celldetails/configuration/idProject/'.$idProject
                .'/idCell/'.$idCell;
            $tabConfiguration->useCache = false;
            $viewConfiguration->addTab($tabConfiguration);
        }


        $isUserAllowedToSeeACLTab = $aclService->isAllowed(
            $connectedUser,
            User_Model_Action_Default::ALLOW(),
            $cell
        );
        if (($isUserAllowedToSeeACLTab === true) && ($granularity->getCellsWithACL() === false)) {
            foreach ($granularity->getNarrowerGranularities() as $orgaNarrowerGranularity) {
                $narrowerGranularity = Orga_Model_Granularity::loadByOrgaGranularity(
                    $orgaNarrowerGranularity
                );
                if ($narrowerGranularity->getCellsWithACL()) {
                    $isUserAllowedToSeeACLTab = ($isUserAllowedToSeeACLTab && true);
                    break;
                }
            }
        }
        if ($isUserAllowedToSeeACLTab) {
            $tabACLs = new UI_Tab('acls');
            if ($tab === 'acls') {
                $tabACLs->active = true;
            }
            $tabACLs->label = __('User', 'name', 'roles');
            $tabACLs->dataSource = 'orga/tab_celldetails/acls/idCell/'.$idCell;
            $tabACLs->useCache = !$isUserAllowedToConfigureProject;
            $viewConfiguration->addTab($tabACLs);
        }


        if (($isUserAllowedToEditCell) && ($granularity->getCellsWithAFConfigTab() === true)) {
            $tabAFConfig = new UI_Tab('aFConfig');
            if ($tab === 'aFConfig') {
                $tabAFConfig->active = true;
            }
            $tabAFConfig->label = __('UI', 'name', 'forms');
            $tabAFConfig->dataSource = 'orga/tab_celldetails/afgranularitiesconfigs/idCell/'.$idCell;
            $tabAFConfig->useCache = !$isUserAllowedToConfigureProject;
            $viewConfiguration->addTab($tabAFConfig);
        }

        $tabInventories = new UI_Tab('inventories');
        try {
            $granularity = $cell->getProject()->getGranularityForInventoryStatus();
        } catch (Core_Exception_UndefinedAttribute $e) {
            $granularity = null;
        }
        if ($granularity === null) {
            $tabInventories->disabled = true;
        } else if ($tab === 'inventories') {
            $tabInventories->active = true;
        }
        $tabInventories->label = __('Orga', 'name', 'inventories');
        $tabInventories->dataSource = 'orga/tab_celldetails/inventories/idCell/'.$idCell;
        $viewConfiguration->addTab($tabInventories);

        $tabInputs = new UI_Tab('inputs');
        if ($tab === 'inputs') {
            $tabInputs->active = true;
        }
        $tabInputs->label = __('UI', 'name', 'inputs');
        $tabInputs->dataSource = 'orga/tab_celldetails/afgranularitiesinputs/idCell/'.$idCell;
        $tabInputs->useCache = !$isUserAllowedToConfigureProject;
        $viewConfiguration->addTab($tabInputs);

        if ($granularity->getCellsGenerateDWCubes() === true) {
            $tabAnalyses = new UI_Tab('reports');
            if ($tab === 'reports') {
                $tabAnalyses->active = true;
            }
            $tabAnalyses->label = __('DW', 'name', 'analyses');
            $tabAnalyses->dataSource = 'orga/tab_celldetails/report/idCell/'.$idCell;
            $tabAnalyses->useCache = true;
            $viewConfiguration->addTab($tabAnalyses);
        }

        if ($granularity->getCellsWithSocialGenericActions() === true) {
            $tabGenericActions = new UI_Tab('genericActions');
            if ($tab === 'genericActions') {
                $tabGenericActions->active = true;
            }
            $tabGenericActions->label = __('Social', 'name', 'actionTemplates');
            $tabGenericActions->dataSource = 'orga/tab_celldetails/genericactions?idCell='.$idCell;
            $viewConfiguration->addTab($tabGenericActions);
        }

        if ($granularity->getCellsWithSocialContextActions() === true) {
            $tabContextActions = new UI_Tab('contextActions');
            if ($tab === 'contextActions') {
                $tabContextActions->active = true;
            }
            $tabContextActions->label = __('Social', 'name', 'actions');
            $tabContextActions->dataSource = 'orga/tab_celldetails/contextactions?idCell='.$idCell;
            $viewConfiguration->addTab($tabContextActions);
        }

        $isUserAllowedToSeeDocTab = $aclService->isAllowed(
            $connectedUser,
            Orga_Action_Cell::INPUT(),
            User_Model_Resource_Entity::loadByEntity($cell)
        );
        if (($isUserAllowedToSeeDocTab)
            && (($granularity->getCellsWithSocialContextActions() === true)
                || ($granularity->getCellsWithSocialGenericActions() === true)
                || ($granularity->getCellsWithInputDocs() === true)
            )
        ) {
            $tabDocuments = new UI_Tab('documents');
            if ($tab === 'documents') {
                $tabDocuments->active = true;
            }
            $tabDocuments->label = __('Doc', 'name', 'documents');
            $tabDocuments->dataSource = 'orga/tab_celldetails/documents?idCell='.$idCell;
            $viewConfiguration->addTab($tabDocuments);
        }

        if ($isUserAllowedToConfigureProject) {
            $tabAdministration = new UI_Tab('administration');
            if ($tab === 'administration') {
                $tabAdministration->active = true;
            }
            $tabAdministration->label = __('DW', 'rebuild', 'dataRebuildTab');
            $tabAdministration->dataSource = 'orga/tab_celldetails/administration?idCell='.$idCell;
            $viewConfiguration->addTab($tabAdministration);
        }

        $this->forward('details', 'cell', 'orga', array(
                'idCell' => $idCell,
                'tab' => $tab,
                'viewConfiguration' => $viewConfiguration
            ));
    }

    /**
     * Action redirigeant vers AF.
     * @Secure("viewCell")
     */
    public function inputAction()
    {
        $idCell = $this->getParam('idCell');
        $orgaCell = Orga_Model_Cell::load($idCell);
        $cell = Orga_Model_Cell::loadByOrgaCell($orgaCell);
        $aFGranularities = Orga_Model_AFGranularities::loadByAFInputOrgaGranularity($orgaCell->getGranularity());
        $cellsGroupDataProvider = $aFGranularities->getCellsGroupDataProviderForContainerCell(
            Orga_Model_Cell::loadByOrgaCell(
                $orgaCell->getParentCellForGranularity($aFGranularities->getAFConfigOrgaGranularity())
            )
        );

        $isUserAllowedToInputCell = User_Service_ACL::getInstance()->isAllowed(
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
        $viewConfiguration->setPageTitle(__('UI', 'name', 'input').' <small>'.$orgaCell->getLabel().'</small>');
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
        $cell = Orga_Model_Cell::loadByOrgaCell(
            Orga_Model_Cell::load($this->getParam('idCell'))
        );
        $inputSet = $this->getParam('inputSet');

        $cell->setAFInputSetPrimary($inputSet);

        if ($inputSet->isInputComplete()) {
            Orga_Service_ETLData::getInstance()->clearDWResultsFromCell($cell);
            Orga_Service_ETLData::getInstance()->populateDWResultsFromCell($cell);
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

        $cell = Orga_Model_Cell::loadByOrgaCell(
            Orga_Model_Cell::load(array('id' => $this->getParam('idCell')))
        );

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

        $cell = Orga_Model_Cell::loadByOrgaCell(
            Orga_Model_Cell::load(array('id' => $this->getParam('idCell')))
        );

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
        $orgaCell = Orga_Model_Cell::load($idCell);
        $cell = Orga_Model_Cell::loadByOrgaCell($orgaCell);
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
        $orgaCell = Orga_Model_Cell::load($idCell);
        $cell = Orga_Model_Cell::loadByOrgaCell($orgaCell);
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
        $orgaCell = Orga_Model_Cell::load($idCell);
        $cell = Orga_Model_Cell::loadByOrgaCell($orgaCell);

        if (!($this->hasParam('display') && ($this->getParam('display') == true))) {
            $exportUrl = 'orga/cell/specificexport/'.
                'idCell/'.$idCell.'/export/'.$this->getParam('export').'/display/true';
        } else {
            $exportUrl = null;
        }

        $specificReportsDirectoryPath = PACKAGE_PATH.'/data/specificExports/'.
            $cell->getProject()->getKey()['id'].'/'.
            str_replace('|', '_', $orgaCell->getGranularity()->getRef()).'/';
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