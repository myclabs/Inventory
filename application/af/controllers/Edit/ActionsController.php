<?php
/**
 * @author  matthieu.napoli
 * @package AF
 */

use Core\Annotation\Secure;

/**
 * @package AF
 */
class AF_Edit_ActionsController extends Core_Controller
{

    /**
     * Permet de modifier une action de type setValue avec un popup personalisé.
     * @Secure("editAF")
     */
    public function updateActionPopupAction()
    {
        $this->view->action = AF_Model_Action::load($this->getParam('idAction'));
        $this->_helper->layout()->disableLayout();
    }

    /**
     * Permet de modifier une action de type setValue avec un popup personalisé.
     * AJAX
     * @Secure("editAF")
     */
    public function updateActionSubmitAction()
    {
        /** @var $af AF_Model_AF */
        $af = AF_Model_AF::load($this->getParam('idAf'));
        if (!$this->getRequest()->isPost()) {
            throw new Core_Exception_NotFound("Page invalide");
        }
        $action = AF_Model_Action::load($this->getParam('idAction'));

        switch (get_class($action)) {
            case 'AF_Model_Action_SetValue_Numeric':
                $locale = Core_Locale::loadDefault();
                try {
                    $value = $locale->readNumber($this->getParam('numericValue'));
                    $uncertainty = $locale->readInteger($this->getParam('numericUncertainty'));
                } catch(Core_Exception_InvalidArgument $e) {
                    UI_Message::addMessageStatic(__('UI', 'formValidation', 'invalidNumber'), UI_Message::TYPE_ALERT);
                    $this->redirect('/af/edit/menu/id/' . $af->getId() . '/onglet/interaction');
                    return;
                }
                /** @var $action AF_Model_Action_SetValue_Numeric */
                $action->setValue(new Calc_Value($value, $uncertainty));
                break;
            case 'AF_Model_Action_SetValue_Checkbox':
                /** @var $action AF_Model_Action_SetValue_Checkbox */
                $action->setChecked($this->getParam('checkboxValue'));
                break;
            case 'AF_Model_Action_SetValue_Select_Single':
                /** @var $action AF_Model_Action_SetValue_Select_Single */
                if ($this->getParam('selectOptionValue') != null) {
                    /** @var $option AF_Model_Component_Select_Option */
                    $option = AF_Model_Component_Select_Option::load($this->getParam('selectOptionValue'));
                    $action->setOption($option);
                } else {
                    $action->setOption(null);
                }
                break;
            case 'AF_Model_Action_SetAlgoValue':
                /** @var $action AF_Model_Action_SetAlgoValue */
                if ($this->getParam('algoSelect') != null) {
                    /** @var $algo Algo_Model_Algo */
                    $algo = Algo_Model_Algo::load($this->getParam('algoSelect'));
                    $action->setAlgo($algo);
                } else {
                    $action->setAlgo(null);
                }
                break;
        }
        $action->save();
        $this->entityManager->flush();
        UI_Message::addMessageStatic(__('UI', 'message', 'updated'), UI_Message::TYPE_SUCCESS);
        $this->redirect('/af/edit/menu/id/' . $af->getId() . '/onglet/interaction');
    }

}
