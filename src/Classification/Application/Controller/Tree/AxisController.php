<?php
/**
 * Classe Classification_Tree_AxisController
 * @author valentin.claras
 * @author sidoine.tardieu
 * @package Classification
 * @subpackage Controller
 */

use Classification\Application\Service\IndicatorAxisService;
use Classification\Domain\Axis;
use Core\Annotation\Secure;
use DI\Annotation\Inject;

/**
 * Classe controlleur de tree des axes.
 * @package Classification
 * @subpackage Controller
 */
class Classification_Tree_AxisController extends UI_Controller_Tree
{
    /**
     * @Inject
     * @var IndicatorAxisService
     */
    private $axisService;

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
     * @Secure("viewClassification")
     */
    public function getnodesAction()
    {
        if ($this->idNode === null) {
            $queryRootAxes = new Core_Model_Query();
            $queryRootAxes->filter->addCondition(Axis::QUERY_NARROWER, null,
                Core_Model_Filter::OPERATOR_NULL);
            $queryRootAxes->order->addOrder(Axis::QUERY_POSITION);
            $axes = Axis::loadList($queryRootAxes);
        } else {
            $axes = Axis::loadByRef($this->idNode)->getDirectBroaders();
        }
        foreach ($axes as $axis) {
            $axisLabel = '<b>' . $axis->getLabel() . '</b> <i>('.$axis->getRef().')</i>';
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
     * @Secure("editClassification")
     */
    public function addnodeAction()
    {
        $ref = $this->getAddElementValue('ref');
        $label = $this->getAddElementValue('label');
        $refParent = $this->getAddElementValue('refParent');

        $refErrors = $this->axisService->getErrorMessageForNewRef($ref);
        if ($refErrors != null) {
            $this->setAddFormElementErrorMessage('ref', $refErrors);
        }

        if (empty($this->_formErrorMessages)) {
            $this->axisService->add($ref, $label, $refParent);
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
     * @Secure("editClassification")
     */
    public function editnodeAction()
    {
        $axis = Axis::loadByRef($this->idNode);
        $newLabel = $this->getEditElementValue('label');
        $newRef = $this->getEditElementValue('ref');
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
                    $queryRootAxis->filter->addCondition(Axis::QUERY_NARROWER, null,
                        Core_Model_Filter::OPERATOR_NULL);
                    $newPosition = Axis::countTotal($queryRootAxis) + 1;
                } else {
                    $newPosition = count(Axis::loadByRef($this->idNode)->getDirectBroaders()) + 1;
                }
            break;
            case 'after':
                $refAfter = $this->_form[$this->id.'_changeOrder']['children'][$this->id.'_selectAfter_child']['value'];
                $currentAxisPosition = Axis::loadByRef($this->idNode)->getPosition();
                $newPosition = Axis::loadByRef($refAfter)->getPosition();
                if (($newParentRef !== '') || ($currentAxisPosition > $newPosition)) {
                    $newPosition += 1;
                }
            break;
            default:
                $newPosition = null;
                break;
        }

        if ($newRef !== $this->idNode) {
            $refErrors = $this->axisService->getErrorMessageForNewRef($newRef);
            if ($refErrors != null) {
                $this->setEditFormElementErrorMessage('ref', $refErrors);
            }
        }

        if (empty($this->_formErrorMessages)) {
            $label = null;
            if (($axis->getRef() !== $newRef) && ($axis->getLabel() !== $newLabel)) {
                $label = $this->axisService->updateRefAndLabel($this->idNode, $newRef, $newLabel);
            } else if ($axis->getLabel() !== $newLabel) {
                $label = $this->axisService->updateLabel($this->idNode, $newLabel);
            } else if ($axis->getRef() !== $newRef) {
                $label = $this->axisService->updateRef($this->idNode, $newRef);
            }
            if ($newParentRef !== '') {
                $label = $this->axisService->updateParent($this->idNode, $newParentRef, $newPosition);
            } else if (($newPosition !== null) && ($axis->getPosition() !== $newPosition)) {
                $label = $this->axisService->updatePosition($this->idNode, $newPosition);
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
     * @Secure("editClassification")
     */
    public function getlistparentsAction()
    {
        $this->addElementList(null, '');
        if (($this->idNode != null) && (Axis::loadByRef($this->idNode)->getDirectNarrower() !== null)) {
            $this->addElementList($this->id.'_root', __('Classification', 'axis', 'rootParentAxisLabel'));
        }
        $queryOrdered = new Core_Model_Query();
        if (!empty($this->idNode)) {
            $queryOrdered->filter->addCondition(
                Axis::QUERY_REF,
                $this->idNode,
                Core_Model_Filter::OPERATOR_NOT_EQUAL
            );
        }
        $queryOrdered->order->addOrder(Axis::QUERY_NARROWER);
        $queryOrdered->order->addOrder(Axis::QUERY_POSITION);
        foreach (Axis::loadList($queryOrdered) as $axis) {
            /** @var Axis $axis */
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
     * @Secure("editClassification")
     */
    public function getlistsiblingsAction()
    {
        $axis = Axis::loadByRef($this->idNode);
        if (($this->getParam('idParent') != null) && ($this->getParam('idParent') !== $this->id.'_root')) {
            $axisParent = Axis::loadByRef($this->getParam('idParent'));
            $siblingAxes = $axisParent->getDirectBroaders();
        } else if (($axis->getDirectNarrower() === null) || ($this->getParam('idParent') === $this->id.'_root')) {
            $queryRootAxes = new Core_Model_Query();
            $queryRootAxes->filter->addCondition(Axis::QUERY_NARROWER, null,
                Core_Model_Filter::OPERATOR_NULL);
            $queryRootAxes->order->addOrder(Axis::QUERY_POSITION);
            $siblingAxes = Axis::loadList($queryRootAxes);
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
     * @Secure("editClassification")
     */
    public function getinfoeditAction()
    {
        $axis = Axis::loadByRef($this->idNode);
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
     * @Secure("editClassification")
     */
    public function deletenodeAction()
    {
        $labelNode = $this->axisService->delete($this->idNode);

        $this->message = __('UI', 'message', 'deleted');

        $this->send();
    }

}
