<?php

namespace AF\Domain\Algorithm\Numeric;

use AF\Domain\Algorithm\Algo;
use AF\Domain\Algorithm\InputSet;
use AF\Domain\Algorithm\Index\Index;
use AF\Domain\Algorithm\Output;
use Classification\Domain\Axis;
use Classification\Domain\ContextIndicator;
use Core\Translation\TranslatedString;
use Core_Exception_UndefinedAttribute;
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
    /**
     * @var TranslatedString
     */
    protected $label;

    /**
     * @var ContextIndicator
     */
    protected $contextIndicator;

    /**
     * @var Collection|Index[]
     */
    protected $indexes;

    public function __construct()
    {
        $this->label = new TranslatedString();
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
     * @return Output
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
     * @return TranslatedString
     */
    public function getLabel()
    {
        return $this->label;
    }

    public function setLabel(TranslatedString $label)
    {
        $this->label = $label;
    }

    /**
     * @return ContextIndicator
     */
    public function getContextIndicator()
    {
        return $this->contextIndicator;
    }

    /**
     * @param ContextIndicator|null $contextIndicator
     */
    public function setContextIndicator(ContextIndicator $contextIndicator = null)
    {
        $this->contextIndicator = $contextIndicator;
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
     * @param Axis $axis
     * @return Index|null
     */
    public function getIndexForAxis(Axis $axis)
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
        return ($this->contextIndicator !== null);
    }
}
