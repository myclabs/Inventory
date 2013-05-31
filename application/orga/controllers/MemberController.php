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
     * Controller de la vue des Member d'un project.
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
        $project = Orga_Model_Project::load(array('id' => $this->view->idProject));
        $this->view->axes = $project->getLastOrderedAxes();

        if ($this->hasParam('display') && ($this->getParam('display') === 'render')) {
            $this->view->display = false;
        } else {
            $this->view->display = true;
        }

        $this->view->idFilterCell = null;
        $this->view->ambiantGranularity = null;
    }

}