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
     * Liste des libellés des Orga_Model_Project en mode traduction.
     *
     * @Secure("editProjects")
     */
    public function projectsAction()
    {
    }

    /**
     * Liste des libellés des Orga_Model_Axis en mode traduction.
     *
     * @Secure("editProject")
     */
    public function axesAction()
    {
        $this->view->idProject = $this->getParam('idProject');
    }

    /**
     * Liste des libellés des Orga_Model_Member en mode traduction.
     *
     * @Secure("editProject")
     */
    public function membersAction()
    {
        $this->view->idProject = $this->getParam('idProject');
        $project = Orga_Model_Project::load($this->view->idProject);
        $this->view->axes = $project->getLastOrderedAxes();
    }

}