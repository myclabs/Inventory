<?php

namespace AF\Domain\Component;

use AF\Domain\InputSet\InputSet;
use AF\Domain\AFGenerationHelper;
use AF\Domain\Input\CheckboxInput;
use AF\Application\Form\Element\Checkbox as FormCheckbox;

/**
 * @author matthieu.napoli
 * @author yoann.croizer
 */
class Checkbox extends Field
{
    /**
     * The default value indicate if the check box is checked or not.
     * @var bool
     */
    protected $defaultValue = false;

    /**
     * {@inheritdoc}
     */
    public function getUIElement(AFGenerationHelper $generationHelper)
    {
        $uiElement = new FormCheckbox($this->ref);
        $uiElement->setLabel($this->uglyTranslate($this->label));
        $uiElement->getElement()->help = $this->uglyTranslate($this->help);
        if ($generationHelper->isReadOnly()) {
            $uiElement->getElement()->setReadOnly();
        }
        // Remplit avec la valeur saisie
        $input = null;
        if ($generationHelper->getInputSet()) {
            /** @var $input CheckboxInput */
            $input = $generationHelper->getInputSet()->getInputForComponent($this);
        }
        if ($input) {
            $uiElement->getElement()->disabled = $input->isDisabled();
            $uiElement->getElement()->hidden = $input->isHidden();
            $uiElement->setValue($input->getValue());
            // Historique de la valeur
            $uiElement->getElement()->addElement($this->getHistoryComponent($input));
        } else {
            $uiElement->getElement()->disabled = !$this->enabled;
            $uiElement->getElement()->hidden = !$this->visible;
            $uiElement->setValue($this->defaultValue);
        }
        // Actions
        foreach ($this->actions as $action) {
            $uiElement->getElement()->addAction($generationHelper->getUIAction($action));
        }
        return $uiElement;
    }

    /**
     * {@inheritdoc}
     */
    public function initializeNewInput(InputSet $inputSet)
    {
        $input = $inputSet->getInputForComponent($this);

        if ($input === null) {
            $input = new CheckboxInput($inputSet, $this);
            $inputSet->setInputForComponent($this, $input);
        }

        $input->setValue($this->defaultValue);
    }

    /**
     * {@inheritdoc}
     */
    public function getNbRequiredFields(InputSet $inputSet = null)
    {
        return 0;
    }

    /**
     * Get the defaultValue of the checkbox element.
     * @return bool
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Set the value, true for check, false else.
     * @param bool $defaultValue
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = (bool) $defaultValue;
    }
}
