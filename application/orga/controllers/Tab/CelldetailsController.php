<?php
/**
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */

use AuditTrail\Domain\Context\OrganizationContext;
use AuditTrail\Domain\EntryRepository;
use Core\Annotation\Secure;
use DI\Annotation\Inject;

/**
 * Controlleur des onglets des détails d'une cellule.
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */
class Orga_Tab_CelldetailsController extends Core_Controller
{
    /**
     * @Inject
     * @var User_Service_ACL
     */
    private $aclService;

    /**
     * @Inject
     * @var Orga_Service_ETLStructure
     */
    private $etlStructureService;

    /**
     * @Inject
     * @var EntryRepository
     */
    private $entryRepository;

    /**
     * Confguration du projet.
     * @Secure("editOrganization")
     */
    public function orgaAction()
    {
        $idCell = $this->getParam('idCell');
        $cell = Orga_Model_Cell::load($idCell);
        $granularity = $cell->getGranularity();
        $organization = $granularity->getOrganization();

        $this->view->idCell = $idCell;
        $this->view->idOrganization = $organization->getId();
        if ($granularity->getRef() === 'global') {
            $this->view->isGlobal = true;
        } else {
            $this->view->isGlobal = false;
        }
        $this->view->hasChildCells = ($cell->countTotalChildCells() > 0);

        $this->view->tab = $this->getParam('tab');

        if ($this->hasParam('display') && ($this->getParam('display') === 'render')) {
            $this->_helper->layout()->disableLayout();
            $this->view->display = false;
        } else {
            $this->view->display = true;
            UI_Datagrid::addHeader();
            UI_Tab_View::addHeader();
            UI_Tree::addHeader();
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
        $idCell = $this->getParam('idCell');
        $cell = Orga_Model_Cell::load($idCell);
        $cellACLResource = User_Model_Resource_Entity::loadByEntity($cell);
        $granularity = $cell->getGranularity();
        $organization = $granularity->getOrganization();
        $organizationResource = User_Model_Resource_Entity::loadByEntity($organization);

        $listDatagridConfiguration = array();

        if (count($granularity->getAxes()) === 0) {
            $isUserAllowedToEditOrganization = $this->aclService->isAllowed(
                $this->_helper->auth(),
                User_Model_Action_Default::EDIT(),
                $organizationResource
            );
        } else {
            $isUserAllowedToEditOrganization = false;
        }
        if ($isUserAllowedToEditOrganization) {
            $datagridConfiguration = new Orga_DatagridConfiguration(
                'organizationACL'.$organization->getId(),
                'datagrid_cell_acls_organization',
                'orga',
                $cell,
                $granularity
            );
            $datagridConfiguration->datagrid->addParam('idOrganization', $organization->getId());
            $datagridConfiguration->datagrid->addParam('idCell', $idCell);

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
        }

        if ($granularity->getCellsWithACL()) {
            $datagridConfiguration = new Orga_DatagridConfiguration(
                'granularityACL'.$granularity->getId(),
                'datagrid_cell_acls_current',
                'orga',
                $cell,
                $granularity
            );
            $datagridConfiguration->datagrid->addParam('idCell', $idCell);

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
            foreach ($cellACLResource->getLinkedSecurityIdentities() as $role) {
                if ($role instanceof User_Model_Role) {
                    $columnRole->list[$role->getRef()] = __('Orga', 'role', $role->getName());
                }
            }
            $datagridConfiguration->datagrid->addCol($columnRole);

            $datagridConfiguration->datagrid->pagination = false;
            $datagridConfiguration->datagrid->addElements = true;
            $datagridConfiguration->datagrid->addPanelTitle = __('Orga', 'role', 'addPanelTitle');
            $datagridConfiguration->datagrid->deleteElements = true;

            $labelDatagrid = $granularity->getLabel();
            $listDatagridConfiguration[$labelDatagrid] = $datagridConfiguration;
        }

        foreach ($granularity->getNarrowerGranularities() as $narrowerGranularity) {
            if ($narrowerGranularity->getCellsWithACL()) {
                $datagridConfiguration = new Orga_DatagridConfiguration(
                    'granularityACL'.$narrowerGranularity->getId(),
                    'datagrid_cell_acls_child',
                    'orga',
                    $cell,
                    $narrowerGranularity
                );
                $datagridConfiguration->datagrid->addParam('idCell', $idCell);

                $columnAdministrators = new UI_Datagrid_Col_Text('administrators', __('Orga', 'role', 'cellGenericAdministrators'));
                $datagridConfiguration->datagrid->addCol($columnAdministrators);

                $columnDetails = new UI_Datagrid_Col_Popup('details', __('Orga', 'role', 'detailCellRolesHeader'));
                $columnDetails->popup->addAttribute('class', 'large');
                $datagridConfiguration->datagrid->addCol($columnDetails);

                $labelDatagrid = $narrowerGranularity->getLabel();
                $listDatagridConfiguration[$labelDatagrid] = $datagridConfiguration;
            }
        }

        $this->forward('child', 'cell', 'orga', array(
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
    public function afconfigurationAction()
    {
        // Désactivation du layout.
        $this->_helper->layout()->disableLayout();
        $idCell = $this->getParam('idCell');
        $cell = Orga_Model_Cell::load($idCell);
        $organization = $cell->getGranularity()->getOrganization();

        $listAFs = array();
        foreach (AF_Model_AF::loadList() as $aF) {
            $listAFs[$aF->getRef()] = $aF->getLabel();
        }

        $listDatagridConfiguration = array();
        $listInputGranularities = $organization->getInputGranularities();
        uasort(
            $listInputGranularities,
            function(Orga_Model_Granularity $a, Orga_Model_Granularity $b) {
                if ($a->getInputConfigGranularity() === $b->getInputConfigGranularity()) {
                    return $a->getPosition() - $b->getPosition();
                }
                return $a->getInputConfigGranularity()->getPosition() - $b->getInputConfigGranularity()->getPosition();
            }
        );
        foreach ($listInputGranularities as $inputGranularity) {
            if ($cell->getGranularity()->isBroaderThan($inputGranularity->getInputConfigGranularity())
                || ($cell->getGranularity()->getRef() === $inputGranularity->getInputConfigGranularity())) {
                $datagridConfiguration = new Orga_DatagridConfiguration(
                    'aFGranularityConfig'.$inputGranularity->getId(),
                    'datagrid_cell_afgranularities_config',
                    'orga',
                    $cell,
                    $inputGranularity->getInputConfigGranularity()
                );
                $datagridConfiguration->datagrid->addParam('idCell', $idCell);
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
        }

        $this->forward('child', 'cell', 'orga', array(
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
        $idCell = $this->getParam('idCell');
        $cell = Orga_Model_Cell::load($idCell);

        $granularityForInventoryStatus = $cell->getGranularity()->getOrganization()->getGranularityForInventoryStatus();
        $crossedOrgaGranularity = $granularityForInventoryStatus->getCrossedGranularity($cell->getGranularity());

        $datagridConfiguration = new Orga_DatagridConfiguration(
            'inventories'.$granularityForInventoryStatus->getId(),
            'datagrid_cell_inventories',
            'orga',
            $cell,
            $crossedOrgaGranularity
        );
        $datagridConfiguration->datagrid->addParam('idCell', $cell->getId());

        // Column Statut
        $columnStateOrga = new UI_Datagrid_Col_List('inventoryStatus', __('UI', 'name', 'status'));
        $columnStateOrga->withEmptyElement = false;

        $isUserAllowedToInputInventoryStatus = $this->aclService->isAllowed(
            $this->_helper->auth(),
            Orga_Action_Cell::INPUT(),
            $cell
        );
        $columnStateOrga->editable = ($isUserAllowedToInputInventoryStatus
            && (($cell->getGranularity()->isBroaderThan($granularityForInventoryStatus))
                || ($cell->getGranularity() === $granularityForInventoryStatus))
        );
        $columnStateOrga->list = array(
                Orga_Model_Cell::STATUS_NOTLAUNCHED => __('Orga', 'inventory', 'notLaunched'),
                Orga_Model_Cell::STATUS_ACTIVE => __('UI', 'property', 'inProgress'),
                Orga_Model_Cell::STATUS_CLOSED => __('UI', 'property', 'closed')
        );
        $columnStateOrga->fieldType = UI_Datagrid_Col_List::FIELD_LIST;
        $columnStateOrga->filterName = Orga_Model_Cell::QUERY_INVENTORYSTATUS;
        $columnStateOrga->entityAlias = Orga_Model_Cell::getAlias();
        $datagridConfiguration->datagrid->addCol($columnStateOrga);

        $columnAdvencementInputs = new UI_Datagrid_Col_Percent('advancementInput', __('Orga', 'inventory', 'completeInputPercentageHeader'));
        $datagridConfiguration->datagrid->addCol($columnAdvencementInputs);

        $columnAdvencementFinishedInputs = new UI_Datagrid_Col_Percent('advancementFinishedInput', __('Orga', 'inventory', 'finishedInputPercentageHeader'));
        $datagridConfiguration->datagrid->addCol($columnAdvencementFinishedInputs);

        $this->forward('child', 'cell', 'orga', array(
            'idCell' => $idCell,
            'datagridConfiguration' => $datagridConfiguration,
            'display' => 'render',
        ));
    }

    /**
     * Action renvoyant le tab.
     * @Secure("viewCell")
     */
    public function afinputsAction()
    {
        // Désactivation du layout.
        $this->_helper->layout()->disableLayout();
        $idCell = $this->getParam('idCell');
        $cell = Orga_Model_Cell::load($idCell);
        $organization = $cell->getGranularity()->getOrganization();
        $granularityForInventoryStatus = $organization->getGranularityForInventoryStatus();

        $listDatagridConfiguration = array();
        $listInputGranularities = $organization->getInputGranularities();
        uasort(
            $listInputGranularities,
            function(Orga_Model_Granularity $a, Orga_Model_Granularity $b) {
                return $a->getPosition() - $b->getPosition();
            }
        );
        foreach ($listInputGranularities as $inputGranularity) {
            if ($cell->getGranularity()->isBroaderThan($inputGranularity)) {
                $datagridConfiguration = new Orga_DatagridConfiguration(
                    'aFGranularity'.$idCell.'Input'.$inputGranularity->getId(),
                    'datagrid_cell_afgranularities_input',
                    'orga',
                    $cell,
                    $inputGranularity
                );
                $datagridConfiguration->datagrid->addParam('idCell', $idCell);

                if ($inputGranularity->isNarrowerThan($granularityForInventoryStatus)
                    || $inputGranularity->getRef() === $granularityForInventoryStatus->getRef()) {
                    $columnStateOrga = new UI_Datagrid_Col_List('inventoryStatus', __('Orga', 'inventory', 'inventoryStatus'));
                    $columnStateOrga->withEmptyElement = false;
                    $columnStateOrga->list = array(
                        Orga_Model_Cell::STATUS_NOTLAUNCHED => __('Orga', 'inventory', 'notLaunched'),
                        Orga_Model_Cell::STATUS_ACTIVE => __('UI', 'property', 'inProgress'),
                        Orga_Model_Cell::STATUS_CLOSED => __('UI', 'property', 'closed'));
                    $columnStateOrga->fieldType = UI_Datagrid_Col_List::FIELD_BOX;
                    $columnStateOrga->filterName = Orga_Model_Cell::QUERY_INVENTORYSTATUS;
                    $columnStateOrga->entityAlias = Orga_Model_Cell::getAlias();
                    $columnStateOrga->editable = false;
                    $datagridConfiguration->datagrid->addCol($columnStateOrga);

                    $colAdvancementInput = new UI_Datagrid_Col_Percent('advancementInput', __('Orga', 'input', 'inputProgress'));
                    $colAdvancementInput->filterName = AF_Model_InputSet_Primary::QUERY_COMPLETION;
                    $colAdvancementInput->sortName = AF_Model_InputSet_Primary::QUERY_COMPLETION;
                    $colAdvancementInput->entityAlias = AF_Model_InputSet_Primary::getAlias();
                    $datagridConfiguration->datagrid->addCol($colAdvancementInput);
                }

                $columnStateInput = new UI_Datagrid_Col_List('stateInput', __('Orga', 'input', 'inputStatus'));
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

                $colLinkEdit = new UI_Datagrid_Col_Link('link', __('UI', 'name', 'details'));
                $datagridConfiguration->datagrid->addCol($colLinkEdit);

                $labelDatagrid = $inputGranularity->getLabel();
                $listDatagridConfiguration[$labelDatagrid] = $datagridConfiguration;
            }
        }

        $this->forward('child', 'cell', 'orga', array(
            'idCell' => $idCell,
            'datagridConfiguration' => $listDatagridConfiguration,
            'display' => 'render',
        ));
    }

    /**
     * Action fournissant la vue des anaylses.
     * @Secure("viewCell")
     */
    public function analysesAction()
    {
        // Désactivation du layout.
        $this->_helper->layout()->disableLayout();
        $idCell = $this->getParam('idCell');
        $cell = Orga_Model_Cell::load($idCell);

        $this->view->idCell = $cell->getId();
        $this->view->idCube = $cell->getDWCube()->getId();
        $this->view->isDWCubeUpToDate = $this->etlStructureService->isCellDWCubeUpToDate($cell);
        $this->view->dWCubesCanBeReset = $this->aclService->isAllowed(
            $this->_helper->auth(),
            User_Model_Action_Default::EDIT(),
            $cell
        );

        $this->view->specificExports = array();
        $specificReportsDirectoryPath = PACKAGE_PATH.'/data/specificExports/'.
            $cell->getGranularity()->getOrganization()->getId().'/'.
            str_replace('|', '_', $cell->getGranularity()->getRef()).'/';
        if (is_dir($specificReportsDirectoryPath)) {
            $specificReportsDirectory = dir($specificReportsDirectoryPath);
            while (false !== ($entry = $specificReportsDirectory->read())) {
                if ((is_file($specificReportsDirectoryPath.$entry)) && (preg_match('#\.xml$#', $entry))) {
                    $fileName = substr($entry, null, -4);
                    if (DW_Export_Specific_Pdf::isValid($specificReportsDirectoryPath.$entry)) {
                        $this->view->specificExports[] = array(
                            'label' => $fileName,
                            'link' => 'orga/cell/specificexport/idCell/'.$idCell.'/export/'.$fileName,
                        );
                    }
                }
            }
        }
    }

    /**
     * Action fournissant la vue d'un Report
     * @Secure("viewReport")
     */
    public function reportAction()
    {
        $idCell = $this->getParam('idCell');
        $cell = Orga_Model_Cell::load($idCell);

        if ($this->hasParam('idReport')) {
            $reportResource = User_Model_Resource_Entity::loadByEntity(
                DW_Model_Report::load($this->getParam('idReport'))
            );
            $reportCanBeUpdated = $this->aclService->isAllowed(
                $this->_helper->auth(),
                User_Model_Action_Default::EDIT(),
                $reportResource
            );
        } else {
            $reportCanBeUpdated = false;
        }
        $reportCanBeSaveAs = $this->aclService->isAllowed(
            $this->_helper->auth(),
            User_Model_Action_Default::VIEW(),
            User_Model_Resource_Entity::loadByEntity($cell)
        );
        $viewConfiguration = new DW_ViewConfiguration();
        $viewConfiguration->setComplementaryPageTitle(' <small>'.$cell->getLabelExtended().'</small>');
        $viewConfiguration->setOutputUrl('orga/cell/details/idCell/'.$cell->getId().'/tab/analyses');
        $viewConfiguration->setSaveURL('orga/tab_celldetails/report/idCell/'.$cell->getId());
        $viewConfiguration->setCanBeUpdated($reportCanBeUpdated);
        $viewConfiguration->setCanBeSavedAs($reportCanBeSaveAs);
        if ($this->hasParam('idReport')) {
            $this->forward('details', 'report', 'dw', array(
                    'idReport' => $this->getParam('idReport'),
                    'viewConfiguration' => $viewConfiguration
                ));
        } else {
            $this->forward('details', 'report', 'dw', array(
                    'idOrganization' => $this->getParam('idOrganization'),
                    'viewConfiguration' => $viewConfiguration
                ));
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
        $idCell = $this->getParam('idCell');
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
        $idCell = $this->getParam('idCell');
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
        $idCell = $this->getParam('idCell');
        $this->view->idCell = $idCell;
        $cell = Orga_Model_Cell::load($idCell);
        $granularity = $cell->getGranularity();

        if ($granularity->getCellsWithInputDocuments()) {
            $this->view->docLibraryForAFInputSetPrimary = $cell->getDocLibraryForAFInputSetsPrimary();
        } else {
            $this->view->docLibraryForAFInputSetPrimary = null;
        }
        if ($granularity->getCellsWithSocialGenericActions()) {
            $this->view->docLibraryForSocialGenericAction = $cell->getDocLibraryForSocialGenericAction();
        } else {
            $this->view->docLibraryForSocialGenericAction = null;
        }
        if ($granularity->getCellsWithSocialContextActions()) {
            $this->view->docLibraryForSocialContextAction = $cell->getDocLibraryForSocialContextAction();
        } else {
            $this->view->docLibraryForSocialContextAction = null;
        }
    }

    /**
     * Action fournissant la vue de l'historique de la cellule.
     * @Secure("viewCell")
     */
    public function historyAction()
    {
        /** @var Orga_Model_Cell $cell */
        $cell = Orga_Model_Cell::load($this->getParam('idCell'));

        $context = new OrganizationContext($cell->getGranularity()->getOrganization());
        $context->setCell($cell);

        $entries = $this->entryRepository->findLatestForOrganizationContext($context, 100);

        $this->view->assign('idCell', $this->getParam('idCell'));
        $this->view->assign('entries', $entries);
        // Désactivation du layout.
        $this->_helper->layout()->disableLayout();
    }

    /**
     * Action fournissant la vue d'administration d'une cellule.
     * @Secure("editOrganization")
     */
    public function administrationAction()
    {
        // Désactivation du layout.
        $this->_helper->layout()->disableLayout();
        $idCell = $this->getParam('idCell');
        $this->view->idCell = $idCell;
        $cell = Orga_Model_Cell::load($idCell);
        $granularity = $cell->getGranularity();

        if ($granularity->getCellsGenerateDWCubes()) {
            $this->view->isDWCubeUpToDate = $this->etlStructureService->isCellDWCubeUpToDate(
                $cell
            );
        }
    }

}