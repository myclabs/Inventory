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
     * Liste des libellés des Orga_Model_Axis en mode traduction.
     *
     * @Secure("editOrgaCube")
     */
    public function axesAction()
    {
        $this->view->idCube = $this->_getParam('idCube');
    }

    /**
     * Liste des libellés des Orga_Model_Member en mode traduction.
     *
     * @Secure("editOrgaCube")
     */
    public function membersAction()
    {
        $this->view->idCube = $this->_getParam('idCube');
        $cube = Orga_Model_Cube::load(array('id' => $this->view->idCube));
        $this->view->axes = $cube->getLastOrderedAxes();
    }

}