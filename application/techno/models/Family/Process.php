<?php
/**
 * @author ronan.gorain
 * @author matthieu.napoli
 * @package Techno
 * @subpackage Family
 */

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
        return new Unit_API($unitInverse->getRef().'.kg_co2e');
    }

}
