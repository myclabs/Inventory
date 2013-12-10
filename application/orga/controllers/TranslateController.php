<?php
/**
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Orga_TranslateController
 * @package Orga
 * @subpackage Controller
 */
class Orga_TranslateController extends Core_Controller
{

    /**
     * Liste des libellés des Orga_Model_Organization en mode traduction.
     *
     * @Secure("editOrganizations")
     */
    public function organizationsAction()
    {
    }

    /**
     * Liste des libellés des Orga_Model_Axis en mode traduction.
     *
     * @Secure("editOrganization")
     */
    public function axesAction()
    {
        $this->view->idOrganization = $this->getParam('idOrganization');
    }

    /**
     * Liste des libellés des Orga_Model_Member en mode traduction.
     *
     * @Secure("editOrganization")
     */
    public function membersAction()
    {
        $this->view->idOrganization = $this->getParam('idOrganization');
        $organization = Orga_Model_Organization::load($this->view->idOrganization);
        $this->view->axes = $organization->getLastOrderedAxes();
    }

    /**
     * Liste des libellés des DW_Model_Report issus des DW des Granularity en mode traduction.
     *
     * @Secure("editOrganization")
     */
    public function granularityreportsAction()
    {
        $this->view->idOrganization = $this->getParam('idOrganization');
        $organization = Orga_Model_Organization::load($this->view->idOrganization);
        $this->view->granularities = $organization->getOrderedGranularities();
    }

}