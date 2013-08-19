<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @author  yoann.croizer
 * @package Algo
 */

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Unit\UnitAPI;

/**
 * @package Algo
 */
abstract class Algo_Model_Numeric extends Algo_Model_Algo
{

    use Core_Model_Entity_Translatable;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $refContext;

    /**
     * @var string
     */
    protected $refIndicator;

    /**
     * @var Collection|Algo_Model_Index[]
     */
    protected $indexes;


    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->indexes = new ArrayCollection();
    }

    /**
     * Méthode permettant de récupérer l'unité associée à un algorithme.
     * Cette méthode est en particulier utilisée lors du controle de la configuration des algos.
     *
     * @return UnitAPI
     */
    abstract public function getUnit();

    /**
     * Execute and index the value
     * @param Algo_Model_InputSet $inputSet
     * @return Algo_Model_Output
     * @throws Core_Exception_UndefinedAttribute If the numeric algo could not be indexed with an indicator
     */
    public function executeAndIndex(Algo_Model_InputSet $inputSet)
    {
        if (!$this->isIndexed()) {
            throw new Core_Exception_UndefinedAttribute("The numeric algo can't be executed without an indicator");
        }
        $result = $this->execute($inputSet);
        // On récupère les membres de classif
        $classifMembers = [];
        foreach ($this->indexes as $resultIndex) {
            $classifMembers[] = $resultIndex->getClassifMember($inputSet);
        }
        return new Algo_Model_Output($result, $this->getContextIndicator(), $classifMembers, $this->label);
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return Classif_Model_ContextIndicator
     */
    public function getContextIndicator()
    {
        if (!$this->refContext || !$this->refIndicator) {
            return null;
        }
        try {
            return Classif_Model_ContextIndicator::loadByRef($this->refContext, $this->refIndicator);
        } catch (Core_Exception_NotFound $e) {
            return null;
        }
    }

    /**
     * @param Classif_Model_ContextIndicator|null $contextIndicator
     */
    public function setContextIndicator(Classif_Model_ContextIndicator $contextIndicator = null)
    {
        if ($contextIndicator) {
            $this->refContext = $contextIndicator->getContext()->getRef();
            $this->refIndicator = $contextIndicator->getIndicator()->getRef();
        } else {
            $this->refContext = null;
            $this->refIndicator = null;
        }
        // Supprime l'indexation dans l'ancien indicateur
        $this->indexes->clear();
    }

    /**
     * @return Algo_Model_Index[]
     */
    public function getIndexes()
    {
        return $this->indexes->toArray();
    }

    /**
     * Retourne l'index correspondant à l'axe passé en paramètre
     * @param Classif_Model_Axis $axis
     * @return Algo_Model_Index|null
     */
    public function getIndexForAxis(Classif_Model_Axis $axis)
    {
        foreach ($this->indexes as $index) {
            if ($index->getClassifAxis() === $axis) {
                return $index;
            }
        }
        return null;
    }

    /**
     * @param Algo_Model_Index $index
     * @return bool
     */
    public function hasIndex(Algo_Model_Index $index)
    {
        return $this->indexes->contains($index);
    }

    /**
     * @param Algo_Model_Index $index
     */
    public function addIndex(Algo_Model_Index $index)
    {
        if (!$this->hasIndex($index)) {
            $this->indexes->add($index);
        }
    }

    /**
     * @param Algo_Model_Index $resultIndex
     */
    public function removeIndex(Algo_Model_Index $resultIndex)
    {
        if ($this->hasIndex($resultIndex)) {
            $this->indexes->removeElement($resultIndex);
        }
    }

    /**
     * Vide l'indexation de l'algorithme
     */
    public function clearIndexes()
    {
        $this->indexes->clear();
    }

    /**
     * Indicate if the Algo could be indexed by Classif indicator and members
     * @return bool
     */
    public function isIndexed()
    {
        return ($this->getContextIndicator() !== null);
    }

}
