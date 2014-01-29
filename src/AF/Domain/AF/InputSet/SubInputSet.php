<?php

namespace AF\Domain\AF\InputSet;

/**
 * @author matthieu.napoli
 * @author thibaud.rolland
 * @author hugo.charbonnier
 */
class SubInputSet extends InputSet
{
    /**
     * Libellé d'une répétition de subAF
     * @var string
     */
    protected $freeLabel;

    /**
     * @return int Nombre de champs remplis dans le composant
     */
    public function getNbRequiredFieldsCompleted()
    {
        $nbRequiredFieldsCompleted = 0;
        foreach ($this->inputs as $input) {
            $nbRequiredFieldsCompleted += $input->getNbRequiredFieldsCompleted();
        }
        return $nbRequiredFieldsCompleted;
    }

    /**
     * @param string $freeLabel
     */
    public function setFreeLabel($freeLabel)
    {
        $this->freeLabel = $freeLabel;
    }

    /**
     * @return string
     */
    public function getFreeLabel()
    {
        return $this->freeLabel;
    }
}
