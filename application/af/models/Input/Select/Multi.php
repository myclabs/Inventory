<?php
/**
 * @author     matthieu.napoli
 * @author     yoann.croizer
 * @package    AF
 * @subpackage Input
 */

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @package    AF
 * @subpackage Input
 */
class AF_Model_Input_Select_Multi extends AF_Model_Input implements Algo_Model_Input_StringCollection
{

    /**
     * All selected options ref
     * @var string[]|Collection
     */
    protected $value;


    /**
     * @param AF_Model_InputSet  $inputSet
     * @param AF_Model_Component $component
     */
    public function __construct(AF_Model_InputSet $inputSet, AF_Model_Component $component)
    {
        parent::__construct($inputSet, $component);
        $this->value = new ArrayCollection();
    }

    /**
     * @return string[] Array of selected options ref
     */
    public function getValue()
    {
        return $this->value->toArray();
    }

    /**
     * @param AF_Model_Component_Select_Option[] $value Array of selected options
     */
    public function setValue($value)
    {
        foreach ($value as $option) {
            if ($option instanceof  AF_Model_Component_Select_Option) {
                $this->value->add($option->getRef());
            } else {
                throw new Core_Exception_InvalidArgument('Value must be an array of AF_Model_Component_Select_Option');
            }
        }
    }

    /**
     * @param AF_Model_Input_Select_Multi $input
     */
    public function setValueFrom(AF_Model_Input_Select_Multi $input)
    {
        $this->value->clear();
        foreach ($input->value as $ref) {
            $this->value->add($ref);
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
            if ($component->getRequired() && count($this->value) > 0) {
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
