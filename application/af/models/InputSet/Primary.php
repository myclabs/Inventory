<?php
/**
 * @author     matthieu.napoli
 * @author     thibaud.rolland
 * @author     hugo.charbonnier
 * @package    AF
 * @subpackage Input
 */

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @package    AF
 * @subpackage Input
 */
class AF_Model_InputSet_Primary extends AF_Model_InputSet
{

    const QUERY_AF = 'af';

    const STATUS_FINISHED = "finished";
    const STATUS_COMPLETE = "complete";
    const STATUS_CALCULATION_INCOMPLETE = "calculation_incomplete";
    const STATUS_INPUT_INCOMPLETE = "input_incomplete";

    /**
     * Est-ce que les calculs ont été effectués
     * @var bool
     */
    protected $calculationComplete = false;

    /**
     * Est-ce que la saisie est marquée comme terminée
     * @var boolean
     */
    protected $finished = false;

    /**
     * @var AF_Model_Output_OutputSet|null
     */
    protected $outputSet;


    /**
     * Marque la saisie comme terminée (uniquement si la saisie est complète)
     * @param boolean $finished
     */
    public function markAsFinished($finished)
    {
        // On vérifie que la saisie est bien complète
        if ($this->isInputComplete()) {
            $this->finished = (bool) $finished;
        }
    }

    /**
     * @return boolean True si la saisie est marquée comme terminée
     */
    public function isFinished()
    {
        // On vérifie que la saisie est bien complète
        if ($this->isInputComplete()) {
            return $this->finished;
        } else {
            return false;
        }
    }

    /**
     * @return bool True si les calculs de l'AF sont fait
     */
    public function isCalculationComplete()
    {
        return $this->isInputComplete() && $this->calculationComplete;
    }

    /**
     * @param bool $calculationComplete
     */
    public function setCalculationComplete($calculationComplete)
    {
        $this->calculationComplete = $calculationComplete;
    }

    /**
     * Supprime l'output
     */
    public function clearOutputSet()
    {
        $this->setOutputSet(null);
    }

    /**
     * @param AF_Model_Output_OutputSet|null $outputSet
     */
    public function setOutputSet(AF_Model_Output_OutputSet $outputSet = null)
    {
        // Supprime l'ancien outputset (problème avec orphanRemoval)
        // @todo http://www.doctrine-project.org/jira/browse/DDC-1666
        if ($this->outputSet) {
            $this->outputSet->delete();
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }

        $this->outputSet = $outputSet;
    }

    /**
     * @return AF_Model_Output_OutputSet|null
     */
    public function getOutputSet()
    {
        return $this->outputSet;
    }

    /**
     * Retourne le statut de la saisie
     * @return string Constante
     */
    public function getStatus()
    {
        if ($this->isInputComplete()) {
            if ($this->isFinished()) {
                return self::STATUS_FINISHED;
            } elseif ($this->isCalculationComplete()) {
                return self::STATUS_COMPLETE;
            } else {
                return self::STATUS_CALCULATION_INCOMPLETE;
            }
        } else {
            return self::STATUS_INPUT_INCOMPLETE;
        }
    }

}
