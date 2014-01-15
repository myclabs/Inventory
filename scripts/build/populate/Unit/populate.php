<?php

require_once 'populatePhysicalQuantities.php';
require_once 'populateStandardUnit.php';
require_once 'populateUnitSystem.php';
require_once 'populateDiscreteUnit.php';
require_once 'populateExtendedUnit.php';
require_once 'populateExtension.php';

class Unit_Populate extends Core_Script_Populate
{
    /**
     * Populate a specific environment.
     *
     * @param string $environment
     *
     * @void
     */
    public function populateEnvironment($environment)
    {
        echo PHP_EOL.'\-- Script de création des Units pour '.$environment.' -->'.PHP_EOL;

        $unitSystems = new Unit_Script_Populate_UnitSystem();
        $unitSystems->run();
        echo PHP_EOL."\t\t".' ..UnitSystems created !'.PHP_EOL;

        $quantitieUnits = new Unit_Script_Populate_PhysicalQuantities();
        $quantitieUnits->run();
        echo PHP_EOL."\t\t".' ..PhysicalQuantities created !'.PHP_EOL;

        $standardUnits = new Unit_Script_Populate_StandardUnit();
        $standardUnits->run();
        echo PHP_EOL."\t\t".' ..StandardUnits created !'.PHP_EOL;

        $quantitieUnits = new Unit_Script_Populate_PhysicalQuantities();
        $quantitieUnits->update();
        echo PHP_EOL."\t\t".' ..PhysicalQuantities updated with referenceUnits !'.PHP_EOL;

        $discreteUnits = new Unit_Script_Populate_DiscreteUnit();
        $discreteUnits->run();
        echo PHP_EOL."\t\t".' ..DiscreteUnits created !'.PHP_EOL;

        $extensions = new Unit_Script_Populate_Extension();
        $extensions->run();
        echo PHP_EOL."\t\t".' ..UnitExtensions created !'.PHP_EOL;

        $extendedUnits = new Unit_Script_Populate_extendedUnit();
        $extendedUnits->run();
        echo PHP_EOL."\t\t".' ..ExtendedUnits created !'.PHP_EOL;

        echo PHP_EOL."\t".'--> Script de création des Units --\\'.PHP_EOL;
    }
}
