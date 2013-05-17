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
class Simulation_SetController extends Core_Controller_Ajax
{
    /**
     * Redirection sur la liste.
     *
     * @Secure("loggedIn")
     */
    public function indexAction()
    {
        $this->_redirect('simulation/set/manage');
    }

    /**
     * Liste les simulations.
     *
     * @Secure("loggedIn")
     */
    public function manageAction()
    {
        $this->view->listAF = array();
        // @todo normalement la liste déroulante des AF devrait être fonction de l'utilisateur.
        foreach (AF_Model_Category::loadList() as $category) {
            foreach ($category->getAFs() as $af) {
                $this->view->listAF[$af->getRef()] = $category->getLabel() . ' - ' . $af->getLabel();
            }
        }
    }

    /**
     * Détail d'un Set.
     *
     * @Secure("loggedIn")
     */
    public function detailsAction()
    {
        if (!($this->_hasParam('idSet'))) {
            $this->_redirect('simulation/set/list');
        }

        $set = Simulation_Model_Set::load(array('id' => $this->_getParam('idSet')));

        $this->view->idSet = $set->getKey()['id'];
        $this->view->idCube = $set->getDWCube()->getKey()['id'];
        $this->view->setName = $set->getLabel();
        $this->view->aFName = $set->getAF()->getLabel();
        $this->view->isSetDWCubeUpToDate = Simulation_Service_ETLStructure::getInstance()->isSetDWCubeUpToDate($set);

        $this->view->activatedTab = ($this->_hasParam('tab')) ? $this->_getParam('tab') : null;
    }

    /**
     * Réinitialise le DW du Set donné.
     *
     * @Secure("loggedIn")
     */
    public function resetdwAction()
    {
        $set = Simulation_Model_Set::load(array('id' => $this->_getParam('idSet')));
        Simulation_Service_ETLStructure::getInstance()->resetSetDWCube($set);
        $this->sendJsonResponse(array('message' => __('DW', 'rebuild', 'confirmationMessage')));
    }

    /**
     * Vue d'un report.
     *
     * @throws Core_Exception_InvalidArgument
     *
     * @Secure("loggedIn")
     */
    public function reportAction()
    {
        $set = Simulation_Model_Set::load(array('id' => $this->_getParam('idSet')));
        $viewConfiguration = new DW_ViewConfiguration();
        $viewConfiguration->setComplementaryPageTitle(' <small>'.$set->getLabel().'</small>');
        $viewConfiguration->setOutputURL('simulation/set/details?idSet='.$set->getKey()['id'].'&tab=analyse');
        $viewConfiguration->setSaveURL('simulation/set/report?idSet='.$set->getKey()['id'].'&');

        if ($this->_hasParam('idReport')) {
            $this->_forward('details', 'report', 'dw', array(
                'idReport' => $this->_getParam('idReport'),
                'viewConfiguration' => $viewConfiguration
            ));
        } else if ($this->_hasParam('idCube')) {
            $this->_forward('details', 'report', 'dw', array(
                'idCube' => $this->_getParam('idCube'),
                'viewConfiguration' => $viewConfiguration
            ));
        } else {
            throw new Core_Exception_InvalidArgument();
        }
    }

}