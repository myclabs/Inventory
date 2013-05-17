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
     * Controller de la vue des Member d'un cube.
     * @Secure("viewOrgaCube")
     */
    public function manageAction()
    {
        if ($this->_hasParam('idCell')) {
            $this->view->idCell = $this->_getParam('idCell');
        } else {
            $this->view->idCell = null;
        }
        $this->view->idCube = $this->_getParam('idCube');
        $cube = Orga_Model_Cube::load(array('id' => $this->view->idCube));
        $this->view->axes = $cube->getLastOrderedAxes();

        if ($this->_hasParam('display') && ($this->_getParam('display') === 'render')) {
            $this->view->display = false;
        } else {
            $this->view->display = true;
        }

        $this->view->idFilterCell = null;
        $this->view->ambiantGranularity = null;
    }

}