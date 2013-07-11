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
 * Inpunt Element for repeated sub AF
 * @package    AF
 * @subpackage Input
 */
class AF_Model_Input_SubAF_Repeated extends AF_Model_Input_SubAF
{

    /**
     * Array of SubSet
     * @var AF_Model_InputSet_Sub[]|Collection
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
     * Get the value of the repeated subAF element, it means an array of subSet.
     * @return AF_Model_InputSet_Sub[]
     */
    public function getValue()
    {
        return $this->value->toArray();
    }

    /**
     * @param AF_Model_InputSet_Sub $subSet
     */
    public function addSubSet(AF_Model_InputSet_Sub $subSet)
    {
        if ($this->value->contains($subSet)) {
            $this->value->add($subSet);
        }
    }

    /**
     * @param AF_Model_InputSet_Sub $subSet
     */
    public function removeSubSet(AF_Model_InputSet_Sub $subSet)
    {
        if ($this->value->contains($subSet)) {
            $this->value->removeElement($subSet);
        }
    }

    /**
     * @return int Nombre de champs remplis dans le composant
     */
    public function getNbRequiredFieldsCompleted()
    {
        $nbRequiredFieldsCompleted = 0;
        if (!$this->isHidden()) {
            foreach ($this->value as $subSet) {
                $nbRequiredFieldsCompleted += $subSet->getNbRequiredFieldsCompleted();
            }
        }
        return $nbRequiredFieldsCompleted;
    }

    /**
     * Ajoute une nouvelle répétition d'un sous-formulaire
     * @param string $freeLabel
     */
    public function addRepeatedSubAf($freeLabel = null)
    {
        /** @var $component AF_Model_Component_SubAF_Repeated */
        $component = $this->getComponent();
        $subInputSet = new AF_Model_InputSet_Sub($component->getCalledAF());
        $subInputSet->setFreeLabel($freeLabel);
        $this->addSubSet($subInputSet);
    }

}
