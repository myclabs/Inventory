<?php
/**
 * @author valentin.claras
 * @package Inventory
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Controlleur des onglets des détails d'une cellule.
 * @author valentin.claras
 * @package Inventory
 * @subpackage Controller
 */
class Inventory_Tab_CelldetailsController extends Core_Controller
{
    /**
     * Confguration du projet.
     * @Secure("editProject")
     */
    public function configurationAction()
    {
        // Désactivation du layout.
        $this->_helper->layout()->disableLayout();
        $this->view->idCell = $this->_getParam('idCell');
        $this->view->idProject = $this->_getParam('idProject');
        $project = Inventory_Model_Project::load(array('id' => $this->view->idProject));

        try {
            // Vérification que la granularité des inventaires a bien été défini.
            $orgaGranularityForInventoryStatus = $project->getOrgaGranularityForInventoryStatus();
            $this->view->areAFGranularitiesDisplayed = true;
            $this->view->listOrgaGranularities = array();
            foreach ($project->getOrgaCube()->getGranularities() as $orgaGranularities) {
                $this->view->listOrgaGranularities[$orgaGranularities->getRef()] = $orgaGranularities->getLabel();
            }
        } catch (Core_Exception_UndefinedAttribute $e) {
            $this->view->areAFGranularitiesDisplayed = false;
            $this->view->errorMessageAFGranularities = __('Inventory', 'configuration', 'orgaGranularityForInventoryStatusNotDefined');
        }

        $this->view->granularitesWithDWCube = array();
        foreach ($project->getOrgaCube()->getGranularities() as $orgaGranularity) {
            $granularityDataProvider = Inventory_Model_GranularityDataProvider::loadByOrgaGranularity($orgaGranularity);
            if ($granularityDataProvider->getCellsGenerateDWCubes()) {
                $this->view->granularitesWithDWCube[$orgaGranularity->getLabel()] = $granularityDataProvider;
            }
        }
    }

    /**
     * Action renvoyant le tab.
     * @Secure("allowCell")
     */
    public function aclsAction()
    {
        // Désactivation du layout.
        $this->_helper->layout()->disableLayout();
        $idCell = $this->_getParam('idCell');
        $orgaCell = Orga_Model_Cell::load($idCell);
        $cellDataProviderACLResource = User_Model_Resource_Entity::loadByEntity(
            Inventory_Model_CellDataProvider::loadByOrgaCell($orgaCell)
        );
        $orgaGranularity = $orgaCell->getGranularity();
        $granularityDataProvider = Inventory_Model_GranularityDataProvider::loadByOrgaGranularity($orgaGranularity);
        $project = Inventory_Model_Project::loadByOrgaCube($orgaGranularity->getCube());
        $projectResource = User_Model_Resource_Entity::loadByEntity($project);

        $listDatagridConfiguration = array();

        if (count($orgaGranularity->getAxes()) === 0) {
            $isUserAllowedToEditProject = User_Service_ACL::getInstance()->isAllowed(
                $this->_helper->auth(),
                User_Model_Action_Default::EDIT(),
                $projectResource
            );
        } else {
            $isUserAllowedToEditProject = false;
        }
        if ($isUserAllowedToEditProject) {
            $datagridConfiguration = new Orga_DatagridConfiguration(
                'projectACL'.$project->getKey()['id'],
                'datagrid_cell_acls_project',
                'inventory',
                $orgaCell,
                $orgaGranularity
            );
            $datagridConfiguration->datagrid->addParam('idProject', $project->getKey()['id']);
            $datagridConfiguration->datagrid->addParam('idCell', $idCell);

            $columnUserFirstName = new UI_Datagrid_Col_Text('userFirstName', __('User', 'name', 'firstName'));
            $columnUserFirstName->addable = false;
            $datagridConfiguration->datagrid->addCol($columnUserFirstName);

            $columnUserLastName = new UI_Datagrid_Col_Text('userLastName', __('User', 'name', 'lastName'));
            $columnUserLastName->addable = false;
            $datagridConfiguration->datagrid->addCol($columnUserLastName);

            $columnUserEmail = new UI_Datagrid_Col_Text('userEmail', __('UI', 'name', 'emailAddress'));
            $datagridConfiguration->datagrid->addCol($columnUserEmail);

            $datagridConfiguration->datagrid->pagination = false;
            $datagridConfiguration->datagrid->addElements = true;
            $datagridConfiguration->datagrid->addPanelTitle = __('Inventory', 'role', 'addAdministratorPanelTitle');
            $datagridConfiguration->datagrid->deleteElements = true;

            $labelDatagrid = __('Inventory', 'role', 'projectAdministrators');
            $listDatagridConfiguration[$labelDatagrid] = $datagridConfiguration;
        }

        if ($granularityDataProvider->getCellsWithACL()) {
            $datagridConfiguration = new Orga_DatagridConfiguration(
                'granularityACL'.$granularityDataProvider->getKey()['id'],
                'datagrid_cell_acls_current',
                'inventory',
                $orgaCell,
                $orgaGranularity
            );
            $datagridConfiguration->datagrid->addParam('idCell', $idCell);

            $columnUserFirstName = new UI_Datagrid_Col_Text('userFirstName', __('User', 'name', 'firstName'));
            $columnUserFirstName->addable = false;
            $datagridConfiguration->datagrid->addCol($columnUserFirstName);

            $columnUserLastName = new UI_Datagrid_Col_Text('userLastName', __('User', 'name', 'lastName'));
            $columnUserLastName->addable = false;
            $datagridConfiguration->datagrid->addCol($columnUserLastName);

            $columnUserEmail = new UI_Datagrid_Col_Text('userEmail', __('UI', 'name', 'emailAddress'));
            $datagridConfiguration->datagrid->addCol($columnUserEmail);

            $columnRole = new UI_Datagrid_Col_List('userRole', __('User', 'name', 'role'));
            $columnRole->list = array();
            foreach ($cellDataProviderACLResource->getLinkedSecurityIdentities() as $role) {
                if ($role instanceof User_Model_Role) {
                    $columnRole->list[$role->getRef()] = $role->getName();
                }
            }
            $datagridConfiguration->datagrid->addCol($columnRole);

            $datagridConfiguration->datagrid->pagination = false;
            $datagridConfiguration->datagrid->addElements = true;
            $datagridConfiguration->datagrid->addPanelTitle = __('Inventory', 'role', 'addPanelTitle');
            $datagridConfiguration->datagrid->deleteElements = true;

            $labelDatagrid = $orgaGranularity->getLabel();
            $listDatagridConfiguration[$labelDatagrid] = $datagridConfiguration;
        }

        foreach ($orgaGranularity->getNarrowerGranularities() as $orgaNarrowerGranularity) {
            $narrowerGranularityDataProvider = Inventory_Model_GranularityDataProvider::loadByOrgaGranularity(
                $orgaNarrowerGranularity
            );
            if ($narrowerGranularityDataProvider->getCellsWithACL()) {
                $datagridConfiguration = new Orga_DatagridConfiguration(
                    'granularityACL'.$narrowerGranularityDataProvider->getKey()['id'],
                    'datagrid_cell_acls_child',
                    'inventory',
                    $orgaCell,
                    $narrowerGranularityDataProvider->getOrgaGranularity()
                );
                $datagridConfiguration->datagrid->addParam('idCell', $idCell);

                $columnAdministrators = new UI_Datagrid_Col_Text('administrators', __('Inventory', 'role', 'cellGenericAdministrators'));
                $datagridConfiguration->datagrid->addCol($columnAdministrators);

                $columnDetails = new UI_Datagrid_Col_Popup('details', __('Inventory', 'role', 'detailCellRolesHeader'));
                $columnDetails->popup->addAttribute('class', 'large');
                $datagridConfiguration->datagrid->addCol($columnDetails);

                $labelDatagrid = $orgaNarrowerGranularity->getLabel();
                $listDatagridConfiguration[$labelDatagrid] = $datagridConfiguration;
            }
        }

        $this->_forward('child', 'cell', 'orga', array(
            'idCell' => $idCell,
            'datagridConfiguration' => $listDatagridConfiguration,
            'display' => 'render',
            'minimize' => false,
        ));

    }

    /**
     * Action renvoyant le tab.
     * @Secure("editCell")
     */
    public function afgranularitiesconfigsAction()
    {
        // Désactivation du layout.
        $this->_helper->layout()->disableLayout();
        $idCell = $this->_getParam('idCell');
        $orgaCell = Orga_Model_Cell::load($idCell);
        $project = Inventory_Model_Project::loadByOrgaCube($orgaCell->getGranularity()->getCube());

        $listAFs = array();
        foreach (AF_Model_AF::loadList() as $aF) {
            $listAFs[$aF->getRef()] = $aF->getLabel();
        }

        $listDatagridConfiguration = array();
        $listAFGranularities = $project->getAFGranularities();
        uasort(
            $listAFGranularities,
            function($a, $b) {
                if ($a->getAFConfigOrgaGranularity() === $b->getAFConfigOrgaGranularity()) {
                    return $a->getAFInputOrgaGranularity()->getPosition() - $b->getAFInputOrgaGranularity()->getPosition();
                }
                return $a->getAFConfigOrgaGranularity()->getPosition() - $b->getAFConfigOrgaGranularity()->getPosition();
            }
        );
        foreach ($listAFGranularities as $aFGranularities) {
            if ($orgaCell->getGranularity()->isBroaderThan($aFGranularities->getAFConfigOrgaGranularity())) {
                $datagridConfiguration = new Orga_DatagridConfiguration(
                    'aFGranularityConfig'.$aFGranularities->getAFInputOrgaGranularity()->getKey()['id'],
                    'datagrid_cell_afgranularities_config',
                    'inventory',
                    $orgaCell,
                    $aFGranularities->getAFConfigOrgaGranularity()
                );
                $datagridConfiguration->datagrid->addParam('idCell', $idCell);
                $idInputGranularity = $aFGranularities->getAFInputOrgaGranularity()->getKey()['id'];
                $datagridConfiguration->datagrid->addParam('idInputGranularity', $idInputGranularity);

                $columnAF = new UI_Datagrid_Col_List('aF', __('AF', 'name', 'accountingForm'));
                $columnAF->list = $listAFs;
                $columnAF->editable = true;
                $columnAF->fieldType = UI_Datagrid_Col_List::FIELD_AUTOCOMPLETE;
                $datagridConfiguration->datagrid->addCol($columnAF);

                $labelDatagrid = $aFGranularities->getAFConfigOrgaGranularity()->getLabel()
                    . ' <small>' . $aFGranularities->getAFInputOrgaGranularity()->getLabel() . '</small>';
                $listDatagridConfiguration[$labelDatagrid] = $datagridConfiguration;
            }
        }

        $this->_forward('child', 'cell', 'orga', array(
            'idCell' => $idCell,
            'datagridConfiguration' => $listDatagridConfiguration,
            'display' => 'render',
            'minimize' => false,
        ));
    }

    /**
     * Action renvoyant le tab.
     * @Secure("viewCell")
     */
    public function inventoriesAction()
    {
        // Désactivation du layout.
        $this->_helper->layout()->disableLayout();
        $idCell = $this->_getParam('idCell');
        $orgaCell = Orga_Model_Cell::load($idCell);
        $cellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell($orgaCell);

        $inventoryGranularity = $cellDataProvider->getProject()->getOrgaGranularityForInventoryStatus();
        $crossedOrgaGranularity = $inventoryGranularity->getCrossedGranularity($orgaCell->getGranularity());

        $datagridConfiguration = new Orga_DatagridConfiguration(
            'inventories'.$inventoryGranularity->getKey()['id'],
            'datagrid_cell_inventories',
            'inventory',
            $orgaCell,
            $crossedOrgaGranularity
        );
        $datagridConfiguration->datagrid->addParam('idCell', $orgaCell->getKey()['id']);

        // Column Statut
        $columnStateInventory = new UI_Datagrid_Col_List('inventoryStatus', __('UI', 'name', 'status'));
        $columnStateInventory->withEmptyElement = false;

        $isUserAllowedToInputInventoryStatus = User_Service_ACL::getInstance()->isAllowed(
            $this->_helper->auth(),
            Inventory_Action_Cell::INPUT(),
            $cellDataProvider
        );
        if ($isUserAllowedToInputInventoryStatus) {
            $columnStateInventory->editable = $orgaCell->getGranularity()->isBroaderThan($inventoryGranularity);
        }
        $columnStateInventory->list = array(
                Inventory_Model_CellDataProvider::STATUS_NOTLAUNCHED => __('Inventory', 'inventory', 'notLaunched'),
                Inventory_Model_CellDataProvider::STATUS_ACTIVE => __('UI', 'property', 'inProgress'),
                Inventory_Model_CellDataProvider::STATUS_CLOSED => __('UI', 'property', 'closed')
        );
        $columnStateInventory->fieldType = UI_Datagrid_Col_List::FIELD_LIST;
        $columnStateInventory->filterName = Inventory_Model_CellDataProvider::QUERY_INVENTORYSTATUS;
        $columnStateInventory->entityAlias = Inventory_Model_CellDataProvider::getAlias();
        $datagridConfiguration->datagrid->addCol($columnStateInventory);

        $columnAdvencementInputs = new UI_Datagrid_Col_Percent('advancementInput', __('Inventory', 'inventory', 'completeInputPercentageHeader'));
        $datagridConfiguration->datagrid->addCol($columnAdvencementInputs);

        $columnAdvencementFinishedInputs = new UI_Datagrid_Col_Percent('advancementFinishedInput', __('Inventory', 'inventory', 'finishedInputPercentageHeader'));
        $datagridConfiguration->datagrid->addCol($columnAdvencementFinishedInputs);

        $this->_forward('child', 'cell', 'orga', array(
            'idCell' => $idCell,
            'datagridConfiguration' => $datagridConfiguration,
            'display' => 'render',
        ));
    }

    /**
     * Action renvoyant le tab.
     * @Secure("viewCell")
     */
    public function afgranularitiesinputsAction()
    {
        // Désactivation du layout.
        $this->_helper->layout()->disableLayout();
        $idCell = $this->_getParam('idCell');
        $orgaCell = Orga_Model_Cell::load($idCell);
        $project = Inventory_Model_Project::loadByOrgaCube($orgaCell->getGranularity()->getCube());

        $listDatagridConfiguration = array();
        $listAFGranularities = $project->getAFGranularities();
        uasort(
            $listAFGranularities,
            function($a, $b) {
                return $a->getAFInputOrgaGranularity()->getPosition() - $b->getAFInputOrgaGranularity()->getPosition();
            }
        );
        foreach ($listAFGranularities as $aFGranularities) {
            if ($orgaCell->getGranularity()->isBroaderThan($aFGranularities->getAFInputOrgaGranularity())) {
                $datagridConfiguration = new Orga_DatagridConfiguration(
                    'aFGranularity'.$idCell.'Input'.$aFGranularities->getAFInputOrgaGranularity()->getKey()['id'],
                    'datagrid_cell_afgranularities_input',
                    'inventory',
                    $orgaCell,
                    $aFGranularities->getAFInputOrgaGranularity()
                );
                $datagridConfiguration->datagrid->addParam('idCell', $idCell);

                $columnStateInventory = new UI_Datagrid_Col_List('inventoryStatus', __('Inventory', 'name', 'inventory'));
                $columnStateInventory->withEmptyElement = false;
                $columnStateInventory->list = array(
                    Inventory_Model_CellDataProvider::STATUS_NOTLAUNCHED => __('Inventory', 'inventory', 'notLaunched'),
                    Inventory_Model_CellDataProvider::STATUS_ACTIVE => __('UI', 'property', 'inProgress'),
                    Inventory_Model_CellDataProvider::STATUS_CLOSED => __('UI', 'property', 'closed'));
                $columnStateInventory->fieldType = UI_Datagrid_Col_List::FIELD_BOX;
                $columnStateInventory->filterName = Inventory_Model_CellDataProvider::QUERY_INVENTORYSTATUS;
                $columnStateInventory->entityAlias = Inventory_Model_CellDataProvider::getAlias();
                $columnStateInventory->editable = false;
                $datagridConfiguration->datagrid->addCol($columnStateInventory);

                $colAdvancementInput = new UI_Datagrid_Col_Percent('advancementInput', __('UI', 'name', 'progress'));
                $colAdvancementInput->filterName = AF_Model_InputSet_Primary::QUERY_COMPLETION;
                $colAdvancementInput->sortName = AF_Model_InputSet_Primary::QUERY_COMPLETION;
                $colAdvancementInput->entityAlias = AF_Model_InputSet_Primary::getAlias();
                $datagridConfiguration->datagrid->addCol($colAdvancementInput);

                $columnStateInput = new UI_Datagrid_Col_List('stateInput', __('UI', 'name', 'status'));
                $imageFinished = new UI_HTML_Image('images/af/bullet_green.png', 'finish');
                $imageComplete = new UI_HTML_Image('images/af/bullet_orange.png', 'complet');
                $imageCalculationIncomplete = new UI_HTML_Image('images/af/bullet_red.png', 'incompletecomplete');
                $imageInputIncomplete = new UI_HTML_Image('images/af/bullet_red.png', 'incomplet');
                $columnStateInput->list = array(
                    AF_Model_InputSet_Primary::STATUS_FINISHED => $imageFinished->render() . ' ' . __('AF', 'inputInput', 'statusFinished'),
                    AF_Model_InputSet_Primary::STATUS_COMPLETE => $imageComplete->render() . ' ' . __('AF', 'inputInput', 'statusComplete'),
                    AF_Model_InputSet_Primary::STATUS_CALCULATION_INCOMPLETE => $imageCalculationIncomplete->render() . ' ' . __('AF', 'inputInput', 'statusCalculationIncomplete'),
                    AF_Model_InputSet_Primary::STATUS_INPUT_INCOMPLETE => $imageInputIncomplete->render() . ' ' . __('AF', 'inputInput', 'statusInputIncomplete'),
                );
                $datagridConfiguration->datagrid->addCol($columnStateInput);

                $columnValIndic = new UI_Datagrid_Col_Number('totalValueGESInput', __('AF', 'inputList', 'GESTotalValueHeader'));
                $datagridConfiguration->datagrid->addCol($columnValIndic);

                $columnIncert = new UI_Datagrid_Col_Number('totalUncertaintyGESInput', '&#177; (%)');
                $datagridConfiguration->datagrid->addCol($columnIncert);

                $colLinkEdit = new UI_Datagrid_Col_Link('link', __('UI', 'name', 'details'));
                $datagridConfiguration->datagrid->addCol($colLinkEdit);

                $labelDatagrid = $aFGranularities->getAFInputOrgaGranularity()->getLabel();
                $listDatagridConfiguration[$labelDatagrid] = $datagridConfiguration;
            }
        }

        $this->_forward('child', 'cell', 'orga', array(
            'idCell' => $idCell,
            'datagridConfiguration' => $listDatagridConfiguration,
            'display' => 'render',
        ));
    }

    /**
     * Action fournissant la vue d'anaylse.
     * @Secure("viewCell")
     */
    public function reportAction()
    {
        $idCell = $this->_getParam('idCell');
        $orgaCell = Orga_Model_Cell::load($idCell);
        $cellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell($orgaCell);

        if (($this->_hasParam('idReport')) || ($this->_hasParam('idCube'))) {
            if ($this->_hasParam('idReport')) {
                $reportResource = User_Model_Resource_Entity::loadByEntity(
                    DW_Model_Report::load(array('id' => $this->_getParam('idReport')))
                );
                $reportCanBeUpdated = User_Service_ACL::getInstance()->isAllowed(
                    $this->_helper->auth(),
                    User_Model_Action_Default::EDIT(),
                    $reportResource
                );
            } else {
                $reportCanBeUpdated = false;
            }
            $reportCanBeSaveAs = User_Service_ACL::getInstance()->isAllowed(
                $this->_helper->auth(),
                User_Model_Action_Default::VIEW(),
                User_Model_Resource_Entity::loadByEntity($cellDataProvider)
            );
            $viewConfiguration = new DW_ViewConfiguration();
            $viewConfiguration->setComplementaryPageTitle(' <small>'.$orgaCell->getLabelExtended().'</small>');
            $viewConfiguration->setOutputURL('inventory/cell/details?idCell='.$orgaCell->getKey()['id'].'&tab=reports');
            $viewConfiguration->setSaveURL('inventory/tab_celldetails/report?idCell='.$orgaCell->getKey()['id'].'&');
            $viewConfiguration->setCanBeUpdated($reportCanBeUpdated);
            $viewConfiguration->setCanBeSavedAs($reportCanBeSaveAs);
            if ($this->_hasParam('idReport')) {
                $this->_forward('details', 'report', 'dw', array(
                    'idReport' => $this->_getParam('idReport'),
                    'viewConfiguration' => $viewConfiguration
                ));
            } else {
                $this->_forward('details', 'report', 'dw', array(
                    'idCube' => $this->_getParam('idCube'),
                    'viewConfiguration' => $viewConfiguration
                ));
            }
        } else {
            // Désactivation du layout.
            $this->_helper->layout()->disableLayout();
        }

        $this->view->idCell = $orgaCell->getKey()['id'];
        $this->view->idCube = $cellDataProvider->getDWCube()->getKey()['id'];
        $this->view->isDWCubeUpToDate = Inventory_Service_ETLStructure::getInstance()->isCellDataProviderDWCubeUpToDate(
            $cellDataProvider
        );
        $this->view->dWCubesCanBeReset = User_Service_ACL::getInstance()->isAllowed(
            $this->_helper->auth(),
            User_Model_Action_Default::EDIT(),
            $cellDataProvider
        );

        $this->view->specificExports = array();
        $currentPackage = Core_Package_Manager::getCurrentPackage();
        $specificReportsDirectoryPath = $currentPackage->getPath().'/data/specificExports/'.
            $cellDataProvider->getProject()->getKey()['id'].'/'.
            str_replace('|', '_', $orgaCell->getGranularity()->getRef()).'/';
        if (is_dir($specificReportsDirectoryPath)) {
            $specificReportsDirectory = dir($specificReportsDirectoryPath);
            while (false !== ($entry = $specificReportsDirectory->read())) {
                if ((is_file($specificReportsDirectoryPath.$entry)) && (preg_match('#\.xml$#', $entry))) {
                    $fileName = substr($entry, null, -4);
                    if (DW_Export_Specific_Pdf::isValid($specificReportsDirectoryPath.$entry)) {
                        $this->view->specificExports[] = array(
                            'label' => $fileName,
                            'link' => 'inventory/cell/specificexport/idCell/'.$idCell.'/export/'.$fileName,
                        );
                    }
                }
            }
        }
    }

    /**
     * Action fournissant la vue des actions génériques.
     * @Secure("problemToSolve")
     */
    public function genericactionsAction()
    {
        // Désactivation du layout.
        $this->_helper->layout()->disableLayout();
        $idCell = $this->_getParam('idCell');
        $this->view->idCell = $idCell;

        $query = new Core_Model_Query();
        $query->order->addOrder(Social_Model_Theme::QUERY_LABEL);
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->themes = Social_Model_Theme::loadList($query);
    }

    /**
     * Action fournissant la vue des actions contextualisées.
     * @Secure("problemToSolve")
     */
    public function contextactionsAction()
    {
        // Désactivation du layout.
        $this->_helper->layout()->disableLayout();
        $idCell = $this->_getParam('idCell');
        $this->view->idCell = $idCell;

        $query = new Core_Model_Query();
        $query->order->addOrder(Social_Model_Theme::QUERY_LABEL);
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->themes = Social_Model_Theme::loadList($query);
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->genericActions = Social_Model_GenericAction::loadList();
    }

    /**
     * Action fournissant la vue des documents pour l'input.
     * @Secure("problemToSolve")
     */
    public function documentsAction()
    {
        // Désactivation du layout.
        $this->_helper->layout()->disableLayout();
        $idCell = $this->_getParam('idCell');
        $this->view->idCell = $idCell;
        $orgaCell = Orga_Model_Cell::load($idCell);
        $cellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell($orgaCell);
        $granularityDataProvider = $cellDataProvider->getGranularityDataProvider();

        if ($granularityDataProvider->getCellsWithInputDocs()) {
            $this->view->docLibraryForAFInputSetPrimary = $cellDataProvider->getDocLibraryForAFInputSetsPrimary();
        } else {
            $this->view->docLibraryForAFInputSetPrimary = null;
        }
        if ($granularityDataProvider->getCellsWithSocialGenericActions()) {
            $this->view->docLibraryForSocialGenericAction = $cellDataProvider->getDocLibraryForSocialGenericAction();
        } else {
            $this->view->docLibraryForSocialGenericAction = null;
        }
        if ($granularityDataProvider->getCellsWithSocialContextActions()) {
            $this->view->docLibraryForSocialContextAction = $cellDataProvider->getDocLibraryForSocialContextAction();
        } else {
            $this->view->docLibraryForSocialContextAction = null;
        }
    }

    /**
     * Action fournissant la vue d'administration d'une cellule.
     * @Secure("editProject")
     */
    public function administrationAction()
    {
        // Désactivation du layout.
        $this->_helper->layout()->disableLayout();
        $idCell = $this->_getParam('idCell');
        $this->view->idCell = $idCell;
        $orgaCell = Orga_Model_Cell::load($idCell);
        $cellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell($orgaCell);
        $granularityDataProvider = $cellDataProvider->getGranularityDataProvider();

        if ($granularityDataProvider->getCellsGenerateDWCubes()) {
            $this->view->isDWCubeUpToDate = Inventory_Service_ETLStructure::getInstance()->isCellDataProviderDWCubeUpToDate(
                $cellDataProvider
            );
        }
    }

//    /**
//     * Action appelé pour l'affichage du fichier d'import
//     */
//    public function importxlsAction()
//    {
//        $ok = false;
//        $validPicture = new UI_HTML_Image('ui/accept.png', 'validPicture');
//        $invalidPicture = new UI_HTML_Image('doc/exclamation.png', 'invalidPicture');
//        require_once (Core_Package_Manager::getPackage('Inventory')->getPath().'/application/inventory/forms/Import/ImportXls.php');
//        $addForm = new importForm('ImportXls', $this->_getAllParams());
//
//        $config = new Zend_Config_Ini(
//                Core_Package_Manager::getCurrentPackage()->getPath().'/application/configs/application.ini',
//                APPLICATION_ENV);
//        $basePath = $config->export->path;
//
//        if (!isset($basePath)) {
//            UI_Message::addMessageStatic(__('Inventory', 'errors', 'pathConfigUnfindable'), $invalidPicture);
//        }
//        if ($this->getRequest()->isPost()) {
//            $post = $this->getRequest()->getPost();
//            if ((isset($_FILES['fileElementForm']['tmp_name'])&&($_FILES['fileElementForm']['error'] == UPLOAD_ERR_OK))) {
//                $chemindestination = $basePath;
//                if (move_uploaded_file($_FILES['fileElementForm']['tmp_name'], $chemindestination.$_FILES['fileElementForm']['name'])) {
//                    $xlsPath = $chemindestination.$_FILES['fileElementForm']['name'];
//                    $ok = true;
//                } else {
//                    UI_Message::addMessageStatic(__('Inventory', 'errors', 'uploadFail'), $invalidPicture);
//                }
//            }
//        }
//        if ($ok) {
//            try {
//                $importxls = new Inventory_ImportXls($xlsPath);
//                $importxls->ImportAndSaveObject($this->_getParam('idCell'));
//                UI_Message::addMessageStatic(__('Inventory', 'messages', 'uploadOk'), $validPicture);
//            } catch (Exception $e) {
//                UI_Message::addMessageStatic(__('Inventory', 'errors', 'importFail'), $invalidPicture);
//            }
//        }
//
//         $this->_redirect($_SERVER['HTTP_REFERER']);
//    }

}