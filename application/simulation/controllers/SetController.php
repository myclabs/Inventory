<?php
/**
 * @author valentin.claras
 * @package Simulation
 */

use Core\Annotation\Secure;
use DI\Annotation\Inject;

/**
 * @author valentin.claras
 * @package Simulation
 */
class Simulation_SetController extends Core_Controller
{
    /**
     * @Inject
     * @var Simulation_Service_ETLStructure
     */
    private $etlStructureService;

    /**
     * Redirection sur la liste.
     *
     * @Secure("loggedIn")
     */
    public function indexAction()
    {
        $this->redirect('simulation/set/manage');
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
            /** @var AF_Model_Category $category */
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
        if (!($this->hasParam('idSet'))) {
            $this->redirect('simulation/set/list');
        }

        $set = Simulation_Model_Set::load($this->getParam('idSet'));

        $this->view->idSet = $set->getKey()['id'];
        $this->view->idCube = $set->getDWCube()->getKey()['id'];
        $this->view->setName = $set->getLabel();
        $this->view->aFName = $set->getAF()->getLabel();
        $this->view->isSetDWCubeUpToDate = $this->etlStructureService->isSetDWCubeUpToDate($set);

        $this->view->activatedTab = ($this->hasParam('tab')) ? $this->getParam('tab') : null;
    }

    /**
     * Réinitialise le DW du Set donné.
     *
     * @Secure("loggedIn")
     */
    public function resetdwAction()
    {
        $set = Simulation_Model_Set::load($this->getParam('idSet'));
        $this->etlStructureService->resetSetDWCube($set);
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
        $set = Simulation_Model_Set::load($this->getParam('idSet'));
        $viewConfiguration = new DW_ViewConfiguration();
        $viewConfiguration->setComplementaryPageTitle(' <small>'.$set->getLabel().'</small>');
        $viewConfiguration->setOutputUrl('simulation/set/details?idSet='.$set->getKey()['id'].'&tab=analyse');
        $viewConfiguration->setSaveURL('simulation/set/report?idSet='.$set->getKey()['id'].'&');

        if ($this->hasParam('idReport')) {
            $this->forward('details', 'report', 'dw', array(
                'idReport' => $this->getParam('idReport'),
                'viewConfiguration' => $viewConfiguration
            ));
        } else if ($this->hasParam('idCube')) {
            $this->forward('details', 'report', 'dw', array(
                'idCube' => $this->getParam('idCube'),
                'viewConfiguration' => $viewConfiguration
            ));
        } else {
            throw new Core_Exception_InvalidArgument();
        }
    }

}