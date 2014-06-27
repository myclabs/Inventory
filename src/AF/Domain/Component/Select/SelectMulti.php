<?php

namespace AF\Domain\Component\Select;

use AF\Domain\Input\Select\SelectMultiInput;
use AF\Domain\Component\Select;
use AF\Domain\InputSet\InputSet;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author matthieu.napoli
 * @author yoann.croizer
 */
class SelectMulti extends Select
{
    /**
     * Constante associée à l'attribut 'type'.
     * Correspond à des cases à cocher à choix multiples.
     * @var integer
     */
    const TYPE_MULTICHECKBOX = 1;

    /**
     * Constante associée à l'attribut 'type'.
     * Correspond à une liste de séléction multiple.
     * @var integer
     */
    const TYPE_MULTISELECT = 2;

    /**
     * @var SelectOption[]|Collection
     */
    protected $defaultValues;

    /**
     * Indicate if the field is a multi choice list or a list of checkBox.
     * @var int
     */
    protected $type = self::TYPE_MULTICHECKBOX;


    public function __construct()
    {
        parent::__construct();
        $this->defaultValues = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function initializeNewInput(InputSet $inputSet)
    {
        $input = $inputSet->getInputForComponent($this);

        if ($input === null) {
            $input = new SelectMultiInput($inputSet, $this);
            $inputSet->setInputForComponent($this, $input);
        }

        $input->setValue($this->defaultValues);
    }

    /**
     * Retourne les options sélectionnées par défaut
     * @return SelectOption[]
     */
    public function getDefaultValues()
    {
        return $this->defaultValues;
    }

    /**
     * @param SelectOption $option
     * @return bool
     */
    public function hasDefaultValue(SelectOption $option)
    {
        return $this->defaultValues->contains($option);
    }

    /**
     * @param SelectOption $option
     */
    public function addDefaultValue(SelectOption $option)
    {
        if (!$this->hasDefaultValue($option)) {
            $this->defaultValues->add($option);
        }
    }

    /**
     * @param SelectOption $option
     */
    public function removeDefaultValue(SelectOption $option)
    {
        if ($this->defaultValues->contains($option)) {
            $this->defaultValues->removeElement($option);
        }
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = (int) $type;
    }
}
