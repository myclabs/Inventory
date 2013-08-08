<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @author  thibaud.rolland
 * @package AF
 */

use Core\Annotation\Secure;

/**
 * Algoconditionsexpression Controller
 * @package AF
 */
class AF_Datagrid_Edit_Algos_ConditionExpressionController extends UI_Controller_Datagrid
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
        $algos = $af->getAlgos();
        foreach ($algos as $algo) {
            if ($algo instanceof Algo_Model_Condition_Expression) {
                $data = [];
                $data['index'] = $algo->getId();
                $data['ref'] = $algo->getRef();
                $data['expression'] = $this->cellLongText('af/edit_algos/popup-expression/id/' . $algo->getId(),
                                                          'af/datagrid_edit_algos_condition-expression/'
                                                              . 'get-expression/id/' . $algo->getId(),
                                                          __('TEC', 'name', 'expression'),
                                                          'zoom-in');
                $this->addLine($data);
            }
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
            $algo = new Algo_Model_Condition_Expression();
            try {
                $algo->setRef($ref);
            } catch (Core_Exception_User $e) {
                $this->setAddElementErrorMessage('ref', $e->getMessage());
                $this->send();
                return;
            }
            try {
                $algo->setExpression($this->getAddElementValue('expression'));
            } catch (TEC_Model_InvalidExpressionException $e) {
                $this->setAddElementErrorMessage('expression',
                                                 __('AF', 'configTreatmentMessage', 'invalidExpression')
                                                     . "<br>" . implode("<br>", $e->getErrors()));
                $this->send();
                return;
            }
            $algo->save();
            $af->addAlgo($algo);
            $af->save();
            $entityManagers = Zend_Registry::get('EntityManagers');
            try {
                $entityManagers['default']->flush();
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
        /** @var $algo Algo_Model_Condition_Expression */
        $algo = Algo_Model_Condition_Expression::load($this->update['index']);
        $newValue = $this->update['value'];
        switch ($this->update['column']) {
            case 'ref':
                $algo->setRef($newValue);
                $this->data = $algo->getRef();
                break;
            case 'expression':
                try {
                    $algo->setExpression($newValue);
                } catch (TEC_Model_InvalidExpressionException $e) {
                    throw new Core_Exception_User('AF', 'configTreatmentMessage', 'invalidExpressionWithErrors',
                                                  ['ERRORS' => implode("<br>", $e->getErrors())]);
                }
                $this->data = $this->cellLongText('af/edit_algos/popup-expression/id/' . $algo->getId(),
                                                  'af/datagrid_edit_algos_condition-expression/'
                                                      . 'get-expression/id/' . $algo->getId(),
                                                  __('TEC', 'name', 'expression'),
                                                  'zoom-in');
                break;
        }
        $algo->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
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
        /** @var $algo Algo_Model_Condition_Expression */
        $algo = Algo_Model_Condition_Expression::load($this->getParam('index'));
        $algo->delete();
        $algo->getSet()->removeAlgo($algo);
        $algo->getSet()->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

    /**
     * Fonction permettant de récupérer la forme brute de l'expression
     * @Secure("editAF")
     */
    public function getExpressionAction()
    {
        /** @var $algo Algo_Model_Condition_Expression */
        $algo = Algo_Model_Condition_Expression::load($this->getParam('id'));
        $this->data = $algo->getExpression();
        $this->send();
    }

}
