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
     * Controller de la vue des Granularity d'un organization.
     * @Secure("viewOrganization")
     */
    public function manageAction()
    {
        if ($this->hasParam('idCell')) {
            $this->view->idCell = $this->getParam('idCell');
        } else {
            $this->view->idCell = null;
        }
        $this->view->idOrganization = $this->getParam('idOrganization');
        $organization = Orga_Model_Organization::load($this->view->idOrganization);
        $this->view->listAxes = array();
        foreach ($organization->getFirstOrderedAxes() as $axis) {
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
        if ($this->hasParam('idCell')) {
            $viewConfiguration->setOutputUrl('orga/cell/details/idCell/'.$this->getParam('idCell').'/tab/organization');
        } else {
            $viewConfiguration->setOutputUrl('orga/organization/details/idOrganization/'.$this->getParam('idOrganization'));
        }
        $viewConfiguration->setSaveURL('orga/granularity/report/idGranularity/'.$granularity->getId().'/idCell/'.$this->getParam('idCell'));
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

}