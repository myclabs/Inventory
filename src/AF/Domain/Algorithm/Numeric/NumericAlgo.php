<?php

namespace AF\Domain\Algorithm\Numeric;

use AF\Domain\Algorithm\Algo;
use AF\Domain\Algorithm\InputSet;
use AF\Domain\Algorithm\Index\Index;
use AF\Domain\Algorithm\Output;
use Classification\Domain\IndicatorAxis;
use Classification\Domain\ContextIndicator;
use Core_Exception_NotFound;
use Core_Exception_UndefinedAttribute;
use Core_Model_Entity_Translatable;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Unit\UnitAPI;

/**
 * Algorithme numérique.
 *
 * @author matthieu.napoli
 * @author hugo.charbonnier
 * @author yoann.croizer
 */
abstract class NumericAlgo extends Algo
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
     * @var Collection|Index[]
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
     * @param InputSet $inputSet
     * @return \AF\Domain\Algorithm\Output
     * @throws Core_Exception_UndefinedAttribute If the numeric algo could not be indexed with an indicator
     */
    public function executeAndIndex(InputSet $inputSet)
    {
        if (!$this->isIndexed()) {
            throw new Core_Exception_UndefinedAttribute("The numeric algo can't be executed without an indicator");
        }
        $result = $this->execute($inputSet);
        // On récupère les membres de classification
        $members = [];
        foreach ($this->indexes as $resultIndex) {
            $members[] = $resultIndex->getClassificationMember($inputSet);
        }
        return new Output($result, $this, $members);
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
     * @return \Classification\Domain\ContextIndicator
     */
    public function getContextIndicator()
    {
        if (!$this->refContext || !$this->refIndicator) {
            return null;
        }
        try {
            return ContextIndicator::loadByRef($this->refContext, $this->refIndicator);
        } catch (Core_Exception_NotFound $e) {
            return null;
        }
    }

    /**
     * @param ContextIndicator|null $contextIndicator
     */
    public function setContextIndicator(ContextIndicator $contextIndicator = null)
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
     * @return Index[]
     */
    public function getIndexes()
    {
        return $this->indexes->toArray();
    }

    /**
     * Retourne l'index correspondant à l'axe passé en paramètre
     * @param \Classification\Domain\IndicatorAxis $axis
     * @return Index|null
     */
    public function getIndexForAxis(IndicatorAxis $axis)
    {
        foreach ($this->indexes as $index) {
            if ($index->getClassificationAxis() === $axis) {
                return $index;
            }
        }
        return null;
    }

    /**
     * @param Index $index
     * @return bool
     */
    public function hasIndex(Index $index)
    {
        return $this->indexes->contains($index);
    }

    /**
     * @param Index $index
     */
    public function addIndex(Index $index)
    {
        if (!$this->hasIndex($index)) {
            $this->indexes->add($index);
        }
    }

    /**
     * @param Index $resultIndex
     */
    public function removeIndex(Index $resultIndex)
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
     * Indicate if the Algo could be indexed by Classification indicator and members
     * @return bool
     */
    public function isIndexed()
    {
        return ($this->getContextIndicator() !== null);
    }
}
