<?php
/**
 * @author  matthieu.napoli
 * @package AF
 */

use Core\Annotation\Secure;

/**
 * @package AF
 */
class AF_Edit_ConditionsController extends Core_Controller
{

    /**
     * Permet de modifier une condition de type elementary avec un popup personalisé.
     * @Secure("editAF")
     */
    public function updateConditionPopupAction()
    {
        $this->view->condition = AF_Model_Condition_Elementary::load($this->getParam('idCondition'));
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
        $condition = AF_Model_Condition_Elementary::load($this->getParam('idCondition'));

        switch (get_class($condition)) {
            case 'AF_Model_Condition_Elementary_Numeric':
                /** @var $condition AF_Model_Condition_Elementary_Numeric */
                $condition->setRelation($this->getParam('relation'));
                // Autorisation de la valeur NULL
                $value = $this->getParam('value');
                if ($value == '') {
                    $value = null;
                }
                $condition->setValue($value);
                break;
            case 'AF_Model_Condition_Elementary_Checkbox':
                /** @var $condition AF_Model_Condition_Elementary_Checkbox */
                $condition->setValue($this->getParam('value'));
                break;
            case 'AF_Model_Condition_Elementary_Select_Single':
                /** @var $condition AF_Model_Condition_Elementary_Select_Single */
                $condition->setRelation($this->getParam('relation'));
                if ($this->getParam('value') != null) {
                    /** @var $option AF_Model_Component_Select_Option */
                    $option = AF_Model_Component_Select_Option::load($this->getParam('value'));
                    $condition->setOption($option);
                } else {
                    $condition->setOption(null);
                }
                break;
            case 'AF_Model_Condition_Elementary_Select_Multi':
                /** @var $condition AF_Model_Condition_Elementary_Select_Multi */
                $condition->setRelation($this->getParam('relation'));
                if ($this->getParam('value') != null) {
                    /** @var $option AF_Model_Component_Select_Option */
                    $option = AF_Model_Component_Select_Option::load($this->getParam('value'));
                    $condition->setOption($option);
                } else {
                    $condition->setOption(null);
                }
                break;
        }
        $condition->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->redirect('/af/edit/menu/id/' . $condition->getAf()->getId() . '/onglet/interaction');
    }

    /**
     * Popup qui affiche une condition expression
     * @Secure("editAF")
     */
    public function popupConditionExpressionAction()
    {
        $this->view->condition = AF_Model_Condition_Expression::load($this->getParam('id'));
        $this->_helper->layout()->disableLayout();
    }

}
