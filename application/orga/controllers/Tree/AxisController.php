<?php
/**
 * @author valentin.claras
 * @author sidoine.tardieu
 * @package Orga
 */

use Core\Annotation\Secure;

/**
 * Controller de l'abre des axes
 * @package Orga
 * UI_Controller_Datagrid
 */
class Orga_Tree_AxisController extends UI_Controller_Tree
{

    /**
     * Fonction renvoyant la liste des nodes pour un node donnée.
     *
     * Récupération de l'id du node (null pour la racine).
     *  $this->idNode
     *
     * Récupération des arguments de la manière suivante :
     *  $this->getParam('nomArgument').
     *
     * @see addNode
     * @Secure("viewOrganization")
     */
    public function getnodesAction()
    {
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));
        if ($this->idNode === null) {
            $axes = $organization->getRootAxes();
        } else {
            $currentAxis = $organization->getAxisByRef($this->idNode);
            $axes = $currentAxis->getDirectBroaders();
        }
        foreach ($axes as $axis) {
            $this->addNode(
                $axis->getRef(),
               '<b>'.$axis->getLabel().'</b> <i>('.$axis->getRef().')</i>',
               (!$axis->hasDirectBroaders()),
                null,
                false,
                true,
                true
            );
        }

        $this->send();
    }

    /**
     * Fonction ajoutant un node.
     *
     * Renvoie un message d'information.
     *
     * @see getAddElementValue
     * @see setAddElementErrorMessage
     * @Secure("editOrganization")
     */
    public function addnodeAction()
    {
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));

        try {
            Core_Tools::checkRef($this->getAddElementValue('addAxis_ref'));
        } catch (Core_Exception_User $e) {
            $this->setAddFormElementErrorMessage('addAxis_ref', $e->getMessage());
        }
        try {
            $existingAxis = $organization->getAxisByRef($this->getAddElementValue('addAxis_ref'));
            $this->setAddFormElementErrorMessage('addAxis_ref', __('UI', 'formValidation', 'alreadyUsedIdentifier'));
        } catch (Core_Exception_NotFound $e) {
            // La référence n'est pas utilisée.
        }

        if (empty($this->_formErrorMessages)) {
            $axis = new Orga_Model_Axis($organization);
            $axis->setRef($this->getAddElementValue('addAxis_ref'));
            $axis->setLabel($this->getAddElementValue('addAxis_label'));
            if ($this->getAddElementValue('addAxis_contextualizing') === 'contextualizing') {
                $axis->setContextualize(true);
            } else {
                $axis->setContextualize(false);
            }
            if ($this->getAddElementValue('addAxis_parent') != null) {
                $narrower = $organization->getAxisByRef($this->getAddElementValue('addAxis_parent'));
                $narrower->addDirectBroader($axis);
            }
            $axis->save();

            if ($axis->getDirectNarrower() === null) {
                $this->message = __('UI', 'message', 'added',
                                        array('AXIS' => $axis->getLabel())
                                    );
            } else {
                $this->message = __('UI', 'message', 'added',
                                        array(
                                                'AXIS' => $axis->getLabel(),
                                                'PARENT' => $axis->getDirectNarrower()->getLabel()
                                            )
                                    );
            }
        }

        $this->send();
    }

    /**
     * Fonction réupérant la liste des parents possible d'un élément.
     *
     * Récupération de l'id du node.
     *  $this->idNode
     *
     * Renvoie un tableau contenant les parents possibles de l'élément au format :
     *  array('id' => id, 'label' => label).
     * @Secure("viewOrganization")
     */
    public function getlistparentsAction()
    {
        $this->addElementList(null, '');

        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));
        foreach ($organization->getFirstOrderedAxes() as $axis) {
            $this->addElementList($axis->getRef(), ' '.$axis->getLabel());
        }

        $this->send();
    }

    /**
     * Fonction réupérant la liste des frères possible d'un élément.
     *
     * Récupération de l'id du node.
     *  $this->idNode
     *
     * Renvoie un un tableau contenant la fratrie de l'élément au format :
     *  array('id' => id, 'label' => label).
     * @Secure("viewOrganization")
     */
    public function getlistsiblingsAction()
    {
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));
        $axis = $organization->getAxisByRef($this->idNode);

        if ($axis->getDirectNarrower() === null) {
            $axes = $organization->getRootAxes();
        } else {
            $axes = $axis->getDirectNarrower()->getDirectBroaders();
        }
        foreach ($axes as $siblingAxis) {
            if ($siblingAxis !== $axis) {
                $this->addElementList($siblingAxis->getRef(), $siblingAxis->getLabel().' ('.$siblingAxis->getRef().')');
            }
        }

        $this->send();
    }

    /**
     * Fonction modifiant l'ordre d'un élément.
     *
     * Récupération de l'id du node.
     *  $this->idNode
     *
     * Récupération de la nouvelle position ('first' | 'last' | 'after').
     * $this->update['position'].
     *
     * Dans le cas d'une position 'after' on récupère aussi l'élément précédent.
     * $this->update['idElement'].
     *
     * Renvoie un message d'information.
     * @Secure("editOrganization")
     */
    public function editnodeAction()
    {
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));
        $axis = $organization->getAxisByRef($this->idNode);

        $newRef = $this->getEditElementValue('ref');
        $newLabel = $this->getEditElementValue('label');

        try {
            Core_Tools::checkRef($newRef);
        } catch (Core_Exception_User $e) {
            $this->setEditFormElementErrorMessage('ref', $e->getMessage());
        }

//        if ($this->getEditElementValue('contextualizing') === 'contextualizing') {
//            $contextualizing = true;
//        } else {
//            $contextualizing = false;
//        }
        $contextualizing = false;
        switch ($this->getEditElementValue('changeOrder')) {
            case 'first':
                $newPosition = 1;
                break;
            case 'last':
                $newPosition = $axis->getLastEligiblePosition();
                break;
            case 'after':
                $currentAxisPosition = $axis->getPosition();
                $refAfter = $this->_form[$this->id.'_changeOrder']['children'][$this->id.'_selectAfter_child']['value'];
                $newPosition = $organization->getAxisByRef($refAfter)->getPosition();
                if (($currentAxisPosition > $newPosition)) {
                    $newPosition += 1;
                }
                break;
            default:
                $newPosition = null;
                break;
        }

        if ($newRef !== $this->idNode) {
            try {
                $existingAxis = $organization->getAxisByRef($newRef);
                $this->setEditFormElementErrorMessage('ref', __('UI', 'formValidation', 'alreadyUsedIdentifier'));
            } catch (Core_Exception_NotFound $e) {
                // La référence n'est pas utilisée.
            }
        }

        if (empty($this->_formErrorMessages)) {
            if ($axis->getRef() !== $newRef) {
                $axis->setRef($newRef);
            }
            if ($axis->getLabel() !== $newLabel) {
                $axis->setLabel($newLabel);
            }
            if ($axis->isContextualizing() !== $contextualizing) {
                $axis->setContextualize($contextualizing);
            }
            if (($newPosition !== null) && ($axis->getPosition() !== $newPosition)) {
                $axis->setPosition($newPosition);
            }
            $this->message = __('UI', 'message', 'updated', array('AXIS' => $axis->getLabel()));
        }

        $this->send();
    }

    /**
     * Fonction supprimant un node.
     *
     * Récupération de l'id du node.
     *  $this->idNode
     *
     * Renvoie une message d'information.
     * @Secure("editOrganization")
     */
    public function deletenodeAction()
    {
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));
        $axis = $organization->getAxisByRef($this->idNode);

        if ($axis->hasGranularities()) {
            throw new Core_Exception_User('Orga', 'axis', 'axisHasGranularities',
                array('AXIS' => $this->getParam('label')));
        } else if ($axis->hasMembers()) {
            throw new Core_Exception_User('Orga', 'axis', 'axisHasMembers',
                    array('AXIS' => $this->getParam('label')));
        } else if ($axis->hasDirectBroaders()) {
            throw new Core_Exception_User('Orga', 'axis', 'axisHasDirectBroaders',
                    array('AXIS' => $this->getParam('label')));
        }
        $axis->delete();
        $this->message = __('UI', 'message', 'deleted', array('AXIS' => $axis->getLabel()));

        $this->send();
    }

    /**
     * Fonction récupérant les informations d'édition pour le formulaire.
     * @Secure("editOrganization")
     */
    public function getinfoeditAction()
    {
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));
        $axis = $organization->getAxisByRef($this->idNode);

        $this->data['ref'] = $axis->getRef();
        $this->data['label'] = $axis->getLabel();
        $this->data['contextualizing'] = $axis->isContextualizing() ? 'contextualizing' : 'noneContextualizing';

        $this->send();
    }
}
