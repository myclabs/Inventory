<?php
/**
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Controlleur du Datagrid listant les Cellules dont l'AF est configuré.
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */
class Orga_Datagrid_Cell_AfgranularitiesController extends UI_Controller_Datagrid
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
     * @Secure("editProject")
     */
    function getelementsAction()
    {
        $project = Orga_Model_Project::load(array('id' => $this->getParam('idProject')));
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
     *  $this->getParam('nomArgument').
     *
     * Renvoie une message d'information.
     *
     * @Secure("editProject")
     */
    function addelementAction()
    {
        $project = Orga_Model_Project::load(array('id' => $this->getParam('idProject')));

        $aFConfigOrgaGranularityRef = $this->getAddElementValue('LabelAFConfigOrgaGranularity');
        if (empty($aFConfigOrgaGranularityRef)) {
            $this->setAddElementErrorMessage('LabelAFConfigOrgaGranularity', __('UI', 'exceptions', 'requiredField'));
        } else {
            $aFConfigOrgaGranularity = Orga_Model_Granularity::loadByRefAndProject($aFConfigOrgaGranularityRef, $project->getOrgaProject());
        }

        $aFInputOrgaGranularityRef = $this->getAddElementValue('LabelAFInputOrgaGranularity');
        if (empty($aFInputOrgaGranularityRef)) {
            $this->setAddElementErrorMessage('LabelAFInputOrgaGranularity', __('UI', 'formValidation', 'emptyRequiredField'));
        } else {
            $aFInputOrgaGranularity = Orga_Model_Granularity::loadByRefAndProject($aFInputOrgaGranularityRef, $project->getOrgaProject());
        }
        if (!($aFInputOrgaGranularity->isNarrowerThan($aFConfigOrgaGranularity))) {
            $this->setAddElementErrorMessage('LabelAFInputOrgaGranularity', __('Orga', 'configuration', 'inputGranularityNeedsToBeNarrowerThanConfigGranularity'));
        } else if (!($aFInputOrgaGranularity->isNarrowerThan($project->getGranularityForInventoryStatus()))) {
            $this->setAddElementErrorMessage('LabelAFInputOrgaGranularity', __('Orga', 'configuration', 'inputGranularityNeedsToBeNarrowerThanOrgaGranularity'));
        }

        if (empty($this->_addErrorMessages)) {
            $aFGranularities = new Orga_Model_AFGranularities();
            $aFGranularities->setProject($project);
            $aFGranularities->setAFConfigOrgaGranularity($aFConfigOrgaGranularity);
            $aFGranularities->setAFInputOrgaGranularity($aFInputOrgaGranularity);
            $aFGranularities->save();
            foreach ($aFInputOrgaGranularity->getCells() as $orgaCell) {
                $cell = Orga_Model_Cell::loadByOrgaCell($orgaCell);
                $cell->setDocBibliographyForAFInputSetPrimary(new Doc_Model_Bibliography());
                $cell->save();
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
     *  $this->getParam('nomArgument').
     *
     * Renvoie un message d'information.
     *
     * @Secure("editProject")
     */
    function deleteelementAction()
    {
        $aFGranularities = Orga_Model_AFGranularities::load(array('id' => $this->delete));
        $aFGranularities->delete();
        foreach ($aFGranularities->getAFInputOrgaGranularity()->getCells() as $orgaCell) {
            $cell = Orga_Model_Cell::loadByOrgaCell($orgaCell);
            $cell->getDocBibliographyForAFInputSetPrimary()->delete();
            $cell->setDocBibliographyForAFInputSetPrimary(null);
            try {
                $cell->getAFInputSetPrimary()->delete();
            } catch (Core_Exception_UndefinedAttribute $e) {
                // Pas de InputSetPrimary.
            }
            $cell->setAFInputSetPrimary(null);
            $cell->save();
        }
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

}