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
class Orga_Datagrid_Organization_InputgranularitiesController extends UI_Controller_Datagrid
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
     * @Secure("editOrganization")
     */
    function getelementsAction()
    {
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));
        foreach ($organization->getInputGranularities() as $inputGranularity) {
            $data = array();
            $data['index'] = $inputGranularity->getId();
            $data['inputConfigGranularity'] = $this->cellList($inputGranularity->getInputConfigGranularity()->getRef());
            $data['inputGranularity'] = $this->cellList($inputGranularity->getRef());
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
     * @Secure("editOrganization")
     */
    function addelementAction()
    {
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));

        $inputConfigGranularityRef = $this->getAddElementValue('inputConfigGranularity');
        if (empty($inputConfigGranularityRef)) {
            $this->setAddElementErrorMessage('inputConfigGranularity', __('UI', 'formValidation', 'emptyRequiredField'));
        } else {
            $inputConfigGranularity = Orga_Model_Granularity::loadByRefAndOrganization($inputConfigGranularityRef, $organization);
        }

        $inputGranularityRef = $this->getAddElementValue('inputGranularity');
        if (empty($inputGranularityRef)) {
            $this->setAddElementErrorMessage('inputGranularity', __('UI', 'formValidation', 'emptyRequiredField'));
        } else {
            $inputGranularity = Orga_Model_Granularity::loadByRefAndOrganization($inputGranularityRef, $organization);
        }

        if (empty($this->_addErrorMessages)) {
            if ($inputGranularity->getInputConfigGranularity() !== null) {
                $this->setAddElementErrorMessage(
                    'inputGranularity',
                    __('Orga', 'configuration', 'granularityIsAlreadyAnInputGranularity')
                );
            } else if (!($inputGranularity->isNarrowerThan($inputConfigGranularity))
                && ($inputGranularity !== $inputConfigGranularity)) {
                $this->setAddElementErrorMessage(
                    'inputGranularity',
                    __('Orga', 'configuration', 'inputGranularityNeedsToBeNarrowerThanFormChoiceGranularity')
                );
            }
        }

        if (empty($this->_addErrorMessages)) {
            $inputGranularity->setInputConfigGranularity($inputConfigGranularity);
            $organization->save();
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
     * @Secure("editOrganization")
     */
    function deleteelementAction()
    {
        $inputGranularity = Orga_Model_Granularity::load($this->delete);
        $inputGranularity->setInputConfigGranularity();
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

}