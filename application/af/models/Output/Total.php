<?php
/**
 * @author     matthieu.napoli
 * @author     cyril.perraud
 * @package    AF
 * @subpackage Output
 */

/**
 * @package    AF
 * @subpackage Output
 */
class AF_Model_Output_Total extends Core_Model_Entity
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
     * @var string
     */
    protected $refIndicator;

    /**
     * @var Calc_Value
     */
    protected $value;


    /**
     * @param AF_Model_Output_OutputSet $outputSet
     */
    public function __construct(AF_Model_Output_OutputSet $outputSet)
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
     * @return Classif_Model_Indicator
     */
    public function getClassifIndicator()
    {
        return Classif_Model_Indicator::loadByRef($this->refIndicator);
    }

    /**
     * @param Classif_Model_Indicator $classifIndicator
     */
    public function setClassifIndicator(Classif_Model_Indicator $classifIndicator)
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
     * @return AF_Model_Output_OutputSet
     */
    public function getOutputSet()
    {
        return $this->outputSet;
    }

    /**
     * @param AF_Model_Output_OutputSet $outputSet
     */
    public function setOutputSet(AF_Model_Output_OutputSet $outputSet)
    {
        $this->outputSet = $outputSet;
    }

}
