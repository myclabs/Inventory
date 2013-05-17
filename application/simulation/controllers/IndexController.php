<?php
/**
 * @author valentin.claras
 * @package Simulation
 */

use Core\Annotation\Secure;

/**
 * @author valentin.claras
 * @package Simulation
 */
class Simulation_IndexController extends Core_Controller
{
    /**
     * Redirection sur la liste.
     *
     * @Secure("loggedIn")
     */
    public function indexAction()
    {
        $this->_redirect('simulation/set/list');
    }
}