<?php
/**
 * @author ronan.gorain
 * @author matthieu.napoli
 * @package Techno
 * @subpackage Family
 */
use Unit\UnitAPI;

/**
 * Classe Process
 * @package Techno
 * @subpackage Family
 */
class Techno_Model_Family_Process extends Techno_Model_Family
{

    /**
     * {@inheritdoc}
     */
    public function getValueUnit()
    {
        // Retourne kg eq. CO2 divisé par l'unité
        $unitInverse = $this->getUnit()->reverse();
        return new UnitAPI($unitInverse->getRef().'.kg_co2e');
    }

}
