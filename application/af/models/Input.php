<?php
/**
 * @author     matthieu.napoli
 * @author     thibaud.rolland
 * @author     hugo.charbonnier
 * @author     yoann.croizer
 * @package    AF
 * @subpackage Input
 */

/**
 * @package    AF
 * @subpackage Input
 */
abstract class AF_Model_Input extends Core_Model_Entity implements Algo_Model_Input
{

    const QUERY_COMPONENT_REF = 'refComponent';
    const QUERY_INPUTSET = 'inputSet';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var AF_Model_InputSet
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
     * @param AF_Model_InputSet  $inputSet
     * @param AF_Model_Component $component
     */
    public function __construct(AF_Model_InputSet $inputSet, AF_Model_Component $component)
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
     * Retourne true si la saisie donnée est égale à la saisie actuelle
     * @param AF_Model_Input $input
     * @return boolean
     */
    public function equals(AF_Model_Input $input)
    {
        return (get_class($this) === get_class($input))
            && ($this->getRefComponent() === $input->getRefComponent())
            && ($this->isDisabled() === $input->isDisabled())
            && ($this->isHidden() === $input->isHidden());
    }

    /**
     * @param AF_Model_InputSet $inputSet
     */
    public function setInputSet(AF_Model_InputSet $inputSet)
    {
        if ($this->inputSet !== $inputSet) {
            $this->inputSet = $inputSet;
        }
    }

    /**
     * @return AF_Model_InputSet
     */
    public function getInputSet()
    {
        return $this->inputSet;
    }

    /**
     * @return AF_Model_Component|null
     */
    public function getComponent()
    {
        try {
            return AF_Model_Component::loadByRef($this->refComponent, $this->inputSet->getAf());
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
