<?php
/**
 * Classe Orga_GranularityController
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
class Orga_GranularityController extends Core_Controller
{
    /**
     * Controller de la vue des Granularity d'un project.
     * @Secure("viewProject")
     */
    public function manageAction()
    {
        if ($this->hasParam('idCell')) {
            $this->view->idCell = $this->getParam('idCell');
        } else {
            $this->view->idCell = null;
        }
        $this->view->idProject = $this->getParam('idProject');
        $project = Orga_Model_Project::load($this->view->idProject);
        $this->view->listAxes = array();
        foreach ($project->getFirstOrderedAxes() as $axis) {
            $this->view->listAxes[$axis->getRef()] = $axis->getLabel();
        }

        if ($this->hasParam('display') && ($this->getParam('display') === 'render')) {
            $this->_helper->layout()->disableLayout();
            $this->view->display = false;
        } else {
            $this->view->display = true;
        }
    }

    /**
     * Affiche le Report de DW d'un Granularity.
     * @Secure("viewReport")
     */
    public function reportAction()
    {
        $granularity = Orga_Model_Granularity::load($this->getParam('idGranularity'));

        $viewConfiguration = new DW_ViewConfiguration();
        $viewConfiguration->setComplementaryPageTitle(' <small>'.$granularity->getLabel().'</small>');
        $viewConfiguration->setOutputUrl('orga/cell/details/idCell/'.$this->getParam('idCell').'/tab/configuration');
        $viewConfiguration->setSaveURL('orga/granularity/report/idGranularity/'.$granularity->getId().'/idCell/'.$this->getParam('idCell'));
        if ($this->hasParam('idReport')) {
            $this->forward('details', 'report', 'dw', array(
                    'idReport' => $this->getParam('idReport'),
                    'viewConfiguration' => $viewConfiguration
                ));
        } else {
            $this->forward('details', 'report', 'dw', array(
                    'idProject' => $this->getParam('idProject'),
                    'viewConfiguration' => $viewConfiguration
                ));
        }
    }

}