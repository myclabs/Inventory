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
     * Controller de la vue des Axis d'un cube.
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
        $this->view->eligibleParents = $cube->getFirstOrderedAxes();

        if ($this->_hasParam('display') && ($this->_getParam('display') === 'render')) {
            $this->view->display = false;
        } else {
            $this->view->display = true;
        }
    }

}