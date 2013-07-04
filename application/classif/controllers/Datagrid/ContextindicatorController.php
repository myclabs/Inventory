<?php
/**
 * Classe du controller du datagrid des indicateurs contextualisés
 * @author cyril.perraud
 * @package Classif
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Classe du controller du datagrid des indicateurs contextualisés
 * @package Classif
 * @subpackage Controller
 */
class Classif_Datagrid_ContextindicatorController extends UI_Controller_Datagrid
{
    /**
     * Fonction renvoyant la liste des éléments peuplant la Datagrid.
     *
     * @Secure("viewClassif")
     */
    public function getelementsAction()
    {
        $this->request->order->addOrder(
            Classif_Model_Context::QUERY_POSITION,
            Core_Model_Order::ORDER_ASC,
            Classif_Model_Context::getAlias()
        );
        $this->request->order->addOrder(
            Classif_Model_Indicator::QUERY_POSITION,
            Core_Model_Order::ORDER_ASC,
            Classif_Model_Indicator::getAlias()
        );

        foreach (Classif_Model_ContextIndicator::loadList($this->request) as $contextIndicator) {
            $data = array();
            $data['index'] = $contextIndicator->getContext()->getRef().'#'.$contextIndicator->getIndicator()->getRef();
            $data['context'] = $this->cellList($contextIndicator->getContext()->getRef());
            $data['indicator'] = $this->cellList($contextIndicator->getIndicator()->getRef());
            $refAxes = array();
            foreach ($contextIndicator->getAxes() as $axis) {
                $refAxes[] = $axis->getRef();
            }
            $data['axes'] = $this->cellList($refAxes);
            $this->addline($data);
        }
        $this->totalElements = Classif_Model_ContextIndicator::countTotal($this->request);

        $this->send();
    }

    /**
     * Fonction permettant d'ajouter un élément.
     *
     * @Secure("editClassif")
     */
    public function addelementAction()
    {
        $refContext = $this->getAddElementValue('context');
        if (empty($refContext)) {
            $this->setAddElementErrorMessage('context', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        $refIndicator = $this->getAddElementValue('indicator');
        if (empty($refIndicator)) {
            $this->setAddElementErrorMessage('indicator', __('UI', 'formValidation', 'emptyRequiredField'));
        }

        if (empty($this->_addErrorMessages)) {
            $context = Classif_Model_Context::loadByRef($refContext);
            $indicator = Classif_Model_Indicator::loadByRef($refIndicator);
            try {
                $contextIndicator = Classif_Model_ContextIndicator::load(array(
                        'context' => $context,
                        'indicator' => $indicator
                ));
                $this->setAddElementErrorMessage('context', __('Classif', 'contextIndicator', 'ContextIndicatorAlreadyExists'));
                $this->setAddElementErrorMessage('indicator', __('Classif', 'contextIndicator', 'ContextIndicatorAlreadyExists'));
            } catch (Core_Exception_NotFound $e) {
                $contextIndicator = new Classif_Model_ContextIndicator();
                $contextIndicator->setContext($context);
                $contextIndicator->setIndicator($indicator);

                try {
                    if ($this->getAddElementValue('axes') != null) {
                        foreach (explode(',', $this->getAddElementValue('axes')) as $refAxis) {
                            $axis = Classif_Model_Axis::loadByRef($refAxis);
                            $contextIndicator->addAxis($axis);
                        }
                    }

                    $contextIndicator->save();
                    $this->message = __('UI', 'message', 'added');
                } catch (Core_Exception_InvalidArgument $e) {
                    $this->setAddElementErrorMessage('axes', __('Classif', 'contextIndicator', 'AxesMustBeTransverse'));
                }
            }
        }

        $this->send();
    }

    /**
     * Fonction supprimant un élément.
     *
     * @Secure("editClassif")
     */
    public function deleteelementAction()
    {
        list($refContext, $refIndicator) = explode('#', $this->delete);
        $context = Classif_Model_Context::loadByRef($refContext);
        $indicator = Classif_Model_Indicator::loadByRef($refIndicator);
        $contextIndicator = Classif_Model_ContextIndicator::load(array(
                    'context' => $context,
                    'indicator' => $indicator
                ));
        $contextIndicator->delete();
        $this->message = __('UI', 'message', 'deleted', array(
                    'LABELINDICATOR' => $contextIndicator->getIndicator()->getLabel(),
             	    'LABELCONTEXT' => $contextIndicator->getContext()->getLabel()
                ));

        $this->send();
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editClassif")
     */
    public function updateelementAction()
    {
        list($refContext, $refIndicator) = explode('#', $this->update['index']);
        $context = Classif_Model_Context::loadByRef($refContext);
        $indicator = Classif_Model_Indicator::loadByRef($refIndicator);
        $contextIndicator = Classif_Model_ContextIndicator::load(array(
                    'context' => $context,
                    'indicator' => $indicator
                ));

        switch ($this->update['column']) {
            case 'axes':
                if (empty($this->update['value'])) {
                    $listRefAxes = array();
                } else {
                    $listRefAxes = explode(',', $this->update['value']);
                }
                foreach ($contextIndicator->getAxes() as $axis) {
                    if (in_array($axis->getRef(), $listRefAxes)) {
                        unset($listRefAxes[array_search($axis->getRef(), $listRefAxes)]);
                    } else {
                        $contextIndicator->removeAxis($axis);
                    }
                }
                foreach ($listRefAxes as $refAxis) {
                    $axis = Classif_Model_Axis::loadByRef($refAxis);
                    try {
                        $contextIndicator->addAxis($axis);
                    } catch (Core_Exception_InvalidArgument $e) {
                        throw new Core_Exception_User('Classif', 'contextIndicator', 'AxesMustBeTransverse');
                    }
                }
                break;
            default:
                parent::updateelementAction();
                break;
        }

        $this->send();
    }
}