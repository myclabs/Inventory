<?php
/**
 * Classe du controller du datagrid des contexts
 * @author cyril.perraud
 * @package Classification
 * @subpackage Controller
 */

use Classification\Domain\ContextIndicator;
use Classification\Domain\Context;
use Core\Annotation\Secure;

/**
 * Classe du controller du datagrid des contexts
 * @package Classification
 * @subpackage Controller
 */
class Classification_Datagrid_ContextController extends UI_Controller_Datagrid
{
    /**
     * Fonction renvoyant la liste des éléments peuplant la Datagrid.
     *
     * @Secure("viewClassif")
     */
    public function getelementsAction()
    {
        foreach (Context::loadList($this->request) as $context) {
            $data = array();
            $data['index'] = $context->getRef();
            $data['label'] = $this->cellText($context->getLabel());
            $data['ref'] = $this->cellText($context->getRef());
            $canUp = !($context->getPosition() === 1);
            $canDown = !($context->getPosition() === $context->getLastEligiblePosition());
            $data['position'] = $this->cellPosition($context->getPosition(), $canUp, $canDown);
            $this->addline($data);
        }
        $this->totalElements = Context::countTotal($this->request);

        $this->send();
    }

    /**
     * Fonction permettant d'ajouter un élément.
     *
     * @Secure("editClassif")
     */
    public function addelementAction()
    {
        $ref = $this->getAddElementValue('ref');
        $label = $this->getAddElementValue('label');

        try {
            Core_Tools::checkRef($ref);
            try {
                Context::loadByRef($ref);
                $this->setAddElementErrorMessage('ref', __('UI', 'formValidation', 'alreadyUsedIdentifier'));
            } catch (Core_Exception_NotFound $e) {
                $context = new Context();
                $context->setRef($ref);
                $context->setLabel($label);
                $context->save();
                $this->message = __('UI', 'message', 'added', array('LABEL' => $context->getLabel()));
            }
        } catch (Core_Exception_User $e) {
            $this->setAddElementErrorMessage('ref', $e->getMessage());
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
        $context = Context::loadByRef($this->delete);

        $queryContextIndicator = new Core_Model_Query();
        $queryContextIndicator->filter->addCondition(ContextIndicator::QUERY_CONTEXT, $context);
        if (ContextIndicator::countTotal($queryContextIndicator) > 0) {
            throw new Core_Exception_User('Classification', 'context', 'ContextIsUsedInContextIndicator');
        }

        $context->delete();
        $this->message = __('UI', 'message', 'deleted', array('LABEL' => $context->getLabel()));
        $this->send();
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editClassif")
     */
    public function updateelementAction()
    {
        $context = Context::loadByRef($this->update['index']);
        switch ($this->update['column']) {
            case 'label':
                $context->setLabel($this->update['value']);
                $this->message = __('UI', 'message', 'updated', array('LABEL' => $context->getLabel()));
                break;
            case 'ref':
                Core_Tools::checkRef($this->update['value']);
                try {
                    if (Context::loadByRef($this->update['value']) !== $context) {
                        throw new Core_Exception_User('UI', 'formValidation', 'alreadyUsedIdentifier');
                    }
                } catch (Core_Exception_NotFound $e) {
                    $context->setRef($this->update['value']);
                    $this->message = __('UI', 'message', 'updated');
                }
                break;
            case 'position' :
                switch ($this->update['value']) {
                    case 'goFirst':
                        $context->setPosition(1);
                        break;
                    case 'goUp':
                        $context->goUp();
                        break;
                    case 'goDown':
                        $context->goDown();
                        break;
                    case 'goLast':
                        $context->setPosition($context->getLastEligiblePosition());
                        break;
                    default :
                        if ($this->update['value'] > $context->getLastEligiblePosition()) {
                            $this->update['value'] = $context->getLastEligiblePosition();
                        }
                        $context->setPosition((int) $this->update['value']);
                        break;
                }
                $this->update['value'] = $context->getPosition();
                $this->message = __('UI', 'message', 'updated');
                break;
            default:
                parent::updateelementAction();
                break;
        }
        $this->data = $this->update['value'];

        $this->send(true);
    }
}
