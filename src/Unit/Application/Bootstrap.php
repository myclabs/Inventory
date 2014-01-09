<?php
/**
 * @author valentin.claras
 * @package Unit
 */

namespace Unit\Application;

use Core_Package_Bootstrap;
use Doctrine\DBAL\Types\Type;
use Unit\Architecture\TypeMapping\UnitAPIType;

/**
 * Bootstrap
 * @author valentin.claras
 * @package Unit
 */
class Bootstrap extends Core_Package_Bootstrap
{
    /**
     * Initialise le mapping des types en BDD
     */
    protected function _initUnitTypeMapping()
    {
        Type::addType(UnitAPIType::TYPE_NAME, 'Unit\Architecture\TypeMapping\UnitAPIType');
    }
}
