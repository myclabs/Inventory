<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @author  thibaud.rolland
 * @package AF
 */

use Core\Annotation\Secure;
use Doctrine\DBAL\DBALException;

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
        /** @var $af AF_Model_AF */
        $af = AF_Model_AF::load($this->getParam('id'));
        $query = new Core_Model_Query();
        $query->filter->addCondition(AF_Model_Condition::QUERY_AF, $af);
        /** @var $conditions AF_Model_Condition_Expression[] */
        $conditions = AF_Model_Condition_Expression::loadList($query);
        foreach ($conditions as $condition) {
            $data = [];
            $data['index'] = $condition->getId();
            $data['ref'] = $condition->getRef();
            $data['expression'] = $this->cellLongText('af/edit_conditions/popup-condition-expression/id/'
                                                          . $condition->getId(),
                                                      'af/datagrid_edit_conditions_expression/get-raw-expression/id/'
                                                          . $condition->getId(),
                                                      __('TEC', 'name', 'expression'),
                                                      'zoom-in');
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
        /** @var $af AF_Model_AF */
        $af = AF_Model_AF::load($this->getParam('id'));
        $ref = $this->getAddElementValue('ref');
        if (empty($ref)) {
            $this->setAddElementErrorMessage('ref', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        // Pas d'erreurs
        if (empty($this->_addErrorMessages)) {
            $condition = new AF_Model_Condition_Expression();
            try {
                $condition->setRef($ref);
            } catch (Core_Exception_User $e) {
                $this->setAddElementErrorMessage('ref', $e->getMessage());
                $this->send();
                return;
            }
            $condition->setAf($af);
            try {
                $condition->setExpression($this->getAddElementValue('expression'));
            } catch (TEC_Model_InvalidExpressionException $e) {
                $this->setAddElementErrorMessage('expression',
                                                 __('AF', 'configTreatmentMessage', 'invalidExpression')
                                                     . "<br>" . implode("<br>", $e->getErrors()));
                $this->send();
                return;
            }
            $condition->save();
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
        /** @var $condition AF_Model_Condition_Expression */
        $condition = AF_Model_Condition_Expression::load($this->update['index']);
        $newValue = $this->update['value'];
        switch ($this->update['column']) {
            case 'ref':
                $condition->setRef($newValue);
                $this->data = $condition->getRef();
                break;
            case 'expression':
                try {
                    $condition->setExpression($newValue);
                } catch (TEC_Model_InvalidExpressionException $e) {
                    throw new Core_Exception_User('AF', 'configTreatmentMessage', 'invalidExpressionWithErrors',
                                                  ['ERRORS' => implode("<br>", $e->getErrors())]);
                }
                $this->data = $this->cellLongText('af/edit_conditions/popup-condition-expression/id/'
                                                      . $condition->getId(),
                                                  'af/datagrid_edit_conditions_expression/get-raw-expression/id/'
                                                      . $condition->getId(),
                                                  __('TEC', 'name', 'expression'),
                                                  'zoom-in');
                break;
        }
        $condition->save();
        $this->entityManager->flush();
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
        /** @var $condition AF_Model_Condition */
        $condition = AF_Model_Condition::load($this->getParam('index'));
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
        /** @var $condition AF_Model_Condition_Expression */
        $condition = AF_Model_Condition_Expression::load($this->getParam('id'));
        $this->data = $condition->getExpression();
        $this->send();
    }

}
