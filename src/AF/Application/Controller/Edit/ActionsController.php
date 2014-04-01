<?php

use AF\Domain\Action\SetAlgoValue;
use AF\Domain\Action\SetValue\SetNumericFieldValue;
use AF\Domain\Action\SetValue\SetCheckboxValue;
use AF\Domain\Action\SetValue\Select\SetSelectSingleValue;
use AF\Domain\AF;
use AF\Domain\Action\Action;
use AF\Domain\Component\Select\SelectOption;
use AF\Domain\Algorithm\Algo;
use Core\Annotation\Secure;

/**
 * @author matthieu.napoli
 */
class AF_Edit_ActionsController extends Core_Controller
{
    /**
     * Permet de modifier une action de type setValue avec un popup personalisé.
     * @Secure("editAF")
     */
    public function updateActionPopupAction()
    {
        $this->view->af = AF::load($this->getParam('idAF'));
        $this->view->action = Action::load($this->getParam('idAction'));
        $this->_helper->layout()->disableLayout();
    }

    /**
     * Permet de modifier une action de type setValue avec un popup personalisé.
     * AJAX
     * @Secure("editAF")
     */
    public function updateActionSubmitAction()
    {
        /** @var $af AF */
        $af = AF::load($this->getParam('idAF'));
        if (!$this->getRequest()->isPost()) {
            throw new Core_Exception_NotFound("Page invalide");
        }
        $action = Action::load($this->getParam('idAction'));

        switch (get_class($action)) {
            case SetNumericFieldValue::class:
                $locale = Core_Locale::loadDefault();
                try {
                    $value = $locale->readNumber($this->getParam('numericValue'));
                    $uncertainty = $locale->readInteger($this->getParam('numericUncertainty'));
                } catch(Core_Exception_InvalidArgument $e) {
                    UI_Message::addMessageStatic(__('UI', 'formValidation', 'invalidNumber'), UI_Message::TYPE_WARNING);
                    $this->redirect('/af/edit/menu/id/' . $af->getId() . '/onglet/interaction');
                    return;
                }
                /** @var $action SetNumericFieldValue */
                $action->setValue(new Calc_Value($value, $uncertainty));
                break;
            case SetCheckboxValue::class:
                /** @var $action SetCheckboxValue */
                $action->setChecked($this->getParam('checkboxValue'));
                break;
            case SetSelectSingleValue::class:
                /** @var $action SetSelectSingleValue */
                if ($this->getParam('selectOptionValue') != null) {
                    /** @var $option SelectOption */
                    $option = SelectOption::load($this->getParam('selectOptionValue'));
                    $action->setOption($option);
                } else {
                    $action->setOption(null);
                }
                break;
            case SetAlgoValue::class:
                /** @var $action SetAlgoValue */
                if ($this->getParam('algoSelect') != null) {
                    /** @var $algo Algo */
                    $algo = Algo::load($this->getParam('algoSelect'));
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
