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
     * Controller de la vue des Axis d'un project.
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
        $this->view->eligibleParents = $project->getFirstOrderedAxes();

        if ($this->hasParam('display') && ($this->getParam('display') === 'render')) {
            $this->_helper->layout()->disableLayout();
            $this->view->display = false;
        } else {
            $this->view->display = true;
        }
    }

}