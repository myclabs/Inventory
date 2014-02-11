<?php
/**
 * Classe du controller du datagrid des indicateurs contextualisés
 * @author cyril.perraud
 * @package Classification
 * @subpackage Controller
 */

use Classification\Domain\ContextIndicator;
use Classification\Domain\IndicatorAxis;
use Classification\Domain\Context;
use Classification\Domain\Indicator;
use Core\Annotation\Secure;

/**
 * Classe du controller du datagrid des indicateurs contextualisés
 * @package Classification
 * @subpackage Controller
 */
class Classification_Datagrid_ContextindicatorController extends UI_Controller_Datagrid
{
    /**
     * Fonction renvoyant la liste des éléments peuplant la Datagrid.
     *
     * @Secure("viewClassif")
     */
    public function getelementsAction()
    {
        $this->request->order->addOrder(
            Context::QUERY_POSITION,
            Core_Model_Order::ORDER_ASC,
            Context::getAlias()
        );
        $this->request->order->addOrder(
            Indicator::QUERY_POSITION,
            Core_Model_Order::ORDER_ASC,
            Indicator::getAlias()
        );

        foreach (ContextIndicator::loadList($this->request) as $contextIndicator) {
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
        $this->totalElements = ContextIndicator::countTotal($this->request);

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
            $context = Context::loadByRef($refContext);
            $indicator = Indicator::loadByRef($refIndicator);
            try {
                $contextIndicator = ContextIndicator::load(array(
                        'context' => $context,
                        'indicator' => $indicator
                ));
                $this->setAddElementErrorMessage('context', __('Classification', 'contextIndicator', 'ContextIndicatorAlreadyExists'));
                $this->setAddElementErrorMessage('indicator', __('Classification', 'contextIndicator', 'ContextIndicatorAlreadyExists'));
            } catch (Core_Exception_NotFound $e) {
                $contextIndicator = new ContextIndicator();
                $contextIndicator->setContext($context);
                $contextIndicator->setIndicator($indicator);

                try {
                    if ($this->getAddElementValue('axes') != null) {
                        foreach ($this->getAddElementValue('axes') as $refAxis) {
                            $axis = IndicatorAxis::loadByRef($refAxis);
                            $contextIndicator->addAxis($axis);
                        }
                    }

                    $contextIndicator->save();
                    $this->message = __('UI', 'message', 'added');
                } catch (Core_Exception_InvalidArgument $e) {
                    $this->setAddElementErrorMessage('axes', __('Classification', 'contextIndicator', 'axesMustBeTransverse'));
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
        $context = Context::loadByRef($refContext);
        $indicator = Indicator::loadByRef($refIndicator);
        $contextIndicator = ContextIndicator::load(array(
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
        $context = Context::loadByRef($refContext);
        $indicator = Indicator::loadByRef($refIndicator);
        $contextIndicator = ContextIndicator::load(array(
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
                    $axis = IndicatorAxis::loadByRef($refAxis);
                    try {
                        $contextIndicator->addAxis($axis);
                    } catch (Core_Exception_InvalidArgument $e) {
                        throw new Core_Exception_User('Classification', 'contextIndicator', 'axesMustBeTransverse');
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
