<?php
namespace AF\Domain\Output;

use Calc_Value;
use Classif\Domain\Indicator;
use Core_Model_Entity;

/**
 * @author matthieu.napoli
 * @author cyril.perraud
 */
class OutputTotal extends Core_Model_Entity
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var OutputSet
     */
    protected $outputSet;

    /**
     * @var string
     */
    protected $refIndicator;

    /**
     * @var Calc_Value
     */
    protected $value;

    public function __construct(OutputSet $outputSet)
    {
        $this->outputSet = $outputSet;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Indicator
     */
    public function getClassifIndicator()
    {
        return Indicator::loadByRef($this->refIndicator);
    }

    /**
     * @param \Classif\Domain\Indicator $classifIndicator
     */
    public function setClassifIndicator(Indicator $classifIndicator)
    {
        $this->refIndicator = $classifIndicator->getRef();
    }

    /**
     * Récupère la valeur de l'OutputElement
     * @return Calc_Value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Fixe la valeur de l'OutputElement
     * @param Calc_Value $value
     */
    public function setValue(Calc_Value $value)
    {
        $this->value = $value;
    }

    /**
     * @return OutputSet
     */
    public function getOutputSet()
    {
        return $this->outputSet;
    }

    /**
     * @param OutputSet $outputSet
     */
    public function setOutputSet(OutputSet $outputSet)
    {
        $this->outputSet = $outputSet;
    }
}
