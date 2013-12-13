<?php

use Core\Annotation\Secure;

/**
 * @author valentin.claras
 */
class Orga_TranslateController extends Core_Controller
{
    /**
     * @Secure("editOrganizations")
     */
    public function organizationsAction()
    {
    }

    /**
     * @Secure("editOrganization")
     */
    public function axesAction()
    {
        $this->view->idOrganization = $this->getParam('idOrganization');
    }

    /**
     * @Secure("editOrganization")
     */
    public function membersAction()
    {
        $this->view->idOrganization = $this->getParam('idOrganization');
        $organization = Orga_Model_Organization::load($this->view->idOrganization);
        $this->view->axes = $organization->getLastOrderedAxes();
    }

    /**
     * @Secure("editOrganization")
     */
    public function granularityreportsAction()
    {
        $this->view->idOrganization = $this->getParam('idOrganization');
        $organization = Orga_Model_Organization::load($this->view->idOrganization);
        $this->view->granularities = $organization->getOrderedGranularities();
    }

}