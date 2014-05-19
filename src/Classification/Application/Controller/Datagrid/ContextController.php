<?php

use Classification\Domain\ClassificationLibrary;
use Classification\Domain\ContextIndicator;
use Classification\Domain\Context;
use Core\Annotation\Secure;

class Classification_Datagrid_ContextController extends UI_Controller_Datagrid
{
    /**
     * @Secure("editClassificationLibrary")
     */
    public function getelementsAction()
    {
        /** @var ClassificationLibrary $library */
        $library = ClassificationLibrary::load($this->getParam('library'));

        foreach ($library->getContexts()->toArray() as $context) {
            $data = array();
            $data['index'] = $context->getId();
            $data['label'] = $this->cellTranslatedText($context->getLabel());
            $data['ref'] = $this->cellText($context->getRef());
            $canUp = !($context->getPosition() === 1);
            $canDown = !($context->getPosition() === $context->getLastEligiblePosition());
            $data['position'] = $this->cellPosition($context->getPosition(), $canUp, $canDown);
            $this->addline($data);
        }

        $this->send();
    }

    /**
     * @Secure("editClassificationLibrary")
     */
    public function addelementAction()
    {
        /** @var ClassificationLibrary $library */
        $library = ClassificationLibrary::load($this->getParam('library'));

        $ref = $this->getAddElementValue('ref');
        $label = $this->getAddElementValue('label');

        try {
            Core_Tools::checkRef($ref);
            try {
                $library->getContextByRef($ref);
                $this->setAddElementErrorMessage('ref', __('UI', 'formValidation', 'alreadyUsedIdentifier'));
            } catch (Core_Exception_NotFound $e) {
                $context = new Context($library);
                $context->setRef($ref);
                $this->translator->set($context->getLabel(), $label);
                $context->save();
                $this->message = __('UI', 'message', 'added');
            }
        } catch (Core_Exception_User $e) {
            $this->setAddElementErrorMessage('ref', $e->getMessage());
        }

        $this->send();
    }

    /**
     * @Secure("editClassificationLibrary")
     */
    public function deleteelementAction()
    {
        $context = Context::load($this->delete);

        $queryContextIndicator = new Core_Model_Query();
        $queryContextIndicator->filter->addCondition(ContextIndicator::QUERY_CONTEXT, $context);
        if (ContextIndicator::countTotal($queryContextIndicator) > 0) {
            throw new Core_Exception_User('Classification', 'context', 'ContextIsUsedInContextIndicator');
        }

        $context->delete();
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

    /**
     * @Secure("editClassificationLibrary")
     */
    public function updateelementAction()
    {
        /** @var ClassificationLibrary $library */
        $library = ClassificationLibrary::load($this->getParam('library'));

        $context = Context::load($this->update['index']);
        switch ($this->update['column']) {
            case 'label':
                $this->translator->set($context->getLabel(), $this->update['value']);
                $this->message = __('UI', 'message', 'updated');
                break;
            case 'ref':
                Core_Tools::checkRef($this->update['value']);
                try {
                    if ($library->getContextByRef($this->update['value']) !== $context) {
                        throw new Core_Exception_User('UI', 'formValidation', 'alreadyUsedIdentifier');
                    }
                } catch (Core_Exception_NotFound $e) {
                    $context->setRef($this->update['value']);
                    $this->message = __('UI', 'message', 'updated');
                }
                break;
            case 'position':
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
                    default:
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
