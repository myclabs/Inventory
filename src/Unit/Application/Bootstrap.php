<?php

namespace Unit\Application;

use Core_Package_Bootstrap;
use Doctrine\DBAL\Types\Type;
use Unit\UnitAPITypeMapping;

/**
 * @author valentin.claras
 */
class Bootstrap extends Core_Package_Bootstrap
{
    /**
     * Initialise le mapping des types en BDD
     */
    protected function _initUnitTypeMapping()
    {
        Type::addType(UnitAPITypeMapping::TYPE_NAME, UnitAPITypeMapping::class);
    }
}
