<?php

namespace AF\Domain\AF\Input\SubAF;

use AF\Domain\AF\Component\SubAF\NotRepeatedSubAF as ComponentNotRepeatedSubAF;
use AF\Domain\AF\InputSet\InputSet;
use AF\Domain\AF\Input\SubAFInput;
use AF\Domain\AF\InputSet\SubInputSet;

/**
 * @author matthieu.napoli
 * @author yoann.croizer
 */
class NotRepeatedSubAFInput extends SubAFInput
{
    /**
     * Value of the subAF element which is a SubSet
     * @var \AF\Domain\AF\InputSet\SubInputSet
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
     * @return \AF\Domain\AF\InputSet\SubInputSet
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param \AF\Domain\AF\InputSet\SubInputSet $value
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
