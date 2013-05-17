<?php
/**
 * Classe Orga_CubeController
 * @author valentin.claras
 * @package    Orga
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Classe controleur de Cube.
 * @package    Orga
 * @subpackage Controller
 */
class Orga_CubeController extends Core_Controller
{
    /**
     * Controller de la vue de la cohérence d'un cube.
     * @Secure("viewOrgaCube")
     */
    public function consistencyAction()
    {
        if ($this->_hasParam('idCell')) {
            $this->view->idCell = $this->_getParam('idCell');
        } else {
            $this->view->idCell = null;
        }
        $this->view->idCube = $this->_getParam('idCube');

        if ($this->_hasParam('display') && ($this->_getParam('display') === 'render')) {
            $this->view->display = false;
        } else {
            $this->view->display = true;
        }
    }

}