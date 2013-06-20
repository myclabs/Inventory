<?php
/**
 * Classe Orga_MemberController
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
class Orga_MemberController extends Core_Controller
{
    /**
     * Controller de la vue des Member d'un organization.
     * @Secure("viewOrganization")
     */
    public function manageAction()
    {
        if ($this->hasParam('idCell')) {
            $idCell = $this->getParam('idCell');
            $this->view->idCell = $idCell;
            $cell = Orga_Model_Cell::load($idCell);
        } else {
            $this->view->idCell = null;
            $cell = null;
        }

        $idOrganization = $this->getParam('idOrganization');
        $this->view->idOrganization = $idOrganization;
        $organization = Orga_Model_Organization::load($idOrganization);

        if (($cell !== null) && ($cell->getGranularity()->hasAxes())) {
            $axes = array();
            $idAxes = array();
            foreach ($cell->getMembers() as $members) {
                $axis = $members->getAxis()->getDirectNarrower();
                while ($axis !== null) {
                    if (!(in_array($axis->getId(), $idAxes))) {
                        $axes[] = $axis;
                        $idAxes[] = $axis->getId();
                    }
                    $axis = $axis->getDirectNarrower();
                }
            }
            $this->view->axes = $axes;
        } else {
            $this->view->axes = $organization->getLastOrderedAxes();
        }

        if ($this->hasParam('display') && ($this->getParam('display') === 'render')) {
            $this->_helper->layout()->disableLayout();
            $this->view->display = false;
        } else {
            $this->view->display = true;
        }
    }

}