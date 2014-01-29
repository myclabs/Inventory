<?php
/**
 * @author valentin.claras
 * @package Simulation
 * @subpackage Controller
 */

use AF\Domain\AF;
use Core\Annotation\Secure;

/**
 * Controlleur du Datagrid listant les Set
 * @author valentin.claras
 * @package Simulation
 * @subpackage Controller
 */
class Simulation_Datagrid_SetController extends UI_Controller_Datagrid
{

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
        $user = $this->_helper->auth->getLoggedInUser();
        $this->request->filter->addCondition(Simulation_Model_Set::QUERY_USER, $user);

        foreach (Simulation_Model_Set::loadList($this->request) as $set) {
            $data = array();
            $data['index'] = $set->getKey()['id'];
            $data['labelAF'] = $set->getAF()->getRef();
            $data['labelSet'] = $set->getLabel();
            $data['nbPrimarySet'] = count($set->getScenarios());
            $data['detail'] = $this->cellLink('simulation/set/details?idSet='.$set->getKey()['id'].'&tab=scenario');
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
        $user = $this->_helper->auth->getLoggedInUser();

        $refAF = $this->getAddElementValue('labelAF');
        if (empty($refAF)) {
            $this->setAddElementErrorMessage('labelAF', __('UI', 'formValidation', 'emptyRequiredField'));
        }

        if (empty($this->_addErrorMessages)) {
            $set = new Simulation_Model_Set();
            $set->setLabel($this->getAddElementValue('labelSet'));
            $set->setUser($user);
            $set->setAF(AF::loadByRef($refAF));
            $set->save();
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
        $set = Simulation_Model_Set::load($this->delete);
        $set->delete();

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
     *  $this->update['column'].
     *
     * Récupération de la nouvelle valeur à modifier de la manière suivante :
     *  $this->update['value'].
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
        $set = Simulation_Model_Set::load($this->update['index']);
        switch ($this->update['column']) {
            case 'labelSet':
                $set->setLabel($this->update['value']);
                break;
            default:
                parent::updateelementAction();
        }
        $set->save();

        $this->message = __('UI', 'message', 'updated');
        $this->send();
    }


}
