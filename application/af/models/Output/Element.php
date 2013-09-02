<?php
/**
 * @author     matthieu.napoli
 * @author     hugo.charbonnier
 * @author     thibaud.rolland
 * @author     yoann.croizer
 * @package    AF
 * @subpackage Output
 */

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @package    AF
 * @subpackage Output
 */
class AF_Model_Output_Element extends Core_Model_Entity
{

    /**
     * @var int
     */
    protected $id;

    /**
     * @var AF_Model_Output_OutputSet
     */
    protected $outputSet;

    /**
     * Identifiant du set de l'AF auquel est relié l'outputElement.
     * @var AF_Model_InputSet
     */
    protected $inputSet;

    /**
     * @var string
     */
    protected $refContext;

    /**
     * @var string
     */
    protected $refIndicator;

    /**
     * @var AF_Model_Output_Index[]|Collection
     */
    protected $indexes;

    /**
     * @var Algo_Model_Numeric
     */
    protected $algo;

    /**
     * @var Calc_Value
     */
    protected $value;


    /**
     * Constructeur de la classe
     * Prend en paramètre un Algo_Model_Output_Elment et récupère ses attributs
     * @param AF_Model_Output_OutputSet $outputSet
     * @param Algo_Model_Output         $algoOutput
     * @param AF_Model_InputSet         $inputSet
     */
    public function __construct(AF_Model_Output_OutputSet $outputSet, Algo_Model_Output $algoOutput = null,
        AF_Model_InputSet $inputSet = null)
    {
        $this->indexes = new ArrayCollection();
        $this->outputSet = $outputSet;
        $this->inputSet = $inputSet;
        if ($algoOutput) {
            $this->algo = $algoOutput->getAlgo();
            $this->value = $algoOutput->getValue();

            foreach ($algoOutput->getClassifMembers() as $member) {
                $index = new AF_Model_Output_Index($member->getAxis(), $member);
                $this->indexes->add($index);
            }
        } else {
            $this->value = new Calc_Value();
        }
    }

    /**
     * @return Classif_Model_ContextIndicator
     */
    public function getContextIndicator()
    {
        return $this->algo->getContextIndicator();
    }

    /**
     * @return AF_Model_Output_Index[]
     */
    public function getIndexes()
    {
        return $this->indexes;
    }

    /**
     * @param Classif_Model_Axis $axis
     * @return AF_Model_Output_Index
     */
    public function getIndexForAxis(Classif_Model_Axis $axis)
    {
        foreach ($this->indexes as $index) {
            if ($index->getAxis() === $axis) {
                return $index;
            }
        }
        throw new Core_Exception_NotFound("Index not found for axis " . $axis->getRef());
    }

    /**
     * Retourne le libellé de l'algo
     * @return string
     */
    public function getLabel()
    {
        return $this->algo->getLabel();
    }

    /**
     * @return AF_Model_InputSet
     */
    public function getInputSet()
    {
        return $this->inputSet;
    }

    /**
     * @param AF_Model_InputSet $set
     */
    public function setInputSet($set)
    {
        $this->inputSet = $set;
    }

    /**
     * @return Calc_Value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param AF_Model_Output_OutputSet $outputSet
     */
    public function setOutputSet($outputSet)
    {
        $this->outputSet = $outputSet;
    }

}
