<?php
/**
 * @author valentin.claras
 * @author diana.dragusin
 * @package Inventory
 */

use Core\Annotation\Secure;

/**
 * @author valentin.claras
 * @author diana.dragusin
 * @package Inventory
 */
class Inventory_CellController extends Core_Controller_Ajax
{
    /**
     * Affiche le détail d'une cellule.
     * @Secure("viewCell")
     */
    public function detailsAction()
    {
        $idCell = $this->_getParam('idCell');
        /** @var Orga_Model_Cell $orgaCell */
        $orgaCell = Orga_Model_Cell::load(array('id' => $idCell));
        $orgaGranularity = $orgaCell->getGranularity();
        $cellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell($orgaCell);
        $granularityDataProvider = Inventory_Model_GranularityDataProvider::loadByOrgaGranularity($orgaGranularity);
        $project = Inventory_Model_Project::loadByOrgaCube($orgaGranularity->getCube());
        $idProject = $project->getKey()['id'];
        $connectedUser = $this->_helper->auth();
        $aclService = User_Service_ACL::getInstance();

        if ($this->_hasParam('tab')) {
            $tab = $this->_getParam('tab');
        } else {
            $tab = 'inputs';
        }


        $viewConfiguration = new Orga_ViewConfiguration();
        $viewConfiguration->setOutputURL('inventory/cell/details?');
        $viewConfiguration->setPageTitle(
            $orgaCell->getLabelExtended().' <small>'.$cellDataProvider->getProject()->getLabel().'</small>'
        );
        foreach ($orgaCell->getParentCells() as $parentOrgaCell) {
            $viewConfiguration->setParentCellIsALink(
                $parentOrgaCell,
                $aclService->isAllowed(
                    $connectedUser,
                    User_Model_Action_Default::VIEW(),
                    Inventory_Model_CellDataProvider::loadByOrgaCell($parentOrgaCell)
                )
            );
        }


        $isUserAllowedToEditCell = $aclService->isAllowed(
            $connectedUser,
            User_Model_Action_Default::EDIT(),
            $cellDataProvider
        );
        if (($granularityDataProvider->getCellsWithOrgaTab() === true) && ($isUserAllowedToEditCell === true)) {
            $viewConfiguration->addBaseTabs();
        }


        $isUserAllowedToConfigureProject = $aclService->isAllowed(
            $connectedUser,
            User_Model_Action_Default::EDIT(),
            $project
        );
        if (($orgaGranularity->getRef() === 'global') && ($isUserAllowedToConfigureProject)) {
            $tabConfiguration = new UI_Tab('configuration');
            if ($tab === 'configuration') {
                $tabConfiguration->active = true;
            }
            $tabConfiguration->label = __('UI', 'name', 'configuration');
            $tabConfiguration->dataSource = 'inventory/tab_celldetails/configuration/idProject/'.$idProject
                .'/idCell/'.$idCell;
            $tabConfiguration->useCache = false;
            $viewConfiguration->addTab($tabConfiguration);
        }


        $isUserAllowedToSeeACLTab = $aclService->isAllowed(
            $connectedUser,
            User_Model_Action_Default::ALLOW(),
            $cellDataProvider
        );
        if (($isUserAllowedToSeeACLTab === true) && ($granularityDataProvider->getCellsWithACL() === false)) {
            foreach ($orgaGranularity->getNarrowerGranularities() as $orgaNarrowerGranularity) {
                $narrowerGranularityDataProvider = Inventory_Model_GranularityDataProvider::loadByOrgaGranularity(
                    $orgaNarrowerGranularity
                );
                if ($narrowerGranularityDataProvider->getCellsWithACL()) {
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
            $tabACLs->dataSource = 'inventory/tab_celldetails/acls/idCell/'.$idCell;
            $tabACLs->useCache = !$isUserAllowedToConfigureProject;
            $viewConfiguration->addTab($tabACLs);
        }


        if (($isUserAllowedToEditCell) && ($granularityDataProvider->getCellsWithAFConfigTab() === true)) {
            $tabAFConfig = new UI_Tab('aFConfig');
            if ($tab === 'aFConfig') {
                $tabAFConfig->active = true;
            }
            $tabAFConfig->label = __('UI', 'name', 'forms');
            $tabAFConfig->dataSource = 'inventory/tab_celldetails/afgranularitiesconfigs/idCell/'.$idCell;
            $tabAFConfig->useCache = !$isUserAllowedToConfigureProject;
            $viewConfiguration->addTab($tabAFConfig);
        }

        $tabInventories = new UI_Tab('inventories');
        try {
            $inventoryGranularity = $cellDataProvider->getProject()->getOrgaGranularityForInventoryStatus();
        } catch (Core_Exception_UndefinedAttribute $e) {
            $inventoryGranularity = null;
        }
        if ($inventoryGranularity === null) {
            $tabInventories->disabled = true;
        } else if ($tab === 'inventories') {
            $tabInventories->active = true;
        }
        $tabInventories->label = __('Inventory', 'name', 'inventories');
        $tabInventories->dataSource = 'inventory/tab_celldetails/inventories/idCell/'.$idCell;
        $viewConfiguration->addTab($tabInventories);

        $tabInputs = new UI_Tab('inputs');
        if ($tab === 'inputs') {
            $tabInputs->active = true;
        }
        $tabInputs->label = __('UI', 'name', 'inputs');
        $tabInputs->dataSource = 'inventory/tab_celldetails/afgranularitiesinputs/idCell/'.$idCell;
        $tabInputs->useCache = !$isUserAllowedToConfigureProject;
        $viewConfiguration->addTab($tabInputs);

        if ($granularityDataProvider->getCellsGenerateDWCubes() === true) {
            $tabAnalyses = new UI_Tab('reports');
            if ($tab === 'reports') {
                $tabAnalyses->active = true;
            }
            $tabAnalyses->label = __('DW', 'name', 'analyses');
            $tabAnalyses->dataSource = 'inventory/tab_celldetails/report/idCell/'.$idCell;
            $tabAnalyses->useCache = true;
            $viewConfiguration->addTab($tabAnalyses);
        }

        if ($granularityDataProvider->getCellsWithSocialGenericActions() === true) {
            $tabGenericActions = new UI_Tab('genericActions');
            if ($tab === 'genericActions') {
                $tabGenericActions->active = true;
            }
            $tabGenericActions->label = __('Social', 'name', 'actionTemplates');
            $tabGenericActions->dataSource = 'inventory/tab_celldetails/genericactions?idCell='.$idCell;
            $viewConfiguration->addTab($tabGenericActions);
        }

        if ($granularityDataProvider->getCellsWithSocialContextActions() === true) {
            $tabContextActions = new UI_Tab('contextActions');
            if ($tab === 'contextActions') {
                $tabContextActions->active = true;
            }
            $tabContextActions->label = __('Social', 'name', 'actions');
            $tabContextActions->dataSource = 'inventory/tab_celldetails/contextactions?idCell='.$idCell;
            $viewConfiguration->addTab($tabContextActions);
        }

        $isUserAllowedToSeeDocTab = $aclService->isAllowed(
            $connectedUser,
            Inventory_Action_Cell::INPUT(),
            User_Model_Resource_Entity::loadByEntity($cellDataProvider)
        );
        if (($isUserAllowedToSeeDocTab)
            && (($granularityDataProvider->getCellsWithSocialContextActions() === true)
                || ($granularityDataProvider->getCellsWithSocialGenericActions() === true)
                || ($granularityDataProvider->getCellsWithInputDocs() === true)
            )
        ) {
            $tabDocuments = new UI_Tab('documents');
            if ($tab === 'documents') {
                $tabDocuments->active = true;
            }
            $tabDocuments->label = __('Doc', 'name', 'documents');
            $tabDocuments->dataSource = 'inventory/tab_celldetails/documents?idCell='.$idCell;
            $viewConfiguration->addTab($tabDocuments);
        }

        if ($isUserAllowedToConfigureProject) {
            $tabAdministration = new UI_Tab('administration');
            if ($tab === 'administration') {
                $tabAdministration->active = true;
            }
            $tabAdministration->label = __('DW', 'rebuild', 'dataRebuildTab');
            $tabAdministration->dataSource = 'inventory/tab_celldetails/administration?idCell='.$idCell;
            $viewConfiguration->addTab($tabAdministration);
        }

        $this->_forward('details', 'cell', 'orga', array(
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
        $idCell = $this->_getParam('idCell');
        $orgaCell = Orga_Model_Cell::load($idCell);
        $cellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell($orgaCell);
        $aFGranularities = Inventory_Model_AFGranularities::loadByAFInputOrgaGranularity($orgaCell->getGranularity());
        $cellsGroupDataProvider = $aFGranularities->getCellsGroupDataProviderForContainerCellDataProvider(
            Inventory_Model_CellDataProvider::loadByOrgaCell(
                $orgaCell->getParentCellForGranularity($aFGranularities->getAFConfigOrgaGranularity())
            )
        );

        $isUserAllowedToInputCell = User_Service_ACL::getInstance()->isAllowed(
            $this->_helper->auth(),
            Inventory_Action_Cell::INPUT(),
            $cellDataProvider
        );

        $viewConfiguration = new AF_ViewConfiguration();
        if ($isUserAllowedToInputCell) {
            $viewConfiguration->setMode(AF_ViewConfiguration::MODE_WRITE);
        } else {
            $viewConfiguration->setMode(AF_ViewConfiguration::MODE_READ);
        }
        $viewConfiguration->setPageTitle(__('UI', 'name', 'input').' <small>'.$orgaCell->getLabel().'</small>');
        $viewConfiguration->addToActionStack('inputsave', 'cell', 'inventory', array('idCell' => $idCell));
        $viewConfiguration->setExitUrl('inventory/cell/details?idCell='.$this->_getParam('fromIdCell'));
        $viewConfiguration->addUrlParam('idCell', $idCell);
        $viewConfiguration->setDisplayConfigurationLink(false);
        $viewConfiguration->addBaseTabs();
        try {
            $viewConfiguration->setIdInputSet($cellDataProvider->getAFInputSetPrimary()->getKey()['id']);
        } catch (Core_Exception_UndefinedAttribute $e) {
            // Pas d'inputSetPrimary : nouvelle saisie.
        }

        $tabComments = new UI_Tab('inputComments');
        $tabComments->label = __('Social', 'name', 'comments');
        $tabComments->dataSource = 'inventory/tab_input/comments/idCell/'.$idCell;
        $tabComments->cacheData = true;
        $viewConfiguration->addTab($tabComments);

        $tabDocs = new UI_Tab('inputDocs');
        $tabDocs->label = __('Doc', 'name', 'documents');
        $tabDocs->dataSource = 'inventory/tab_input/docs/idCell/'.$idCell;
        $tabDocs->cacheData = true;
        $viewConfiguration->addTab($tabDocs);

        $this->_forward('display', 'af', 'af', array(
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
        $cellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell(
            Orga_Model_Cell::load($this->_getParam('idCell'))
        );
        $inputSet = $this->_getParam('inputSet');

        $cellDataProvider->setAFInputSetPrimary($inputSet);

        if ($inputSet->isInputComplete()) {
            Inventory_Service_ETLData::getInstance()->clearDWResultsFromCellDataProvider($cellDataProvider);
            Inventory_Service_ETLData::getInstance()->populateDWResultsFromCellDataProvider($cellDataProvider);
        }

        $this->_helper->viewRenderer->setNoRender(true);
    }

    /**
     * Réinitialise le DW du CellDataProvider donné et ceux des cellules enfants.
     * @Secure("editCell")
     */
    public function resetdwsAction()
    {
        /** @var Core_Work_Dispatcher $workDispatcher */
        $workDispatcher = Zend_Registry::get('workDispatcher');

        $cellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell(
            Orga_Model_Cell::load(array('id' => $this->_getParam('idCell')))
        );

        try {
            // Lance la tache en arrière plan
            $workDispatcher->runBackground(
                new Core_Work_ServiceCall_Task(
                    'Inventory_Service_ETLStructure',
                    'resetCellDataProviderAndChildrenDWCubes',
                    [$cellDataProvider]
                )
            );
        } catch (Core_Exception_NotFound $e) {
            throw new Core_Exception_User('DW', 'rebuild', 'analysisDataRebuildFailMessage');
        }
        $this->sendJsonResponse(array('message' => __('UI', 'message', 'operationInProgress')));
    }

    /**
     * Re-calcule l'input du CellDataProvider donné et ceux des cellules enfants.
     * @Secure("editCell")
     */
    public function calculateinputsAction()
    {
        /** @var Core_Work_Dispatcher $workDispatcher */
        $workDispatcher = Zend_Registry::get('workDispatcher');

        $cellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell(
            Orga_Model_Cell::load(array('id' => $this->_getParam('idCell')))
        );

        try {
            // Lance la tache en arrière plan
            $workDispatcher->runBackground(
                new Core_Work_ServiceCall_Task(
                    'Inventory_Service_ETLStructure',
                    'resetCellDataProviderAndChildrenCalculationsAndDWCubes',
                    [$cellDataProvider]
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
        $idCell = $this->_getParam('idCell');
        $this->view->idCell = $idCell;
        $orgaCell = Orga_Model_Cell::load($idCell);
        $cellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell($orgaCell);
        $this->view->documentLibrary = $cellDataProvider->getDocLibraryForSocialGenericAction();

        $this->_forward('generic-action-details', 'action', 'social', array(
            'title' => __('Social', 'actionTemplate', 'actionTemplateDetails').
                ' <small>'.$cellDataProvider->getOrgaCell()->getLabel().'</small>',
            'returnUrl' => 'inventory/cell/details/idCell/'.$idCell.'/tab/genericActions',
        ));
    }

    /**
     * Action fournissant le détails d'une action générique.
     * @Secure("problemToSolve")
     */
    public function contextactiondetailsAction()
    {
        $idCell = $this->_getParam('idCell');
        $this->view->idCell = $idCell;
        $orgaCell = Orga_Model_Cell::load($idCell);
        $cellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell($orgaCell);
        $this->view->documentLibrary = $cellDataProvider->getDocLibraryForSocialContextAction();

        $this->_forward('context-action-details', 'action', 'social', array(
            'title' => __('Social', 'action', 'actionDetails').
                ' <small>'.$cellDataProvider->getOrgaCell()->getLabel().'</small>',
            'returnUrl' => 'inventory/cell/details/idCell/'.$idCell.'/tab/contextActions',
        ));
    }

    /**
     * Action fournissant un export spécifique.
     * @Secure("viewCell")
     */
    public function specificexportAction()
    {
        $idCell = $this->_getParam('idCell');
        $orgaCell = Orga_Model_Cell::load($idCell);
        $cellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell($orgaCell);

        if (!($this->_hasParam('display') && ($this->_getParam('display') == true))) {
            $exportUrl = 'inventory/cell/specificexport/'.
                'idCell/'.$idCell.'/export/'.$this->_getParam('export').'/display/true';
        } else {
            $exportUrl = null;
        }

        $specificReportsDirectoryPath = PACKAGE_PATH.'/data/specificExports/'.
            $cellDataProvider->getProject()->getKey()['id'].'/'.
            str_replace('|', '_', $orgaCell->getGranularity()->getRef()).'/';
        $specificReports = new DW_Export_Specific_Pdf(
            $specificReportsDirectoryPath.$this->_getParam('export').'.xml',
            $cellDataProvider->getDWCube(),
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