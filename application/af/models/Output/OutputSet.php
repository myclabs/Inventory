<?php
/**
 * @author     matthieu.napoli
 * @package    AF
 * @subpackage Output
 */

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @package    AF
 * @subpackage Output
 */
class AF_Model_Output_OutputSet extends Core_Model_Entity
{

    /**
     * @var int
     */
    protected $id;

    /**
     * @var AF_Model_InputSet_Primary|null
     */
    protected $inputSet;

    /**
     * @var AF_Model_Output_Element[]|Collection
     */
    protected $elements;

    /**
     * @var AF_Model_Output_Total[]|Collection
     */
    protected $totals;


    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->elements = new ArrayCollection();
        $this->totals = new ArrayCollection();
    }

    /**
     * @param AF_Model_InputSet   $inputSet
     * @param Algo_Model_Output[] $algoOutputs
     */
    public function addAlgoOutputs(AF_Model_InputSet $inputSet, array $algoOutputs)
    {
        foreach ($algoOutputs as $algoOutput) {
            $this->elements->add(new AF_Model_Output_Element($this, $algoOutput, $inputSet));
        }
    }

    /**
     * @param AF_Model_Output_OutputSet $outputSet
     */
    public function mergeOutputSet(AF_Model_Output_OutputSet $outputSet)
    {
        foreach ($outputSet->getElements() as $outputElement) {
            $this->elements->add($outputElement);
            $outputElement->setOutputSet($this);
        }
    }

    /**
     * @return AF_Model_Output_Element[]
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
            /** @var $outputElements AF_Model_Output_Element[] */
            $calcValue = new Calc_Calculation_Value();
            // On fait la somme des outputElement
            foreach ($outputElements as $outputElement) {
                $calcValue->addComponents($outputElement->getValue(), Calc_Calculation::SUM);
            }
            $calcValue->setOperation(Calc_Calculation::ADD_OPERATION);
            /** @var $indicator Classif_Model_Indicator */
            $indicator = Classif_Model_Indicator::load($idIndicator);
            // On créer l'outputTotal et on le sauvegarde
            $total = new AF_Model_Output_Total($this);
            $total->setClassifIndicator($indicator);
            $total->setValue($calcValue->calculate());

            $this->totals->add($total);
        }
    }

    /**
     * @return AF_Model_Output_Total[]
     */
    public function getTotals()
    {
        return $this->totals;
    }

    /**
     * Return the AF_Model_Output_Total for a given Classif_Model_Indicator
     * @param Classif_Model_Indicator $indicator
     * @return AF_Model_Output_Total|null
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
     * @return AF_Model_InputSet_Primary
     */
    public function getInputSet()
    {
        return $this->inputSet;
    }

    /**
     * @param AF_Model_InputSet_Primary $inputSet
     */
    public function setInputSet(AF_Model_InputSet_Primary $inputSet)
    {
        $this->inputSet = $inputSet;
    }

}
