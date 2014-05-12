<?php

use Core\Annotation\Secure;

/**
 * @author valentin.claras
 */
class Orga_GranularityController extends Core_Controller
{
    /**
     * @Secure("editOrganization")
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
            $this->view->listAxes[$axis->getRef()] = $this->translationHelper->toString($axis->getLabel());
        }

        if ($this->hasParam('display') && ($this->getParam('display') === 'render')) {
            $this->_helper->layout()->disableLayout();
            $this->view->display = false;
        } else {
            $this->view->display = true;
        }
    }

    /**
     * @Secure("editOrganizationAndCells")
     */
    public function viewReportAction()
    {
        $granularity = Orga_Model_Granularity::load($this->getParam('idGranularity'));
        $idOrganization = $granularity->getOrganization()->getId();

        $viewConfiguration = new DW_ViewConfiguration();
        $viewConfiguration->setComplementaryPageTitle(' <small>'.$this->translationHelper->toString($granularity->getLabel()).'</small>');
        $viewConfiguration->setOutputUrl('orga/organization/edit/idOrganization/' . $idOrganization . '/tab/reports/');
        $viewConfiguration->setSaveURL('orga/granularity/view-report/idGranularity/' . $granularity->getId());

        if ($this->hasParam('idReport')) {
            $this->forward('details', 'report', 'dw',
                [
                    'viewConfiguration' => $viewConfiguration
                ]
            );
        } else {
            $this->forward('details', 'report', 'dw',
                [
                    'idCube' => $granularity->getDWCube()->getId(),
                    'viewConfiguration' => $viewConfiguration
                ]
            );
        }
    }

}
