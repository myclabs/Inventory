<?php

namespace AF\Domain\Input;

use AF\Domain\Algorithm\Input\BooleanInput;

/**
 * Input Element for checkBox.
 *
 * @author matthieu.napoli
 * @author yoann.croizer
 */
class CheckboxInput extends Input implements BooleanInput, InputErrorField
{
    use InputErrorMessage;

    /**
     * True if the checkbox is checked, else false
     * @var bool
     */
    protected $value = false;

    /**
     * @return int Nombre de champs remplis dans le composant
     */
    public function getNbRequiredFieldsCompleted()
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function hasValue()
    {
        // Impossible de savoir si l'utilisateur a volontairement pas coché la case.
        return true;
    }

    /**
     * Get the value of the checbox element.
     * @return bool
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the value of a checkbox element.
     * @param bool $value set true if the checkbox is checked, else set false
     */
    public function setValue($value)
    {
        $this->value = (boolean) $value;
    }

    /**
     * {@inheritdoc}
     */
    public function equals(Input $input)
    {
        $equals = parent::equals($input);
        if (!$equals) {
            return false;
        }

        if ($input instanceof CheckboxInput) {
            return $this->getValue() === $input->getValue();
        }

        return false;
    }
}
