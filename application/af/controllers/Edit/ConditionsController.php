<?php
/**
 * @author  matthieu.napoli
 * @package AF
 */

use Core\Annotation\Secure;

/**
 * @package AF
 */
class AF_Edit_ConditionsController extends Core_Controller_Ajax
{

    /**
     * Permet de modifier une condition de type elementary avec un popup personalisé.
     * @Secure("editAF")
     */
    public function updateConditionPopupAction()
    {
        $this->view->condition = AF_Model_Condition_Elementary::load($this->_getParam('idCondition'));
        $this->_helper->layout()->disableLayout();
    }

    /**
     * Permet de modifier une condition de type elementary avec un popup personalisé.
     * AJAX
     * @Secure("editAF")
     */
    public function updateConditionSubmitAction()
    {
        if (!$this->getRequest()->isPost()) {
            throw new Core_Exception_NotFound("Page invalide");
        }
        $condition = AF_Model_Condition_Elementary::load($this->_getParam('idCondition'));

        switch (get_class($condition)) {
            case 'AF_Model_Condition_Elementary_Numeric':
                /** @var $condition AF_Model_Condition_Elementary_Numeric */
                $condition->setRelation($this->_getParam('relation'));
                // Autorisation de la valeur NULL
                $value = $this->_getParam('value');
                if ($value == '') {
                    $value = null;
                }
                $condition->setValue($value);
                break;
            case 'AF_Model_Condition_Elementary_Checkbox':
                /** @var $condition AF_Model_Condition_Elementary_Checkbox */
                $condition->setValue($this->_getParam('value'));
                break;
            case 'AF_Model_Condition_Elementary_Select_Single':
                /** @var $condition AF_Model_Condition_Elementary_Select_Single */
                $condition->setRelation($this->_getParam('relation'));
                if ($this->_getParam('value') != null) {
                    /** @var $option AF_Model_Component_Select_Option */
                    $option = AF_Model_Component_Select_Option::load($this->_getParam('value'));
                    $condition->setOption($option);
                } else {
                    $condition->setOption(null);
                }
                break;
            case 'AF_Model_Condition_Elementary_Select_Multi':
                /** @var $condition AF_Model_Condition_Elementary_Select_Multi */
                $condition->setRelation($this->_getParam('relation'));
                if ($this->_getParam('value') != null) {
                    /** @var $option AF_Model_Component_Select_Option */
                    $option = AF_Model_Component_Select_Option::load($this->_getParam('value'));
                    $condition->setOption($option);
                } else {
                    $condition->setOption(null);
                }
                break;
        }
        $condition->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->_redirect('/af/edit/menu/id/' . $condition->getAf()->getId() . '/onglet/interaction');
    }

    /**
     * Popup qui affiche une condition expression
     * @Secure("editAF")
     */
    public function popupConditionExpressionAction()
    {
        $this->view->condition = AF_Model_Condition_Expression::load($this->_getParam('id'));
        $this->_helper->layout()->disableLayout();
    }

}
