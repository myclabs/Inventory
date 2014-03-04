<?php

namespace AF\Domain\Output;

use AF\Domain\InputSet\InputSet;
use AF\Domain\InputSet\PrimaryInputSet;
use AF\Domain\Algorithm\Output;
use Calc_Calculation;
use Calc_Calculation_Value;
use Classif_Model_ContextIndicator;
use Classif_Model_Indicator;
use Core_Model_Entity;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author matthieu.napoli
 */
class OutputSet extends Core_Model_Entity
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var \AF\Domain\InputSet\PrimaryInputSet|null
     */
    protected $inputSet;

    /**
     * @var OutputElement[]|Collection
     */
    protected $elements;

    /**
     * @var OutputTotal[]|Collection
     */
    protected $totals;


    public function __construct()
    {
        $this->elements = new ArrayCollection();
        $this->totals = new ArrayCollection();
    }

    /**
     * @param \AF\Domain\InputSet\InputSet $inputSet
     * @param Output[] $algoOutputs
     */
    public function addAlgoOutputs(InputSet $inputSet, array $algoOutputs)
    {
        foreach ($algoOutputs as $algoOutput) {
            $this->elements->add(new OutputElement($this, $algoOutput, $inputSet));
        }
    }

    /**
     * @param OutputSet $outputSet
     */
    public function mergeOutputSet(OutputSet $outputSet)
    {
        foreach ($outputSet->getElements() as $outputElement) {
            $this->elements->add($outputElement);
            $outputElement->setOutputSet($this);
        }
    }

    /**
     * @return OutputElement[]
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * Calcule le total des outputs elements classé par indicateur de classif
     */
    public function calculateTotals()
    {
        // On vide les anciens totaux
        $this->totals->clear();

        $classifIndicator = [];
        // Pour chaque outputElement
        foreach ($this->elements as $outputElement) {
            // On rempli un tableau de tableau indexé par l'identifiant de l'indicateur
            // qui le contient
            $idIndicator = $outputElement->getContextIndicator()->getIndicator()->getId();
            $classifIndicator[$idIndicator][] = $outputElement;
        }

        // Pour chaque index d'indicateur différent
        foreach ($classifIndicator as $idIndicator => $outputElements) {
            /** @var $outputElements OutputElement[] */
            $calcValue = new Calc_Calculation_Value();
            // On fait la somme des outputElement
            foreach ($outputElements as $outputElement) {
                $calcValue->addComponents($outputElement->getValue(), Calc_Calculation::SUM);
            }
            $calcValue->setOperation(Calc_Calculation::ADD_OPERATION);
            /** @var $indicator Classif_Model_Indicator */
            $indicator = Classif_Model_Indicator::load($idIndicator);
            // On créer l'outputTotal et on le sauvegarde
            $total = new OutputTotal($this);
            $total->setClassifIndicator($indicator);
            $total->setValue($calcValue->calculate());

            $this->totals->add($total);
        }
    }

    /**
     * @return OutputTotal[]
     */
    public function getTotals()
    {
        return $this->totals;
    }

    /**
     * Return the OutputTotal for a given Classif_Model_Indicator
     * @param Classif_Model_Indicator $indicator
     * @return OutputTotal|null
     */
    public function getTotalByIndicator(Classif_Model_Indicator $indicator)
    {
        foreach ($this->totals as $outputTotal) {
            if ($outputTotal->getClassifIndicator() === $indicator) {
                return $outputTotal;
            }
        }
        return null;
    }

    /**
     * @return Classif_Model_Indicator[]
     */
    public function getIndicators()
    {
        $indicators = new ArrayCollection();
        foreach ($this->elements as $outputElement) {
            $indicator = $outputElement->getContextIndicator()->getIndicator();
            if (!$indicators->contains($indicator)) {
                $indicators->add($indicator);
            }
        }
        return $indicators;
    }

    /**
     * @param Classif_Model_Indicator $indicator
     * @return Classif_Model_ContextIndicator[]
     */
    public function getContextIndicatorsByIndicator(Classif_Model_Indicator $indicator)
    {
        $contextIndicators = new ArrayCollection();
        foreach ($this->elements as $outputElement) {
            $currentContextIndicator = $outputElement->getContextIndicator();
            if ($currentContextIndicator->getIndicator() === $indicator
                && !$contextIndicators->contains($currentContextIndicator)
            ) {
                $contextIndicators->add($currentContextIndicator);
            }
        }
        return $contextIndicators;
    }

    /**
     * @return PrimaryInputSet
     */
    public function getInputSet()
    {
        return $this->inputSet;
    }

    /**
     * @param PrimaryInputSet $inputSet
     */
    public function setInputSet(PrimaryInputSet $inputSet)
    {
        $this->inputSet = $inputSet;
    }
}
