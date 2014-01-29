<?php

namespace AF\Domain\Input;

use AF\Domain\Component\Component;
use AF\Domain\InputSet\InputSet;
use Core_Exception_NotFound;
use Core_Model_Entity;

/**
 * @author matthieu.napoli
 * @author thibaud.rolland
 * @author hugo.charbonnier
 * @author yoann.croizer
 */
abstract class Input extends Core_Model_Entity implements \AF\Domain\Algorithm\Input\Input
{
    const QUERY_COMPONENT_REF = 'refComponent';
    const QUERY_INPUTSET = 'inputSet';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var InputSet
     */
    protected $inputSet;

    /**
     * Composant AF associé
     * @var string
     */
    protected $refComponent;

    /**
     * Indicate if the field is hidden (true) or not (false).
     * @var bool
     */
    protected $hidden = false;

    /**
     * Indicate if the field is disabled (true) or not (false).
     * @var bool
     */
    protected $disabled = false;


    /**
     * @param InputSet  $inputSet
     * @param Component $component
     */
    public function __construct(InputSet $inputSet, Component $component)
    {
        $this->inputSet = $inputSet;
        $this->refComponent = $component->getRef();
        // Ajoute cet input à l'inputset
        $inputSet->setInputForComponent($component, $this);
    }

    /**
     * @return string
     */
    public function getRef()
    {
        return $this->refComponent;
    }

    /**
     * @return int Nombre de champs remplis dans le composant
     */
    abstract public function getNbRequiredFieldsCompleted();

    /**
     * @return bool Est-ce que le champ contient une saisie
     */
    abstract public function hasValue();

    /**
     * Retourne true si la saisie donnée est égale à la saisie actuelle
     * @param Input $input
     * @return boolean
     */
    public function equals(Input $input)
    {
        return (get_class($this) === get_class($input))
        && ($this->getRefComponent() === $input->getRefComponent())
        && ($this->isDisabled() === $input->isDisabled())
        && ($this->isHidden() === $input->isHidden());
    }

    /**
     * @param InputSet $inputSet
     */
    public function setInputSet(InputSet $inputSet)
    {
        if ($this->inputSet !== $inputSet) {
            $this->inputSet = $inputSet;
        }
    }

    /**
     * @return InputSet
     */
    public function getInputSet()
    {
        return $this->inputSet;
    }

    /**
     * @return Component|null
     */
    public function getComponent()
    {
        try {
            return Component::loadByRef($this->refComponent, $this->inputSet->getAf());
        } catch (Core_Exception_NotFound $e) {
            return null;
        }
    }

    /**
     * @return string
     */
    public function getRefComponent()
    {
        return $this->refComponent;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return $this->hidden;
    }

    /**
     * @param bool $isHidden
     */
    public function setHidden($isHidden)
    {
        $this->hidden = (bool) $isHidden;
    }

    /**
     * @return bool
     */
    public function isDisabled()
    {
        return $this->disabled;
    }

    /**
     * @param bool $isDisabled
     */
    public function setDisabled($isDisabled)
    {
        $this->disabled = (bool) $isDisabled;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
