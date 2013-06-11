<?php
/**
 * Classe Classif_Tree_AxisController
 * @author valentin.claras
 * @author sidoine.tardieu
 * @package Classif
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Classe controlleur de tree des axes.
 * @package Classif
 * @subpackage Controller
 */
class Classif_Tree_AxisController extends UI_Controller_Tree
{
    /**
     * Fonction renvoyant la liste des éléments peuplant la Datagrid.
     *
     * Récupération des paramètres de tri de la manière suivante :
     *  $this->order['colonne'|'direction'|'nombreElement'|'startElement'].
     *
     * Récupération des paramètres du filtre de la mnière suivante :
     *  $this->filter['indexDeLaColonne'].
     *
     * Récupération des arguments de la manière suivante :
     *  $this->getParam('nomArgument').
     *
     * Renvoie la liste d'éléments, le nombre total et un message optionnel.
     *
     * @Secure("viewClassif")
     */
    public function getnodesAction()
    {
        if ($this->idNode === null) {
            $queryRootAxes = new Core_Model_Query();
            $queryRootAxes->filter->addCondition(Classif_Model_Axis::QUERY_NARROWER, null,
                Core_Model_Filter::OPERATOR_NULL);
            $queryRootAxes->order->addOrder(Classif_Model_Axis::QUERY_POSITION);
            $axes = Classif_Model_Axis::loadList($queryRootAxes);
        } else {
            $axes = Classif_Model_Axis::loadByRef($this->idNode)->getDirectBroaders();
        }
        foreach ($axes as $axis) {
            $axisLabel = ($axis->getLabel() == '') ? $axis->getRef() : $axis->getLabel();
            $this->addNode($axis->getRef(), $axisLabel, (!$axis->hasdirectBroaders()), null, false, true, true);
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
     * @Secure("editClassif")
     */
    public function addnodeAction()
    {
        /** @var Classif_Service_Axis $axisService */
        $axisService = $this->get('Classif_Service_Axis');

        $ref = $this->getAddElementValue('ref');
        $label = $this->getAddElementValue('label');
        $refParent = $this->getAddElementValue('refParent');

        $refErrors = $axisService->getErrorMessageForNewRef($ref);
        if ($refErrors != null) {
            $this->setAddFormElementErrorMessage('ref', $refErrors);
        }

        if (empty($this->_formErrorMessages)) {
            $axisService->add($ref, $label, $refParent);
            $this->message = __('UI', 'message', 'added');
        }

        $this->send();
    }

    /**
     * Fonction modifiant un node.
     *
     * Récupération de l'id du node.
     *  $this->idNode
     *
     * Renvoie un message d'information.
     *
     * @see getEditElementValue
     * @see setEditElementErrorMessage
     *
     * @Secure("editClassif")
     */
    public function editnodeAction()
    {
        /** @var Classif_Service_Axis $axisService */
        $axisService = $this->get('Classif_Service_Axis');

        $axis = Classif_Model_Axis::loadByRef($this->idNode);
        $newRef = $this->getEditElementValue('ref');
        $newLabel = $this->getEditElementValue('label');
        $newParentRef = $this->getEditElementValue('changeParent');
        if ($newParentRef !== '') {
            $newParentRef = ($newParentRef === ($this->id.'_root')) ? null : $newParentRef;
        }
        switch ($this->getEditElementValue('changeOrder')) {
            case 'first':
                $newPosition = 1;
            break;
            case 'last':
                if ($newParentRef === '') {
                    $newPosition = $axis->getLastEligiblePosition();
                } else if ($newParentRef === null) {
                    $queryRootAxis = new Core_Model_Query();
                    $queryRootAxis->filter->addCondition(Classif_Model_Axis::QUERY_NARROWER, null,
                        Core_Model_Filter::OPERATOR_NULL);
                    $newPosition = Classif_Model_Axis::countTotal($queryRootAxis) + 1;
                } else {
                    $newPosition = count(Classif_Model_Axis::loadByRef($this->idNode)->getDirectBroaders()) + 1;
                }
            break;
            case 'after':
                $refAfter = $this->_form[$this->id.'_changeOrder']['children'][$this->id.'_selectAfter_child']['value'];
                $currentAxisPosition = Classif_Model_Axis::loadByRef($this->idNode)->getPosition();
                $newPosition = Classif_Model_Axis::loadByRef($refAfter)->getPosition();
                if (($newParentRef !== '') || ($currentAxisPosition > $newPosition)) {
                    $newPosition += 1;
                }
            break;
            default:
                $newPosition = null;
                break;
        }

        if ($newRef !== $this->idNode) {
            $refErrors = $axisService->getErrorMessageForNewRef($newRef);
            if ($refErrors != null) {
                $this->setEditFormElementErrorMessage('ref', $refErrors);
            }
        }

        if (empty($this->_formErrorMessages)) {
            $label = null;
            if (($axis->getRef() !== $newRef) && ($axis->getLabel() !== $newLabel)) {
                $label = $axisService->updateRefAndLabel($this->idNode, $newRef, $newLabel);
            } else if ($axis->getLabel() !== $newLabel) {
                $label = $axisService->updateLabel($this->idNode, $newLabel);
            } else if ($axis->getRef() !== $newRef) {
                $label = $axisService->updateRef($this->idNode, $newRef);
            }
            if ($newParentRef !== '') {
                $label = $axisService->updateParent($this->idNode, $newParentRef, $newPosition);
            } else if (($newPosition !== null) && ($axis->getPosition() !== $newPosition)) {
                $label = $axisService->updatePosition($this->idNode, $newPosition);
            }
            if ($label !== null) {
                $this->message = __('UI', 'message', 'updated');
            } else {
                $this->message = __('UI', 'message', 'nullUpdated');
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
     * Renvoie un un tableau contenant les parents possibles de l'élément au format :
     *  array('id' => id, 'label' => label).
     *
     * @Secure("editClassif")
     */
    public function getlistparentsAction()
    {
        $this->addElementList(null, '');
        if (($this->idNode != null) && (Classif_Model_Axis::loadByRef($this->idNode)->getDirectNarrower() !== null)) {
            $this->addElementList($this->id.'_root', __('Classif', 'axis', 'rootParentAxisLabel'));
        }
        $queryOrdered = new Core_Model_Query();
        if (!empty($this->idNode)) {
            $queryOrdered->filter->addCondition(
                Classif_Model_Axis::QUERY_REF,
                $this->idNode,
                Core_Model_Filter::OPERATOR_NOT_EQUAL
            );
        }
        $queryOrdered->order->addOrder(Classif_Model_Axis::QUERY_NARROWER);
        $queryOrdered->order->addOrder(Classif_Model_Axis::QUERY_POSITION);
        foreach (Classif_Model_Axis::loadList($queryOrdered) as $axis) {
            /** @var Classif_Model_Axis $axis */
            $this->addElementList($axis->getRef(), $axis->getLabel());
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
     *
     * @Secure("editClassif")
     */
    public function getlistsiblingsAction()
    {
        $axis = Classif_Model_Axis::loadByRef($this->idNode);
        if (($this->getParam('idParent') != null) && ($this->getParam('idParent') !== $this->id.'_root')) {
            $axisParent = Classif_Model_Axis::loadByRef($this->getParam('idParent'));
            $siblingAxes = $axisParent->getDirectBroaders();
        } else if (($axis->getDirectNarrower() === null) || ($this->getParam('idParent') === $this->id.'_root')) {
            $queryRootAxes = new Core_Model_Query();
            $queryRootAxes->filter->addCondition(Classif_Model_Axis::QUERY_NARROWER, null,
                Core_Model_Filter::OPERATOR_NULL);
            $queryRootAxes->order->addOrder(Classif_Model_Axis::QUERY_POSITION);
            $siblingAxes = Classif_Model_Axis::loadList($queryRootAxes);
        } else {
            $siblingAxes = $axis->getDirectNarrower()->getDirectBroaders();
        }

        foreach ($siblingAxes as $siblingAxis) {
            if ($siblingAxis->getRef() !== $this->idNode) {
                $this->addElementList($siblingAxis->getRef(), $siblingAxis->getLabel());
            }
        }

        $this->send();
    }

    /**
     * Fonction récupérant les informations d'édition pour le formulaire.
     *
     * @Secure("editClassif")
     */
    public function getinfoeditAction()
    {
        $axis = Classif_Model_Axis::loadByRef($this->idNode);
        $this->data['ref'] = $axis->getRef();
        $this->data['label'] = $axis->getLabel();
        $this->send();
    }

    /**
     * Fonction supprimant un node.
     *
     * Récupération de l'id du node.
     *  $this->idNode
     *
     * Renvoie une message d'information.
     *
     * @Secure("editClassif")
     */
    public function deletenodeAction()
    {
        $this->message = __('UI', 'message', 'deleted');

        $this->send();
    }

}