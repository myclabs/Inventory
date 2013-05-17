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
     *  $this->_getParam('nomArgument').
     *
     * @see addNode
     * @Secure("viewOrgaCube")
     */
    public function getnodesAction()
    {
        $cube = Orga_Model_Cube::load(array('id' => $this->_getParam('idCube')));
        if ($this->idNode === null) {
            $axes = $cube->getRootAxes();
        } else {
            $currentAxis = Orga_Model_Axis::loadByRefAndCube($this->idNode, $cube);
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
     * @Secure("editOrgaCube")
     */
    public function addnodeAction()
    {
        $cube = Orga_Model_Cube::load(array('id' => $this->_getParam('idCube')));

        try {
            Core_Tools::checkRef($this->getAddElementValue('addAxis_ref'));
        } catch (Core_Exception_User $e) {
            $this->setAddFormElementErrorMessage('addAxis_ref', $e->getMessage());
        }
        try {
            $existingAxis = Orga_Model_Axis::loadByRefAndCube($this->getAddElementValue('addAxis_ref'), $cube);
            $this->setAddFormElementErrorMessage('addAxis_ref', __('UI', 'formValidation', 'alreadyUsedIdentifier'));
        } catch (Core_Exception_NotFound $e) {
            // La référence n'est pas utilisée.
        }

        if (empty($this->_formErrorMessages)) {
            $axis = new Orga_Model_Axis();
            $axis->setRef($this->getAddElementValue('addAxis_ref'));
            $axis->setLabel($this->getAddElementValue('addAxis_label'));
            if ($this->getAddElementValue('addAxis_contextualizing') === 'contextualizing') {
                $axis->setContextualize(true);
            } else {
                $axis->setContextualize(false);
            }
            $axis->setCube($cube);
            if ($this->getAddElementValue('addAxis_parent') != null) {
                $narrower = Orga_Model_Axis::loadByRefAndCube($this->getAddElementValue('addAxis_parent'), $cube);
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
     * @Secure("viewOrgaCube")
     */
    public function getlistparentsAction()
    {
        $this->addElementList(null, '');

        $cube = Orga_Model_Cube::load(array('id' => $this->_getParam('idCube')));
        foreach ($cube->getFirstOrderedAxes() as $axis) {
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
     * @Secure("viewOrgaCube")
     */
    public function getlistsiblingsAction()
    {
        $cube = Orga_Model_Cube::load(array('id' => $this->_getParam('idCube')));
        $axis = Orga_Model_Axis::loadByRefAndCube($this->idNode, $cube);

        if ($axis->getDirectNarrower() === null) {
            $axes = $cube->getRootAxes();
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
     * @Secure("editOrgaCube")
     */
    public function editnodeAction()
    {
        $cube = Orga_Model_Cube::load($this->_getParam('idCube'));
        $axis = Orga_Model_Axis::loadByRefAndCube($this->idNode, $cube);

        $newRef = $this->getEditElementValue('ref');
        $newLabel = $this->getEditElementValue('label');

        try {
            Core_Tools::checkRef($newRef);
        } catch (Core_Exception_User $e) {
            $this->setEditFormElementErrorMessage('ref', $e->getMessage());
        }

        if ($this->getEditElementValue('contextualizing') === 'contextualizing') {
            $contextualizing = true;
        } else {
            $contextualizing = false;
        }
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
                $newPosition = Orga_Model_Axis::loadByRefAndCube($refAfter, $cube)->getPosition();
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
                $existingAxis = Orga_Model_Axis::loadByRefAndCube($newRef, $cube);
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
     * @Secure("editOrgaCube")
     */
    public function deletenodeAction()
    {
        $cube = Orga_Model_Cube::load($this->_getParam('idCube'));
        $axis = Orga_Model_Axis::loadByRefAndCube($this->idNode, $cube);

        if ($axis->hasGranularities()) {
            throw new Core_Exception_User('Orga', 'axis', 'axisHasGranularities',
                array('AXIS' => $this->_getParam('label')));
        } else if ($axis->hasMembers()) {
            throw new Core_Exception_User('Orga', 'axis', 'axisHasMembers',
                    array('AXIS' => $this->_getParam('label')));
        } else if ($axis->hasDirectBroaders()) {
            throw new Core_Exception_User('Orga', 'axis', 'axisHasDirectBroaders',
                    array('AXIS' => $this->_getParam('label')));
        }
        $axis->delete();
        $this->message = __('UI', 'message', 'deleted', array('AXIS' => $axis->getLabel()));

        $this->send();
    }

    /**
     * Fonction récupérant les informations d'édition pour le formulaire.
     * @Secure("editOrgaCube")
     */
    public function getinfoeditAction()
    {
        $cube = Orga_Model_Cube::load(array('id' => $this->_getParam('idCube')));
        $axis = Orga_Model_Axis::loadByRefAndCube($this->idNode, $cube);

        $this->data['ref'] = $axis->getRef();
        $this->data['label'] = $axis->getLabel();
        $this->data['contextualizing'] = $axis->isContextualizing() ? 'contextualizing' : 'noneContextualizing';

        $this->send();
    }
}
