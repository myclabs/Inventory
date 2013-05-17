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
    public function setValue($value)
    {
        if ($value instanceof AF_Model_Component_Select_Option) {
            $this->value = $value->getRef();
        } elseif (null === $value) {
            $this->value = null;
        } else {
            throw new Core_Exception_InvalidArgument('The value parameter must be an instance of '
                                                         . 'AF_Model_Component_Select_Option');
        }
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

}
