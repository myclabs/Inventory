<?php
/**
 * @author valentin.claras
 * @package Simulation
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Controlleur du Datagrid listant les Simulations d'un Set
 * @author valentin.claras
 * @package Simulation
 * @subpackage Controller
 */
class Simulation_Datagrid_ScenarioController extends UI_Controller_Datagrid
{
    /**
     * Set dont sont issues les Simulations.
     * @var Simulation_Model_Set
     */
    public $set;

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::init()
     */
    public function init()
    {
        parent::init();
        if (!$this->hasParam('idSet')) {
            throw new Core_Exception_InvalidHTTPQuery('L\'id du Set n\'a pas été spécifié.');
        } else {
            $this->set = Simulation_Model_Set::load($this->getParam('idSet'));
        }
    }

    /**
     * Fonction renvoyant la liste des éléments peuplant la Datagrid.
     *
     * Récupération des paramètres de tris et filtres de la manière suivante :
     *  $this->request.
     *
     * Récupération des arguments de la manière suivante :
     *  $this->getParam('nomArgument').
     *
     * Renvoie la liste d'éléments, le nombre total et un message optionnel.
     *
     * @Secure("loggedIn")
     */
    function getelementsAction()
    {
        $this->request->filter->addCondition(Simulation_Model_Scenario::QUERY_SET, $this->set);

        foreach (Simulation_Model_Scenario::loadList($this->request) as $scenario) {
            $data = array();
            $data['index'] = $scenario->getKey()['id'];
            $data['labelScenario'] = $scenario->getLabel();

            try {
                $aFInputSetPrimary = $scenario->getAFInputSetPrimary();
                $percent = $aFInputSetPrimary->getCompletion();
                $progressBarColor = null;
                switch ($aFInputSetPrimary->getStatus()) {
                    case AF_Model_InputSet_Primary::STATUS_FINISHED:
                        $progressBarColor = 'success';
                        break;
                    case AF_Model_InputSet_Primary::STATUS_COMPLETE:
                        $progressBarColor = 'warning';
                        break;
                    case AF_Model_InputSet_Primary::STATUS_CALCULATION_INCOMPLETE:
                        $progressBarColor = 'danger';
                        break;
                    case AF_Model_InputSet_Primary::STATUS_INPUT_INCOMPLETE:
                        $progressBarColor = 'danger';
                        break;
                }
                $data['advancementInput'] = $this->cellPercent($percent, $progressBarColor);
                $data['stateInput'] = $aFInputSetPrimary->getStatus();
            } catch (Core_Exception_UndefinedAttribute $e) {
                $data['advancementInput'] = 0;
                $data['stateInput'] = AF_Model_InputSet_Primary::STATUS_INPUT_INCOMPLETE;
            }
            $data['link'] = $this->cellLink('simulation/scenario/details?idScenario='.$scenario->getKey()['id'], __('UI', 'datagridContent', 'linkLabel'), 'share-alt');
            $this->addLine($data);
        }

        $this->send();

    }

    /**
     * Fonction permettant d'ajouter un élément.
     *
     * Récupération des champs du formulaire de la manière suivante :
     *  $this->add['nomDuChamps'].
     *
     * Récupération des arguments de la manière suivante :
     *  $this->getParam('nomArgument').
     *
     * Renvoie une message d'information.
     *
     * @Secure("loggedIn")
     */
    function addelementAction()
    {
        $labelScenario = $this->getAddElementValue('labelScenario');
        if (empty($labelScenario)) {
            $this->setAddElementErrorMessage('labelScenario', __('UI', 'formValidation', 'emptyRequiredField'));
        }

        if (empty($this->_addErrorMessages)) {
            $scenario = new Simulation_Model_Scenario();
            $scenario->setLabel($labelScenario);
            $scenario->setSet($this->set);
            $scenario->save();
            $this->message = __('UI', 'message', 'added');
        }

        $this->send();
    }

    /**
     * Fonction supprimant un élément.
     *
     * Récupération de la ligne à supprimer de la manière suivante :
     *  $this->delete.
     *
     * Récupération des arguments de la manière suivante :
     *  $this->getParam('nomArgument').
     *
     * Renvoie un message d'information.
     *
     * @Secure("loggedIn")
     */
    function deleteelementAction()
    {
        $scenario = Simulation_Model_Scenario::load($this->delete);
        $scenario->delete();

        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

    /**
     * Fonction modifiant un élément.
     *
     * Récupération de la ligne à modifier de la manière suivante :
     *  $this->update['index'].
     *
     * Récupération de la colonne à modifier de la manière suivante :
     *  $this->update['colonne'].
     *
     * Récupération de la nouvelle valeur à modifier de la manière suivante :
     *  $this->update['valeur'].
     *
     * Récupération des arguments de la manière suivante :
     *  $this->getParam('nomArgument').
     *
     * Renvoie un message d'information et la nouvelle donnée à afficher dans la cellule.
     *
     * @Secure("loggedIn")
     */
    function updateelementAction()
    {
        $scenario = Simulation_Model_Scenario::load($this->update['index']);
        switch ($this->update['column']) {
            case 'labelScenario':
                $scenario->setLabel($this->update['value']);
                $this->data = $scenario->getLabel();
                $this->message = __('UI', 'message', 'updated');
                break;
            default:
                parent::updateelementAction();
        }
        $scenario->save();

        $this->send();
    }


}