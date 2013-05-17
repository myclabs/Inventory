<?php
/**
 * @author valentin.claras
 * @package Inventory
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Controlleur du Datagrid listant les Cellules dont l'AF est configuré.
 * @author valentin.claras
 * @package Inventory
 * @subpackage Controller
 */
class Inventory_Datagrid_Cell_AfgranularitiesController extends UI_Controller_Datagrid
{
    /**
     * Fonction renvoyant la liste des éléments peuplant la Datagrid.
     *
     * Récupération des paramètres de tris et filtres de la manière suivante :
     *  $this->request.
     *
     * Récupération des arguments de la manière suivante :
     *  $this->_getParam('nomArgument').
     *
     * Renvoie la liste d'éléments, le nombre total et un message optionnel.
     *
     * @Secure("editProject")
     */
    function getelementsAction()
    {
        $project = Inventory_Model_Project::load(array('id' => $this->_getParam('idProject')));
        foreach ($project->getAFGranularities() as $aFGranularities) {
            $data = array();
            $data['index'] = $aFGranularities->getKey()['id'];
            $data['LabelAFConfigOrgaGranularity'] = $aFGranularities->getAFConfigOrgaGranularity()->getRef();
            $data['LabelAFInputOrgaGranularity'] = $aFGranularities->getAFInputOrgaGranularity()->getRef();
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
     *  $this->_getParam('nomArgument').
     *
     * Renvoie une message d'information.
     *
     * @Secure("editProject")
     */
    function addelementAction()
    {
        $project = Inventory_Model_Project::load(array('id' => $this->_getParam('idProject')));

        $aFConfigOrgaGranularityRef = $this->getAddElementValue('LabelAFConfigOrgaGranularity');
        if (empty($aFConfigOrgaGranularityRef)) {
            $this->setAddElementErrorMessage('LabelAFConfigOrgaGranularity', __('UI', 'exceptions', 'requiredField'));
        } else {
            $aFConfigOrgaGranularity = Orga_Model_Granularity::loadByRefAndCube($aFConfigOrgaGranularityRef, $project->getOrgaCube());
        }

        $aFInputOrgaGranularityRef = $this->getAddElementValue('LabelAFInputOrgaGranularity');
        if (empty($aFInputOrgaGranularityRef)) {
            $this->setAddElementErrorMessage('LabelAFInputOrgaGranularity', __('UI', 'formValidation', 'emptyRequiredField'));
        } else {
            $aFInputOrgaGranularity = Orga_Model_Granularity::loadByRefAndCube($aFInputOrgaGranularityRef, $project->getOrgaCube());
        }
        if (!($aFInputOrgaGranularity->isNarrowerThan($aFConfigOrgaGranularity))) {
            $this->setAddElementErrorMessage('LabelAFInputOrgaGranularity', __('Inventory', 'configuration', 'inputGranularityNeedsToBeNarrowerThanConfigGranularity'));
        } else if (!($aFInputOrgaGranularity->isNarrowerThan($project->getOrgaGranularityForInventoryStatus()))) {
            $this->setAddElementErrorMessage('LabelAFInputOrgaGranularity', __('Inventory', 'configuration', 'inputGranularityNeedsToBeNarrowerThanInventoryGranularity'));
        }

        if (empty($this->_addErrorMessages)) {
            $aFGranularities = new Inventory_Model_AFGranularities();
            $aFGranularities->setProject($project);
            $aFGranularities->setAFConfigOrgaGranularity($aFConfigOrgaGranularity);
            $aFGranularities->setAFInputOrgaGranularity($aFInputOrgaGranularity);
            $aFGranularities->save();
            foreach ($aFInputOrgaGranularity->getCells() as $orgaCell) {
                $cellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell($orgaCell);
                $cellDataProvider->setDocBibliographyForAFInputSetPrimary(new Doc_Model_Bibliography());
                $cellDataProvider->save();
            }
        }

        $this->message = __('UI', 'message', 'added');
        $this->send();
    }

    /**
     * Fonction supprimant un élément.
     *
     * Récupération de la ligne à supprimer de la manière suivante :
     *  $this->delete.
     *
     * Récupération des arguments de la manière suivante :
     *  $this->_getParam('nomArgument').
     *
     * Renvoie un message d'information.
     *
     * @Secure("editProject")
     */
    function deleteelementAction()
    {
        $aFGranularities = Inventory_Model_AFGranularities::load(array('id' => $this->delete));
        $aFGranularities->delete();
        foreach ($aFGranularities->getAFInputOrgaGranularity()->getCells() as $orgaCell) {
            $cellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell($orgaCell);
            $cellDataProvider->getDocBibliographyForAFInputSetPrimary()->delete();
            $cellDataProvider->setDocBibliographyForAFInputSetPrimary(null);
            try {
                $cellDataProvider->getAFInputSetPrimary()->delete();
            } catch (Core_Exception_UndefinedAttribute $e) {
                // Pas de InputSetPrimary.
            }
            $cellDataProvider->setAFInputSetPrimary(null);
            $cellDataProvider->save();
        }
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

}