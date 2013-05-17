<?php
/**
 * Classe Orga_GranularityController
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
class Orga_GranularityController extends Core_Controller
{
    /**
     * Controller de la vue des Granularity d'un cube.
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
        $this->view->listAxes = array();
        foreach ($cube->getFirstOrderedAxes() as $axis) {
            $this->view->listAxes[$axis->getRef()] = $axis->getLabel();
        }

        if ($this->_hasParam('display') && ($this->_getParam('display') === 'render')) {
            $this->view->display = false;
        } else {
            $this->view->display = true;
        }
    }

}