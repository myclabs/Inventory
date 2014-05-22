<?php

namespace AF\Domain\Input\SubAF;

use AF\Domain\Component\SubAF\NotRepeatedSubAF as ComponentNotRepeatedSubAF;
use AF\Domain\InputSet\InputSet;
use AF\Domain\Input\SubAFInput;
use AF\Domain\InputSet\SubInputSet;

/**
 * @author matthieu.napoli
 * @author yoann.croizer
 */
class NotRepeatedSubAFInput extends SubAFInput
{
    /**
     * Value of the subAF element which is a SubSet
     * @var SubInputSet
     */
    protected $value;

    /**
     * @param InputSet                  $inputSet
     * @param ComponentNotRepeatedSubAF $component
     */
    public function __construct(InputSet $inputSet, ComponentNotRepeatedSubAF $component)
    {
        parent::__construct($inputSet, $component);
        $this->value = new SubInputSet($component->getCalledAF());
    }

    /**
     * @return SubInputSet
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param SubInputSet $value
     */
    public function setValue(SubInputSet $value)
    {
        $this->value = $value;
    }

    /**
     * @return int Nombre de champs remplis dans le composant
     */
    public function getNbRequiredFieldsCompleted()
    {
        if (!$this->isHidden()) {
            return $this->value->getNbRequiredFieldsCompleted();
        }
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function hasValue()
    {
        return false;
    }
}
