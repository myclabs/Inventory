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
class Simulation_ScenarioController extends Core_Controller_Ajax
{

    /**
     * Génération du formulaire d'une Simulation.
     *
     * @Secure("loggedIn")
     */
    public function detailsAction()
    {
        if (!($this->hasParam('idScenario'))) {
            if ($this->hasParam('idSet')) {
                $this->redirect('simulation/set/scenarios?idSet='.
                    $this->getParam('idSet'));
            }
            $this->redirect('simulation/set/manage');
        }
        $scenario = Simulation_Model_Scenario::load($this->getParam('idScenario'));


        $viewConfiguration = new AF_ViewConfiguration();
        $viewConfiguration->setPageTitle(
            $scenario->getLabel().
                ' <small> '.$scenario->getSet()->getLabel().' | '.$scenario->getSet()->getAF()->getLabel().'</small>'
        );
        $viewConfiguration->addToActionStack('save', 'scenario', 'simulation', array(
            'idScenario' => $scenario->getKey()['id']
        ));
        $viewConfiguration->addUrlParam('idScenario', $this->getParam('idScenario'));
        $viewConfiguration->setExitUrl('simulation/set/details?idSet='.$scenario->getSet()->getKey()['id'].'&tab=scenario');
        $viewConfiguration->setDisplayConfigurationLink(false);
        $viewConfiguration->addBaseTabs();
        try {
            $viewConfiguration->setIdInputSet($scenario->getAFInputSetPrimary()->getKey()['id']);
        } catch (Core_Exception_UndefinedAttribute $e) {
            // Pas d'inputSetPrimary : nouvelle saisie !
        }

        $this->forward('display', 'af', 'af', array(
            'id' => $scenario->getSet()->getAF()->getKey()['id'],
            'viewConfiguration' => $viewConfiguration
        ));
    }

    /**
     * Fonction de sauvegarde de l'AF.
     *
     * @Secure("loggedIn")
     */
    public function saveAction()
    {
        $scenario = Simulation_Model_Scenario::load($this->getParam('idScenario'));
        $inputSet = $this->getParam('inputSet');

        $scenario->setAFInputSetPrimary($inputSet);

        if ($inputSet->isInputComplete()) {
            Simulation_Service_ETLData::getInstance()->clearDWResultsFromScenario($scenario);
            Simulation_Service_ETLData::getInstance()->populateDWResultsFromScenario($scenario);
        }

        $this->_helper->viewRenderer->setNoRender(true);
    }

}