<?php
/**
 * Classe Orga_AxisController
 * @author valentin.claras
 * @author sidoine.tardieu
 * @package    Orga
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Classe controleur de Axis.
 * @package    Orga
 * @subpackage Controller
 */
class Orga_AxisController extends Core_Controller
{
    /**
     * Controller de la vue des Axis d'un organization.
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
        $this->view->eligibleParents = $organization->getFirstOrderedAxes();

        if ($this->hasParam('display') && ($this->getParam('display') === 'render')) {
            $this->_helper->layout()->disableLayout();
            $this->view->display = false;
        } else {
            $this->view->display = true;
        }
    }

}