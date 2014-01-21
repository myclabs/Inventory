<?php
/**
 * @author     matthieu.napoli
 * @author     yoann.croizer
 * @package    AF
 * @subpackage Input
 */

/**
 * Input Element for text fields
 * @package    AF
 * @subpackage Input
 */
class AF_Model_Input_Text extends AF_Model_Input implements Algo_Model_Input_Numeric
{

    /**
     * @var string
     */
    protected $value;


    /**
     * @param AF_Model_InputSet  $inputSet
     * @param AF_Model_Component $component
     */
    public function __construct(AF_Model_InputSet $inputSet, AF_Model_Component $component)
    {
        parent::__construct($inputSet, $component);
    }

    /**
     * @return int Nombre de champs remplis dans le composant
     */
    public function getNbRequiredFieldsCompleted()
    {
        if (!$this->isHidden()) {
            /** @var $component AF_Model_Component_Text */
            $component = $this->getComponent();
            if ($component && $component->getRequired() && $this->value != null) {
                return 1;
            }
        }
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function hasValue()
    {
        return $this->value != null;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = trim($value);
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

        if ($input instanceof AF_Model_Input_Text) {
            return $this->getValue() == $input->getValue();
        }

        return false;
    }

}
