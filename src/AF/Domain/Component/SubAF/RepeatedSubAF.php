<?php

namespace AF\Domain\Component\SubAF;

use AF\Domain\InputSet\InputSet;
use AF\Domain\Component\SubAF;
use AF\Domain\Input\SubAF\RepeatedSubAFInput;

/**
 * Gestion des sous formulaires et des repetitions de sous formulaires.
 *
 * @author matthieu.napoli
 * @author hugo.charbonnier
 * @author thibaud.rolland
 * @author yoann.croizer
 */
class RepeatedSubAF extends SubAF
{
    /**
     * Constante associée à l'attribut minInputNumber.
     * Aucun sous module au départ.
     * @var int
     */
    const MININPUTNUMBER_0 = 0;
    /**
     * Constante associée à l'attribut minInputNumber.
     * Un sous module supprimable au départ.
     * @var int
     */
    const MININPUTNUMBER_1_DELETABLE = 1;
    /**
     * Constante associée à l'attribut minInputNumber.
     * Un sous module non supprimable au départ.
     * @var int
     */
    const MININPUTNUMBER_1_NOT_DELETABLE = 2;

    /**
     * Attribut utilisé pour les sous modules répétés.
     * A pour valeur possible :
     *  - 0 Aucun sous module au départ.
     *  - 1 Un sous module supprimable au départ.
     *  - 2 Un sous module non supprimable au départ.
     * @var integer
     */
    protected $minInputNumber = 0;


    /**
     * {@inheritdoc}
     */
    public function initializeNewInput(InputSet $inputSet)
    {
        $input = $inputSet->getInputForComponent($this);

        if ($input === null) {
            $input = new RepeatedSubAFInput($inputSet, $this);
            $inputSet->setInputForComponent($this, $input);
        }

        switch ($this->minInputNumber) {
            case self::MININPUTNUMBER_0:
                break;
            case self::MININPUTNUMBER_1_DELETABLE:
            case self::MININPUTNUMBER_1_NOT_DELETABLE:
                $subInputSet = $input->addRepeatedSubAf();
                $this->calledAF->initializeNewInput($subInputSet);
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getNbRequiredFields(InputSet $inputSet = null)
    {
        if ($inputSet) {
            /** @var $input RepeatedSubAFInput */
            $input = $inputSet->getInputForComponent($this);
            if ($input) {
                // Si le sous-af est caché
                if ($input->isHidden()) {
                    return 0;
                }
                $subInputSets = $input->getValue();
                $nbRequiredFields = 0;
                foreach ($subInputSets as $subInputSet) {
                    $nbRequiredFields += $this->getCalledAF()->getNbRequiredFields($subInputSet);
                }
                return $nbRequiredFields;
            }
        }
        // Pas de saisie
        if ($this->getMinInputNumber() == self::MININPUTNUMBER_0) {
            return 0;
        }
        return $this->getCalledAF()->getNbRequiredFields();
    }

    /**
     * @return int Nombre minimum d'apparition du formulaire
     */
    public function getMinInputNumber()
    {
        return $this->minInputNumber;
    }

    /**
     * Définit le nombre minimum d'apparition du formulaire
     * A pour valeur possible :
     *  - 0 Aucun sous module au départ.
     *  - 1 Un sous module supprimable au départ.
     *  - 2 Un sous module non supprimable au départ.
     * @param int $minInputNumber
     */
    public function setMinInputNumber($minInputNumber)
    {
        $this->minInputNumber = (int) $minInputNumber;
    }
}
