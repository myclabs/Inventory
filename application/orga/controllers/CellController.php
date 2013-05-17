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
     * @Secure("viewOrgaCube")
     */
    public function organisationAction()
    {
        $this->view->idCell = $this->_getParam('idCell');
        $cell = Orga_Model_Cell::load(array('id' => $this->_getParam('idCell')));
        $this->view->isGlobal = $cell->getGranularity()->getRef() === 'global';
        $this->view->activatedTab = $this->_getParam('tab');
        if (empty($this->view->activatedTab)) {
            $this->view->activatedTab = 'childCells';
        }
        if ($this->_hasParam('outputUrl')) {
            $outputUrl = $this->_getParam('outputUrl');
        } else {
            $outputUrl = urlencode('orga/cell/organisation?tab=childCells&');
        }
        $this->view->outputUrl = $outputUrl;
        $this->view->hasChildCells = count($cell->getGranularity()->getNarrowerGranularities()) > 0;

        if ($this->_hasParam('display') && ($this->_getParam('display') === 'render')) {
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
        $this->view->idCell = $this->_getParam('idCell');
        $cell = Orga_Model_Cell::load(array('id' => $this->_getParam('idCell')));
        $this->view->granularities = $cell->getGranularity()->getNarrowerGranularities();

        if (($this->_hasParam('minimize')) && ($this->_getParam('minimize') === false)) {
            $this->view->minimize = false;
        } else {
            $this->view->minimize = true;
        }

        if ($this->_hasParam('datagridConfiguration')) {
            $datagridConfiguration = $this->_getParam('datagridConfiguration');
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
                if ($this->_hasParam('outputUrl')) {
                    $outputUrl = urldecode($this->_getParam('outputUrl'));
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

        if ($this->_hasParam('display') && ($this->_getParam('display') === 'render')) {
            $this->view->display = false;
        } else {
            $this->view->display = true;
        }
    }

    /**
     * Action pour la pertinence des cellules enfants.
     * @Secure("viewOrgaCube")
     */
    public function relevantAction()
    {
        $cell = Orga_Model_Cell::load(array('id' => $this->_getParam('idCell')));
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

        $this->_forward('child', 'cell', 'orga', array('datagridConfiguration' => $listDatagridConfiguration));
    }

    /**
     *
     * @Secure("viewCell")
     */
    public function detailsAction()
    {
        $this->view->headLink()->appendStylesheet('css/orga/navigation.css');
        $this->view->idCell = $this->_getParam('idCell');
        $cell = Orga_Model_Cell::load(array('id' => $this->_getParam('idCell')));
        $this->view->cell = $cell;
        $this->view->activatedTab = $this->_getParam('tab');

        if ($this->_hasParam('viewConfiguration')) {
            $viewConfiguration = $this->_getParam('viewConfiguration');
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

}