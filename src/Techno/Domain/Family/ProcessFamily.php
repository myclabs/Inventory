<?php

namespace Techno\Domain\Family;

use Unit\UnitAPI;

/**
 * Famille de processus.
 *
 * @author ronan.gorain
 * @author matthieu.napoli
 */
class ProcessFamily extends Family
{
    /**
     * {@inheritdoc}
     */
    public function getValueUnit()
    {
        // Retourne kg eq. CO2 divisé par l'unité
        $unitInverse = $this->getUnit()->reverse();
        return new UnitAPI($unitInverse->getRef() . '.kg_co2e');
    }
}
