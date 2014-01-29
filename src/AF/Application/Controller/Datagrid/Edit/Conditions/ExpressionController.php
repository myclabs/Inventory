<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @author  thibaud.rolland
 * @package AF
 */

use AF\Domain\AF;
use AF\Domain\Condition\Condition;
use AF\Domain\Condition\ExpressionCondition;
use Core\Annotation\Secure;
use Doctrine\DBAL\DBALException;
use TEC\Exception\InvalidExpressionException;

/**
 * @package AF
 */
class AF_Datagrid_Edit_Conditions_ExpressionController extends UI_Controller_Datagrid
{

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::getelementsAction()
     * @Secure("editAF")
     */
    public function getelementsAction()
    {
        /** @var $af AF */
        $af = AF::load($this->getParam('id'));
        $query = new Core_Model_Query();
        $query->filter->addCondition(Condition::QUERY_AF, $af);
        /** @var $conditions \AF\Domain\Condition\ExpressionCondition[] */
        $conditions = ExpressionCondition::loadList($query);
        foreach ($conditions as $condition) {
            $data = [];
            $data['index'] = $condition->getId();
            $data['ref'] = $condition->getRef();
            $data['expression'] = $this->cellLongText(
                'af/edit_conditions/popup-condition-expression/id/' . $condition->getId(),
                'af/datagrid_edit_conditions_expression/get-raw-expression/id/' . $condition->getId(),
                __('TEC', 'name', 'expression'),
                'zoom-in'
            );
            $this->addLine($data);
        }
        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::addelementAction()
     * @Secure("editAF")
     */
    public function addelementAction()
    {
        /** @var $af AF */
        $af = AF::load($this->getParam('id'));
        $ref = $this->getAddElementValue('ref');
        if (empty($ref)) {
            $this->setAddElementErrorMessage('ref', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        // Pas d'erreurs
        if (empty($this->_addErrorMessages)) {
            $condition = new ExpressionCondition();
            try {
                $condition->setRef($ref);
            } catch (Core_Exception_User $e) {
                $this->setAddElementErrorMessage('ref', $e->getMessage());
                $this->send();
                return;
            }
            try {
                $condition->setExpression($this->getAddElementValue('expression'));
            } catch (InvalidExpressionException $e) {
                $this->setAddElementErrorMessage(
                    'expression',
                    __('AF', 'configTreatmentMessage', 'invalidExpression') . "<br>" . implode("<br>", $e->getErrors())
                );
                $this->send();
                return;
            }
            $condition->setAf($af);
            $condition->save();
            $af->addCondition($condition);
            try {
                $this->entityManager->flush();
            } catch (Core_ORM_DuplicateEntryException $e) {
                $this->setAddElementErrorMessage('ref', __('UI', 'formValidation', 'alreadyUsedIdentifier'));
                $this->send();
                return;
            }
            $this->message = __('UI', 'message', 'added');
        }
        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::updateelementAction()
     * @Secure("editAF")
     */
    public function updateelementAction()
    {
        /** @var $condition ExpressionCondition */
        $condition = ExpressionCondition::load($this->update['index']);
        $newValue = $this->update['value'];
        switch ($this->update['column']) {
            case 'ref':
                $condition->setRef($newValue);
                $this->data = $condition->getRef();
                break;
            case 'expression':
                try {
                    $condition->setExpression($newValue);
                } catch (InvalidExpressionException $e) {
                    throw new Core_Exception_User(
                        'AF',
                        'configTreatmentMessage',
                        'invalidExpressionWithErrors',
                        ['ERRORS' => implode("<br>", $e->getErrors())]
                    );
                }
                $this->data = $this->cellLongText(
                    'af/edit_conditions/popup-condition-expression/id/' . $condition->getId(),
                    'af/datagrid_edit_conditions_expression/get-raw-expression/id/' . $condition->getId(),
                    __('TEC', 'name', 'expression'),
                    'zoom-in'
                );
                break;
        }
        $condition->save();
        try {
            $this->entityManager->flush();
        } catch (Core_ORM_DuplicateEntryException $e) {
            throw new Core_Exception_User('UI', 'formValidation', 'alreadyUsedIdentifier');
        }
        $this->message = __('UI', 'message', 'updated');
        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::deleteelementAction()
     * @Secure("editAF")
     */
    public function deleteelementAction()
    {
        /** @var $condition Condition */
        $condition = Condition::load($this->getParam('index'));
        try {
            $condition->delete();
            $this->entityManager->flush();
            $this->message = __('UI', 'message', 'deleted');
        } catch (DBALException $e) {
            throw new Core_Exception_User('AF', 'configInteractionMessage', 'conditionUsedByActionDeletionDenied');
        }
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

    /**
     * Fonction permettant de récupérer l'expression
     * @Secure("editAF")
     */
    public function getRawExpressionAction()
    {
        /** @var $condition ExpressionCondition */
        $condition = ExpressionCondition::load($this->getParam('id'));
        $this->data = $condition->getExpression();
        $this->send();
    }
}
