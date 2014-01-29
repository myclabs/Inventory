<?php

namespace AF\Domain\Component;

use AF\Domain\Component\Field;
use AF\Domain\InputSet\InputSet;
use AF\Domain\AFGenerationHelper;
use AF\Domain\Input\CheckboxInput;
use UI_Form_Element_Checkbox;

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
        $uiElement = new UI_Form_Element_Checkbox($this->ref);
        $uiElement->setLabel($this->label);
        $uiElement->getElement()->help = $this->help;
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
