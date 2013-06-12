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
                /** @var $action AF_Model_Action_SetValue_Numeric */
                $calcValue = new Calc_Value();
                $calcValue->digitalValue = $this->getParam('numericValue');
                $calcValue->relativeUncertainty = $this->getParam('numericUncertainty');
                $action->setValue($calcValue);
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
        $this->redirect('/af/edit/menu/id/' . $af->getId() . '/onglet/interaction');
    }

}
