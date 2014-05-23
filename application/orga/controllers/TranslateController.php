<?php

use Core\Annotation\Secure;

/**
 * @author valentin.claras
 */
class Orga_TranslateController extends Core_Controller
{
    /**
     * @Secure("editOrganization")
     */
    public function axesAction()
    {
        $this->view->assign('idOrganization', $this->getParam('idOrganization'));
    }

    /**
     * @Secure("editOrganization")
     */
    public function membersAction()
    {
        $this->view->assign('idOrganization', $this->getParam('idOrganization'));
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));
        $this->view->assign('axes', $organization->getLastOrderedAxes());
    }

    /**
     * @Secure("editOrganization")
     */
    public function granularityReportsAction()
    {
        $this->view->assign('idOrganization', $this->getParam('idOrganization'));
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));
        $this->view->assign('granularities', $organization->getOrderedGranularities());
    }

}