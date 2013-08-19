<?php
/**
 * @author     matthieu.napoli
 * @author     yoann.croizer
 * @package    AF
 * @subpackage Input
 */

/**
 * Inpunt Element for SelectSingle fields
 * @package    AF
 * @subpackage Input
 */
class AF_Model_Input_Select_Single extends AF_Model_Input implements Algo_Model_Input_String
{

    /**
     * Selected option's ref
     * @var string
     */
    protected $value;


    /**
     * @return string Option ref
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param AF_Model_Component_Select_Option $value
     */
    public function setValue(AF_Model_Component_Select_Option $value = null)
    {
        if ($value) {
            $this->value = $value->getRef();
        } else {
            $this->value = null;
        }
    }

    /**
     * @param AF_Model_Input_Select_Single $input
     */
    public function setValueFrom(AF_Model_Input_Select_Single $input)
    {
        $this->value = $input->value;
    }

    /**
     * @return int Nombre de champs remplis dans le composant
     */
    public function getNbRequiredFieldsCompleted()
    {
        if (!$this->isHidden()) {
            /** @var $component AF_Model_Component_Numeric */
            $component = $this->getComponent();
            if ($component->getRequired() && $this->value != null) {
                return 1;
            }
        }
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function equals(AF_Model_Input $input)
    {
        $equals = parent::equals($input);
        if (! $equals) {
            return false;
        }

        if ($input instanceof AF_Model_Input_Select_Single) {
            return $this->getValue() === $input->getValue();
        }

        return false;
    }

}
