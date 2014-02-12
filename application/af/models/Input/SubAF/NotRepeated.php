<?php
/**
 * @author     matthieu.napoli
 * @author     yoann.croizer
 * @package    AF
 * @subpackage Input
 */

/**
 * Inpunt Element for non repeated sub AF
 * @package    AF
 * @subpackage Input
 */
class AF_Model_Input_SubAF_NotRepeated extends AF_Model_Input_SubAF
{
    /**
     * Value of the subAF element which is a SubSet
     * @var AF_Model_InputSet_Sub
     */
    protected $value;


    /**
     * @param AF_Model_InputSet                    $inputSet
     * @param AF_Model_Component_SubAF_NotRepeated $component
     */
    public function __construct(AF_Model_InputSet $inputSet, AF_Model_Component_SubAF_NotRepeated $component)
    {
        parent::__construct($inputSet, $component);
        $this->value = new AF_Model_InputSet_Sub($component->getCalledAF());
    }

    /**
     * @return AF_Model_InputSet_Sub
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param AF_Model_InputSet_Sub $value
     */
    public function setValue($value)
    {
        if ($value instanceof  AF_Model_InputSet_Sub) {
            $this->value = $value;
        } else {
            throw new Core_Exception_InvalidArgument('Value parameter must be an AF_Model_InputSet_Sub');
        }
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
